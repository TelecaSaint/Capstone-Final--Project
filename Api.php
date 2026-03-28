<?php
// api.php — AJAX endpoints for MathQuest
require_once 'config.php';

$user = current_user();
if (!$user) { json_out(['error' => 'Unauthorized'], 401); }

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {

        // ── Save a problem attempt and update XP / streak ─────────────
        case 'save_attempt':
            if ($user['role'] !== 'student') json_out(['error' => 'Forbidden'], 403);

            $subject    = $_POST['subject']         ?? '';
            $difficulty = $_POST['difficulty']      ?? 'medium';
            $question   = $_POST['question']        ?? '';
            $userAns    = $_POST['user_answer']     ?? '';
            $correctAns = $_POST['correct_answer']  ?? '';
            $isCorrect  = (int)($_POST['is_correct']  ?? 0);
            $xpEarned   = (int)($_POST['xp_earned']   ?? 0);
            $hintUsed   = (int)($_POST['hint_used']   ?? 0);
            $timeTaken  = (int)($_POST['time_taken']  ?? 0);

            // Insert attempt
            db()->prepare('INSERT INTO attempts
                (user_id,subject,difficulty,question,user_answer,correct_answer,is_correct,xp_earned,hint_used,time_taken)
                VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$user['id'],$subject,$difficulty,$question,$userAns,$correctAns,$isCorrect,$xpEarned,$hintUsed,$timeTaken]);

            // Update stats
            $stats = db()->prepare('SELECT * FROM student_stats WHERE user_id=?');
            $stats->execute([$user['id']]);
            $s = $stats->fetch();

            if ($s) {
                $newXP      = $s['xp'] + $xpEarned;
                $newSolved  = $s['problems_solved']  + 1;
                $newCorrect = $s['problems_correct'] + $isCorrect;
                $level      = max(1, (int)floor($newXP / 500) + 1);

                // Streak: if last_active was yesterday, increment; if today already, keep; else reset
                $today     = date('Y-m-d');
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                $streak    = $s['streak'];
                if ($s['last_active'] === $yesterday) $streak++;
                elseif ($s['last_active'] !== $today)  $streak = 1;

                $longest = max($s['longest_streak'], $streak);

                db()->prepare('UPDATE student_stats
                    SET xp=?,level=?,streak=?,longest_streak=?,problems_solved=?,problems_correct=?,last_active=?
                    WHERE user_id=?')
                    ->execute([$newXP,$level,$streak,$longest,$newSolved,$newCorrect,$today,$user['id']]);

                // Refresh session
                $_SESSION['user']['xp']              = $newXP;
                $_SESSION['user']['level']           = $level;
                $_SESSION['user']['streak']          = $streak;
                $_SESSION['user']['problems_solved'] = $newSolved;

                json_out(['ok'=>true,'xp'=>$newXP,'level'=>$level,'streak'=>$streak]);
            }
            json_out(['error'=>'Stats not found'],404);

        // ── Get current student stats ─────────────────────────────────
        case 'get_stats':
            if ($user['role'] !== 'student') json_out(['error'=>'Forbidden'],403);
            $stmt = db()->prepare('SELECT * FROM student_stats WHERE user_id=?');
            $stmt->execute([$user['id']]);
            $s = $stmt->fetch();
            if (!$s) json_out(['error'=>'No stats'],404);
            $acc = $s['problems_solved'] > 0
                 ? round(($s['problems_correct'] / $s['problems_solved']) * 100)
                 : 0;
            json_out(['ok'=>true,'xp'=>$s['xp'],'level'=>$s['level'],'streak'=>$s['streak'],
                      'problems_solved'=>$s['problems_solved'],'accuracy'=>$acc]);

        // ── Leaderboard ───────────────────────────────────────────────
        case 'leaderboard':
            $stmt = db()->query(
                'SELECT u.full_name, u.avatar, s.xp, s.level
                 FROM users u JOIN student_stats s ON s.user_id=u.id
                 WHERE u.role="student"
                 ORDER BY s.xp DESC LIMIT 10'
            );
            json_out(['ok'=>true,'board'=>$stmt->fetchAll()]);

        // ── Admin: all students ───────────────────────────────────────
        case 'admin_students':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $stmt = db()->query(
                'SELECT u.id,u.full_name,u.username,u.avatar,u.class_name,u.last_login,
                        s.xp,s.level,s.streak,s.problems_solved,s.problems_correct,s.last_active
                 FROM users u LEFT JOIN student_stats s ON s.user_id=u.id
                 WHERE u.role="student" ORDER BY s.xp DESC'
            );
            json_out(['ok'=>true,'students'=>$stmt->fetchAll()]);

        // ── Admin: add student ────────────────────────────────────────
        case 'add_student':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $un   = trim($_POST['username']  ?? '');
            $name = trim($_POST['full_name'] ?? '');
            $pw   = $_POST['password']       ?? '';
            $cls  = $_POST['class_name']     ?? 'Grade 6A';
            if (!$un || !$name || !$pw) json_out(['error'=>'Missing fields'],400);

            $hash = password_hash($pw, PASSWORD_BCRYPT);
            db()->prepare('INSERT INTO users (username,password,full_name,role,class_name) VALUES (?,?,?,"student",?)')
                ->execute([$un,$hash,$name,$cls]);
            $newId = (int)db()->lastInsertId();
            db()->prepare('INSERT INTO student_stats (user_id) VALUES (?)')->execute([$newId]);
            json_out(['ok'=>true,'id'=>$newId]);

        // ── Admin: remove student ─────────────────────────────────────
        case 'remove_student':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) json_out(['error'=>'No ID'],400);
            db()->prepare('DELETE FROM users WHERE id=? AND role="student"')->execute([$id]);
            json_out(['ok'=>true]);

        // ── Admin: assignments ────────────────────────────────────────
        case 'get_assignments':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $rows = db()->query(
                'SELECT a.*, COUNT(s.id) AS submitted,
                        AVG(s.score) AS avg_score
                 FROM assignments a
                 LEFT JOIN submissions s ON s.assignment_id=a.id
                 GROUP BY a.id ORDER BY a.created_at DESC'
            )->fetchAll();
            json_out(['ok'=>true,'assignments'=>$rows]);

        case 'add_assignment':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $title = trim($_POST['title']      ?? '');
            $subj  = $_POST['subject']         ?? 'Algebra';
            $diff  = $_POST['difficulty']      ?? 'medium';
            $num   = (int)($_POST['num_problems'] ?? 10);
            $due   = $_POST['due_date']        ?? null;
            if (!$title) json_out(['error'=>'Missing title'],400);
            db()->prepare('INSERT INTO assignments (title,subject,difficulty,num_problems,due_date,created_by) VALUES (?,?,?,?,?,?)')
                ->execute([$title,$subj,$diff,$num,$due,$user['id']]);
            json_out(['ok'=>true,'id'=>db()->lastInsertId()]);

        case 'delete_assignment':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $id = (int)($_POST['id'] ?? 0);
            db()->prepare('DELETE FROM assignments WHERE id=?')->execute([$id]);
            json_out(['ok'=>true]);

        // ── Admin: course content ─────────────────────────────────────
        case 'get_content':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $rows = db()->query('SELECT * FROM content ORDER BY subject, created_at')->fetchAll();
            json_out(['ok'=>true,'content'=>$rows]);

        case 'add_content':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $subj  = $_POST['subject']     ?? '';
            $title = trim($_POST['title']  ?? '');
            $type  = $_POST['type']        ?? 'Lesson';
            $icons = ['Lesson'=>'📖','Practice Set'=>'🧮','Quiz'=>'📝','Video'=>'🎬'];
            $icon  = $icons[$type] ?? '📄';
            if (!$title||!$subj) json_out(['error'=>'Missing fields'],400);
            db()->prepare('INSERT INTO content (subject,title,type,icon,created_by) VALUES (?,?,?,?,?)')
                ->execute([$subj,$title,$type,$icon,$user['id']]);
            json_out(['ok'=>true]);

        case 'delete_content':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $id = (int)($_POST['id'] ?? 0);
            db()->prepare('DELETE FROM content WHERE id=?')->execute([$id]);
            json_out(['ok'=>true]);

        // ── Admin: grade book ─────────────────────────────────────────
        case 'gradebook':
            if ($user['role'] !== 'admin') json_out(['error'=>'Forbidden'],403);
            $stmt = db()->query(
                'SELECT u.full_name,
                        SUM(CASE WHEN a.subject="Algebra"    THEN a.is_correct END) as alg_c,
                        COUNT(CASE WHEN a.subject="Algebra"  THEN 1 END)            as alg_t,
                        SUM(CASE WHEN a.subject="Arithmetic" THEN a.is_correct END) as arith_c,
                        COUNT(CASE WHEN a.subject="Arithmetic" THEN 1 END)          as arith_t,
                        SUM(CASE WHEN a.subject="Geometry"   THEN a.is_correct END) as geo_c,
                        COUNT(CASE WHEN a.subject="Geometry"  THEN 1 END)           as geo_t,
                        SUM(CASE WHEN a.subject="Fractions"  THEN a.is_correct END) as frac_c,
                        COUNT(CASE WHEN a.subject="Fractions" THEN 1 END)           as frac_t,
                        SUM(CASE WHEN a.subject="Statistics" THEN a.is_correct END) as stat_c,
                        COUNT(CASE WHEN a.subject="Statistics" THEN 1 END)          as stat_t,
                        SUM(a.is_correct) as total_c, COUNT(a.id) as total_t
                 FROM users u
                 LEFT JOIN attempts a ON a.user_id=u.id
                 WHERE u.role="student"
                 GROUP BY u.id ORDER BY total_c DESC'
            );
            json_out(['ok'=>true,'grades'=>$stmt->fetchAll()]);

        default:
            json_out(['error'=>'Unknown action'],400);
    }
} catch (PDOException $e) {
    json_out(['error'=>'DB error: '.$e->getMessage()],500);
}