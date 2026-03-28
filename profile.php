<?php
require_once 'config.php';
$user = require_login('student');
$stats = db()->prepare('SELECT * FROM student_stats WHERE user_id=?');
$stats->execute([$user['id']]);
$s=$stats->fetch()?:[];
$xp=$s['xp']??0;$level=$s['level']??1;$streak=$s['streak']??0;
$solved=$s['problems_solved']??0;$correct=$s['problems_correct']??0;
$accuracy=$solved>0?round($correct/$solved*100):0;
$longestStreak=$s['longest_streak']??0;

$badges=db()->prepare('SELECT b.* FROM badges b JOIN user_badges ub ON ub.badge_id=b.id WHERE ub.user_id=? ORDER BY ub.earned_at DESC');
$badges->execute([$user['id']]);
$earnedBadges=$badges->fetchAll();
$earnedSlugs=array_column($earnedBadges,'slug');

$allBadges=db()->query('SELECT * FROM badges ORDER BY id')->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Profile</title>
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
.main{padding:28px;max-width:900px;margin:0 auto}
.panel{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:22px;margin-bottom:20px;animation:pIn 0.4s ease backwards}
@keyframes pIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.ptitle{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.14em;color:var(--tdim);text-transform:uppercase;margin-bottom:18px}
.profile-hero{display:flex;align-items:center;gap:24px;flex-wrap:wrap}
.avatar-wrap{width:88px;height:88px;border-radius:50%;background:linear-gradient(135deg,var(--cdim),var(--vdim));border:2px solid rgba(0,229,255,0.35);display:flex;align-items:center;justify-content:center;font-size:2.8em;flex-shrink:0;box-shadow:0 0 28px rgba(0,229,255,0.18);animation:avPulse 3s ease-in-out infinite}
@keyframes avPulse{0%,100%{box-shadow:0 0 16px rgba(0,229,255,0.15)}50%{box-shadow:0 0 32px rgba(0,229,255,0.3)}}
.profile-name{font-family:'Syne',sans-serif;font-weight:800;font-size:1.5em;margin-bottom:4px}
.profile-role{font-size:0.85em;color:var(--cyan);margin-bottom:12px}
.stat-chips{display:flex;gap:10px;flex-wrap:wrap}
.chip{padding:5px 14px;background:var(--s2);border:1px solid var(--b);border-radius:20px;font-family:'Syne',sans-serif;font-size:0.75em;font-weight:600;color:var(--tmid)}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:22px}
.stat-box{background:var(--s2);border:1px solid var(--b);border-radius:10px;padding:14px;text-align:center}
.stat-val{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em;color:var(--cyan);margin-bottom:3px}
.stat-lbl{font-size:0.72em;color:var(--tdim)}
.bar{height:6px;background:var(--s2);border-radius:3px;overflow:hidden;margin:6px 0 4px}
.bar-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,#0099cc,var(--cyan))}
.badges-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:12px}
.badge-card{background:var(--s2);border:1px solid var(--b);border-radius:10px;padding:14px 8px;text-align:center;transition:all 0.2s;position:relative}
.badge-card:hover{background:var(--s3);border-color:var(--bb);transform:scale(1.03)}
.badge-card.locked{opacity:0.2}
.badge-ico{font-size:1.8em;margin-bottom:6px}
.badge-name{font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;color:var(--tdim)}
.badge-card:not(.locked) .badge-name{color:var(--tmid)}
@media(max-width:600px){.main{padding:16px}.stat-grid{grid-template-columns:repeat(2,1fr)}.nav-links{display:none}}
</style>
</head>
<body>
<nav>
  <a href="dashboard.php" class="nav-logo">MQ</a>
  <div class="nav-links">
    <a href="dashboard.php"   class="nav-link">🏠 Home</a>
    <a href="assignments.php" class="nav-link">📋 Assignments</a>
    <a href="leaderboard.php" class="nav-link">🏅 Leaderboard</a>
    <a href="progress.php"    class="nav-link">📈 Progress</a>
    <a href="profile.php"     class="nav-link active">🧙 Profile</a>
  </div>
  <a href="logout.php" class="logout">Sign Out</a>
</nav>
<main class="main">

  <div class="panel" style="animation-delay:.04s">
    <div class="ptitle">🧙 My Profile</div>
    <div class="profile-hero">
      <div class="avatar-wrap"><?=htmlspecialchars($user['avatar'])?></div>
      <div>
        <div class="profile-name"><?=htmlspecialchars($user['full_name'])?></div>
        <div class="profile-role">Level <?=$level?> · <?=htmlspecialchars($user['class_name']??'')?></div>
        <div class="stat-chips">
          <div class="chip">⭐ <?=number_format($xp)?> XP</div>
          <div class="chip">🔥 <?=$streak?> day streak</div>
          <div class="chip">🎯 <?=$accuracy?>% accuracy</div>
        </div>
      </div>
    </div>
    <div style="margin-top:18px">
      <div style="display:flex;justify-content:space-between;font-size:0.78em;color:var(--tdim);margin-bottom:4px">
        <span>XP Progress — Level <?=$level?></span><span><?=number_format($xp%500)?> / 500</span>
      </div>
      <div class="bar" style="height:8px;margin-bottom:0"><div class="bar-fill" style="width:<?=min(100,round(($xp%500)/500*100))?>%"></div></div>
    </div>
    <div class="stat-grid">
      <div class="stat-box"><div class="stat-val"><?=$solved?></div><div class="stat-lbl">Problems Solved</div></div>
      <div class="stat-box"><div class="stat-val"><?=$accuracy?>%</div><div class="stat-lbl">Accuracy</div></div>
      <div class="stat-box"><div class="stat-val"><?=$streak?></div><div class="stat-lbl">Current Streak</div></div>
      <div class="stat-box"><div class="stat-val"><?=$longestStreak?></div><div class="stat-lbl">Longest Streak</div></div>
    </div>
  </div>

  <div class="panel" id="badges" style="animation-delay:.12s">
    <div class="ptitle">🏆 Badges (<?=count($earnedBadges)?>/<?=count($allBadges)?>)</div>
    <div class="badges-grid">
      <?php foreach($allBadges as $b):$earned=in_array($b['slug'],$earnedSlugs);?>
      <div class="badge-card <?=$earned?'':'locked'?>" title="<?=htmlspecialchars($b['description'])?>">
        <div class="badge-ico"><?=$b['icon']?></div>
        <div class="badge-name"><?=htmlspecialchars($b['name'])?></div>
        <?php if($earned):?><div style="font-size:0.6em;color:var(--green);margin-top:3px">✓ Earned</div><?php endif;?>
      </div>
      <?php endforeach;?>
    </div>
  </div>

</main>
</body>
</html>