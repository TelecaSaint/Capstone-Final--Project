<?php require_once 'config.php'; $user = require_login('student'); ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Progress</title>
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
.main{padding:28px;max-width:1200px;margin:0 auto}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em;margin-bottom:6px}
.page-sub{color:var(--tdim);font-size:0.9em;margin-bottom:28px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px}
.panel{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:22px;animation:pIn 0.4s ease backwards;transition:background 0.3s}
@keyframes pIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.ptitle{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.14em;color:var(--tdim);text-transform:uppercase;margin-bottom:16px}
.scard{padding:18px 20px}
.sc-val{font-family:'Syne',sans-serif;font-weight:800;font-size:2em;line-height:1;margin-bottom:3px}
.sc-val.cyan{color:var(--cyan)}.sc-val.green{color:var(--green)}.sc-val.amber{color:var(--amber)}.sc-val.violet{color:#a78bfa}
.sc-label{font-size:0.78em;color:var(--tdim)}
.sc-ico{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1em;margin-bottom:10px}
.sc-ico.cyan{background:var(--cdim);border:1px solid rgba(0,119,204,0.2)}
.sc-ico.green{background:var(--gdim);border:1px solid rgba(0,168,84,0.2)}
.sc-ico.amber{background:var(--adim);border:1px solid rgba(196,127,0,0.25)}
.sc-ico.violet{background:var(--vdim);border:1px solid rgba(124,58,237,0.25)}
.bar{height:6px;background:var(--s2);border-radius:3px;overflow:hidden;margin:6px 0 12px}
.bar-fill{height:100%;border-radius:3px}
table{width:100%;border-collapse:collapse}
th{padding:9px 12px;text-align:left;font-family:'Syne',sans-serif;font-size:0.63em;font-weight:600;letter-spacing:0.1em;color:var(--tdim);text-transform:uppercase;border-bottom:1px solid var(--b)}
td{padding:11px 12px;font-size:0.87em;border-bottom:1px solid var(--b)}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--s2)}
.correct{color:var(--green);font-weight:600}.wrong{color:var(--red);font-weight:600}
@media(max-width:900px){.g4{grid-template-columns:1fr 1fr}.g2{grid-template-columns:1fr}}
@media(max-width:600px){.main{padding:16px}.g4{grid-template-columns:1fr 1fr}.nav-links{display:none}}
</style>
</head>
<body>
<nav>
  <a href="dashboard.php" class="nav-logo">MQ</a>
  <div class="nav-links">
    <a href="dashboard.php"   class="nav-link">🏠 Home</a>
    <a href="assignments.php" class="nav-link">📋 Assignments</a>
    <a href="leaderboard.php" class="nav-link">🏅 Leaderboard</a>
    <a href="progress.php"    class="nav-link active">📈 Progress</a>
    <a href="profile.php"     class="nav-link">🧙 Profile</a>
  </div>
  <div class="nav-right">
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn"><span id="themeIcon">☀️</span><span id="themeLabel">Light</span></button>
    <a href="logout.php" class="logout">Sign Out</a>
  </div>
</nav>
<main class="main">
  <div class="page-title">📈 Progress Report</div>
  <div class="page-sub">Your full learning history — see where you're strong and where to improve.</div>
  <div class="g4">
    <div class="panel scard" style="animation-delay:.04s"><div class="sc-ico cyan">⭐</div><div class="sc-val cyan">1,250</div><div class="sc-label">Total XP</div></div>
    <div class="panel scard" style="animation-delay:.08s"><div class="sc-ico green">✓</div><div class="sc-val green">47</div><div class="sc-label">Problems Solved</div></div>
    <div class="panel scard" style="animation-delay:.12s"><div class="sc-ico amber">🎯</div><div class="sc-val amber">87%</div><div class="sc-label">Accuracy</div></div>
    <div class="panel scard" style="animation-delay:.16s"><div class="sc-ico violet">🔥</div><div class="sc-val violet">7</div><div class="sc-label">Current Streak</div></div>
  </div>
  <div class="g2">
    <div class="panel" style="animation-delay:.2s">
      <div class="ptitle">📚 Subject Breakdown</div>
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;font-size:0.83em;margin-bottom:4px"><span>Arithmetic</span><span style="color:var(--tdim)">0/1 correct · <b style="color:var(--green)">0%</b></span></div>
        <div class="bar"><div class="bar-fill" style="width:0%;background:var(--green)"></div></div>
      </div>
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;font-size:0.83em;margin-bottom:4px"><span>Statistics</span><span style="color:var(--tdim)">0/1 correct · <b style="color:var(--red)">0%</b></span></div>
        <div class="bar"><div class="bar-fill" style="width:0%;background:var(--red)"></div></div>
      </div>
    </div>
    <div class="panel" style="animation-delay:.24s">
      <div class="ptitle">📅 30-Day Activity</div>
      <div style="display:flex;align-items:flex-end;gap:3px;height:80px">
        <div title="2026-03-28: 2 attempts" style="flex:1;height:80px;background:var(--red);border-radius:3px 3px 0 0;opacity:0.8;cursor:pointer;transition:opacity 0.2s" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8"></div>
      </div>
      <div style="font-size:0.72em;color:var(--tdim);margin-top:8px;text-align:center">1 active day · 2 total attempts</div>
    </div>
  </div>
  <div class="panel" style="animation-delay:.28s">
    <div class="ptitle">🕐 Recent Attempts</div>
    <div style="overflow-x:auto">
      <table>
        <thead><tr><th>Subject</th><th>Question</th><th>Your Answer</th><th>Correct</th><th>Result</th><th>XP</th><th>Date</th></tr></thead>
        <tbody>
          <tr>
            <td><span style="font-family:'Syne',sans-serif;font-size:0.8em;font-weight:600">Statistics</span></td>
            <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">4,8,6,10,7. Mean=?</td>
            <td>5</td><td style="color:var(--green)">7</td>
            <td><span class="wrong">✗ Wrong</span></td>
            <td style="color:var(--cyan);font-family:'Syne',sans-serif;font-weight:600">+0</td>
            <td style="color:var(--tdim)">Mar 28</td>
          </tr>
          <tr>
            <td><span style="font-family:'Syne',sans-serif;font-size:0.8em;font-weight:600">Arithmetic</span></td>
            <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">48 × 25 = ?</td>
            <td>12</td><td style="color:var(--green)">1200</td>
            <td><span class="wrong">✗ Wrong</span></td>
            <td style="color:var(--cyan);font-family:'Syne',sans-serif;font-weight:600">+0</td>
            <td style="color:var(--tdim)">Mar 28</td>
          </tr>
        </tbody>
      </table>
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