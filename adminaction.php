<?php
require_once 'config.php';
require_login();

$user = current_user();
if ($user['role'] !== 'admin') {
    redirect('dashboard.php');
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function json_error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

function json_ok($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

// ─── ROUTER ───────────────────────────────────────────────────────────────────
switch ($action) {

    // ── Create Assignment ────────────────────────────────────────────────────
    case 'create_assignment':
        $title       = trim($_POST['title'] ?? '');
        $topic       = trim($_POST['topic'] ?? '');
        $difficulty  = $_POST['difficulty'] ?? 'medium';
        $due_date    = $_POST['due_date'] ?? null;
        $xp_reward   = (int)($_POST['xp_reward'] ?? 100);
        $problem_ids = $_POST['problem_ids'] ?? ''; // comma-separated or JSON

        if (!$title)      json_error('Title is required.');
        if (!$topic)      json_error('Topic is required.');
        if (!in_array($difficulty, ['easy','medium','hard'])) json_error('Invalid difficulty.');
        if ($xp_reward < 1 || $xp_reward > 10000)            json_error('XP reward must be 1–10000.');

        // Normalise due_date — allow null/empty
        $due_date = ($due_date && $due_date !== '') ? $due_date : null;

        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO assignments
                (title, topic, difficulty, due_date, xp_reward, created_by, created_at)
            VALUES
                (:title, :topic, :difficulty, :due_date, :xp_reward, :created_by, NOW())
        ");
        $stmt->execute([
            ':title'      => $title,
            ':topic'      => $topic,
            ':difficulty' => $difficulty,
            ':due_date'   => $due_date,
            ':xp_reward'  => $xp_reward,
            ':created_by' => $user['id'],
        ]);
        $assignment_id = $pdo->lastInsertId();
        json_ok(['assignment_id' => (int)$assignment_id, 'message' => 'Assignment created successfully.']);

    // ── Delete Assignment ────────────────────────────────────────────────────
    case 'delete_assignment':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_error('Missing assignment id.');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) json_error('Assignment not found.', 404);
        json_ok(['message' => 'Assignment deleted.']);

    // ── Update Assignment ────────────────────────────────────────────────────
    case 'update_assignment':
        $id          = (int)($_POST['id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $topic       = trim($_POST['topic'] ?? '');
        $difficulty  = $_POST['difficulty'] ?? 'medium';
        $due_date    = $_POST['due_date'] ?? null;
        $xp_reward   = (int)($_POST['xp_reward'] ?? 100);

        if (!$id)    json_error('Missing assignment id.');
        if (!$title) json_error('Title is required.');
        if (!$topic) json_error('Topic is required.');
        if (!in_array($difficulty, ['easy','medium','hard'])) json_error('Invalid difficulty.');

        $due_date = ($due_date && $due_date !== '') ? $due_date : null;

        $pdo = db();
        $stmt = $pdo->prepare("
            UPDATE assignments
            SET title = :title, topic = :topic, difficulty = :difficulty,
                due_date = :due_date, xp_reward = :xp_reward, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':title'      => $title,
            ':topic'      => $topic,
            ':difficulty' => $difficulty,
            ':due_date'   => $due_date,
            ':xp_reward'  => $xp_reward,
            ':id'         => $id,
        ]);
        json_ok(['message' => 'Assignment updated.']);

    // ── Create / Update User ─────────────────────────────────────────────────
    case 'create_user':
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'student';
        $grade    = (int)($_POST['grade'] ?? 0);

        if (!$username) json_error('Username is required.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Invalid email.');
        if (strlen($password) < 6) json_error('Password must be at least 6 characters.');
        if (!in_array($role, ['admin','student'])) json_error('Invalid role.');

        $pdo = db();

        // Check duplicate
        $dup = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
        $dup->execute([':u' => $username, ':e' => $email]);
        if ($dup->fetch()) json_error('Username or email already exists.', 409);

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, grade, created_at)
            VALUES (:username, :email, :hash, :role, :grade, NOW())
        ");
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':hash'     => $hash,
            ':role'     => $role,
            ':grade'    => $grade,
        ]);
        $uid = $pdo->lastInsertId();

        // Seed student_stats row
        if ($role === 'student') {
            $pdo->prepare("INSERT INTO student_stats (user_id, xp, streak, level) VALUES (:uid, 0, 0, 1)")
                ->execute([':uid' => $uid]);
        }

        json_ok(['user_id' => (int)$uid, 'message' => 'User created.']);

    // ── Delete User ──────────────────────────────────────────────────────────
    case 'delete_user':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_error('Missing user id.');
        if ($id === (int)$user['id']) json_error('Cannot delete yourself.');

        $pdo = db();
        $pdo->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $id]);
        json_ok(['message' => 'User deleted.']);

    // ── Reset Password ───────────────────────────────────────────────────────
    case 'reset_password':
        $id          = (int)($_POST['id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';
        if (!$id) json_error('Missing user id.');
        if (strlen($new_password) < 6) json_error('Password must be at least 6 characters.');

        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        $pdo  = db();
        $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id")
            ->execute([':hash' => $hash, ':id' => $id]);
        json_ok(['message' => 'Password reset successfully.']);

    // ── Award Badge ──────────────────────────────────────────────────────────
    case 'award_badge':
        $user_id  = (int)($_POST['user_id'] ?? 0);
        $badge_id = (int)($_POST['badge_id'] ?? 0);
        if (!$user_id || !$badge_id) json_error('Missing user_id or badge_id.');

        $pdo = db();
        // Prevent duplicate
        $dup = $pdo->prepare("SELECT id FROM user_badges WHERE user_id = :u AND badge_id = :b");
        $dup->execute([':u' => $user_id, ':b' => $badge_id]);
        if ($dup->fetch()) json_error('User already has this badge.', 409);

        $pdo->prepare("INSERT INTO user_badges (user_id, badge_id, awarded_at) VALUES (:u, :b, NOW())")
            ->execute([':u' => $user_id, ':b' => $badge_id]);
        json_ok(['message' => 'Badge awarded.']);

    // ── Revoke Badge ─────────────────────────────────────────────────────────
    case 'revoke_badge':
        $user_id  = (int)($_POST['user_id'] ?? 0);
        $badge_id = (int)($_POST['badge_id'] ?? 0);
        if (!$user_id || !$badge_id) json_error('Missing user_id or badge_id.');

        $pdo = db();
        $pdo->prepare("DELETE FROM user_badges WHERE user_id = :u AND badge_id = :b")
            ->execute([':u' => $user_id, ':b' => $badge_id]);
        json_ok(['message' => 'Badge revoked.']);

    // ── Get Stats (dashboard summary) ────────────────────────────────────────
    case 'get_stats':
        $pdo = db();
        $stats = [];

        $stats['total_users']       = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
        $stats['total_assignments'] = (int)$pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn();
        $stats['total_attempts']    = (int)$pdo->query("SELECT COUNT(*) FROM attempts")->fetchColumn();
        $stats['correct_attempts']  = (int)$pdo->query("SELECT COUNT(*) FROM attempts WHERE is_correct=1")->fetchColumn();
        $stats['accuracy']          = $stats['total_attempts']
            ? round($stats['correct_attempts'] / $stats['total_attempts'] * 100, 1)
            : 0;

        json_ok(['stats' => $stats]);

    default:
        json_error("Unknown action: '$action'", 400);
}