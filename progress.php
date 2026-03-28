<?php
require_once 'config.php';
$user = require_login('student');

$stats = db()->prepare('SELECT * FROM student_stats WHERE user_id=?');
$stats->execute([$user['id']]);
$s = $stats->fetch() ?: [];
$xp=$s['xp']??0; $level=$s['level']??1; $streak=$s['streak']??0;
$solved=$s['problems_solved']??0; $correct=$s['problems_correct']??0;
$accuracy=$solved>0?round($correct/$solved*100):0;

// 30-day daily attempts
$daily = db()->prepare('SELECT DATE(attempted_at) as day, COUNT(*) as total, SUM(is_correct) as correct FROM attempts WHERE user_id=? AND attempted_at>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY DATE(attempted_at) ORDER BY day ASC');
$daily->execute([$user['id']]);
$dailyRows=$daily->fetchAll();

// Per subject
$bySubj = db()->prepare('SELECT subject, COUNT(*) as total, SUM(is_correct) as correct, AVG(time_taken) as avg_time FROM attempts WHERE user_id=? GROUP BY subject');
$bySubj->execute([$user['id']]);
$subjData=$bySubj->fetchAll();

// Recent attempts
$recent = db()->prepare('SELECT subject,question,user_answer,correct_answer,is_correct,xp_earned,attempted_at FROM attempts WHERE user_id=? ORDER BY attempted_at DESC LIMIT 15');
$recent->execute([$user['id']]);
$recentRows=$recent->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Progress</title>
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
.main{padding:28px;max-width:1200px;margin:0 auto}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em;margin-bottom:6px}
.page-sub{color:var(--tdim);font-size:0.9em;margin-bottom:28px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px}
.panel{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:22px;animation:pIn 0.4s ease backwards}
@keyframes pIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.ptitle{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.14em;color:var(--tdim);text-transform:uppercase;margin-bottom:16px}
.scard{padding:18px 20px}
.sc-val{font-family:'Syne',sans-serif;font-weight:800;font-size:2em;line-height:1;margin-bottom:3px}
.sc-val.cyan{color:var(--cyan)}.sc-val.green{color:var(--green)}.sc-val.amber{color:var(--amber)}.sc-val.violet{color:#a78bfa}
.sc-label{font-size:0.78em;color:var(--tdim)}
.sc-ico{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1em;margin-bottom:10px}
.sc-ico.cyan{background:var(--cdim);border:1px solid rgba(0,229,255,0.2)}
.sc-ico.green{background:var(--gdim);border:1px solid rgba(0,230,118,0.2)}
.sc-ico.amber{background:var(--adim);border:1px solid rgba(255,171,0,0.25)}
.sc-ico.violet{background:var(--vdim);border:1px solid rgba(124,58,237,0.25)}
.bar{height:6px;background:var(--s2);border-radius:3px;overflow:hidden;margin:6px 0 12px}
.bar-fill{height:100%;border-radius:3px}
table{width:100%;border-collapse:collapse}
th{padding:9px 12px;text-align:left;font-family:'Syne',sans-serif;font-size:0.63em;font-weight:600;letter-spacing:0.1em;color:var(--tdim);text-transform:uppercase;border-bottom:1px solid var(--b)}
td{padding:11px 12px;font-size:0.87em;border-bottom:1px solid rgba(255,255,255,0.04)}
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
  <a href="logout.php" class="logout">Sign Out</a>
</nav>
<main class="main">
  <div class="page-title">📈 Progress Report</div>
  <div class="page-sub">Your full learning history — see where you're strong and where to improve.</div>

  <div class="g4">
    <div class="panel scard" style="animation-delay:.04s"><div class="sc-ico cyan">⭐</div><div class="sc-val cyan"><?=number_format($xp)?></div><div class="sc-label">Total XP</div></div>
    <div class="panel scard" style="animation-delay:.08s"><div class="sc-ico green">✓</div><div class="sc-val green"><?=$solved?></div><div class="sc-label">Problems Solved</div></div>
    <div class="panel scard" style="animation-delay:.12s"><div class="sc-ico amber">🎯</div><div class="sc-val amber"><?=$accuracy?>%</div><div class="sc-label">Accuracy</div></div>
    <div class="panel scard" style="animation-delay:.16s"><div class="sc-ico violet">🔥</div><div class="sc-val violet"><?=$streak?></div><div class="sc-label">Current Streak</div></div>
  </div>

  <div class="g2">
    <div class="panel" style="animation-delay:.2s">
      <div class="ptitle">📚 Subject Breakdown</div>
      <?php
      $colors=['algebra'=>'var(--cyan)','arithmetic'=>'var(--green)','fractions'=>'var(--amber)','geometry'=>'#a78bfa','statistics'=>'var(--red)'];
      foreach($subjData as $row):
        $acc=$row['total']>0?round($row['correct']/$row['total']*100):0;
        $col=$colors[strtolower($row['subject'])]??'var(--cyan)';
      ?>
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;font-size:0.83em;margin-bottom:4px">
          <span><?=htmlspecialchars(ucfirst($row['subject']))?></span>
          <span style="color:var(--tdim)"><?=$row['correct']?>/<?=$row['total']?> correct · <b style="color:<?=$col?>"><?=$acc?>%</b></span>
        </div>
        <div class="bar"><div class="bar-fill" style="width:<?=$acc?>%;background:<?=$col?>"></div></div>
      </div>
      <?php endforeach;?>
      <?php if(empty($subjData)):?><div style="color:var(--tdim);font-size:0.85em;font-style:italic">No attempts yet. Start solving!</div><?php endif;?>
    </div>

    <div class="panel" style="animation-delay:.24s">
      <div class="ptitle">📅 30-Day Activity</div>
      <?php if(empty($dailyRows)):?>
        <div style="color:var(--tdim);font-size:0.85em;font-style:italic">No activity in the last 30 days.</div>
      <?php else:?>
        <div style="display:flex;align-items:flex-end;gap:3px;height:80px">
          <?php
          $maxTotal=max(array_column($dailyRows,'total'))?:1;
          foreach($dailyRows as $d):
            $h=round(($d['total']/$maxTotal)*80);
            $acc=$d['total']>0?round($d['correct']/$d['total']*100):0;
            $col=$acc>=80?'var(--green)':($acc>=50?'var(--amber)':'var(--red)');
          ?>
          <div title="<?=$d['day']?>: <?=$d['total']?> attempts, <?=$acc?>% accuracy" style="flex:1;height:<?=$h?>px;background:<?=$col?>;border-radius:3px 3px 0 0;opacity:0.8;cursor:pointer;transition:opacity 0.2s" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8"></div>
          <?php endforeach;?>
        </div>
        <div style="font-size:0.72em;color:var(--tdim);margin-top:8px;text-align:center"><?=count($dailyRows)?> active days · <?=array_sum(array_column($dailyRows,'total'))?> total attempts</div>
      <?php endif;?>
    </div>
  </div>

  <div class="panel" style="animation-delay:.28s">
    <div class="ptitle">🕐 Recent Attempts</div>
    <?php if(empty($recentRows)):?>
      <div style="color:var(--tdim);font-size:0.85em;font-style:italic;padding:10px 0">No attempts yet. Go solve some problems!</div>
    <?php else:?>
    <div style="overflow-x:auto">
      <table>
        <thead><tr><th>Subject</th><th>Question</th><th>Your Answer</th><th>Correct</th><th>Result</th><th>XP</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach($recentRows as $r):?>
        <tr>
          <td><span style="font-family:'Syne',sans-serif;font-size:0.8em;font-weight:600"><?=htmlspecialchars(ucfirst($r['subject']))?></span></td>
          <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=htmlspecialchars($r['question'])?></td>
          <td><?=htmlspecialchars($r['user_answer'])?></td>
          <td style="color:var(--green)"><?=htmlspecialchars($r['correct_answer'])?></td>
          <td><?=$r['is_correct']?'<span class="correct">✓ Correct</span>':'<span class="wrong">✗ Wrong</span>'?></td>
          <td style="color:var(--cyan);font-family:'Syne',sans-serif;font-weight:600">+<?=$r['xp_earned']?></td>
          <td style="color:var(--tdim)"><?=date('M j',strtotime($r['attempted_at']))?></td>
        </tr>
        <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <?php endif;?>
  </div>
</main>
</body>
</html>