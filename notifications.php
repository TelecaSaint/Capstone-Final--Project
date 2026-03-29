<?php require_once 'config.php'; $user = require_login('student'); ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Notifications</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* ── Dark mode ─────────────────────────────────────────────── */
:root,[data-theme="dark"]{
  --bg:#080b14;--s1:#0e1220;--s2:#141827;
  --b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.12);
  --cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);
  --green:#00e676;--red:#ff5252;--rdim:rgba(255,82,82,0.08);
  --text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68);
  --nav-bg:rgba(8,11,20,0.97);
}
/* ── Light mode ────────────────────────────────────────────── */
[data-theme="light"]{
  --bg:#f0f4ff;--s1:#ffffff;--s2:#e8edf8;
  --b:rgba(0,0,0,0.08);--bb:rgba(0,0,0,0.15);
  --cyan:#0077cc;--cdim:rgba(0,119,204,0.10);
  --green:#00a854;--red:#cc3333;--rdim:rgba(204,51,51,0.06);
  --text:#0f1423;--tdim:rgba(15,20,35,0.45);--tmid:rgba(15,20,35,0.72);
  --nav-bg:rgba(240,244,255,0.97);
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;transition:background 0.3s,color 0.3s}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:var(--nav-bg);border-bottom:1px solid var(--b);position:sticky;top:0;z-index:100;backdrop-filter:blur(12px);transition:background 0.3s}
.nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15em;background:linear-gradient(135deg,var(--cyan),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
.nav-back{padding:6px 12px;border-radius:7px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none;transition:all 0.2s}
.nav-back:hover{background:var(--s2);color:var(--tmid)}
.nav-right{display:flex;align-items:center;gap:10px}
.theme-toggle{background:var(--s2);border:1px solid var(--b);border-radius:20px;padding:5px 12px;cursor:pointer;font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;color:var(--tmid);display:flex;align-items:center;gap:5px;transition:all 0.22s}
.theme-toggle:hover{border-color:var(--bb);color:var(--text)}
.logout{padding:6px 12px;background:var(--rdim);border:1px solid rgba(255,82,82,0.2);border-radius:7px;color:var(--red);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none}
.main{padding:28px;max-width:740px;margin:0 auto}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em;margin-bottom:6px}
.page-sub{color:var(--tdim);font-size:0.9em;margin-bottom:24px}
.notif-card{background:var(--s1);border:1px solid rgba(255,82,82,0.2);border-left:3px solid var(--red);border-radius:12px;padding:16px 18px;margin-bottom:12px;animation:pIn 0.4s ease backwards;transition:background 0.3s}
@keyframes pIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.notif-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.notif-subject{font-family:'Syne',sans-serif;font-size:0.7em;font-weight:700;letter-spacing:0.1em;color:var(--red);text-transform:uppercase}
.notif-date{font-size:0.75em;color:var(--tdim)}
.notif-q{font-size:0.9em;color:var(--tmid);margin-bottom:8px}
.notif-answers{display:flex;gap:12px;font-size:0.82em;flex-wrap:wrap}
.ans-wrong{color:var(--red)}.ans-right{color:var(--green)}
.practice-btn{display:inline-block;margin-top:10px;padding:7px 16px;background:var(--cdim);border:1px solid rgba(0,119,204,0.2);border-radius:7px;color:var(--cyan);font-family:'Syne',sans-serif;font-size:0.74em;font-weight:600;text-decoration:none;transition:all 0.2s}
.practice-btn:hover{background:var(--cdim);opacity:0.8}
.empty{text-align:center;padding:60px 20px;color:var(--tdim)}
@media(max-width:600px){.main{padding:16px}}
</style>
</head>
<body>
<nav>
  <a href="dashboard.php" class="nav-logo">MQ</a>
  <a href="dashboard.php" class="nav-back">← Dashboard</a>
  <div class="nav-right">
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn"><span id="themeIcon">☀️</span><span id="themeLabel">Light</span></button>
    <a href="logout.php" class="logout">Sign Out</a>
  </div>
</nav>
<main class="main">
  <div class="page-title">🔔 Notifications</div>
  <div class="page-sub">Problems you got wrong — practice them again to improve!</div>

  <?php
  $notifs = db()->prepare('
    SELECT question, subject, attempted_at, user_answer, correct_answer
    FROM attempts
    WHERE user_id = ? AND is_correct = 0
    ORDER BY attempted_at DESC
    LIMIT 20
  ');
  $notifs->execute([$user['id']]);
  $rows = $notifs->fetchAll();

  if(empty($rows)): ?>
    <div class="empty">
      <div style="font-size:3em;margin-bottom:12px">✅</div>
      <div style="font-family:'Syne',sans-serif;font-weight:700;margin-bottom:6px">All caught up!</div>
      <div style="font-size:0.88em">No missed problems. Keep it up!</div>
    </div>
  <?php else: foreach($rows as $i=>$n): ?>
  <div class="notif-card" style="animation-delay:<?= $i*0.05 ?>s">
    <div class="notif-top">
      <span class="notif-subject">❌ <?= htmlspecialchars(ucfirst($n['subject'])) ?></span>
      <span class="notif-date"><?= date('M j, Y', strtotime($n['attempted_at'])) ?></span>
    </div>
    <div class="notif-q"><?= htmlspecialchars($n['question']) ?></div>
    <div class="notif-answers">
      <span class="ans-wrong">✗ Your answer: <?= htmlspecialchars($n['user_answer']) ?></span>
      <span class="ans-right">✓ Correct: <?= htmlspecialchars($n['correct_answer']) ?></span>
    </div>
    <a href="problem.php?subject=<?= urlencode($n['subject']) ?>" class="practice-btn">↺ Practice <?= htmlspecialchars(ucfirst($n['subject'])) ?> →</a>
  </div>
  <?php endforeach; endif; ?>
</main>
<script>
const html=document.documentElement;
applyTheme(localStorage.getItem('mq_theme')||'dark');
function applyTheme(t){html.setAttribute('data-theme',t);localStorage.setItem('mq_theme',t);document.getElementById('themeIcon').textContent=t==='dark'?'☀️':'🌙';document.getElementById('themeLabel').textContent=t==='dark'?'Light':'Dark';}
function toggleTheme(){applyTheme(html.getAttribute('data-theme')==='dark'?'light':'dark');}
</script>
</body>
</html>