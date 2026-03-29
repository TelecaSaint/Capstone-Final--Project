<?php
require_once 'config.php';

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
            $stmt = db()->prepare("
                SELECT id, username, password, full_name, role, avatar
                FROM users
                WHERE username = :username
                  AND role     = :role
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
                    'avatar'    => $user['avatar'],
                ];
                $upd = db()->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $upd->execute([':id' => $user['id']]);
                redirect($user['role'] === 'admin' ? 'admin.php' : 'dashboard.php');
            } else {
                $error = 'Invalid username or password.';
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
/* ── Dark mode (default) ───────────────────────────────────── */
:root, [data-theme="dark"] {
  --bg:    #080b14;
  --s1:    #0e1220;
  --s2:    #141827;
  --b:     rgba(255,255,255,0.07);
  --bb:    rgba(255,255,255,0.13);
  --cyan:  #00e5ff;
  --cdim:  rgba(0,229,255,0.12);
  --violet:#7c3aed;
  --vdim:  rgba(124,58,237,0.18);
  --text:  #e8eaf2;
  --tdim:  rgba(232,234,242,0.42);
  --tmid:  rgba(232,234,242,0.68);
  --green: #00e676;
  --orb1:  rgba(0,229,255,0.055);
  --orb2:  rgba(124,58,237,0.07);
  --grid:  rgba(0,229,255,0.025);
  --scan-color: var(--cyan);
  --corner-color: rgba(0,229,255,0.18);
  --shadow: 0 24px 60px rgba(0,0,0,0.5);
}

/* ── Light mode ────────────────────────────────────────────── */
[data-theme="light"] {
  --bg:    #f0f4ff;
  --s1:    #ffffff;
  --s2:    #e8edf8;
  --b:     rgba(0,0,0,0.08);
  --bb:    rgba(0,0,0,0.15);
  --cyan:  #0077cc;
  --cdim:  rgba(0,119,204,0.10);
  --violet:#6d28d9;
  --vdim:  rgba(109,40,217,0.10);
  --text:  #0f1423;
  --tdim:  rgba(15,20,35,0.45);
  --tmid:  rgba(15,20,35,0.72);
  --green: #00a854;
  --orb1:  rgba(0,119,204,0.08);
  --orb2:  rgba(109,40,217,0.07);
  --grid:  rgba(0,119,204,0.04);
  --scan-color: var(--cyan);
  --corner-color: rgba(0,119,204,0.25);
  --shadow: 0 24px 60px rgba(0,0,0,0.10);
}

*{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;transition:background 0.3s,color 0.3s}
.grid-bg{position:fixed;inset:0;z-index:0;background-image:linear-gradient(var(--grid) 1px,transparent 1px),linear-gradient(90deg,var(--grid) 1px,transparent 1px);background-size:64px 64px;mask-image:radial-gradient(ellipse at center,black 30%,transparent 75%)}
.orb{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0;animation:orbDrift 14s ease-in-out infinite}
.o1{width:450px;height:450px;background:var(--orb1);top:-120px;left:-120px}
.o2{width:350px;height:350px;background:var(--orb2);bottom:-100px;right:-100px;animation-delay:-7s}
@keyframes orbDrift{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(25px,18px) scale(1.06)}}
.scan{position:fixed;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--scan-color),transparent);opacity:0.25;animation:scan 8s linear infinite;z-index:1;pointer-events:none}
@keyframes scan{0%{top:-1px}100%{top:100vh}}
.corner{position:fixed;width:36px;height:36px;border-color:var(--corner-color);border-style:solid;z-index:1;pointer-events:none}
.ctl{top:18px;left:18px;border-width:1px 0 0 1px}.ctr{top:18px;right:18px;border-width:1px 1px 0 0}
.cbl{bottom:18px;left:18px;border-width:0 0 1px 1px}.cbr{bottom:18px;right:18px;border-width:0 1px 1px 0}

/* ── Theme toggle button ───────────────────────────────────── */
.theme-toggle{position:fixed;top:18px;right:64px;z-index:100;background:var(--s1);border:1px solid var(--b);border-radius:20px;padding:6px 14px;cursor:pointer;font-family:'Syne',sans-serif;font-size:0.72em;font-weight:600;color:var(--tmid);display:flex;align-items:center;gap:6px;transition:all 0.22s;letter-spacing:0.05em}
.theme-toggle:hover{border-color:var(--bb);color:var(--text)}
.theme-icon{font-size:1em}

