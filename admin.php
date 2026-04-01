<?php
require_once 'config.php';
$user = require_login('admin');

$pdo = db();

// ── Stats overview ───────────────────────────────────────────
$totalStudents = $pdo->query('SELECT COUNT(*) FROM users WHERE role="student"')->fetchColumn();
$totalAttempts = $pdo->query('SELECT COUNT(*) FROM attempts')->fetchColumn();
$avgAccuracy   = $pdo->query('SELECT ROUND(AVG(is_correct)*100) FROM attempts')->fetchColumn() ?? 0;
$totalAssign   = $pdo->query('SELECT COUNT(*) FROM assignments')->fetchColumn();

// ── Students list ────────────────────────────────────────────
// email and grade removed — not in the users table
$students = $pdo->query('
    SELECT u.id, u.username,
           COALESCE(s.xp,0)     as xp,
           COALESCE(s.level,1)  as level,
           COALESCE(s.streak,0) as streak,
           (SELECT COUNT(*) FROM attempts a WHERE a.user_id=u.id AND a.is_correct=1) as solved,
           (SELECT COUNT(*) FROM attempts a WHERE a.user_id=u.id) as total_att
    FROM users u
    LEFT JOIN student_stats s ON s.user_id = u.id
    WHERE u.role = "student"
    ORDER BY xp DESC
')->fetchAll();

// ── Leaderboard top 5 ────────────────────────────────────────
$lb = $pdo->query('
    SELECT u.username, COALESCE(s.xp,0) as xp, COALESCE(s.level,1) as level
    FROM users u
    LEFT JOIN student_stats s ON s.user_id=u.id
    WHERE u.role="student"
    ORDER BY xp DESC LIMIT 5
')->fetchAll();

// ── Assignments ──────────────────────────────────────────────
$assignments = $pdo->query('
    SELECT a.id, a.title, a.subject, a.difficulty, a.due_date, a.num_problems
    FROM assignments a
    ORDER BY a.due_date ASC
')->fetchAll();

// ── Badges ───────────────────────────────────────────────────
$allBadges = $pdo->query('
    SELECT b.id, b.name, b.description, b.icon,
           COUNT(ub.user_id) as earned_count
    FROM badges b
    LEFT JOIN user_badges ub ON ub.badge_id = b.id
    GROUP BY b.id
    ORDER BY b.id
')->fetchAll();

// ── Recent attempts ──────────────────────────────────────────
$attempts = $pdo->query('
    SELECT a.question, a.subject, a.difficulty, a.is_correct,
           a.time_taken, a.attempted_at, u.username
    FROM attempts a
    JOIN users u ON u.id = a.user_id
    ORDER BY a.attempted_at DESC
    LIMIT 20
')->fetchAll();

// ── Subject accuracy ─────────────────────────────────────────
$subjStats = $pdo->query('
    SELECT subject, ROUND(AVG(is_correct)*100) as acc, COUNT(*) as total
    FROM attempts GROUP BY subject ORDER BY total DESC
')->fetchAll();

// ── Flash message from admin_actions.php redirect ────────────
$flash = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
:root,[data-theme="dark"]{
  --bg:#080b14;--s1:#0e1220;--s2:#141827;--s3:#1a2035;
  --b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.12);
  --cyan:#00e5ff;--cdim:rgba(0,229,255,0.10);
  --violet:#7c3aed;--vdim:rgba(124,58,237,0.15);
  --amber:#ffab00;--adim:rgba(255,171,0,0.15);
  --green:#00e676;--gdim:rgba(0,230,118,0.12);
  --red:#ff5252;--rdim:rgba(255,82,82,0.12);
  --text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68);
  --nav-bg:rgba(8,11,20,0.97);--shadow:0 24px 60px rgba(0,0,0,0.5);
}
[data-theme="light"]{
  --bg:#f0f4ff;--s1:#ffffff;--s2:#e8edf8;--s3:#d8e0f0;
  --b:rgba(0,0,0,0.08);--bb:rgba(0,0,0,0.15);
  --cyan:#0077cc;--cdim:rgba(0,119,204,0.10);
  --violet:#6d28d9;--vdim:rgba(109,40,217,0.10);
  --amber:#c47f00;--adim:rgba(196,127,0,0.12);
  --green:#00a854;--gdim:rgba(0,168,84,0.10);
  --red:#cc3333;--rdim:rgba(204,51,51,0.10);
  --text:#0f1423;--tdim:rgba(15,20,35,0.45);--tmid:rgba(15,20,35,0.72);
  --nav-bg:rgba(240,244,255,0.97);--shadow:0 8px 32px rgba(0,0,0,0.10);
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;transition:background 0.3s,color 0.3s}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:var(--nav-bg);border-bottom:1px solid var(--b);position:sticky;top:0;z-index:100;backdrop-filter:blur(12px);transition:background 0.3s}
.nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15em;background:linear-gradient(135deg,var(--violet),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
.nav-badge{display:inline-block;padding:2px 8px;background:var(--vdim);border:1px solid var(--violet);border-radius:5px;font-family:'Syne',sans-serif;font-size:0.6em;font-weight:700;color:#a78bfa;letter-spacing:0.08em;vertical-align:middle;margin-left:6px}
.nav-links{display:flex;align-items:center;gap:4px}
.nav-link{padding:6px 12px;border-radius:7px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;letter-spacing:0.06em;text-decoration:none;transition:all 0.2s;cursor:pointer;border:none;background:transparent}
.nav-link:hover{background:var(--s2);color:var(--tmid)}
.nav-link.active{background:var(--vdim);color:#a78bfa;border:1px solid rgba(124,58,237,0.3)}
.nav-right{display:flex;align-items:center;gap:10px}
.theme-toggle{background:var(--s2);border:1px solid var(--b);border-radius:20px;padding:5px 12px;cursor:pointer;font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;color:var(--tmid);display:flex;align-items:center;gap:5px;transition:all 0.22s}
.theme-toggle:hover{border-color:var(--bb);color:var(--text)}
.logout{padding:6px 12px;background:var(--rdim);border:1px solid rgba(255,82,82,0.2);border-radius:7px;color:var(--red);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none;transition:all 0.2s}
.logout:hover{background:rgba(255,82,82,0.2)}
.main{padding:24px 28px;max-width:1400px;margin:0 auto}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.panel{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:22px;position:relative;overflow:hidden;transition:border-color 0.25s,background 0.3s;animation:pIn 0.45s ease backwards}
.panel:hover{border-color:var(--bb)}
@keyframes pIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
.ptitle{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.14em;color:var(--tdim);text-transform:uppercase;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:8px}
.scard{padding:18px 20px}
.sc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.sc-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1em}
.sc-icon.violet{background:var(--vdim);border:1px solid rgba(124,58,237,0.25)}
.sc-icon.cyan{background:var(--cdim);border:1px solid rgba(0,119,204,0.2)}
.sc-icon.amber{background:var(--adim);border:1px solid rgba(255,171,0,0.25)}
.sc-icon.green{background:var(--gdim);border:1px solid rgba(0,230,118,0.2)}
.sc-val{font-family:'Syne',sans-serif;font-weight:800;font-size:2em;line-height:1;margin-bottom:3px}
.sc-val.violet{color:#a78bfa}.sc-val.cyan{color:var(--cyan)}.sc-val.amber{color:var(--amber)}.sc-val.green{color:var(--green)}
.sc-label{font-size:0.78em;color:var(--tdim)}
.section{display:none}.section.active{display:block}
.tbl{width:100%;border-collapse:collapse;font-size:0.85em}
.tbl th{font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.1em;color:var(--tdim);text-transform:uppercase;padding:8px 12px;text-align:left;border-bottom:1px solid var(--b)}
.tbl td{padding:10px 12px;border-bottom:1px solid var(--b);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:var(--s2)}
.pill{display:inline-block;padding:2px 9px;border-radius:20px;font-family:'Syne',sans-serif;font-size:0.7em;font-weight:600}
.pill-easy{background:var(--gdim);color:var(--green);border:1px solid var(--green)}
.pill-med{background:var(--adim);color:var(--amber);border:1px solid var(--amber)}
.pill-hard{background:var(--rdim);color:var(--red);border:1px solid var(--red)}
.pill-ok{background:var(--gdim);color:var(--green)}
.pill-fail{background:var(--rdim);color:var(--red)}
.bar{height:6px;background:var(--s2);border-radius:3px;overflow:hidden}
.bar-fill{height:100%;border-radius:3px}
.bar-fill.violet{background:linear-gradient(90deg,var(--violet),#a78bfa)}
.bar-fill.cyan{background:linear-gradient(90deg,#0077cc,var(--cyan))}
.bar-fill.green{background:var(--green)}.bar-fill.amber{background:var(--amber)}.bar-fill.red{background:var(--red)}
.btn{padding:8px 16px;border-radius:8px;font-family:'Syne',sans-serif;font-size:0.75em;font-weight:700;letter-spacing:0.07em;cursor:pointer;border:none;transition:all 0.2s}
.btn-primary{background:linear-gradient(135deg,var(--violet),#a78bfa);color:#fff;box-shadow:0 4px 14px var(--vdim)}
.btn-primary:hover{transform:translateY(-1px);filter:brightness(1.08)}
.btn-danger{background:var(--rdim);border:1px solid rgba(255,82,82,0.3);color:var(--red)}
.btn-danger:hover{background:var(--red);color:#fff}
.badge-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
.badge-card{background:var(--s2);border:1px solid var(--b);border-radius:10px;padding:16px;text-align:center;transition:all 0.2s}
.badge-card:hover{border-color:var(--bb);transform:translateY(-2px)}
.badge-card-icon{font-size:2em;margin-bottom:8px}
.badge-card-name{font-family:'Syne',sans-serif;font-weight:700;font-size:0.85em;margin-bottom:3px}
.badge-card-desc{font-size:0.74em;color:var(--tdim);margin-bottom:10px}
.badge-card-count{font-family:'Syne',sans-serif;font-weight:700;font-size:1.1em;color:#a78bfa}
.badge-card-label{font-size:0.7em;color:var(--tdim)}
.lb-item{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:9px;margin-bottom:7px;background:var(--s2);border:1px solid var(--b);transition:all 0.2s}
.lb-item:hover{border-color:var(--bb)}
.lb-item.gold{background:rgba(255,171,0,0.07);border-color:rgba(255,171,0,0.3)}
.lb-item.silver{background:rgba(192,192,192,0.07);border-color:rgba(192,192,192,0.2)}
.lb-item.bronze{background:rgba(205,127,50,0.07);border-color:rgba(205,127,50,0.2)}
.lb-rank{font-family:'Syne',sans-serif;font-weight:800;font-size:1.1em;width:28px;flex-shrink:0}
.lb-av{width:36px;height:36px;border-radius:50%;background:var(--vdim);border:1px solid var(--violet);display:flex;align-items:center;justify-content:center;font-size:1em;font-family:'Syne',sans-serif;font-weight:700;color:#a78bfa;flex-shrink:0}
.lb-info{flex:1}
.lb-name{font-family:'Syne',sans-serif;font-weight:600;font-size:0.9em}
.lb-sub{font-size:0.75em;color:var(--tdim);margin-top:1px}
.lb-xp{font-family:'Syne',sans-serif;font-weight:700;font-size:0.9em;color:#a78bfa}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.form-field{display:flex;flex-direction:column;gap:5px}
.form-label{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--tdim)}
.form-input,.form-select{background:var(--s2);border:1px solid var(--b);border-radius:8px;padding:9px 12px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.9em;outline:none;transition:all 0.2s;width:100%}
.form-input:focus,.form-select:focus{border-color:var(--violet);box-shadow:0 0 0 3px var(--vdim)}
.form-input::placeholder{color:var(--tdim)}
.attempt-item{display:flex;align-items:center;gap:12px;padding:10px 12px;background:var(--s2);border:1px solid var(--b);border-radius:9px;margin-bottom:7px;transition:background 0.2s}
.attempt-item:hover{background:var(--s3)}
.attempt-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.attempt-dot.ok{background:var(--green)}.attempt-dot.fail{background:var(--red)}
.subj-row{margin-bottom:14px}
.subj-head{display:flex;justify-content:space-between;font-size:0.82em;margin-bottom:5px}
.alert{padding:11px 16px;border-radius:9px;font-size:0.85em;font-weight:500;margin-bottom:18px}
.alert-success{background:var(--gdim);border:1px solid rgba(0,168,84,0.3);color:var(--green)}
.alert-error{background:var(--rdim);border:1px solid rgba(255,82,82,0.3);color:var(--red)}
.empty{color:var(--tdim);font-size:0.85em;font-style:italic;padding:12px 0}
@media(max-width:900px){.g4{grid-template-columns:1fr 1fr}.form-row{grid-template-columns:1fr}}
@media(max-width:600px){.main{padding:14px}.g2,.g4{grid-template-columns:1fr}.nav-links{display:none}}
</style>
</head>
<body>
<nav>
  <div style="display:flex;align-items:center;gap:8px">
    <a href="admin.php" class="nav-logo">MQ</a>
    <span class="nav-badge">ADMIN</span>
  </div>
  <div class="nav-links">
    <button class="nav-link active" onclick="showTab('overview',this)">📊 Overview</button>
    <button class="nav-link" onclick="showTab('students',this)">👥 Students</button>
    <button class="nav-link" onclick="showTab('assignments',this)">📋 Assignments</button>
    <button class="nav-link" onclick="showTab('leaderboard',this)">🏅 Leaderboard</button>
    <button class="nav-link" onclick="showTab('badges',this)">🏆 Badges</button>
    <button class="nav-link" onclick="showTab('attempts',this)">📜 Attempts</button>
  </div>
  <div class="nav-right">
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">
      <span id="themeIcon">☀️</span><span id="themeLabel">Light</span>
    </button>
    <a href="logout.php" class="logout">Sign Out</a>
  </div>
</nav>

<main class="main">

<?php if($flash): ?>
  <div class="alert alert-success">✓ <?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- ── OVERVIEW ──────────────────────────────────────────── -->
<div class="section active" id="tab-overview">
  <div class="g4">
    <div class="panel scard" style="animation-delay:.04s">
      <div class="sc-top"><div class="sc-icon violet">👥</div></div>
      <div class="sc-val violet"><?= $totalStudents ?></div>
      <div class="sc-label">Total Students</div>
    </div>
    <div class="panel scard" style="animation-delay:.08s">
      <div class="sc-top"><div class="sc-icon cyan">📝</div></div>
      <div class="sc-val cyan"><?= number_format($totalAttempts) ?></div>
      <div class="sc-label">Total Attempts</div>
    </div>
    <div class="panel scard" style="animation-delay:.12s">
      <div class="sc-top"><div class="sc-icon green">🎯</div></div>
      <div class="sc-val green"><?= $avgAccuracy ?>%</div>
      <div class="sc-label">Class Avg Accuracy</div>
    </div>
    <div class="panel scard" style="animation-delay:.16s">
      <div class="sc-top"><div class="sc-icon amber">📋</div></div>
      <div class="sc-val amber"><?= $totalAssign ?></div>
      <div class="sc-label">Assignments</div>
    </div>
  </div>

  <div class="g2">
    <div class="panel" style="animation-delay:.2s">
      <div class="ptitle">📊 Subject Performance</div>
      <?php if(empty($subjStats)): ?>
        <div class="empty">No attempt data yet.</div>
      <?php else: foreach($subjStats as $s):
        $col = $s['acc']>=80?'green':($s['acc']>=50?'amber':'red');
      ?>
      <div class="subj-row">
        <div class="subj-head">
          <span style="font-weight:500;text-transform:capitalize"><?= htmlspecialchars($s['subject']) ?></span>
          <span style="color:var(--tdim)"><?= $s['acc'] ?>% · <?= $s['total'] ?> attempts</span>
        </div>
        <div class="bar"><div class="bar-fill <?= $col ?>" style="width:<?= $s['acc'] ?>%"></div></div>
      </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="panel" style="animation-delay:.24s">
      <div class="ptitle">🏅 Top Students</div>
      <?php if(empty($lb)): ?>
        <div class="empty">No students yet.</div>
      <?php else: foreach($lb as $i=>$row):
        $cls=$i===0?'gold':($i===1?'silver':($i===2?'bronze':''));
        $medal=$i===0?'🥇':($i===1?'🥈':($i===2?'🥉':($i+1)));
        $initials=strtoupper(substr($row['username'],0,2));
      ?>
      <div class="lb-item <?= $cls ?>">
        <span class="lb-rank"><?= $medal ?></span>
        <div class="lb-av"><?= htmlspecialchars($initials) ?></div>
        <div class="lb-info">
          <div class="lb-name"><?= htmlspecialchars($row['username']) ?></div>
          <div class="lb-sub">Level <?= $row['level'] ?></div>
        </div>
        <span class="lb-xp"><?= number_format($row['xp']) ?> XP</span>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- ── STUDENTS ───────────────────────────────────────────── -->
<div class="section" id="tab-students">
  <div class="panel">
    <div class="ptitle">
      👥 All Students
      <span style="color:var(--tdim);font-size:0.9em;font-weight:400;text-transform:none"><?= $totalStudents ?> enrolled</span>
    </div>
    <?php if(empty($students)): ?>
      <div class="empty">No students enrolled yet.</div>
    <?php else: ?>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <!-- email and grade columns removed — not in DB -->
            <th>Username</th><th>XP</th>
            <th>Level</th><th>Solved</th><th>Accuracy</th><th>Streak</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($students as $s):
            $acc = $s['total_att'] > 0 ? round($s['solved']/$s['total_att']*100) : 0;
            $initials = strtoupper(substr($s['username'],0,2));
          ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:9px">
                <div style="width:28px;height:28px;border-radius:50%;background:var(--vdim);border:1px solid var(--violet);display:flex;align-items:center;justify-content:center;font-size:0.75em;font-family:'Syne',sans-serif;font-weight:700;color:#a78bfa;flex-shrink:0"><?= htmlspecialchars($initials) ?></div>
                <span style="font-family:'Syne',sans-serif;font-weight:600;font-size:0.9em"><?= htmlspecialchars($s['username']) ?></span>
              </div>
            </td>
            <td style="font-family:'Syne',sans-serif;font-weight:700;color:#a78bfa"><?= number_format($s['xp']) ?></td>
            <td style="font-family:'Syne',sans-serif;font-weight:600;color:var(--cyan)"><?= $s['level'] ?></td>
            <td><?= $s['solved'] ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px;min-width:80px">
                <div class="bar" style="flex:1;height:5px">
                  <div class="bar-fill <?= $acc>=80?'green':($acc>=50?'amber':'red') ?>" style="width:<?= $acc ?>%"></div>
                </div>
                <span style="font-size:0.8em;color:var(--tdim)"><?= $acc ?>%</span>
              </div>
            </td>
            <td><span style="color:var(--amber);font-family:'Syne',sans-serif;font-weight:600"><?= $s['streak'] ?>🔥</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── ASSIGNMENTS ────────────────────────────────────────── -->
<div class="section" id="tab-assignments">
  <div class="g2">
    <div class="panel">
      <div class="ptitle">📋 All Assignments</div>
      <?php if(empty($assignments)): ?>
        <div class="empty">No assignments yet — create one!</div>
      <?php else: ?>
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead>
            <tr><th>Title</th><th>Subject</th><th>Difficulty</th><th>Due Date</th><th>Problems</th></tr>
          </thead>
          <tbody>
            <?php foreach($assignments as $a):
              $diff=$a['difficulty'];
              $pillCls=$diff==='easy'?'pill-easy':($diff==='medium'?'pill-med':'pill-hard');
              $dl=$a['due_date']?(int)ceil((strtotime($a['due_date'])-time())/86400):999;
            ?>
            <tr>
              <td style="font-family:'Syne',sans-serif;font-weight:600;font-size:0.88em"><?= htmlspecialchars($a['title']) ?></td>
              <td style="font-size:0.85em;text-transform:capitalize"><?= htmlspecialchars($a['subject']) ?></td>
              <td><span class="pill <?= $pillCls ?>"><?= ucfirst($diff) ?></span></td>
              <td style="font-size:0.82em;color:<?= $dl<=0?'var(--red)':($dl<=3?'var(--amber)':'var(--tdim)') ?>">
                <?= $a['due_date'] ? date('M j, Y', strtotime($a['due_date'])) : '—' ?>
                <?= $dl<=0?' (overdue)':($dl===1?' (tomorrow)':'') ?>
              </td>
              <td style="font-family:'Syne',sans-serif;font-weight:600;color:var(--cyan)"><?= $a['num_problems'] ?? '—' ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="ptitle">➕ New Assignment</div>
      <form method="POST" action="admin_actions.php?action=create_assignment">
        <div class="form-row">
          <div class="form-field">
            <label class="form-label">Title</label>
            <input class="form-input" type="text" name="title" placeholder="Assignment title" required>
          </div>
          <div class="form-field">
            <label class="form-label">Subject</label>
            <select class="form-select" name="subject">
              <option value="algebra">Algebra</option>
              <option value="arithmetic">Arithmetic</option>
              <option value="fractions">Fractions</option>
              <option value="geometry">Geometry</option>
              <option value="statistics">Statistics</option>
              <option value="wordproblems">Word Problems</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-field">
            <label class="form-label">Difficulty</label>
            <select class="form-select" name="difficulty">
              <option value="easy">Easy</option>
              <option value="medium">Medium</option>
              <option value="hard">Hard</option>
            </select>
          </div>
          <div class="form-field">
            <label class="form-label">Due Date</label>
            <input class="form-input" type="date" name="due_date">
          </div>
        </div>
        <div class="form-row">
          <div class="form-field">
            <label class="form-label">Number of Problems</label>
            <input class="form-input" type="number" name="num_problems" value="10" min="1" max="50">
          </div>
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%;padding:11px;margin-top:4px">➕ CREATE ASSIGNMENT</button>
      </form>
    </div>
  </div>
</div>

<!-- ── LEADERBOARD ────────────────────────────────────────── -->
<div class="section" id="tab-leaderboard">
  <div class="panel" style="max-width:640px;margin:0 auto">
    <div class="ptitle">🏅 Full Class Leaderboard</div>
    <?php if(empty($students)): ?>
      <div class="empty">No students yet.</div>
    <?php else: foreach($students as $i=>$s):
      $cls=$i===0?'gold':($i===1?'silver':($i===2?'bronze':''));
      $medal=$i===0?'🥇':($i===1?'🥈':($i===2?'🥉':$i+1));
      $acc=$s['total_att']>0?round($s['solved']/$s['total_att']*100):0;
      $initials=strtoupper(substr($s['username'],0,2));
    ?>
    <div class="lb-item <?= $cls ?>">
      <span class="lb-rank" style="font-size:<?= $i<3?'1.3em':'0.9em' ?>;color:<?= $i<3?'var(--amber)':'var(--tdim)' ?>"><?= $medal ?></span>
      <div class="lb-av"><?= htmlspecialchars($initials) ?></div>
      <div class="lb-info">
        <div class="lb-name"><?= htmlspecialchars($s['username']) ?></div>
        <div class="lb-sub">Level <?= $s['level'] ?> · <?= $s['solved'] ?> solved · <?= $acc ?>% accuracy</div>
      </div>
      <div style="text-align:right">
        <div class="lb-xp"><?= number_format($s['xp']) ?> XP</div>
        <div style="font-size:0.72em;color:var(--amber)"><?= $s['streak'] ?>🔥</div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- ── BADGES ─────────────────────────────────────────────── -->
<div class="section" id="tab-badges">
  <div class="panel">
    <div class="ptitle">🏆 Badge Overview <span style="color:var(--tdim);font-size:0.9em;font-weight:400;text-transform:none">Students earned per badge</span></div>
    <?php if(empty($allBadges)): ?>
      <div class="empty">No badges defined yet.</div>
    <?php else: ?>
    <div class="badge-grid">
      <?php foreach($allBadges as $b): ?>
      <div class="badge-card">
        <div class="badge-card-icon"><?= htmlspecialchars($b['icon'] ?? '🏅') ?></div>
        <div class="badge-card-name"><?= htmlspecialchars($b['name']) ?></div>
        <div class="badge-card-desc"><?= htmlspecialchars($b['description'] ?? '') ?></div>
        <div class="badge-card-count"><?= (int)$b['earned_count'] ?></div>
        <div class="badge-card-label">students earned</div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── ATTEMPTS ───────────────────────────────────────────── -->
<div class="section" id="tab-attempts">
  <div class="panel">
    <div class="ptitle">📜 Recent Attempt History <span style="color:var(--tdim);font-size:0.9em;font-weight:400;text-transform:none">Last 20 attempts</span></div>
    <?php if(empty($attempts)): ?>
      <div class="empty">No attempts recorded yet.</div>
    <?php else: foreach($attempts as $a): ?>
    <div class="attempt-item">
      <div class="attempt-dot <?= $a['is_correct']?'ok':'fail' ?>"></div>
      <div style="width:28px;height:28px;border-radius:50%;background:var(--vdim);border:1px solid var(--violet);display:flex;align-items:center;justify-content:center;font-size:0.75em;font-family:'Syne',sans-serif;font-weight:700;color:#a78bfa;flex-shrink:0"><?= strtoupper(substr($a['username'],0,2)) ?></div>
      <div style="flex:1;min-width:0">
        <div style="font-size:0.85em;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
          <?= htmlspecialchars(substr($a['question'] ?: '(no question text)', 0, 70)) ?>
        </div>
        <div style="font-size:0.74em;color:var(--tdim);margin-top:2px">
          <?= htmlspecialchars($a['username']) ?> ·
          <span style="text-transform:capitalize"><?= htmlspecialchars($a['subject']) ?></span> ·
          <span class="pill <?= $a['difficulty']==='easy'?'pill-easy':($a['difficulty']==='medium'?'pill-med':'pill-hard') ?>"><?= $a['difficulty'] ?></span> ·
          <?= date('M j, g:ia', strtotime($a['attempted_at'])) ?>
          <?= $a['time_taken'] ? ' · '.gmdate('i:s',$a['time_taken']) : '' ?>
        </div>
      </div>
      <span class="pill <?= $a['is_correct']?'pill-ok':'pill-fail' ?>"><?= $a['is_correct']?'✓ Correct':'✗ Wrong' ?></span>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

</main>

<script>
const html = document.documentElement;
applyTheme(localStorage.getItem('mq_theme') || 'dark');
function applyTheme(t){
  html.setAttribute('data-theme',t);
  localStorage.setItem('mq_theme',t);
  document.getElementById('themeIcon').textContent  = t==='dark'?'☀️':'🌙';
  document.getElementById('themeLabel').textContent = t==='dark'?'Light':'Dark';
}
function toggleTheme(){ applyTheme(html.getAttribute('data-theme')==='dark'?'light':'dark'); }

function showTab(name, btn){
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-link').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  btn.classList.add('active');
}

// Re-open correct tab after assignment form submit
const urlTab = new URLSearchParams(window.location.search).get('tab');
if(urlTab){
  const btn = document.querySelector(`.nav-link[onclick*="${urlTab}"]`);
  if(btn) showTab(urlTab, btn);
}
</script>

<?php require_once 'chat_bubble.php'; ?>
</body>
</html>