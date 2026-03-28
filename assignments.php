<?php
require_once 'config.php';
$user = require_login('student');

$asgns = db()->query('SELECT a.*, COUNT(s.id) as submitted FROM assignments a LEFT JOIN submissions s ON s.assignment_id=a.id AND s.user_id='.(int)$user['id'].' GROUP BY a.id ORDER BY a.due_date ASC')->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Assignments</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#080b14;--s1:#0e1220;--s2:#141827;--s3:#1a2035;--b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.12);--cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--violet:#7c3aed;--vdim:rgba(124,58,237,0.15);--amber:#ffab00;--adim:rgba(255,171,0,0.15);--green:#00e676;--gdim:rgba(0,230,118,0.12);--red:#ff5252;--text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68)}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:rgba(8,11,20,0.97);border-bottom:1px solid var(--b);position:sticky;top:0;z-index:100;backdrop-filter:blur(12px)}
.nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15em;background:linear-gradient(135deg,var(--cyan),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
.nav-links{display:flex;gap:4px}
.nav-link{padding:6px 12px;border-radius:7px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none;transition:all 0.2s}
.nav-link:hover{background:var(--s2);color:var(--tmid)}
.nav-link.active{background:var(--cdim);color:var(--cyan);border:1px solid rgba(0,229,255,0.2)}
.logout{padding:6px 12px;background:rgba(255,82,82,0.08);border:1px solid rgba(255,82,82,0.2);border-radius:7px;color:#ff8a80;font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none}
.main{padding:28px;max-width:1100px;margin:0 auto}
.page-header{margin-bottom:28px}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em;margin-bottom:6px}
.page-sub{color:var(--tdim);font-size:0.9em}
.asgn-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:18px}
.asgn-card{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:22px;transition:all 0.25s;animation:pIn 0.4s ease backwards}
.asgn-card:hover{border-color:var(--bb);transform:translateY(-2px)}
@keyframes pIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.asgn-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px}
.asgn-title{font-family:'Syne',sans-serif;font-weight:700;font-size:1em;margin-bottom:4px}
.asgn-subject{font-size:0.78em;color:var(--tdim)}
.pill{display:inline-block;padding:3px 9px;border-radius:5px;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.06em}
.pill-easy{background:var(--gdim);color:var(--green);border:1px solid rgba(0,230,118,0.2)}
.pill-medium{background:var(--adim);color:var(--amber);border:1px solid rgba(255,171,0,0.2)}
.pill-hard{background:rgba(255,82,82,0.09);color:var(--red);border:1px solid rgba(255,82,82,0.2)}
.pill-done{background:var(--cdim);color:var(--cyan);border:1px solid rgba(0,229,255,0.2)}
.asgn-meta{display:flex;gap:14px;margin-bottom:14px;font-size:0.8em;color:var(--tdim)}
.bar{height:5px;background:var(--s2);border-radius:3px;overflow:hidden;margin-bottom:12px}
.bar-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,#0099cc,var(--cyan))}
.asgn-btn{width:100%;padding:10px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:8px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.8em;font-weight:700;letter-spacing:0.08em;cursor:pointer;transition:all 0.2s}
.asgn-btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,229,255,0.3)}
.asgn-btn.done{background:var(--gdim);color:var(--green);border:1px solid rgba(0,230,118,0.25);box-shadow:none}
.empty{text-align:center;padding:60px 20px;color:var(--tdim)}
.empty-ico{font-size:3em;margin-bottom:12px}
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
  <a href="logout.php" class="logout">Sign Out</a>
</nav>
<main class="main">
  <div class="page-header">
    <div class="page-title">📋 Assignments</div>
    <div class="page-sub">Complete your assigned problems to earn XP and keep your grade up.</div>
  </div>
  <?php if(empty($asgns)):?>
  <div class="empty"><div class="empty-ico">📭</div><div style="font-family:'Syne',sans-serif;font-weight:700;margin-bottom:8px">No assignments yet</div><div>Your teacher hasn't assigned anything yet. Check back soon!</div></div>
  <?php else:?>
  <div class="asgn-grid">
    <?php foreach($asgns as $i=>$a):
      $done = (bool)$a['submitted'];
      $daysLeft = (int)ceil((strtotime($a['due_date'])-time())/86400);
      $overdue = $daysLeft < 0;
      $dueText = $overdue ? 'Overdue' : ($daysLeft===0 ? 'Due today' : $daysLeft.'d left');
      $dueColor = $overdue ? 'var(--red)' : ($daysLeft<=2 ? 'var(--amber)' : 'var(--tdim)');
      $pillClass = 'pill-'.$a['difficulty'];
    ?>
    <div class="asgn-card" style="animation-delay:<?=$i*0.06?>s<?=$done?';border-color:rgba(0,230,118,0.2)':''?>">
      <div class="asgn-top">
        <div>
          <div class="asgn-title"><?=htmlspecialchars($a['title'])?></div>
          <div class="asgn-subject"><?=htmlspecialchars($a['subject'])?></div>
        </div>
        <?php if($done):?><span class="pill pill-done">✓ Done</span>
        <?php else:?><span class="pill <?=$pillClass?>"><?=ucfirst($a['difficulty'])?></span><?php endif;?>
      </div>
      <div class="asgn-meta">
        <span>📝 <?=$a['num_problems']?> problems</span>
        <span style="color:<?=$dueColor?>">📅 <?=$dueText?></span>
      </div>
      <div class="bar"><div class="bar-fill" style="width:<?=$done?100:0?>%"></div></div>
      <button class="asgn-btn <?=$done?'done':''?>" onclick="location.href='problem.php?subject=<?=strtolower($a['subject'])?>&assignment=<?=$a['id']?>'">
        <?=$done?'✓ Completed':'▶ Start Assignment'?>
      </button>
    </div>
    <?php endforeach;?>
  </div>
  <?php endif;?>
</main>
</body>
</html>