.card{position:relative;z-index:10;width:100%;max-width:420px;padding:20px;animation:cardIn 0.7s cubic-bezier(0.16,1,0.3,1) both}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(0.97)}to{opacity:1;transform:none}}
.logo-wrap{text-align:center;margin-bottom:32px}
.logo-icon{display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;background:linear-gradient(135deg,var(--cdim),var(--vdim));border:1px solid var(--corner-color);border-radius:14px;font-size:1.7em;margin-bottom:12px;box-shadow:0 0 28px var(--cdim)}
.logo-name{font-family:'Syne',sans-serif;font-weight:800;font-size:1.55em;letter-spacing:0.04em;background:linear-gradient(135deg,var(--cyan) 0%,#a78bfa 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.logo-tag{color:var(--tdim);font-size:0.82em;margin-top:4px}
.role-tabs{display:grid;grid-template-columns:1fr 1fr;border:1px solid var(--b);border-radius:10px;overflow:hidden;margin-bottom:22px;background:var(--s1)}
.role-tab{padding:12px;text-align:center;cursor:pointer;font-family:'Syne',sans-serif;font-size:0.76em;font-weight:600;letter-spacing:0.07em;color:var(--tdim);transition:all 0.22s;border:none;background:transparent;display:flex;align-items:center;justify-content:center;gap:6px}
.role-tab.active{background:linear-gradient(135deg,var(--cdim),var(--vdim));color:var(--cyan)}
.role-tab:hover:not(.active){background:var(--b);color:var(--tmid)}
.form-box{background:var(--s1);border:1px solid var(--b);border-radius:16px;padding:26px;box-shadow:var(--shadow);transition:background 0.3s,border-color 0.3s}
.role-hint{background:var(--s2);border:1px solid var(--b);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:0.78em;color:var(--tdim);display:flex;align-items:flex-start;gap:8px;line-height:1.5}
.role-hint b{color:var(--tmid)}
.field{margin-bottom:15px}
.field-label{display:block;font-size:0.72em;font-weight:500;letter-spacing:0.07em;color:var(--tdim);text-transform:uppercase;margin-bottom:6px}
.field-wrap{position:relative}
.fi{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:0.95em;opacity:0.45;pointer-events:none}
.field-input{width:100%;padding:11px 13px 11px 38px;background:var(--s2);border:1px solid var(--b);border-radius:9px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.93em;outline:none;transition:all 0.22s}
.field-input::placeholder{color:var(--tdim)}
.field-input:focus{border-color:var(--cyan);background:var(--cdim);box-shadow:0 0 0 3px var(--cdim)}
.row{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
.check-label{display:flex;gap:7px;align-items:center;font-size:0.83em;color:var(--tdim);cursor:pointer}
.check-label input{accent-color:var(--cyan);cursor:pointer}
.link{color:var(--cyan);font-size:0.83em;text-decoration:none;opacity:0.75;transition:opacity 0.2s}
.link:hover{opacity:1}
.submit-btn{width:100%;padding:13px;background:linear-gradient(135deg,var(--cyan) 0%,#0099cc 100%);border:none;border-radius:9px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.88em;font-weight:700;letter-spacing:0.09em;cursor:pointer;transition:all 0.22s;box-shadow:0 4px 22px var(--cdim)}
.submit-btn.admin-btn{background:linear-gradient(135deg,#a78bfa 0%,var(--violet) 100%);box-shadow:0 4px 22px var(--vdim)}
.submit-btn:hover{transform:translateY(-2px)}
.submit-btn:active{transform:translateY(0)}
.err{background:rgba(255,82,82,0.09);border:1px solid rgba(255,82,82,0.28);border-radius:8px;padding:9px 13px;color:#ff8a80;font-size:0.83em;margin-bottom:13px;display:flex;align-items:center;gap:8px}
.status-bar{position:fixed;bottom:14px;left:50%;transform:translateX(-50%);display:flex;align-items:center;gap:7px;background:var(--s1);border:1px solid var(--b);border-radius:20px;padding:5px 13px;font-size:0.7em;color:var(--tdim);z-index:10}
.sdot{width:5px;height:5px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green);animation:sdotPulse 2s ease infinite}
@keyframes sdotPulse{0%,100%{opacity:1}50%{opacity:0.35}}
@media(max-width:480px){.card{padding:14px}.form-box{padding:18px}}
</style>
</head>
<body>
<div class="grid-bg"></div>
<div class="orb o1"></div><div class="orb o2"></div>
<div class="scan"></div>
<div class="corner ctl"></div><div class="corner ctr"></div>
<div class="corner cbl"></div><div class="corner cbr"></div>

<!-- Theme toggle -->
<button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">
  <span class="theme-icon" id="themeIcon">☀️</span>
  <span id="themeLabel">Light</span>
</button>

<div class="card">
  <div class="logo-wrap">
    <div class="logo-icon">∑</div>
    <div class="logo-name">MathQuest</div>
    <div class="logo-tag">AI-Powered Learning Platform</div>
  </div>

  <div class="role-tabs">
    <button type="button" class="role-tab <?= $selected_role === 'student' ? 'active' : '' ?>" onclick="setRole('student')">🎮 Student</button>
    <button type="button" class="role-tab <?= $selected_role === 'admin'   ? 'active' : '' ?>" onclick="setRole('admin')">🛡 Admin</button>
  </div>

  <div class="form-box">
    <div class="role-hint" id="roleHint">
      <?php if ($selected_role === 'admin'): ?>
        <span>🛡</span><span><b>Admin Login</b> — Teacher &amp; admin access. Manage students, view class analytics, and assign content.</span>
      <?php else: ?>
        <span>🎓</span><span><b>Student Login</b> — Enter your student username and password to access your dashboard, quests, and assignments.</span>
      <?php endif; ?>
    </div>

    <?php if ($error): ?>
      <div class="err">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($selected_role) ?>">

      <div class="field">
        <label class="field-label">Username</label>
        <div class="field-wrap">
          <span class="fi">◈</span>
          <input class="field-input" name="username" type="text"
                 placeholder="Enter your username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 autocomplete="username" required>
        </div>
      </div>

      <div class="field">
        <label class="field-label">Password</label>
        <div class="field-wrap">
          <span class="fi">◉</span>
          <input class="field-input" name="password" type="password"
                 placeholder="••••••••" autocomplete="current-password" required>
        </div>
      </div>

      <div class="row">
        <label class="check-label"><input type="checkbox" name="remember"> Keep me signed in</label>
        <a href="#" class="link">Forgot password?</a>
      </div>

      <button type="submit" class="submit-btn <?= $selected_role === 'admin' ? 'admin-btn' : '' ?>" id="submitBtn">
        <?= $selected_role === 'admin' ? 'ADMIN SIGN IN →' : 'SIGN IN →' ?>
      </button>
    </form>
  </div>
</div>

<div class="status-bar"><div class="sdot"></div>All systems operational</div>

<script>
// ── Theme toggle ──────────────────────────────────────────────
const html  = document.documentElement;
const saved = localStorage.getItem('mq_theme') || 'dark';
applyTheme(saved);

function applyTheme(theme) {
  html.setAttribute('data-theme', theme);
  localStorage.setItem('mq_theme', theme);
  document.getElementById('themeIcon').textContent  = theme === 'dark' ? '☀️' : '🌙';
  document.getElementById('themeLabel').textContent = theme === 'dark' ? 'Light' : 'Dark';
}

function toggleTheme() {
  applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
}

// ── Role switching ────────────────────────────────────────────
function setRole(r) {
  document.getElementById('roleInput').value = r;
  document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
  event.currentTarget.classList.add('active');
  const hint = document.getElementById('roleHint');
  const btn  = document.getElementById('submitBtn');
  if (r === 'admin') {
    hint.innerHTML  = '<span>🛡</span><span><b>Admin Login</b> — Teacher & admin access. Manage students, view class analytics, and assign content.</span>';
    btn.className   = 'submit-btn admin-btn';
    btn.textContent = 'ADMIN SIGN IN →';
  } else {
    hint.innerHTML  = '<span>🎓</span><span><b>Student Login</b> — Enter your student username and password to access your dashboard, quests, and assignments.</span>';
    btn.className   = 'submit-btn';
    btn.textContent = 'SIGN IN →';
  }
}
</script>
</body>
</html>