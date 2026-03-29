<?php
require_once 'config.php';
require_login();

$user = current_user();

// Expect: ?assignment_id=X&attempt_id=Y
$assignment_id = (int) ($_GET['assignment_id'] ?? 0);
$attempt_id    = (int) ($_GET['attempt_id']    ?? 0);

$assignment = null;
$attempt    = null;
$next_assignment = null;

try {
    $pdo = db();

    if ($assignment_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = :id");
        $stmt->execute([':id' => $assignment_id]);
        $assignment = $stmt->fetch();
    }

    if ($attempt_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM attempts WHERE id = :id AND student_id = :uid");
        $stmt->execute([':id' => $attempt_id, ':uid' => $user['id']]);
        $attempt = $stmt->fetch();
    }

    // Fetch a next assignment the student hasn't completed correctly yet
    $stmt = $pdo->prepare("
        SELECT a.* FROM assignments a
        WHERE a.id != :current
          AND a.id NOT IN (
              SELECT assignment_id FROM attempts
              WHERE student_id = :uid AND is_correct = 1
          )
        ORDER BY RAND()
        LIMIT 1
    ");
    $stmt->execute([':current' => $assignment_id ?: 0, ':uid' => $user['id']]);
    $next_assignment = $stmt->fetch();

    // Stats for the sidebar
    $ss = $pdo->prepare("SELECT xp, streak, total_correct, total_attempts FROM student_stats WHERE student_id = :id");
    $ss->execute([':id' => $user['id']]);
    $stats = $ss->fetch() ?? ['xp' => 0, 'streak' => 0, 'total_correct' => 0, 'total_attempts' => 0];

} catch (PDOException $e) {
    error_log('[feedback] ' . $e->getMessage());
    $stats = ['xp' => 0, 'streak' => 0, 'total_correct' => 0, 'total_attempts' => 0];
}

$topic      = htmlspecialchars($assignment['topic']       ?? 'Math');
$title      = htmlspecialchars($assignment['title']       ?? 'Problem');
$difficulty = $assignment['difficulty'] ?? 'medium';
$given      = htmlspecialchars($attempt['answer_given']   ?? '—');
$time_taken = (int) ($attempt['time_taken'] ?? 0);

$diff_colors = ['easy' => '#22c55e', 'medium' => '#f59e0b', 'hard' => '#ef4444'];
$diff_color  = $diff_colors[$difficulty] ?? '#f59e0b';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Feedback</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  /* ── Design tokens ──────────────────────────────────────────────────── */
  :root {
    --bg:          #080b14;
    --surface:     #0f1422;
    --surface2:    #161d30;
    --border:      #1e2840;
    --text:        #e8eaf6;
    --muted:       #6b7a99;
    --accent:      #6366f1;
    --accent-glow: rgba(99,102,241,.25);
    --danger:      #ef4444;
    --danger-glow: rgba(239,68,68,.18);
    --warning:     #f59e0b;
    --success:     #22c55e;
    --radius:      14px;
    --font-head:   'Syne', sans-serif;
    --font-body:   'DM Sans', sans-serif;
  }
  [data-theme="light"] {
    --bg:        #f0f4ff;
    --surface:   #ffffff;
    --surface2:  #f5f7ff;
    --border:    #dde3f5;
    --text:      #0f1422;
    --muted:     #6b7a99;
    --accent-glow: rgba(99,102,241,.12);
    --danger-glow: rgba(239,68,68,.1);
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: var(--font-body);
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: grid;
    grid-template-rows: auto 1fr;
  }

  /* ── Topbar ─────────────────────────────────────────────────────────── */
  .topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 28px;
    height: 60px;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .logo {
    font-family: var(--font-head);
    font-weight: 800;
    font-size: 1.1rem;
    color: var(--accent);
    text-decoration: none;
    letter-spacing: -0.5px;
  }
  .topbar-right { display: flex; align-items: center; gap: 12px; }
  .stat-chip {
    display: flex; align-items: center; gap: 6px;
    font-size: .8rem; font-weight: 600;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 4px 12px;
    color: var(--muted);
  }
  .stat-chip span { color: var(--text); }
  .theme-btn {
    width: 36px; height: 36px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface2);
    color: var(--muted);
    cursor: pointer;
    display: grid; place-items: center;
    font-size: 1rem;
    transition: border-color .2s, color .2s;
  }
  .theme-btn:hover { border-color: var(--accent); color: var(--accent); }

  /* ── Main layout ─────────────────────────────────────────────────────── */
  .main {
    max-width: 780px;
    margin: 0 auto;
    padding: 48px 24px 80px;
    width: 100%;
  }

  /* ── Wrong banner ─────────────────────────────────────────────────────── */
  .wrong-banner {
    background: var(--danger-glow);
    border: 1px solid var(--danger);
    border-radius: var(--radius);
    padding: 28px 32px;
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 36px;
    animation: slideDown .45s cubic-bezier(.22,1,.36,1);
  }
  @keyframes slideDown {
    from { opacity: 0; transform: translateY(-16px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .wrong-icon {
    width: 52px; height: 52px; flex-shrink: 0;
    border-radius: 50%;
    background: var(--danger);
    display: grid; place-items: center;
    font-size: 1.5rem;
  }
  .wrong-banner h2 {
    font-family: var(--font-head);
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--danger);
    margin-bottom: 4px;
  }
  .wrong-banner p { color: var(--muted); font-size: .9rem; }

  /* ── Card ────────────────────────────────────────────────────────────── */
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 28px 32px;
    margin-bottom: 20px;
    animation: fadeUp .4s ease both;
  }
  .card:nth-child(2) { animation-delay: .05s; }
  .card:nth-child(3) { animation-delay: .1s; }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .card-label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--muted);
    margin-bottom: 12px;
  }

  /* ── Answer comparison ─────────────────────────────────────────────── */
  .answer-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 4px;
  }
  .answer-box {
    background: var(--surface2);
    border-radius: 10px;
    padding: 16px 18px;
    border: 1px solid var(--border);
  }
  .answer-box .alabel {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 8px;
  }
  .answer-box .avalue {
    font-family: var(--font-head);
    font-size: 1.2rem;
    font-weight: 700;
  }
  .answer-box.wrong  { border-color: var(--danger);  }
  .answer-box.wrong  .alabel  { color: var(--danger); }
  .answer-box.wrong  .avalue  { color: var(--danger); text-decoration: line-through; opacity: .7; }
  .answer-box.correct { border-color: var(--success); }
  .answer-box.correct .alabel { color: var(--success); }
  .answer-box.correct .avalue { color: var(--success); }

  /* ── Explanation ────────────────────────────────────────────────────── */
  .explanation {
    line-height: 1.8;
    color: var(--muted);
    font-size: .95rem;
  }
  .explanation strong { color: var(--text); }

  /* ── Topic/difficulty pill row ─────────────────────────────────────── */
  .meta-row {
    display: flex; gap: 10px; align-items: center;
    margin-bottom: 18px;
    flex-wrap: wrap;
  }
  .pill {
    font-size: .75rem; font-weight: 600;
    border-radius: 20px;
    padding: 4px 12px;
    background: var(--surface2);
    border: 1px solid var(--border);
    color: var(--muted);
  }
  .pill.diff { border-color: <?= $diff_color ?>; color: <?= $diff_color ?>; }

  /* ── Tips list ──────────────────────────────────────────────────────── */
  .tips { list-style: none; display: flex; flex-direction: column; gap: 10px; }
  .tips li {
    display: flex; gap: 12px; align-items: flex-start;
    font-size: .9rem; color: var(--muted);
    background: var(--surface2);
    border-radius: 10px;
    padding: 12px 16px;
    border: 1px solid var(--border);
  }
  .tips li .tip-icon { flex-shrink: 0; font-size: 1rem; margin-top: 1px; }
  .tips li strong { color: var(--text); display: block; margin-bottom: 2px; }

  /* ── Time badge ─────────────────────────────────────────────────────── */
  .time-badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: .82rem; font-weight: 600;
    color: var(--warning);
    background: rgba(245,158,11,.1);
    border: 1px solid rgba(245,158,11,.3);
    border-radius: 20px;
    padding: 4px 12px;
  }

  /* ── Action buttons ─────────────────────────────────────────────────── */
  .actions {
    display: flex; gap: 12px; flex-wrap: wrap;
    margin-top: 32px;
  }
  .btn {
    display: inline-flex; align-items: center; gap: 8px;
    font-family: var(--font-body);
    font-size: .9rem; font-weight: 600;
    padding: 12px 22px;
    border-radius: 10px;
    border: none; cursor: pointer;
    text-decoration: none;
    transition: opacity .15s, transform .15s;
  }
  .btn:hover { opacity: .85; transform: translateY(-1px); }
  .btn-primary { background: var(--accent); color: #fff; }
  .btn-outline {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--muted);
  }
  .btn-outline:hover { border-color: var(--accent); color: var(--accent); }
  .btn-retry {
    background: var(--danger);
    color: #fff;
  }

  @media (max-width: 540px) {
    .answer-row { grid-template-columns: 1fr; }
    .card { padding: 20px; }
    .wrong-banner { flex-direction: column; text-align: center; }
  }
