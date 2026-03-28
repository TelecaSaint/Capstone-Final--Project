<?php
require_once 'config.php';
$user = require_login('student');
$board = db()->query('SELECT u.full_name,u.avatar,u.class_name,s.xp,s.level,s.problems_solved,s.streak FROM users u JOIN student_stats s ON s.user_id=u.id WHERE u.role="student" ORDER BY s.xp DESC LIMIT 20')->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Leaderboard</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#080b14;--s1:#0e1220;--s2:#141827;--s3:#1a2035;--b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.12);--cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--violet:#7c3aed;--vdim:rgba(124,58,237,0.15);--amber:#ffab00;--green:#00e676;--gdim:rgba(0,230,118,0.12);--red:#ff5252;--text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68)}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:rgba(8,11,20,0.97);border-bottom:1px solid var(--b);position:sticky;top:0;z-index:100;backdrop-filter:blur(12px)}
.nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15em;background:linear-gradient(135deg,var(--cyan),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
.nav-links{display:flex;gap:4px}
.nav-link{padding:6px 12px;border-radius:7px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none;transition:all 0.2s}
.nav-link:hover{background:var(--s2);color:var(--tmid)}
.nav-link.active{background:var(--cdim);color:var(--cyan);border:1px solid rgba(0,229,255,0.2)}
.logout{padding:6px 12px;background:rgba(255,82,82,0.08);border:1px solid rgba(255,82,82,0.2);border-radius:7px;color:#ff8a80;font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none}
.main{padding:28px;max-width:800px;margin:0 auto}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em;margin-bottom:6px}
.page-sub{color:var(--tdim);font-size:0.9em;margin-bottom:28px}
.podium{display:flex;justify-content:center;align-items:flex-end;gap:16px;margin-bottom:32px;padding:20px 0}
.pod{text-align:center;animation:pIn 0.5s ease backwards}
@keyframes pIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
.pod-av{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--cdim),var(--vdim));border:2px solid rgba(0,229,255,0.3);display:flex;align-items:center;justify-content:center;font-size:1.8em;margin:0 auto 8px}
.pod-av.gold{border-color:var(--amber);background:rgba(255,171,0,0.1)}
.pod-av.silver{border-color:rgba(200,200,200,0.4);background:rgba(200,200,200,0.07)}
.pod-av.bronze{border-color:rgba(180,100,50,0.4);background:rgba(180,100,50,0.07)}
.pod-name{font-family:'Syne',sans-serif;font-weight:700;font-size:0.82em;margin-bottom:4px}
.pod-xp{font-size:0.75em;color:var(--cyan);font-weight:600}
.pod-block{border-radius:8px 8px 0 0;width:80px;margin:8px auto 0}
.lb-list{background:var(--s1);border:1px solid var(--b);border-radius:14px;overflow:hidden}
.lb-row{display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--b);transition:background 0.2s;animation:pIn 0.4s ease backwards}
.lb-row:last-child{border-bottom:none}
.lb-row:hover{background:var(--s2)}
.lb-row.you{background:var(--cdim);border-left:3px solid var(--cyan)}
.lb-rank{font-family:'Syne',sans-serif;font-weight:800;font-size:1em;width:32px;flex-shrink:0;text-align:center}
.r1{color:var(--amber)}.r2{color:#c0c0c0}.r3{color:#cd7f32}
.lb-av{width:38px;height:38px;border-radius:50%;background:var(--s3);border:1px solid var(--b);display:flex;align-items:center;justify-content:center;font-size:1.2em;flex-shrink:0}
.lb-info{flex:1;min-width:0}
.lb-name{font-family:'Syne',sans-serif;font-weight:600;font-size:0.92em;margin-bottom:2px}
.lb-sub{font-size:0.75em;color:var(--tdim)}
.lb-stats{display:flex;gap:18px;text-align:right;flex-shrink:0}
.lb-stat-val{font-family:'Syne',sans-serif;font-weight:700;font-size:0.9em;color:var(--cyan)}
.lb-stat-lbl{font-size:0.68em;color:var(--tdim)}
@media(max-width:600px){.main{padding:16px}.lb-stats{display:none}.nav-links{display:none}}
</style>
</head>
<body>
<nav>
  <a href="dashboard.php" class="nav-logo">MQ</a>
  <div class="nav-links">
    <a href="dashboard.php"   class="nav-link">🏠 Home</a>
    <a href="assignments.php" class="nav-link">📋 Assignments</a>
    <a href="leaderboard.php" class="nav-link active">🏅 Leaderboard</a>
    <a href="progress.php"    class="nav-link">📈 Progress</a>
    <a href="profile.php"     class="nav-link">🧙 Profile</a>
  </div>
  <a href="logout.php" class="logout">Sign Out</a>
</nav>
<main class="main">
  <div class="page-title">🏅 Leaderboard</div>
  <div class="page-sub">Top students ranked by XP earned. Keep solving to climb the ranks!</div>

  <?php if(count($board)>=3):?>
  <div class="podium">
    <!-- 2nd -->
    <div class="pod" style="animation-delay:.1s">
      <div class="pod-av silver"><?=htmlspecialchars($board[1]['avatar'])?></div>
      <div class="pod-name"><?=htmlspecialchars(explode(' ',$board[1]['full_name'])[0])?></div>
      <div class="pod-xp"><?=number_format($board[1]['xp'])?> XP</div>
      <div class="pod-block" style="height:60px;background:rgba(200,200,200,0.12);border:1px solid rgba(200,200,200,0.2)"><div style="padding-top:10px;font-size:1.4em">🥈</div></div>
    </div>
    <!-- 1st -->
    <div class="pod" style="animation-delay:.05s">
      <div class="pod-av gold" style="width:68px;height:68px;font-size:2.2em"><?=htmlspecialchars($board[0]['avatar'])?></div>
      <div class="pod-name" style="font-size:0.9em"><?=htmlspecialchars(explode(' ',$board[0]['full_name'])[0])?></div>
      <div class="pod-xp"><?=number_format($board[0]['xp'])?> XP</div>
      <div class="pod-block" style="height:80px;background:rgba(255,171,0,0.1);border:1px solid rgba(255,171,0,0.25)"><div style="padding-top:10px;font-size:1.8em">🥇</div></div>
    </div>
    <!-- 3rd -->
    <div class="pod" style="animation-delay:.15s">
      <div class="pod-av bronze"><?=htmlspecialchars($board[2]['avatar'])?></div>
      <div class="pod-name"><?=htmlspecialchars(explode(' ',$board[2]['full_name'])[0])?></div>
      <div class="pod-xp"><?=number_format($board[2]['xp'])?> XP</div>
      <div class="pod-block" style="height:45px;background:rgba(180,100,50,0.1);border:1px solid rgba(180,100,50,0.2)"><div style="padding-top:5px;font-size:1.2em">🥉</div></div>
    </div>
  </div>
  <?php endif;?>

  <div class="lb-list">
    <?php foreach($board as $i=>$row):
      $isYou=$row['full_name']===$user['full_name'];
      $rankClass=$i===0?'r1':($i===1?'r2':($i===2?'r3':''));
      $rankIcon=$i===0?'🥇':($i===1?'🥈':($i===2?'🥉':($i+1)));
    ?>
    <div class="lb-row <?=$isYou?'you':''?>" style="animation-delay:<?=$i*0.04?>s">
      <div class="lb-rank <?=$rankClass?>"><?=$rankIcon?></div>
      <div class="lb-av"><?=htmlspecialchars($row['avatar'])?></div>
      <div class="lb-info">
        <div class="lb-name"><?=htmlspecialchars($row['full_name'])?><?=$isYou?' ✦ (You)':''?></div>
        <div class="lb-sub">Level <?=$row['level']?> · <?=htmlspecialchars($row['class_name'])?></div>
      </div>
      <div class="lb-stats">
        <div><div class="lb-stat-val"><?=number_format($row['xp'])?></div><div class="lb-stat-lbl">XP</div></div>
        <div><div class="lb-stat-val"><?=$row['problems_solved']?></div><div class="lb-stat-lbl">Solved</div></div>
        <div><div class="lb-stat-val"><?=$row['streak']?>🔥</div><div class="lb-stat-lbl">Streak</div></div>
      </div>
    </div>
    <?php endforeach;?>
  </div>
</main>
</body>
</html>