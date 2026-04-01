<?php
require_once 'config.php';
$user = require_login();

$pdo = db();

// ── Fetch student stats ───────────────────────────────────────
$stats = $pdo->prepare("SELECT * FROM student_stats WHERE user_id = :uid");
$stats->execute([':uid' => $user['id']]);
$s = $stats->fetch() ?: ['xp' => 0, 'streak' => 0, 'level' => 1];

// ── XP progress to next level ─────────────────────────────────
function xp_for_level(int $l): int {
    return (int)(100 * ($l - 1) + 50 * ($l - 1) * ($l - 2));
}
$xp_current    = (int)$s['xp'];
$level         = (int)$s['level'];
$xp_this_level = xp_for_level($level);
$xp_next_level = xp_for_level($level + 1);
$xp_pct        = $xp_next_level > $xp_this_level
    ? min(100, round(($xp_current - $xp_this_level) / ($xp_next_level - $xp_this_level) * 100))
    : 100;

// ── Fetch badges ──────────────────────────────────────────────
$badge_rows = $pdo->prepare("
    SELECT b.name, b.description, b.icon
    FROM user_badges ub
    JOIN badges b ON b.id = ub.badge_id
    WHERE ub.user_id = :uid
");
$badge_rows->execute([':uid' => $user['id']]);
$badges = $badge_rows->fetchAll();

// ── Fetch attempt totals ──────────────────────────────────────
$att = $pdo->prepare("SELECT COUNT(*) as total, SUM(is_correct) as correct FROM attempts WHERE user_id = :uid");
$att->execute([':uid' => $user['id']]);
$a = $att->fetch() ?: ['total' => 0, 'correct' => 0];
$accuracy = $a['total'] ? round($a['correct'] / $a['total'] * 100) : 0;

// ── Handle password change POST ───────────────────────────────
$pw_success = $pw_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current  = $_POST['current_password'] ?? '';
    $new_pw   = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Fetch hash
    $row = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
    $row->execute([':id' => $user['id']]);
    $hash = $row->fetchColumn();

    if (!password_verify($current, $hash)) {
        $pw_error = 'Current password is incorrect.';
    } elseif (strlen($new_pw) < 6) {
        $pw_error = 'New password must be at least 6 characters.';
    } elseif ($new_pw !== $confirm) {
        $pw_error = 'New passwords do not match.';
    } else {
        $new_hash = password_hash($new_pw, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password_hash = :h WHERE id = :id")
            ->execute([':h' => $new_hash, ':id' => $user['id']]);
        $pw_success = 'Password updated successfully!';
    }
}

// ── Avatar initials + colour ──────────────────────────────────
$initials = strtoupper(substr($user['username'], 0, 2));
$avatar_colours = ['#6366f1','#0891b2','#059669','#d97706','#dc2626','#7c3aed','#db2777'];
$avatar_colour  = $avatar_colours[crc32($user['username']) % count($avatar_colours)];

$grade_label = isset($user['grade']) && $user['grade'] ? 'Grade ' . $user['grade'] : 'No grade set';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root,[data-theme="dark"]{--bg:#080b14;--s1:#0e1220;--s2:#141827;--s3:#1a2035;--b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.12);--cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--violet:#7c3aed;--vdim:rgba(124,58,237,0.15);--amber:#ffab00;--adim:rgba(255,171,0,0.15);--green:#00e676;--gdim:rgba(0,230,118,0.12);--red:#ff5252;--rdim:rgba(255,82,82,0.1);--text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68);--nav-bg:rgba(8,11,20,0.97)}
[data-theme="light"]{--bg:#f0f4ff;--s1:#ffffff;--s2:#e8edf8;--s3:#d8e0f0;--b:rgba(0,0,0,0.08);--bb:rgba(0,0,0,0.15);--cyan:#0077cc;--cdim:rgba(0,119,204,0.10);--violet:#6d28d9;--vdim:rgba(109,40,217,0.10);--amber:#c47f00;--adim:rgba(196,127,0,0.12);--green:#00a854;--gdim:rgba(0,168,84,0.10);--red:#cc3333;--rdim:rgba(204,51,51,0.08);--text:#0f1423;--tdim:rgba(15,20,35,0.45);--tmid:rgba(15,20,35,0.72);--nav-bg:rgba(240,244,255,0.97)}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;transition:background 0.3s,color 0.3s}

/* ── Nav ── */
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

/* ── Layout ── */
.main{padding:28px;max-width:1100px;margin:0 auto;display:flex;flex-direction:column;gap:20px}
.panel{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:24px;animation:pIn 0.4s ease backwards;transition:background 0.3s}
@keyframes pIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.ptitle{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.14em;color:var(--tdim);text-transform:uppercase;margin-bottom:18px}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:20px}

/* ── Hero profile card ── */
.profile-hero{display:flex;align-items:center;gap:24px;flex-wrap:wrap}
.avatar{width:80px;height:80px;border-radius:20px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:1.8em;color:#fff;flex-shrink:0;letter-spacing:0.02em;box-shadow:0 4px 24px rgba(0,0,0,0.3)}
.profile-info{flex:1;min-width:0}
.profile-username{font-family:'Syne',sans-serif;font-weight:800;font-size:1.5em;margin-bottom:3px}
.profile-meta{font-size:0.84em;color:var(--tdim);margin-bottom:12px}
.profile-meta span{margin-right:14px}
.role-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:700;letter-spacing:0.06em;text-transform:uppercase}
.role-badge.student{background:var(--cdim);color:var(--cyan);border:1px solid rgba(0,119,204,0.25)}
.role-badge.admin{background:var(--vdim);color:#a78bfa;border:1px solid rgba(124,58,237,0.25)}

/* ── XP bar ── */
.xp-bar-wrap{margin-top:14px}
.xp-bar-meta{display:flex;justify-content:space-between;font-size:0.78em;color:var(--tdim);margin-bottom:6px}
.xp-bar-meta b{color:var(--cyan);font-family:'Syne',sans-serif}
.xp-bar{height:8px;background:var(--s2);border-radius:4px;overflow:hidden}
.xp-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,#0099cc,var(--cyan));transition:width 1.2s cubic-bezier(.4,0,.2,1)}

/* ── Stat mini cards ── */
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:20px}
.stat-mini{background:var(--s2);border:1px solid var(--b);border-radius:10px;padding:14px 16px;text-align:center}
.stat-mini-val{font-family:'Syne',sans-serif;font-weight:800;font-size:1.4em;margin-bottom:2px}
.stat-mini-label{font-size:0.72em;color:var(--tdim)}

/* ── Badges ── */
.badges-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px}
.badge-card{background:var(--s2);border:1px solid var(--b);border-radius:10px;padding:16px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:8px;transition:transform 0.2s,border-color 0.2s}
.badge-card:hover{transform:translateY(-2px);border-color:var(--amber)}
.badge-ico{font-size:2em;line-height:1}
.badge-name{font-family:'Syne',sans-serif;font-size:0.78em;font-weight:700;color:var(--text)}
.badge-desc{font-size:0.72em;color:var(--tdim);line-height:1.4}
.badge-date{font-size:0.68em;color:var(--tdim);margin-top:2px}
.no-badges{text-align:center;padding:32px;color:var(--tdim);font-size:0.88em}
.no-badges .no-badge-ico{font-size:2.5em;margin-bottom:8px}

/* ── Password form ── */
.pw-form{display:flex;flex-direction:column;gap:14px}
.field{display:flex;flex-direction:column;gap:6px}
.field label{font-family:'Syne',sans-serif;font-size:0.7em;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--tdim)}
.field input{width:100%;padding:11px 14px;background:var(--s2);border:1.5px solid var(--b);border-radius:9px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.9em;outline:none;transition:border-color 0.2s,background 0.2s}
.field input:focus{border-color:var(--cyan);background:var(--cdim)}
.btn-save{padding:12px 24px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:9px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.82em;font-weight:700;letter-spacing:0.08em;cursor:pointer;transition:all 0.22s;align-self:flex-start}
.btn-save:hover{transform:translateY(-1px);filter:brightness(1.08);box-shadow:0 6px 20px rgba(0,229,255,0.2)}
.alert{padding:11px 14px;border-radius:9px;font-size:0.85em;font-weight:500}
.alert-success{background:var(--gdim);border:1px solid rgba(0,168,84,0.3);color:var(--green)}
.alert-error{background:var(--rdim);border:1px solid rgba(255,82,82,0.3);color:var(--red)}