</style>
</head>
<body>

<!-- Topbar -->
<header class="topbar">
  <a href="dashboard.php" class="logo">MathQuest</a>
  <div class="topbar-right">
    <div class="stat-chip">⚡ XP <span><?= number_format((int)$stats['xp']) ?></span></div>
    <div class="stat-chip">🔥 Streak <span><?= (int)$stats['streak'] ?></span></div>
    <button class="theme-btn" id="themeToggle" title="Toggle theme">🌙</button>
  </div>
</header>

<main class="main">

  <!-- Wrong banner -->
  <div class="wrong-banner">
    <div class="wrong-icon">✗</div>
    <div>
      <h2>Not quite right</h2>
      <p>Don't worry — every mistake is a step forward. Let's break down what happened.</p>
      <?php if ($time_taken > 0): ?>
        <div class="time-badge" style="margin-top:10px">
          ⏱ You answered in <?= $time_taken ?>s
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Problem context -->
  <div class="card">
    <div class="card-label">Problem</div>
    <div class="meta-row">
      <span class="pill"><?= $topic ?></span>
      <span class="pill diff"><?= ucfirst($difficulty) ?></span>
    </div>
    <p style="font-family:var(--font-head);font-size:1.05rem;font-weight:600;margin-bottom:16px">
      <?= $title ?>
    </p>

    <div class="answer-row">
      <div class="answer-box wrong">
        <div class="alabel">Your answer</div>
        <div class="avalue"><?= $given ?: '—' ?></div>
      </div>
      <div class="answer-box correct">
        <div class="alabel">Correct answer</div>
        <div class="avalue"><?= htmlspecialchars($assignment['description'] ?? '—') ?></div>
      </div>
    </div>
  </div>

  <!-- Explanation -->
  <div class="card">
    <div class="card-label">Why?</div>
    <div class="explanation">
      <?php
        // If the assignment has a description that doubles as an explanation, show it.
        // Otherwise show a generic nudge.
        $desc = trim($assignment['description'] ?? '');
        if ($desc !== '') {
          echo '<p>' . nl2br(htmlspecialchars($desc)) . '</p>';
        } else {
          echo '<p>Review the steps for <strong>' . $topic . '</strong> and try working through the problem again from scratch. Focus on the <strong>' . ucfirst($difficulty) . '</strong>-level techniques for this topic.</p>';
        }
      ?>
    </div>
  </div>

  <!-- Tips -->
  <div class="card">
    <div class="card-label">Tips to remember</div>
    <ul class="tips">
      <li>
        <span class="tip-icon">📐</span>
        <div>
          <strong>Write out every step</strong>
          Skipping steps is the #1 cause of careless errors — even on easy problems.
        </div>
      </li>
      <li>
        <span class="tip-icon">🔁</span>
        <div>
          <strong>Check your work backwards</strong>
          Plug your answer back in and verify it satisfies the original condition.
        </div>
      </li>
      <li>
        <span class="tip-icon">🧠</span>
        <div>
          <strong>Revisit the topic</strong>
          Head to the Progress page to see which concepts need more practice.
        </div>
      </li>
    </ul>
  </div>

  <!-- Actions -->
  <div class="actions">
    <?php if ($assignment_id > 0): ?>
      <a href="problem.php?assignment_id=<?= $assignment_id ?>" class="btn btn-retry">
        🔄 Try again
      </a>
    <?php endif; ?>

    <?php if ($next_assignment): ?>
      <a href="problem.php?assignment_id=<?= $next_assignment['id'] ?>" class="btn btn-primary">
        Next problem →
      </a>
    <?php endif; ?>

    <a href="progress.php" class="btn btn-outline">📊 View progress</a>
    <a href="dashboard.php" class="btn btn-outline">🏠 Dashboard</a>
  </div>

</main>

<script>
  // ── Theme ──────────────────────────────────────────────────────────────
  const html  = document.documentElement;
  const btn   = document.getElementById('themeToggle');
  const saved = localStorage.getItem('mq_theme') || 'dark';
  applyTheme(saved);

  btn.addEventListener('click', () => {
    const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    localStorage.setItem('mq_theme', next);
  });

  function applyTheme(t) {
    html.dataset.theme = t;
    btn.textContent = t === 'dark' ? '☀️' : '🌙';
  }
</script>
</body>
</html>