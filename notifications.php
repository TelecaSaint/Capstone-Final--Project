<?php
require_once 'config.php';
$user = require_login('student');
$notifs = db()->prepare('SELECT subject,question,user_answer,correct_answer,attempted_at FROM attempts WHERE user_id=? AND is_correct=0 ORDER BY attempted_at DESC LIMIT 20');
$notifs->execute([$user['id']]);
$rows=$notifs->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Notifications</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#080b14;--s1:#0e1220;--s2:#141827;--b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.12);--cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--green:#00e676;--red:#ff5252;--text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68)}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:rgba(8,11,20,0.97);border-bottom:1px solid var(--b);position:sticky;top:0;z-index:100;backdrop-filter:blur(12px)}
.nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15em;background:linear-gradient(135deg,var(--cyan),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
.nav-back{padding:6px 12px;border-radius:7px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none;transition:all 0.2s}
.nav-back:hover{background:var(--s2);color:var(--tmid)}
.logout{padding:6px 12px;background:rgba(255,82,82,0.08);border:1px solid rgba(255,82,82,0.2);border-radius:7px;color:#ff8a80;font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none}
.main{padding:28px;max-width:740px;margin:0 auto}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em;margin-bottom:6px}
.page-sub{color:var(--tdim);font-size:0.9em;margin-bottom:24px}
.notif-card{background:var(--s1);border:1px solid rgba(255,82,82,0.2);border-left:3px solid var(--red);border-radius:12px;padding:16px 18px;margin-bottom:12px;animation:pIn 0.4s ease backwards}
@keyframes pIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.notif-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.notif-subject{font-family:'Syne',sans-serif;font-size:0.7em;font-weight:700;letter-spacing:0.1em;color:var(--red);text-transform:uppercase}
.notif-date{font-size:0.75em;color:var(--tdim)}
.notif-q{font-size:0.9em;color:var(--tmid);margin-bottom:8px}
.notif-answers{display:flex;gap:12px;font-size:0.82em}
.ans-wrong{color:var(--red)}.ans-right{color:var(--green)}
.practice-btn{display:inline-block;margin-top:10px;padding:7px 16px;background:var(--cdim);border:1px solid rgba(0,229,255,0.2);border-radius:7px;color:var(--cyan);font-family:'Syne',sans-serif;font-size:0.74em;font-weight:600;text-decoration:none;transition:all 0.2s}
.practice-btn:hover{background:rgba(0,229,255,0.18)}
.empty{text-align:center;padding:60px 20px;color:var(--tdim)}
</style>
</head>
<body>
<nav>
  <a href="dashboard.php" class="nav-logo">MQ</a>
  <a href="dashboard.php" class="nav-back">← Dashboard</a>
  <a href="logout.php" class="logout">Sign Out</a>
</nav>
<main class="main">
  <div class="page-title">🔔 Notifications</div>
  <div class="page-sub">Problems you got wrong — practice them again to improve!</div>
  <?php if(empty($rows)):?>
  <div class="empty"><div style="font-size:3em;margin-bottom:12px">✅</div><div style="font-family:'Syne',sans-serif;font-weight:700;margin-bottom:8px">All caught up!</div><div>No missed problems. Keep up the great work!</div></div>
  <?php else: foreach($rows as $i=>$r):?>
  <div class="notif-card" style="animation-delay:<?=$i*0.05?>s">
    <div class="notif-top">
      <span class="notif-subject">❌ <?=htmlspecialchars(ucfirst($r['subject']))?></span>
      <span class="notif-date"><?=date('M j, Y',strtotime($r['attempted_at']))?></span>
    </div>
    <div class="notif-q"><?=htmlspecialchars($r['question'])?></div>
    <div class="notif-answers">
      <span class="ans-wrong">✗ Your answer: <?=htmlspecialchars($r['user_answer'])?></span>
      <span class="ans-right">✓ Correct: <?=htmlspecialchars($r['correct_answer'])?></span>
    </div>
    <a href="problem.php?subject=<?=urlencode($r['subject'])?>" class="practice-btn">↺ Practice <?=htmlspecialchars(ucfirst($r['subject']))?> →</a>
  </div>
  <?php endforeach; endif;?>
</main>
</body>
</html>