/* ── Account info ── */
.info-rows{display:flex;flex-direction:column;gap:0}
.info-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--b);font-size:0.88em}
.info-row:last-child{border-bottom:none}
.info-row-label{color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.78em;font-weight:600;letter-spacing:0.06em;text-transform:uppercase}
.info-row-val{color:var(--text);font-weight:500}

@media(max-width:768px){.g2{grid-template-columns:1fr}.stat-row{grid-template-columns:repeat(3,1fr)}.profile-hero{gap:16px}.avatar{width:64px;height:64px;font-size:1.4em}}
@media(max-width:600px){.main{padding:16px}.nav-links{display:none}.stat-row{grid-template-columns:1fr 1fr}}
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
  <div class="nav-right">
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">
      <span id="themeIcon">☀️</span><span id="themeLabel">Light</span>
    </button>
    <a href="logout.php" class="logout">Sign Out</a>
  </div>
</nav>

<main class="main">

  <!-- ── Hero card ── -->
  <div class="panel" style="animation-delay:.04s">
    <div class="profile-hero">
      <div class="avatar" style="background:<?= $avatar_colour ?>"><?= htmlspecialchars($initials) ?></div>
      <div class="profile-info">
        <div class="profile-username"><?= htmlspecialchars($user['username']) ?></div>
        <div class="profile-meta">
          <span><?= htmlspecialchars($user['email'] ?? '') ?></span>
          <span><?= $grade_label ?></span>
        </div>
        <span class="role-badge <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div style="font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.1em;color:var(--tdim);text-transform:uppercase;margin-bottom:4px">Level</div>
        <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:2.8em;color:var(--cyan);line-height:1"><?= $level ?></div>
      </div>
    </div>

    <div class="xp-bar-wrap">
      <div class="xp-bar-meta">
        <span><b><?= number_format($xp_current) ?> XP</b> earned</span>
        <span><?= number_format($xp_next_level) ?> XP to level <?= $level + 1 ?></span>
      </div>
      <div class="xp-bar">
        <div class="xp-bar-fill" id="xpFill" style="width:0%"></div>
      </div>
    </div>

    <div class="stat-row">
      <div class="stat-mini">
        <div class="stat-mini-val" style="color:var(--green)"><?= (int)$a['correct'] ?></div>
        <div class="stat-mini-label">Problems Solved</div>
      </div>
      <div class="stat-mini">
        <div class="stat-mini-val" style="color:var(--amber)"><?= $accuracy ?>%</div>
        <div class="stat-mini-label">Accuracy</div>
      </div>
      <div class="stat-mini">
        <div class="stat-mini-val" style="color:var(--red)">🔥 <?= (int)$s['streak'] ?></div>
        <div class="stat-mini-label">Day Streak</div>
      </div>
    </div>
  </div>

  <!-- ── Badges + Account info ── -->
  <div class="g2">

    <!-- Badges -->
    <div class="panel" style="animation-delay:.08s">
      <div class="ptitle">🏅 Badges Earned (<?= count($badges) ?>)</div>
      <?php if (empty($badges)): ?>
        <div class="no-badges">
          <div class="no-badge-ico">🎖️</div>
          <div>No badges yet — keep solving problems to unlock them!</div>
        </div>
      <?php else: ?>
        <div class="badges-grid">
          <?php foreach ($badges as $b): ?>
            <div class="badge-card">
              <div class="badge-ico"><?= htmlspecialchars($b['icon'] ?? '🏅') ?></div>
              <div class="badge-name"><?= htmlspecialchars($b['name']) ?></div>
              <div class="badge-desc"><?= htmlspecialchars($b['description'] ?? '') ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Account info -->
    <div class="panel" style="animation-delay:.12s">
      <div class="ptitle">👤 Account Info</div>
      <div class="info-rows">
        <div class="info-row">
          <span class="info-row-label">Username</span>
          <span class="info-row-val"><?= htmlspecialchars($user['username']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-row-label">Email</span>
          <span class="info-row-val"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
        </div>
        <div class="info-row">
          <span class="info-row-label">Grade</span>
          <span class="info-row-val"><?= $grade_label ?></span>
        </div>
        <div class="info-row">
          <span class="info-row-label">Role</span>
          <span class="info-row-val"><?= ucfirst($user['role']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-row-label">Total Attempts</span>
          <span class="info-row-val"><?= (int)$a['total'] ?></span>
        </div>
        <div class="info-row">
          <span class="info-row-label">Total XP</span>
          <span class="info-row-val" style="color:var(--cyan);font-family:'Syne',sans-serif;font-weight:700"><?= number_format($xp_current) ?></span>
        </div>
      </div>
    </div>

  </div>

  <!-- ── Change Password ── -->
  <div class="panel" style="animation-delay:.16s">
    <div class="ptitle">🔒 Change Password</div>

    <?php if ($pw_success): ?>
      <div class="alert alert-success" style="margin-bottom:16px">✓ <?= htmlspecialchars($pw_success) ?></div>
    <?php endif; ?>
    <?php if ($pw_error): ?>
      <div class="alert alert-error" style="margin-bottom:16px">✗ <?= htmlspecialchars($pw_error) ?></div>
    <?php endif; ?>

    <form method="POST" class="pw-form" style="max-width:440px">
      <input type="hidden" name="change_password" value="1">
      <div class="field">
        <label>Current Password</label>
        <input type="password" name="current_password" required placeholder="Enter current password">
      </div>
      <div class="field">
        <label>New Password</label>
        <input type="password" name="new_password" required placeholder="At least 6 characters" minlength="6">
      </div>
      <div class="field">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required placeholder="Repeat new password">
      </div>
      <button type="submit" class="btn-save">🔒 Update Password</button>
    </form>
  </div>

</main>

<script>
// ── Theme ──────────────────────────────────────────────────────
const html = document.documentElement;
applyTheme(localStorage.getItem('mq_theme') || 'dark');
function applyTheme(t) {
  html.setAttribute('data-theme', t);
  localStorage.setItem('mq_theme', t);
  document.getElementById('themeIcon').textContent  = t === 'dark' ? '☀️' : '🌙';
  document.getElementById('themeLabel').textContent = t === 'dark' ? 'Light' : 'Dark';
}
function toggleTheme() { applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'); }

// ── Animate XP bar on load ─────────────────────────────────────
window.addEventListener('load', () => {
  setTimeout(() => {
    document.getElementById('xpFill').style.width = '<?= $xp_pct ?>%';
  }, 300);
});
</script>
</body>
</html>