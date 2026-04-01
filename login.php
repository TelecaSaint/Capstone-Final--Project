<?php
require_once 'config.php';

// Redirect if already logged in
if (current_user()) {
    redirect(current_user()['role'] === 'admin' ? 'admin.php' : 'dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'student';

    if ($username === '' || $password === '') {
        $error = 'Please fill in both fields.';
    } else {
        try {
            // Uses the db() function from config.php
            $stmt = db()->prepare("
                SELECT id, username, password, full_name, role, avatar 
                FROM users 
                WHERE username = :username AND role = :role 
                LIMIT 1
            ");
            $stmt->execute([':username' => $username, ':role' => $role]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                
                $_SESSION['user'] = [
                    'id'        => $user['id'],
                    'username'  => $user['username'],
                    'full_name' => $user['full_name'],
                    'role'      => $user['role'],
                    'avatar'    => $user['avatar']
                ];

                $upd = db()->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $upd->execute([':id' => $user['id']]);

                redirect($user['role'] === 'admin' ? 'admin.php' : 'dashboard.php');
            } else {
                $error = 'Invalid username or password for the selected role.';
            }
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = 'A server error occurred. Please try again.';
        }
    }
}

$selected_role = $_POST['role'] ?? 'student';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>MathQuest — Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#080b14; --s1:#0e1220; --s2:#141827; --b:rgba(255,255,255,0.07); --cyan:#00e5ff; --text:#e8eaf2; --tdim:rgba(232,234,242,0.42); }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: var(--s1); border: 1px solid var(--b); padding: 35px; border-radius: 20px; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        .logo { font-family: 'Syne'; font-weight: 800; font-size: 1.6rem; color: var(--cyan); text-align: center; margin-bottom: 25px; display: block; text-decoration: none; }
        .role-tabs { display: grid; grid-template-columns: 1fr 1fr; margin-bottom: 20px; border: 1px solid var(--b); border-radius: 10px; overflow: hidden; background: var(--s2); }
        .role-tab { padding: 12px; background: transparent; color: var(--tdim); border: none; cursor: pointer; font-family: 'Syne'; font-size: 0.75rem; font-weight: 700; transition: 0.2s; }
        .role-tab.active { background: rgba(0,229,255,0.1); color: var(--cyan); }
        .field { margin-bottom: 15px; }
        .field label { display: block; font-size: 0.7rem; color: var(--tdim); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 1px; }
        .field input { width: 100%; padding: 12px; background: var(--s2); border: 1px solid var(--b); border-radius: 8px; color: #fff; outline: none; box-sizing: border-box; }
        .field input:focus { border-color: var(--cyan); }
        .submit-btn { width: 100%; padding: 14px; background: var(--cyan); border: none; border-radius: 8px; font-family: 'Syne'; font-weight: 800; cursor: pointer; color: #080b14; margin-top: 10px; }
        .err { background: rgba(255,82,82,0.1); border: 1px solid rgba(255,82,82,0.2); color: #ff8a80; padding: 10px; border-radius: 8px; font-size: 0.8rem; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

<div class="card">
    <a href="index.php" class="logo">MATHQUEST</a>

    <div class="role-tabs">
        <button type="button" class="role-tab <?= $selected_role === 'student' ? 'active' : '' ?>" onclick="setRole('student')">🎮 STUDENT</button>
        <button type="button" class="role-tab <?= $selected_role === 'admin' ? 'active' : '' ?>" onclick="setRole('admin')">🛡 ADMIN</button>
    </div>

    <?php if ($error): ?>
        <div class="err"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($selected_role) ?>">
        
        <div class="field">
            <label>Username</label>
            <input name="username" type="text" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>

        <div class="field">
            <label>Password</label>
            <input name="password" type="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
            <?= $selected_role === 'admin' ? 'ADMIN ACCESS →' : 'START ADVENTURE →' ?>
        </button>
    </form>
</div>

<script>
function setRole(r) {
    document.getElementById('roleInput').value = r;
    document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
    event.currentTarget.classList.add('active');
    document.getElementById('submitBtn').textContent = r === 'admin' ? 'ADMIN ACCESS →' : 'START ADVENTURE →';
}
</script>

</body>
</html>