<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'student';

    try {
        $stmt = db()->prepare("
            SELECT id, username, password, full_name, role, avatar
            FROM users
            WHERE username = :username
              AND role = :role
            LIMIT 1
        ");

        $stmt->execute([
            ':username' => $username,
            ':role' => $role
        ]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'avatar' => $user['avatar']
            ];

            $update = db()->prepare("
                UPDATE users
                SET last_login = NOW()
                WHERE id = :id
            ");

            $update->execute([
                ':id' => $user['id']
            ]);

            if ($user['role'] === 'admin') {
                redirect('admin.php');
            } else {
                redirect('dashboard.php');
            }
        } else {
            $error = 'Invalid username or password.';
        }

    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
:root{--bg:#080b14;--s1:#0e1220;--s2:#141827;--b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.13);--cyan:#00e5ff;--cdim:rgba(0,229,255,0.12);--cglow:rgba(0,229,255,0.22);--violet:#7c3aed;--vdim:rgba(124,58,237,0.18);--text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68);--green:#00e676;--red:#ff5252;--amber:#ffab00}
*{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.grid-bg{position:fixed;inset:0;z-index:0;background-image:linear-gradient(rgba(0,229,255,0.025) 1px,transparent 1px),linear-gradient(90deg,rgba(0,229,255,0.025) 1px,transparent 1px);background-size:64px 64px;mask-image:radial-gradient(ellipse at center,black 30%,transparent 75%)}
.orb{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0;animation:orbDrift 14s ease-in-out infinite}
.o1{width:450px;height:450px;background:rgba(0,229,255,0.055);top:-120px;left:-120px;animation-delay:0s}
.o2{width:350px;height:350px;background:rgba(124,58,237,0.07);bottom:-100px;right:-100px;animation-delay:-7s}
@keyframes orbDrift{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(25px,18px) scale(1.06)}}
.scan{position:fixed;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent 0%,var(--cyan) 50%,transparent 100%);opacity:0.25;animation:scan 8s linear infinite;z-index:1;pointer-events:none}
@keyframes scan{0%{top:-1px}100%{top:100vh}}
.corner{position:fixed;width:36px;height:36px;border-color:rgba(0,229,255,0.18);border-style:solid;z-index:1;pointer-events:none}
.ctl{top:18px;left:18px;border-width:1px 0 0 1px}.ctr{top:18px;right:18px;border-width:1px 1px 0 0}
.cbl{bottom:18px;left:18px;border-width:0 0 1px 1px}.cbr{bottom:18px;right:18px;border-width:0 1px 1px 0}
.card{position:relative;z-index:10;width:100%;max-width:420px;padding:20px;animation:cardIn 0.7s cubic-bezier(0.16,1,0.3,1) both}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(0.97)}to{opacity:1;transform:none}}
.logo-wrap{text-align:center;margin-bottom:32px}
.logo-icon{display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;background:linear-gradient(135deg,rgba(0,229,255,0.12),rgba(124,58,237,0.18));border:1px solid rgba(0,229,255,0.28);border-radius:14px;font-size:1.7em;margin-bottom:12px;box-shadow:0 0 28px rgba(0,229,255,0.12)}
.logo-name{font-family:'Syne',sans-serif;font-weight:800;font-size:1.55em;letter-spacing:0.04em;background:linear-gradient(135deg,var(--cyan) 0%,#a78bfa 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.logo-tag{color:var(--tdim);font-size:0.82em;margin-top:4px}
.role-tabs{display:grid;grid-template-columns:1fr 1fr;border:1px solid var(--b);border-radius:10px;overflow:hidden;margin-bottom:22px;background:var(--s1)}
.role-tab{padding:12px;text-align:center;cursor:pointer;font-family:'Syne',sans-serif;font-size:0.76em;font-weight:600;letter-spacing:0.07em;color:var(--tdim);transition:all 0.22s;border:none;background:transparent;display:flex;align-items:center;justify-content:center;gap:6px}
.role-tab.active{background:linear-gradient(135deg,var(--cdim),var(--vdim));color:var(--cyan)}
.role-tab:hover:not(.active){background:rgba(255,255,255,0.035);color:var(--tmid)}
.form-box{background:var(--s1);border:1px solid var(--b);border-radius:16px;padding:26px;box-shadow:0 24px 60px rgba(0,0,0,0.5),0 0 0 1px rgba(255,255,255,0.025)}
/* Role hint */
.role-hint{background:var(--s2);border:1px solid var(--b);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:0.78em;color:var(--tdim);display:flex;align-items:flex-start;gap:8px;line-height:1.5}
.role-hint b{color:var(--tmid)}
.field{margin-bottom:15px}
.field-label{display:block;font-size:0.72em;font-weight:500;letter-spacing:0.07em;color:var(--tdim);text-transform:uppercase;margin-bottom:6px}
.field-wrap{position:relative}
.fi{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:0.95em;opacity:0.45;pointer-events:none}
.field-input{width:100%;padding:11px 13px 11px 38px;background:var(--s2);border:1px solid var(--b);border-radius:9px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.93em;outline:none;transition:all 0.22s}
.field-input::placeholder{color:var(--tdim)}
.field-input:focus{border-color:rgba(0,229,255,0.45);background:rgba(0,229,255,0.035);box-shadow:0 0 0 3px rgba(0,229,255,0.07)}
.field-input.shake{animation:shake 0.35s ease}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
.row{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
.check-label{display:flex;gap:7px;align-items:center;font-size:0.83em;color:var(--tdim);cursor:pointer}
.check-label input{accent-color:var(--cyan);cursor:pointer}
.link{color:var(--cyan);font-size:0.83em;text-decoration:none;opacity:0.75;transition:opacity 0.2s}
.link:hover{opacity:1}
.submit-btn{width:100%;padding:13px;background:linear-gradient(135deg,var(--cyan) 0%,#0099cc 100%);border:none;border-radius:9px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.88em;font-weight:700;letter-spacing:0.09em;cursor:pointer;transition:all 0.22s;box-shadow:0 4px 22px rgba(0,229,255,0.28);position:relative;overflow:hidden}
.submit-btn.admin-btn{background:linear-gradient(135deg,#a78bfa 0%,var(--violet) 100%);box-shadow:0 4px 22px rgba(124,58,237,0.35)}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,229,255,0.42)}
.submit-btn.admin-btn:hover{box-shadow:0 8px 30px rgba(124,58,237,0.5)}
.submit-btn:active{transform:translateY(0)}
.err{display:none;background:rgba(255,82,82,0.09);border:1px solid rgba(255,82,82,0.28);border-radius:8px;padding:9px 13px;color:#ff8a80;font-size:0.83em;margin-bottom:13px;align-items:center;gap:8px}
.err.show{display:flex}
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

<div class="card">
  <div class="logo-wrap">
    <div class="logo-icon">∑</div>
    <div class="logo-name">MathQuest</div>
    <div class="logo-tag">AI-Powered Learning Platform</div>
  </div>

  <div class="role-tabs">
    <button class="role-tab active" id="tabS" onclick="setRole('student')">🎮 Student</button>
    <button class="role-tab" id="tabA" onclick="setRole('admin')">🛡 Admin</button>
  </div>

  <div class="form-box">
    <div class="role-hint" id="roleHint">
      <span>🎓</span>
      <span><b>Student Login</b> — Enter your student username and password to access your dashboard, quests, and assignments.</span>
    </div>

    <div class="err" id="err">⚠ Invalid credentials — please try again.</div>

    <form id="lf" onsubmit="handleLogin(event)">
      <div class="field">
        <label class="field-label">Username</label>
        <div class="field-wrap">
          <span class="fi">◈</span>
          <input class="field-input" id="un" type="text" placeholder="Enter your username" autocomplete="username" required>
        </div>
      </div>
      <div class="field">
        <label class="field-label">Password</label>
        <div class="field-wrap">
          <span class="fi">◉</span>
          <input class="field-input" id="pw" type="password" placeholder="••••••••" autocomplete="current-password" required>
        </div>
      </div>
      <div class="row">
        <label class="check-label"><input type="checkbox" id="remember"> Keep me signed in</label>
        <a href="#" class="link">Forgot password?</a>
      </div>
      <button type="submit" class="submit-btn" id="submitBtn">SIGN IN →</button>
    </form>
  </div>
</div>

<div class="status-bar"><div class="sdot"></div>All systems operational</div>

<script>
// ─── Credentials ─────────────────────────────────────────────────────────────
// To add users, add entries to these objects.
const CREDENTIALS = {
  student: {
    'student':  { password: 'student123',  name: 'Alex',         avatar: '👨‍🎓' },
    'sarah':    { password: 'sarah123',    name: 'Sarah',        avatar: '👩‍🎓' },
    'james':    { password: 'james123',    name: 'James',        avatar: '🧑‍🎓' },
  },
  admin: {
    'admin':    { password: 'admin123',    name: 'Mrs. Williams', avatar: '👩‍🏫' },
    'teacher':  { password: 'teacher123',  name: 'Mr. Smith',    avatar: '👨‍🏫' },
  }
};

// ─── Audio ────────────────────────────────────────────────────────────────────
const AudioCtx = window.AudioContext || window.webkitAudioContext;
let actx;
function ac(){if(!actx)actx=new AudioCtx();return actx;}
function beep(f,d=0.12,vol=0.08,type='sine'){
  try{const a=ac(),o=a.createOscillator(),g=a.createGain();
  o.connect(g);g.connect(a.destination);o.type=type;o.frequency.value=f;
  g.gain.setValueAtTime(vol,a.currentTime);
  g.gain.exponentialRampToValueAtTime(0.001,a.currentTime+d);
  o.start();o.stop(a.currentTime+d);}catch(e){}}
function playSuccess(){[523,659,784].forEach((f,i)=>setTimeout(()=>beep(f,0.25,0.1),i*100));}

// ─── Role switching ───────────────────────────────────────────────────────────
let role = 'student';
function setRole(r) {
  role = r;
  document.getElementById('tabS').classList.toggle('active', r === 'student');
  document.getElementById('tabA').classList.toggle('active', r === 'admin');
  const hint = document.getElementById('roleHint');
  const btn  = document.getElementById('submitBtn');
  if (r === 'admin') {
    hint.innerHTML = '<span>🛡</span><span><b>Admin Login</b> — Teacher & admin access. Manage students, view class analytics, and assign content.</span>';
    btn.className = 'submit-btn admin-btn';
    btn.textContent = 'ADMIN SIGN IN →';
  } else {
    hint.innerHTML = '<span>🎓</span><span><b>Student Login</b> — Enter your student username and password to access your dashboard, quests, and assignments.</span>';
    btn.className = 'submit-btn';
    btn.textContent = 'SIGN IN →';
  }
  document.getElementById('err').classList.remove('show');
  beep(600, 0.08);
}

// ─── Login handler ────────────────────────────────────────────────────────────
function handleLogin(e) {
  e.preventDefault();
  const u = document.getElementById('un').value.trim().toLowerCase();
  const p = document.getElementById('pw').value;
  const errEl = document.getElementById('err');

  if (!u || !p) {
    showError('Please fill in both fields.');
    return;
  }

  const pool = CREDENTIALS[role];
  const match = pool[u];

  if (!match || match.password !== p) {
    showError('Invalid credentials — please try again.');
    beep(200, 0.2, 0.12, 'sawtooth');
    return;
  }

  // Successful login — store session data
  errEl.classList.remove('show');
  const storage = document.getElementById('remember').checked ? localStorage : sessionStorage;
  storage.setItem('mqUser',   match.name);
  storage.setItem('mqRole',   role);
  storage.setItem('mqAvatar', match.avatar);
  // Also mirror to localStorage so dashboards can always read it
  localStorage.setItem('mqUser',   match.name);
  localStorage.setItem('mqRole',   role);
  localStorage.setItem('mqAvatar', match.avatar);

  playSuccess();
  setTimeout(() => {
    window.location.href = role === 'admin' ? 'admin.html' : 'dashboard.html';
  }, 350);
}

function showError(msg) {
  const el = document.getElementById('err');
  el.innerHTML = '⚠ ' + msg;
  el.classList.add('show');
  ['un','pw'].forEach(id => {
    const inp = document.getElementById(id);
    inp.classList.add('shake');
    inp.addEventListener('animationend', () => inp.classList.remove('shake'), {once:true});
  });
}
</script>
</body>
</html>
