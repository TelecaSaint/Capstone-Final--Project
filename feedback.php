<?php
require_once 'config.php';
$user = require_login();

// 1. Get IDs
$assignment_id = (int)($_GET['assignment_id'] ?? 0);
$attempt_id    = (int)($_GET['attempt_id']    ?? 0);

// 2. Default states to prevent "Undefined variable" errors
$is_correct = false;
$next_id    = null; 
$explanation = "Review the core concepts of this topic and try again.";
$correct_val = "—";

try {
    $pdo = db();

    // Fetch Assignment & Solution
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = ?");
    $stmt->execute([$assignment_id]);
    $assignment = $stmt->fetch();
    
    if($assignment) {
        // Change 'solution' to whatever your column name is (e.g., 'answer')
        $correct_val = $assignment['solution'] ?? $assignment['answer'] ?? 'Check Assignment';
        
        // Fetch Explanation from DB
        if (!empty($assignment['explanation'])) {
            $explanation = $assignment['explanation'];
        } elseif (!empty($assignment['description'])) {
            $explanation = $assignment['description'];
        }
    }

    // Fetch the Attempt
    $stmt = $pdo->prepare("SELECT * FROM attempts WHERE id = ? AND student_id = ?");
    $stmt->execute([$attempt_id, $user['id']]);
    $attempt = $stmt->fetch();
    if($attempt) {
        $is_correct = (bool)$attempt['is_correct'];
    }

    // Fetch Next Assignment (Fixing the $next_id error)
    $stmt = $pdo->prepare("SELECT id FROM assignments WHERE id != ? ORDER BY RAND() LIMIT 1");
    $stmt->execute([$assignment_id]);
    $next_row = $stmt->fetch();
    $next_id = $next_row['id'] ?? null;

} catch (Exception $e) {
    error_log($e->getMessage());
}

$status_color = $is_correct ? 'var(--green)' : 'var(--red)';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>MathQuest — Feedback</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@800&family=DM+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#080b14; --s1:#0e1220; --s2:#141827; --b:rgba(255,255,255,0.07);
            --cyan:#00e5ff; --green:#00e676; --red:#ff5252; --text:#e8eaf2;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); margin: 0; }
        nav { display: flex; align-items: center; padding: 0 30px; height: 60px; background: var(--s1); border-bottom: 1px solid var(--b); }
        .logo { font-family: 'Syne'; font-weight: 800; color: var(--cyan); text-decoration: none; }
        
        .container { max-width: 650px; margin: 40px auto; padding: 0 20px; }
        .panel { background: var(--s1); border: 1px solid var(--b); border-radius: 16px; padding: 30px; margin-bottom: 20px; }
        
        .status-header { text-align: center; margin-bottom: 30px; }
        .status-title { font-family: 'Syne'; font-size: 2.2em; color: <?= $status_color ?>; margin: 10px 0; }
        
        .compare-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
        .box { background: var(--s2); padding: 20px; border-radius: 12px; border: 1px solid var(--b); }
        .box-label { font-size: 0.65rem; color: rgba(255,255,255,0.4); text-transform: uppercase; margin-bottom: 5px; display: block; }
        .box-val { font-family: 'Syne'; font-size: 1.6rem; font-weight: 800; }

        .explanation-box { background: rgba(124, 58, 237, 0.05); border: 1px dashed var(--b); padding: 20px; border-radius: 12px; line-height: 1.6; }
        .explanation-title { font-family: 'Syne'; font-size: 0.8em; color: var(--cyan); margin-bottom: 10px; display: block; text-transform: uppercase; }

        .btn { 
            display: block; width: 100%; padding: 16px; margin-top: 10px; border-radius: 12px; 
            text-decoration: none; font-weight: 800; text-align: center; font-family: 'Syne';
            transition: 0.2s; border: none; cursor: pointer;
        }
        .btn-main { background: var(--cyan); color: #080b14; }
        .btn-sub { background: var(--s2); color: var(--text); border: 1px solid var(--b); }
        .btn:hover { transform: translateY(-2px); filter: brightness(1.1); }
    </style>
</head>
<body>

<nav><a href="dashboard.php" class="logo">MATHQUEST</a></nav>

<div class="container">
    <div class="panel">
        <div class="status-header">
            <div style="font-size: 0.7em; font-weight: 800; color: <?= $status_color ?>; letter-spacing: 2px;">
                <?= $is_correct ? 'MISSION ACCOMPLISHED' : 'MISSION FAILED' ?>
            </div>
            <h1 class="status-title"><?= $is_correct ? 'Success!' : 'Defeated' ?></h1>
        </div>

        <div class="compare-grid">
            <div class="box" style="border-color: <?= $is_correct ? 'var(--green)' : 'var(--red)' ?>">
                <span class="box-label">Your Input</span>
                <span class="box-val" style="color: <?= $is_correct ? 'var(--green)' : 'var(--red)' ?>">
                    <?= htmlspecialchars($attempt['answer_given'] ?? '??') ?>
                </span>
            </div>
            <div class="box" style="border-color: var(--green)">
                <span class="box-label">Target Answer</span>
                <span class="box-val" style="color: var(--green)">
                    <?= htmlspecialchars($correct_val) ?>
                </span>
            </div>
        </div>

        <div class="explanation-box">
            <span class="explanation-title">💡 Logic Breakdown</span>
            <div style="font-size: 0.95rem; color: rgba(255,255,255,0.8);">
                <?= nl2br(htmlspecialchars($explanation)) ?>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <?php if($next_id): ?>
                <a href="problem.php?assignment_id=<?= $next_id ?>" class="btn btn-main">NEXT QUEST →</a>
            <?php endif; ?>
            
            <?php if(!$is_correct): ?>
                <a href="problem.php?assignment_id=<?= $assignment_id ?>" class="btn btn-sub">RETRY LEVEL</a>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn btn-sub">BACK TO HQ</a>
        </div>
    </div>
</div>

</body>
</html>