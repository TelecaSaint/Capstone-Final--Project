<?php
require_once 'config.php';
$user = require_login('student');
$isCorrect   = filter_var($_SESSION['last_correct']   ?? false, FILTER_VALIDATE_BOOLEAN);
$userAnswer  = $_SESSION['last_user_answer']   ?? '';
$correctAns  = $_SESSION['last_correct_answer']?? '';
$explanation = $_SESSION['last_explanation']   ?? '';
$question    = $_SESSION['last_question']      ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Result</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#080b14;--s1:#0e1220;--s2:#141827;--b:rgba(255,255,255,0.07);--cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--violet:#7c3aed;--vdim:rgba(124,58,237,0.15);--green:#00e676;--gdim:rgba(0,230,118,0.1);--red:#ff5252;--rdim:rgba(255,82,82,0.09);--text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68)}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:36px 20px}
<?php if($isCorrect):?>body{background-image:radial-gradient(ellipse at 50% 0%,rgba(0,230,118,0.07) 0%,transparent 55%)}
<?php else:?>body{background-image:radial-gradient(ellipse at 50% 0%,rgba(255,82,82,0.07) 0%,transparent 55%)}<?php endif;?>
.container{width:100%;max-width:600px;animation:fadeUp 0.5s ease both}
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
.hero{text-align:center;margin-bottom:24px}
.hero-glyph{font-size:4.5em;display:block;margin-bottom:10px}
.hero-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.8em;margin-bottom:5px}
.hero-title.win{color:var(--green)}.hero-title.lose{color:var(--red)}
.hero-sub{color:var(--tdim);font-size:0.88em;font-style:italic}
.panel{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:22px;margin-bottom:16px}
.ptitle{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.12em;color:var(--tdim);text-transform:uppercase;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--b)}
.ans-pair{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.ans-box{background:var(--s2);border:1px solid var(--b);border-radius:9px;padding:14px;text-align:center}
.ans-box.wrong-box{border-color:rgba(255,82,82,0.3);background:var(--rdim)}
.ans-box.right-box{border-color:rgba(0,230,118,0.3);background:var(--gdim)}
.ab-lbl{font-family:'Syne',sans-serif;font-size:0.64em;font-weight:600;letter-spacing:0.1em;color:var(--tdim);margin-bottom:7px;text-transform:uppercase}
.ab-val{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em}
.ab-val.wrong{color:var(--red)}.ab-val.correct{color:var(--green)}
.steps{background:var(--s2);border:1px solid var(--b);border-left:3px solid var(--cyan);border-radius:0 8px 8px 0;padding:14px;font-size:0.88em;line-height:1.9;color:var(--tmid);white-space:pre-wrap}
.btn-row{display:flex;gap:12px}
.btn{flex:1;padding:14px;border:none;border-radius:9px;font-family:'Syne',sans-serif;font-size:0.84em;font-weight:700;letter-spacing:0.08em;cursor:pointer;transition:all 0.22s}
.btn-next{background:linear-gradient(135deg,var(--cyan),#0099cc);color:#020d14;box-shadow:0 4px 18px rgba(0,229,255,0.25)}
.btn-next:hover{transform:translateY(-2px);box-shadow:0 8px 26px rgba(0,229,255,0.4)}
.btn-retry{background:rgba(255,171,0,0.15);border:1px solid rgba(255,171,0,0.3);color:#ffab00}
.btn-retry:hover{background:rgba(255,171,0,0.25)}
@media(max-width:500px){.ans-pair{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="container">
  <div class="hero">
    <span class="hero-glyph"><?=$isCorrect?'⚡':'✗'?></span>
    <div class="hero-title <?=$isCorrect?'win':'lose'?>"><?=$isCorrect?'Correct!':'Not Quite'?></div>
    <div class="hero-sub"><?=$isCorrect?'Great work! You earned XP.':'Review the solution below and try again.'?></div>
  </div>

  <?php if(!$isCorrect && $userAnswer):?>
  <div class="panel">
    <div class="ptitle">📊 Answer Comparison</div>
    <div class="ans-pair">
      <div class="ans-box wrong-box"><div class="ab-lbl">Your Answer</div><div class="ab-val wrong"><?=htmlspecialchars($userAnswer)?></div></div>
      <div class="ans-box right-box"><div class="ab-lbl">Correct Answer</div><div class="ab-val correct"><?=htmlspecialchars($correctAns)?></div></div>
    </div>
  </div>
  <?php endif;?>

  <?php if($explanation):?>
  <div class="panel">
    <div class="ptitle">📐 Step-by-Step Solution</div>
    <div class="steps"><?=htmlspecialchars($explanation)?></div>
  </div>
  <?php endif;?>

  <div class="btn-row">
    <?php if(!$isCorrect):?><button class="btn btn-retry" onclick="location.href='problem.php'">↺ Try Again</button><?php endif;?>
    <button class="btn btn-next" onclick="location.href='dashboard.php'">Back to Dashboard →</button>
  </div>
</div>
</body>
</html>