<?php require_once 'config.php'; $user = require_login('student'); ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Assignments</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root,[data-theme="dark"]{--bg:#080b14;--s1:#0e1220;--s2:#141827;--s3:#1a2035;--b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.12);--cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--violet:#7c3aed;--vdim:rgba(124,58,237,0.15);--amber:#ffab00;--adim:rgba(255,171,0,0.15);--green:#00e676;--gdim:rgba(0,230,118,0.12);--red:#ff5252;--text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68);--nav-bg:rgba(8,11,20,0.97)}
[data-theme="light"]{--bg:#f0f4ff;--s1:#ffffff;--s2:#e8edf8;--s3:#d8e0f0;--b:rgba(0,0,0,0.08);--bb:rgba(0,0,0,0.15);--cyan:#0077cc;--cdim:rgba(0,119,204,0.10);--violet:#6d28d9;--vdim:rgba(109,40,217,0.10);--amber:#c47f00;--adim:rgba(196,127,0,0.12);--green:#00a854;--gdim:rgba(0,168,84,0.10);--red:#cc3333;--text:#0f1423;--tdim:rgba(15,20,35,0.45);--tmid:rgba(15,20,35,0.72);--nav-bg:rgba(240,244,255,0.97)}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;transition:background 0.3s,color 0.3s}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:var(--nav-bg);border-bottom:1px solid var(--b);position:sticky;top:0;z-index:100;backdrop-filter:blur(12px);transition:background 0.3s}
.nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15em;background:linear-gradient(135deg,var(--cyan),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
.nav-links{display:flex;gap:4px}
.nav-link{padding:6px 12px;border-radius:7px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none;transition:all 0.2s}
.nav-link:hover{background:var(--s2);color:var(--tmid)}
.nav-link.active{background:var(--cdim);color:var(--cyan);border:1px solid rgba(0,119,204,0.2)}
.nav-right{display:flex;align-items:center;gap:10px}
.theme-toggle{background:var(--s2);border:1px solid var(--b);border-radius:20px;padding:5px 12px;cursor:pointer;font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;color:var(--tmid);display:flex;align-items:center;gap:5px;transition:all 0.22s}
.theme-toggle:hover{border-color:var(--bb);color:var(--text)}
.logout{padding:6px 12px;background:rgba(255,82,82,0.08);border:1px solid rgba(255,82,82,0.2);border-radius:7px;color:var(--red);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none}
.main{padding:28px;max-width:1100px;margin:0 auto}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em;margin-bottom:6px}
.page-sub{color:var(--tdim);font-size:0.9em;margin-bottom:28px}
.asgn-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:18px}
.asgn-card{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:22px;transition:all 0.25s;animation:pIn 0.4s ease backwards}
.asgn-card:hover{border-color:var(--bb);transform:translateY(-2px)}
@keyframes pIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.asgn-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px}
.asgn-title{font-family:'Syne',sans-serif;font-weight:700;font-size:1em;margin-bottom:4px}
.asgn-subject{font-size:0.78em;color:var(--tdim)}
.pill{display:inline-block;padding:3px 9px;border-radius:5px;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.06em}
.pill-easy{background:var(--gdim);color:var(--green);border:1px solid rgba(0,168,84,0.2)}
.pill-medium{background:var(--adim);color:var(--amber);border:1px solid rgba(196,127,0,0.2)}
.pill-hard{background:rgba(255,82,82,0.09);color:var(--red);border:1px solid rgba(204,51,51,0.2)}
.asgn-meta{display:flex;gap:14px;margin-bottom:14px;font-size:0.8em;color:var(--tdim)}
.bar{height:5px;background:var(--s2);border-radius:3px;overflow:hidden;margin-bottom:12px}
.bar-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,#0099cc,var(--cyan))}
.asgn-btn{width:100%;padding:10px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:8px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.8em;font-weight:700;letter-spacing:0.08em;cursor:pointer;transition:all 0.2s}
.asgn-btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px var(--cdim)}
@media(max-width:600px){.main{padding:16px}.nav-links{display:none}}
</style>
</head>
<body>
<nav>
  <a href="dashboard.php" class="nav-logo">MQ</a>
  <div class="nav-links">
    <a href="dashboard.php"   class="nav-link">🏠 Home</a>
    <a href="assignments.php" class="nav-link active">📋 Assignments</a>
    <a href="leaderboard.php" class="nav-link">🏅 Leaderboard</a>
    <a href="progress.php"    class="nav-link">📈 Progress</a>
    <a href="profile.php"     class="nav-link">🧙 Profile</a>
  </div>
  <div class="nav-right">
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn"><span id="themeIcon">☀️</span><span id="themeLabel">Light</span></button>
    <a href="logout.php" class="logout">Sign Out</a>
  </div>
</nav>
<main class="main">
  <div class="page-title">📋 Assignments</div>
  <div class="page-sub">Complete your assigned problems to earn XP and keep your grade up.</div>
  <div class="asgn-grid">
    <div class="asgn-card" style="animation-delay:0s">
      <div class="asgn-top"><div><div class="asgn-title">Algebra Basics</div><div class="asgn-subject">Algebra</div></div><span class="pill pill-easy">Easy</span></div>
      <div class="asgn-meta"><span>📝 10 problems</span><span>📅 3d left</span></div>
      <div class="bar"><div class="bar-fill" style="width:0%"></div></div>
      <button class="asgn-btn" onclick="location.href='problem.php?subject=algebra&assignment=1'">▶ Start Assignment</button>
    </div>
    <div class="asgn-card" style="animation-delay:0.06s">
      <div class="asgn-top"><div><div class="asgn-title">Fraction Challenge</div><div class="asgn-subject">Fractions</div></div><span class="pill pill-medium">Medium</span></div>
      <div class="asgn-meta"><span>📝 15 problems</span><span>📅 7d left</span></div>
      <div class="bar"><div class="bar-fill" style="width:0%"></div></div>
      <button class="asgn-btn" onclick="location.href='problem.php?subject=fractions&assignment=2'">▶ Start Assignment</button>
    </div>
    <div class="asgn-card" style="animation-delay:0.12s">
      <div class="asgn-top"><div><div class="asgn-title">Geometry Quiz</div><div class="asgn-subject">Geometry</div></div><span class="pill pill-medium">Medium</span></div>
      <div class="asgn-meta"><span>📝 10 problems</span><span>📅 12d left</span></div>
      <div class="bar"><div class="bar-fill" style="width:0%"></div></div>
      <button class="asgn-btn" onclick="location.href='problem.php?subject=geometry&assignment=3'">▶ Start Assignment</button>
    </div>
  </div>
</main>
<script>
const html=document.documentElement;
applyTheme(localStorage.getItem('mq_theme')||'dark');
function applyTheme(t){html.setAttribute('data-theme',t);localStorage.setItem('mq_theme',t);document.getElementById('themeIcon').textContent=t==='dark'?'☀️':'🌙';document.getElementById('themeLabel').textContent=t==='dark'?'Light':'Dark';}
function toggleTheme(){applyTheme(html.getAttribute('data-theme')==='dark'?'light':'dark');}
</script>
</body>
</html>