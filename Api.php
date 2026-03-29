<?php
require_once 'config.php';
require_login();

header('Content-Type: application/json');

$user    = current_user();
$user_id = (int)$user['id'];
$method  = $_SERVER['REQUEST_METHOD'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

function json_error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}
function json_ok($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

// XP thresholds per level (level N needs sum of all previous + this many XP)
function xp_for_level(int $level): int {
    // Quadratic curve: level 1=0, 2=100, 3=250, 4=450 …
    return (int)(100 * ($level - 1) + 50 * ($level - 1) * ($level - 2));
}

function compute_level(int $xp): int {
    $level = 1;
    while (xp_for_level($level + 1) <= $xp) $level++;
    return min($level, 50); // cap at 50
}

// ─── ROUTER ───────────────────────────────────────────────────────────────────
switch ($action) {

    // ── Submit Attempt ───────────────────────────────────────────────────────
    // POST: topic, difficulty, is_correct, time_taken, hint_used, assignment_id?
    case 'submit_attempt':
        if ($method !== 'POST') json_error('POST required.');

        $topic         = trim($_POST['topic'] ?? '');
        $difficulty    = $_POST['difficulty'] ?? 'medium';
        $is_correct    = (int)(bool)($_POST['is_correct'] ?? 0);
        $time_taken    = (int)($_POST['time_taken'] ?? 0);   // seconds
        $hint_used     = (int)(bool)($_POST['hint_used'] ?? 0);
        $assignment_id = $_POST['assignment_id'] ? (int)$_POST['assignment_id'] : null;
        $problem_text  = trim($_POST['problem_text'] ?? '');
        $user_answer   = trim($_POST['user_answer'] ?? '');

        if (!$topic)                                              json_error('Topic is required.');
        if (!in_array($difficulty, ['easy','medium','hard']))     json_error('Invalid difficulty.');

        $pdo = db();

        // 1. Insert attempt
        $stmt = $pdo->prepare("
            INSERT INTO attempts
                (user_id, assignment_id, topic, difficulty, is_correct,
                 time_taken, hint_used, problem_text, user_answer, attempted_at)
            VALUES
                (:uid, :aid, :topic, :diff, :correct,
                 :time, :hint, :prob, :ans, NOW())
        ");
        $stmt->execute([
            ':uid'     => $user_id,
            ':aid'     => $assignment_id,
            ':topic'   => $topic,
            ':diff'    => $difficulty,
            ':correct' => $is_correct,
            ':time'    => $time_taken,
            ':hint'    => $hint_used,
            ':prob'    => $problem_text,
            ':ans'     => $user_answer,
        ]);
        $attempt_id = (int)$pdo->lastInsertId();

        // 2. Fetch / ensure student_stats row
        $stats = $pdo->prepare("SELECT * FROM student_stats WHERE user_id = :uid");
        $stats->execute([':uid' => $user_id]);
        $row = $stats->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Create row if missing (shouldn't happen but defensive)
            $pdo->prepare("INSERT INTO student_stats (user_id, xp, streak, level, last_active) VALUES (:uid,0,0,1,NOW())")
                ->execute([':uid' => $user_id]);
            $row = ['xp' => 0, 'streak' => 0, 'level' => 1, 'last_active' => null];
        }

        // 3. Calculate XP gain
        $xp_gain = 0;
        if ($is_correct) {
            $base = ['easy' => 20, 'medium' => 40, 'hard' => 70][$difficulty];
            // Speed bonus: full bonus if under 30 s, scales down to 60 s
            $speed_bonus = 0;
            if ($time_taken <= 30)       $speed_bonus = (int)($base * 0.5);
            elseif ($time_taken <= 60)   $speed_bonus = (int)($base * 0.25);
            // Hint penalty
            $hint_penalty = $hint_used ? (int)($base * 0.2) : 0;
            $xp_gain = max(0, $base + $speed_bonus - $hint_penalty);
        }

        // 4. Streak logic
        $today      = date('Y-m-d');
        $last_active = $row['last_active'] ? date('Y-m-d', strtotime($row['last_active'])) : null;
        $new_streak  = (int)$row['streak'];

        if ($is_correct) {
            if ($last_active === null || $last_active < date('Y-m-d', strtotime('-1 day'))) {
                // No activity yesterday → reset streak
                $new_streak = 1;
            } elseif ($last_active === date('Y-m-d', strtotime('-1 day'))) {
                // Active yesterday → extend
                $new_streak++;
            }
            // If last_active is today, streak stays the same
        }

        // 5. Streak XP multiplier
        $streak_multiplier = 1.0;
        if ($new_streak >= 7)       $streak_multiplier = 1.5;
        elseif ($new_streak >= 3)   $streak_multiplier = 1.25;
        $xp_gain = (int)($xp_gain * $streak_multiplier);

        // 6. Update stats
        $new_xp    = (int)$row['xp'] + $xp_gain;
        $old_level = (int)$row['level'];
        $new_level = compute_level($new_xp);
        $leveled_up = $new_level > $old_level;

        $pdo->prepare("
            UPDATE student_stats
            SET xp = :xp, streak = :streak, level = :level,
                last_active = NOW()
            WHERE user_id = :uid
        ")->execute([
            ':xp'     => $new_xp,
            ':streak' => $new_streak,
            ':level'  => $new_level,
            ':uid'    => $user_id,
        ]);

        // 7. Auto-award badges
        $new_badges = [];
        $badge_check = [
            ['condition' => $new_xp >= 500,   'name' => 'Rising Star'],
            ['condition' => $new_xp >= 2000,  'name' => 'Math Warrior'],
            ['condition' => $new_xp >= 10000, 'name' => 'Legend'],
            ['condition' => $new_streak >= 3,  'name' => '3-Day Streak'],
            ['condition' => $new_streak >= 7,  'name' => 'Week Warrior'],
            ['condition' => $difficulty === 'hard' && $is_correct, 'name' => 'Hard Mode'],
        ];

        foreach ($badge_check as $bc) {
            if (!$bc['condition']) continue;
            // Look up badge
            $brow = $pdo->prepare("SELECT id FROM badges WHERE name = :n");
            $brow->execute([':n' => $bc['name']]);
            $badge = $brow->fetch(PDO::FETCH_ASSOC);
            if (!$badge) continue;

            // Check if already awarded
            $exists = $pdo->prepare("SELECT id FROM user_badges WHERE user_id=:u AND badge_id=:b");
            $exists->execute([':u' => $user_id, ':b' => $badge['id']]);
            if ($exists->fetch()) continue;

            $pdo->prepare("INSERT INTO user_badges (user_id, badge_id, awarded_at) VALUES (:u,:b,NOW())")
                ->execute([':u' => $user_id, ':b' => $badge['id']]);
            $new_badges[] = $bc['name'];
        }

        json_ok([
            'attempt_id'        => $attempt_id,
            'xp_gained'         => $xp_gain,
            'xp_total'          => $new_xp,
            'streak'            => $new_streak,
            'level'             => $new_level,
            'leveled_up'        => $leveled_up,
            'streak_multiplier' => $streak_multiplier,
            'new_badges'        => $new_badges,
            'xp_next_level'     => xp_for_level($new_level + 1),
        ]);

    // ── Get Student Stats ────────────────────────────────────────────────────
    case 'get_stats':
        $target_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $user_id;
        // Non-admins can only see own stats
        if ($target_id !== $user_id && $user['role'] !== 'admin') {
            json_error('Unauthorized.', 403);
        }

        $pdo = db();
        $row = $pdo->prepare("SELECT * FROM student_stats WHERE user_id = :uid");
        $row->execute([':uid' => $target_id]);
        $s = $row->fetch(PDO::FETCH_ASSOC);

        if (!$s) json_error('Stats not found.', 404);

        // Total & correct attempts
        $att = $pdo->prepare("SELECT COUNT(*) as total, SUM(is_correct) as correct FROM attempts WHERE user_id=:uid");
        $att->execute([':uid' => $target_id]);
        $a = $att->fetch(PDO::FETCH_ASSOC);

        // Weak topics (most wrong)
        $weak = $pdo->prepare("
            SELECT topic, COUNT(*) as cnt
            FROM attempts
            WHERE user_id=:uid AND is_correct=0
            GROUP BY topic ORDER BY cnt DESC LIMIT 3
        ");
        $weak->execute([':uid' => $target_id]);
        $weak_topics = $weak->fetchAll(PDO::FETCH_COLUMN);

        json_ok([
            'xp'           => (int)$s['xp'],
            'streak'       => (int)$s['streak'],
            'level'        => (int)$s['level'],
            'xp_next_level'=> xp_for_level((int)$s['level'] + 1),
            'total_attempts'  => (int)$a['total'],
            'correct_attempts'=> (int)$a['correct'],
            'accuracy'        => $a['total'] ? round($a['correct'] / $a['total'] * 100, 1) : 0,
            'weak_topics'     => $weak_topics,
        ]);

    // ── Get Recent Attempts ──────────────────────────────────────────────────
    case 'get_attempts':
        $target_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $user_id;
        if ($target_id !== $user_id && $user['role'] !== 'admin') json_error('Unauthorized.', 403);

        $limit  = min((int)($_GET['limit'] ?? 20), 100);
        $offset = (int)($_GET['offset'] ?? 0);

        $pdo  = db();
        $stmt = $pdo->prepare("
            SELECT id, topic, difficulty, is_correct, time_taken,
                   hint_used, attempted_at
            FROM attempts
            WHERE user_id = :uid
            ORDER BY attempted_at DESC
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':uid', $target_id, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,     PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,    PDO::PARAM_INT);
        $stmt->execute();
        $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_ok(['attempts' => $attempts]);

    // ── Leaderboard ──────────────────────────────────────────────────────────
    case 'leaderboard':
        $limit = min((int)($_GET['limit'] ?? 10), 50);
        $pdo   = db();
        $rows  = $pdo->prepare("
            SELECT u.username, u.grade, s.xp, s.streak, s.level,
                   RANK() OVER (ORDER BY s.xp DESC) as rank_pos
            FROM student_stats s
            JOIN users u ON u.id = s.user_id
            WHERE u.role = 'student'
            ORDER BY s.xp DESC
            LIMIT :lim
        ");
        $rows->bindValue(':lim', $limit, PDO::PARAM_INT);
        $rows->execute();
        json_ok(['leaderboard' => $rows->fetchAll(PDO::FETCH_ASSOC)]);

    // ── Get Notifications ────────────────────────────────────────────────────
    case 'get_notifications':
        $pdo = db();
        // Missed assignments (past due, no correct attempt)
        $missed = $pdo->prepare("
            SELECT a.id, a.title, a.topic, a.difficulty, a.due_date, a.xp_reward
            FROM assignments a
            WHERE a.due_date < NOW()
              AND NOT EXISTS (
                  SELECT 1 FROM attempts att
                  WHERE att.assignment_id = a.id
                    AND att.user_id = :uid
                    AND att.is_correct = 1
              )
            ORDER BY a.due_date DESC
            LIMIT 10
        ");
        $missed->execute([':uid' => $user_id]);

        json_ok(['missed_assignments' => $missed->fetchAll(PDO::FETCH_ASSOC)]);

    default:
        json_error("Unknown action: '$action'");
}