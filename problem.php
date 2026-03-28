<?php
require_once 'config.php';
$user = require_login('student');
$subject = htmlspecialchars($_GET['subject'] ?? 'algebra');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Battle</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<!-- All original CSS preserved exactly -->
<style>
:root{--bg:#080b14;--s1:#0e1220;--s2:#141827;--s3:#1a2035;--b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.13);--cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--cglow:rgba(0,229,255,0.2);--violet:#7c3aed;--vdim:rgba(124,58,237,0.15);--amber:#ffab00;--green:#00e676;--gdim:rgba(0,230,118,0.1);--red:#ff5252;--rdim:rgba(255,82,82,0.1);--text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68)}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:grid;grid-template-rows:auto auto 1fr;background-image:radial-gradient(ellipse at 20% 0%,rgba(0,229,255,0.05) 0%,transparent 45%),radial-gradient(ellipse at 80% 100%,rgba(124,58,237,0.06) 0%,transparent 45%)}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 26px;height:54px;background:rgba(8,11,20,0.97);border-bottom:1px solid var(--b);backdrop-filter:blur(12px)}
.nav-left{display:flex;align-items:center;gap:14px}
.back-btn{display:flex;align-items:center;gap:5px;color:var(--tdim);font-size:0.82em;cursor:pointer;background:none;border:none;padding:6px 10px;border-radius:7px;transition:all 0.2s;font-family:'DM Sans',sans-serif}
.back-btn:hover{color:var(--text);background:var(--s2)}
.nav-quest{font-family:'Syne',sans-serif;font-size:0.72em;font-weight:600;letter-spacing:0.08em;color:var(--tdim)}
.nav-quest b{color:var(--tmid)}
.nav-right{display:flex;align-items:center;gap:16px}
.nav-stat{font-size:0.82em;color:var(--tmid);display:flex;align-items:center;gap:4px}
.nav-stat b{color:var(--cyan)}
.prog-strip{background:var(--s1);border-bottom:1px solid var(--b);padding:10px 26px}
.prog-meta{display:flex;justify-content:space-between;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.08em;color:var(--tdim);margin-bottom:7px}
.prog-bar{height:5px;background:var(--s2);border-radius:3px;overflow:hidden}
.prog-fill{height:100%;background:linear-gradient(90deg,#0099cc,var(--cyan));border-radius:3px;transition:width 0.8s ease;box-shadow:0 0 8px rgba(0,229,255,0.35)}
.prob-dots{display:flex;gap:4px;margin-top:7px}
.pd{width:22px;height:5px;border-radius:2px;background:var(--s2);border:1px solid var(--b);transition:all 0.3s}
.pd.done{background:var(--cyan);border-color:var(--cyan);box-shadow:0 0 6px rgba(0,229,255,0.4)}
.pd.wrong{background:var(--red);border-color:var(--red)}
.pd.cur{background:rgba(0,229,255,0.35);border-color:rgba(0,229,255,0.5);animation:curPulse 1.5s ease infinite}
@keyframes curPulse{0%,100%{opacity:1}50%{opacity:0.55}}
.battle{display:grid;grid-template-columns:1fr 340px;gap:22px;padding:22px 26px;max-width:1200px;margin:0 auto;width:100%}
.arena{display:flex;flex-direction:column;gap:18px}
.ai-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--cdim);border:1px solid rgba(0,229,255,0.2);border-radius:20px;font-size:0.72em;color:var(--cyan);font-family:'Syne',sans-serif;font-weight:600;letter-spacing:0.06em;margin-bottom:6px}
.ai-dot{width:5px;height:5px;border-radius:50%;background:var(--cyan);animation:aiPulse 1s ease infinite}
@keyframes aiPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.5;transform:scale(0.8)}}
.subject-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:4px}
.stab{padding:6px 14px;background:var(--s2);border:1px solid var(--b);border-radius:7px;font-family:'Syne',sans-serif;font-size:0.72em;font-weight:600;letter-spacing:0.06em;color:var(--tdim);cursor:pointer;transition:all 0.2s}
.stab.active{background:var(--cdim);border-color:rgba(0,229,255,0.3);color:var(--cyan)}
.stab:hover:not(.active){background:var(--s3);color:var(--tmid)}
.prob-card{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:32px;text-align:center;transition:border-color 0.3s;position:relative;overflow:hidden}
.prob-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,0.015) 0%,transparent 60%);pointer-events:none}
.prob-card.correct{border-color:rgba(0,230,118,0.45);box-shadow:0 0 32px rgba(0,230,118,0.1)}
.prob-card.wrong{border-color:rgba(255,82,82,0.45);box-shadow:0 0 32px rgba(255,82,82,0.1)}
.prob-type{display:inline-block;padding:4px 12px;background:var(--s2);border:1px solid var(--b);border-radius:5px;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.1em;color:var(--tdim);text-transform:uppercase;margin-bottom:18px}
.prob-q{font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(1.8em,4vw,2.6em);color:var(--text);margin-bottom:6px;line-height:1.1}
.prob-ctx{font-size:0.84em;color:var(--tdim);font-style:italic;margin-bottom:24px}
.ans-label{font-family:'Syne',sans-serif;font-size:0.7em;font-weight:600;letter-spacing:0.1em;color:var(--tdim);text-transform:uppercase;margin-bottom:8px;text-align:left}
.ans-wrap{position:relative;margin-bottom:16px}
.ans-input{width:100%;padding:16px 20px;background:var(--s2);border:1.5px solid var(--b);border-radius:10px;color:var(--text);font-family:'Syne',sans-serif;font-size:1.4em;font-weight:700;text-align:center;letter-spacing:0.08em;outline:none;transition:all 0.25s}
.ans-input::placeholder{color:var(--tdim);font-weight:400;font-size:0.8em}
.ans-input:focus{border-color:rgba(0,229,255,0.5);background:rgba(0,229,255,0.04);box-shadow:0 0 0 3px rgba(0,229,255,0.08)}
.ans-input.correct{border-color:rgba(0,230,118,0.6);background:rgba(0,230,118,0.06);color:var(--green)}
.ans-input.wrong{border-color:rgba(255,82,82,0.6);background:rgba(255,82,82,0.06);color:var(--red);animation:shake 0.35s ease}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-8px)}75%{transform:translateX(8px)}}
.btn-row{display:flex;gap:10px}
.btn{flex:1;padding:14px;border:none;border-radius:9px;font-family:'Syne',sans-serif;font-size:0.82em;font-weight:700;letter-spacing:0.08em;cursor:pointer;transition:all 0.22s}
.btn-clear{background:var(--rdim);border:1px solid rgba(255,82,82,0.2);color:var(--red)}
.btn-clear:hover{background:rgba(255,82,82,0.18);border-color:rgba(255,82,82,0.4)}
.btn-submit{background:linear-gradient(135deg,var(--cyan) 0%,#0099cc 100%);color:#020d14;flex:2.5;box-shadow:0 4px 18px rgba(0,229,255,0.25)}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 26px rgba(0,229,255,0.4)}
.btn-submit:disabled{opacity:0.4;cursor:not-allowed;transform:none}
.sidebar{display:flex;flex-direction:column;gap:16px}
.s-panel{background:var(--s1);border:1px solid var(--b);border-radius:12px;padding:18px}
.sp-title{font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.12em;color:var(--tdim);text-transform:uppercase;margin-bottom:12px;padding-bottom:9px;border-bottom:1px solid var(--b)}
.timer-display{text-align:center;font-family:'Syne',sans-serif;font-weight:800;font-size:2.2em;letter-spacing:0.04em;color:var(--cyan)}
.timer-display.warn{color:var(--amber);animation:timerWarn 0.6s ease infinite}
.timer-display.crit{color:var(--red);animation:timerWarn 0.3s ease infinite}
@keyframes timerWarn{0%,100%{opacity:1}50%{opacity:0.5}}
.timer-sub{text-align:center;font-size:0.72em;color:var(--tdim);margin-top:4px}
.hint-cost{font-size:0.75em;color:var(--tdim);margin-bottom:9px}
.hint-box{background:rgba(0,229,255,0.06);border:1px solid rgba(0,229,255,0.18);border-radius:8px;padding:12px;font-size:0.88em;color:var(--tmid);line-height:1.6;margin-bottom:10px;display:none;font-style:italic}
.hint-box.show{display:block}
.hint-btn{width:100%;padding:10px;background:var(--s2);border:1px solid var(--b);border-radius:8px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.73em;font-weight:600;letter-spacing:0.07em;cursor:pointer;transition:all 0.2s}
.hint-btn:hover{background:var(--s3);color:var(--tmid);border-color:var(--bb)}
.hint-btn:disabled{opacity:0.35;cursor:not-allowed}
.ai-hint-box{background:linear-gradient(135deg,rgba(0,229,255,0.06),rgba(124,58,237,0.06));border:1px solid rgba(0,229,255,0.2);border-radius:8px;padding:12px;font-size:0.86em;color:var(--tmid);line-height:1.7;margin-bottom:10px;display:none}
.ai-hint-box.show{display:block}
.ai-hint-box-header{font-family:'Syne',sans-serif;font-size:0.68em;font-weight:700;letter-spacing:0.1em;color:var(--cyan);margin-bottom:7px;display:flex;align-items:center;gap:5px}
.ai-btn{width:100%;padding:10px;background:linear-gradient(135deg,var(--cdim),var(--vdim));border:1px solid rgba(0,229,255,0.22);border-radius:8px;color:var(--cyan);font-family:'Syne',sans-serif;font-size:0.73em;font-weight:600;letter-spacing:0.07em;cursor:pointer;transition:all 0.2s}
.ai-btn:hover{background:linear-gradient(135deg,rgba(0,229,255,0.18),rgba(124,58,237,0.15));border-color:rgba(0,229,255,0.35)}
.ai-btn:disabled{opacity:0.35;cursor:not-allowed}
.ldots{display:inline-flex;gap:4px;align-items:center}
.ldots span{width:5px;height:5px;border-radius:50%;background:var(--cyan);animation:ldot 1s ease infinite}
.ldots span:nth-child(2){animation-delay:.2s}.ldots span:nth-child(3){animation-delay:.4s}
@keyframes ldot{0%,80%,100%{transform:scale(0.5);opacity:0.3}40%{transform:scale(1);opacity:1}}
.overlay{position:fixed;inset:0;z-index:200;display:flex;align-items:center;justify-content:center;background:rgba(8,11,20,0.85);backdrop-filter:blur(8px);opacity:0;pointer-events:none;transition:opacity 0.3s}
.overlay.show{opacity:1;pointer-events:all}
.result-box{background:var(--s1);border:1px solid var(--b);border-radius:18px;padding:40px 48px;text-align:center;max-width:400px;transform:scale(0.85);transition:transform 0.4s cubic-bezier(0.34,1.56,0.64,1)}
.overlay.show .result-box{transform:scale(1)}
.res-emoji{font-size:3.5em;display:block;margin-bottom:10px}
.res-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.7em;margin-bottom:6px}
.res-title.win{color:var(--green)}.res-title.lose{color:var(--red)}
.res-sub{font-size:0.88em;color:var(--tdim);margin-bottom:12px;font-style:italic}
.res-xp{font-family:'Syne',sans-serif;font-size:1em;font-weight:700;color:var(--cyan);margin:10px 0}
.res-btn{padding:13px 32px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:9px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.85em;font-weight:700;letter-spacing:0.09em;cursor:pointer;transition:all 0.22s;box-shadow:0 4px 18px rgba(0,229,255,0.28);margin-top:6px}
.res-btn:hover{transform:translateY(-2px);box-shadow:0 8px 26px rgba(0,229,255,0.42)}
.res-btn.retry{background:linear-gradient(135deg,#e65100,var(--amber));box-shadow:0 4px 18px rgba(255,171,0,0.25)}
.diff-row{display:flex;gap:6px;margin-bottom:14px}
.diff-btn{flex:1;padding:7px;background:var(--s2);border:1px solid var(--b);border-radius:7px;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.06em;color:var(--tdim);cursor:pointer;transition:all 0.2s;text-align:center}
.diff-btn.active.e{background:rgba(0,230,118,0.12);border-color:rgba(0,230,118,0.3);color:var(--green)}
.diff-btn.active.m{background:rgba(255,171,0,0.12);border-color:rgba(255,171,0,0.3);color:var(--amber)}
.diff-btn.active.h{background:rgba(255,82,82,0.1);border-color:rgba(255,82,82,0.25);color:var(--red)}
.diff-btn:hover:not(.active){background:var(--s3);color:var(--tmid)}
.mon-row{display:flex;align-items:center;gap:12px;margin-bottom:16px;padding:12px 14px;background:var(--s2);border:1px solid var(--b);border-radius:10px}
.mon-ico{font-size:2em;flex-shrink:0}
.mon-info{flex:1;min-width:0}
.mon-name{font-family:'Syne',sans-serif;font-weight:700;font-size:0.88em;margin-bottom:4px}
.mon-hp-label{font-size:0.72em;color:var(--tdim);margin-bottom:4px}
.mon-hp-bar{height:5px;background:var(--bg);border-radius:3px;overflow:hidden}
.mon-hp-fill{height:100%;background:linear-gradient(90deg,var(--red),#ff7043);transition:width 0.8s ease}
@media(max-width:900px){.battle{grid-template-columns:1fr;padding:16px}.sidebar{display:grid;grid-template-columns:1fr 1fr}}
@media(max-width:600px){.sidebar{grid-template-columns:1fr}nav{padding:0 14px}.prob-q{font-size:1.7em}}
</style>
</head>
<body>
<nav>
  <div class="nav-left">
    <button class="back-btn" onclick="window.location.href='dashboard.php'">← Back</button>
    <span class="nav-quest">Quest: <b>Algebra Castle</b></span>
  </div>
  <div class="nav-right">
    <div class="nav-stat">⭐ <b id="navXP"><?= number_format((int)($user['xp'] ?? 0)) ?></b></div>
    <div class="nav-stat">🔥 <b id="navStreak"><?= (int)($user['streak'] ?? 0) ?></b></div>
  </div>
</nav>

<div class="prog-strip">
  <div class="prog-meta">
    <span>PROBLEM <span id="curProbN">1</span> / 10</span>
    <span id="progPct">0% COMPLETE</span>
  </div>
  <div class="prog-bar"><div class="prog-fill" id="progFill" style="width:0%"></div></div>
  <div class="prob-dots" id="probDots"></div>
</div>

<div class="battle">
  <div class="arena">
    <div>
      <div class="subject-tabs">
        <button class="stab <?= $subject==='algebra'?'active':'' ?>" onclick="setSubject('algebra',this)">∑ Algebra</button>
        <button class="stab <?= $subject==='arithmetic'?'active':'' ?>" onclick="setSubject('arithmetic',this)">+ Arithmetic</button>
        <button class="stab <?= $subject==='geometry'?'active':'' ?>" onclick="setSubject('geometry',this)">📐 Geometry</button>
        <button class="stab <?= $subject==='fractions'?'active':'' ?>" onclick="setSubject('fractions',this)">½ Fractions</button>
        <button class="stab <?= $subject==='statistics'?'active':'' ?>" onclick="setSubject('statistics',this)">📊 Statistics</button>
      </div>
    </div>
    <div class="mon-row">
      <div class="mon-ico" id="monIco">🐉</div>
      <div class="mon-info">
        <div class="mon-name" id="monName">Equation Dragon</div>
        <div class="mon-hp-label">HP: <span id="monHPVal">75</span>/100</div>
        <div class="mon-hp-bar"><div class="mon-hp-fill" id="monHP" style="width:75%"></div></div>
      </div>
      <div style="font-size:0.75em;color:var(--tdim);text-align:right;flex-shrink:0">
        <span class="ai-pill"><span class="ai-dot"></span>AI</span>
      </div>
    </div>
    <div class="prob-card" id="probCard">
      <div class="prob-type" id="probType">LOADING...</div>
      <div class="prob-q" id="probQ"><span class="ldots"><span></span><span></span><span></span></span></div>
      <div class="prob-ctx" id="probCtx">Generating AI problem...</div>
      <div class="ans-label">Your Answer:</div>
      <div class="ans-wrap"><input class="ans-input" id="ansInput" type="text" placeholder="Enter your answer" autocomplete="off"></div>
      <div class="btn-row">
        <button class="btn btn-clear" onclick="clearAns()">↺ Clear</button>
        <button class="btn btn-submit" id="submitBtn" onclick="submitAnswer()" disabled>⚔ SUBMIT</button>
      </div>
    </div>
  </div>
  <div class="sidebar">
    <div class="s-panel">
      <div class="sp-title">⏱ Time Remaining</div>
      <div class="timer-display" id="timerDisp">02:30</div>
      <div class="timer-sub">Seconds counting down</div>
    </div>
    <div class="s-panel">
      <div class="sp-title">📊 Difficulty</div>
      <div class="diff-row">
        <button class="diff-btn e" onclick="setDiff('easy',this)">Easy</button>
        <button class="diff-btn m active" onclick="setDiff('medium',this)">Med</button>
        <button class="diff-btn h" onclick="setDiff('hard',this)">Hard</button>
      </div>
      <div style="font-size:0.78em;color:var(--tdim);font-style:italic" id="diffDesc">Medium problems with 2-step equations</div>
    </div>
    <div class="s-panel">
      <div class="sp-title">💡 Oracle Hint</div>
      <div class="hint-cost">Costs 10 XP to reveal</div>
      <div class="hint-box" id="hintBox"></div>
      <button class="hint-btn" id="hintBtn" onclick="useHint()">💡 Reveal Hint (−10 XP)</button>
    </div>
    <div class="s-panel">
      <div class="sp-title">✨ AI Guidance</div>
      <div class="ai-hint-box" id="aiHintBox">
        <div class="ai-hint-box-header"><span class="ai-dot"></span>AI ORACLE</div>
        <span id="aiHintText"></span>
      </div>
      <button class="ai-btn" id="aiBtn" onclick="getAIHint()">✨ Ask AI Oracle</button>
    </div>
  </div>
</div>

<div class="overlay" id="overlay">
  <div class="result-box">
    <span class="res-emoji" id="resEmoji">⚔</span>
    <div class="res-title" id="resTitle">Correct!</div>
    <div class="res-sub" id="resSub"></div>
    <div class="res-xp" id="resXP"></div>
    <div style="display:flex;gap:10px;justify-content:center;margin-top:14px">
      <button class="res-btn retry" id="resBtnRetry" style="display:none" onclick="tryAgain()">↺ Try Again</button>
      <button class="res-btn" id="resBtnNext" onclick="nextProblem()">Next Problem →</button>
    </div>
  </div>
</div>

<script>
const AudioCtx=window.AudioContext||window.webkitAudioContext;let actx;
function ac(){if(!actx)actx=new AudioCtx();return actx;}
function beep(f,d=0.15,vol=0.08,type='sine'){try{const a=ac(),o=a.createOscillator(),g=a.createGain();o.connect(g);g.connect(a.destination);o.type=type;o.frequency.value=f;g.gain.setValueAtTime(vol,a.currentTime);g.gain.exponentialRampToValueAtTime(0.001,a.currentTime+d);o.start();o.stop(a.currentTime+d);}catch(e){}}
function playCorrect(){[523,659,784,1047].forEach((f,i)=>setTimeout(()=>beep(f,0.2,0.09),i*80));}
function playWrong(){[300,200].forEach((f,i)=>setTimeout(()=>beep(f,0.25,0.1,'sawtooth'),i*150));}
function playTick(){beep(800,0.05,0.04);}
function playWarning(){beep(440,0.1,0.08,'triangle');}
function playClick(){beep(600,0.06,0.04);}

let currentSubject='<?= $subject ?>',currentDiff='medium',currentProblem=null;
let probIndex=0,userXP=<?= (int)($user['xp']??0) ?>,streak=<?= (int)($user['streak']??0) ?>;
let hintUsed=false,aiUsed=false,timerSecs=150,timerInt=null,startTime=Date.now();
const results=[];

const monsters={algebra:{ico:'🐉',name:'Equation Dragon'},arithmetic:{ico:'👾',name:'Number Goblin'},geometry:{ico:'🔷',name:'Shape Specter'},fractions:{ico:'🌀',name:'Fraction Phantom'},statistics:{ico:'📡',name:'Data Wraith'}};

function setSubject(s,el){
  currentSubject=s;
  document.querySelectorAll('.stab').forEach(b=>b.classList.remove('active'));
  el.classList.add('active');
  const m=monsters[s];
  document.getElementById('monIco').textContent=m.ico;
  document.getElementById('monName').textContent=m.name;
  document.getElementById('monHP').style.width='100%';
  document.getElementById('monHPVal').textContent='100';
  playClick();fetchAIProblem();
}

const diffDescs={easy:'Simple 1-step problems',medium:'Medium problems with 2-step equations',hard:'Multi-step challenge problems'};
function setDiff(d,el){
  currentDiff=d;
  document.querySelectorAll('.diff-btn').forEach(b=>b.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('diffDesc').textContent=diffDescs[d];
  playClick();fetchAIProblem();
}

function clearAns(){document.getElementById('ansInput').value='';}

async function fetchAIProblem(){
  const submitBtn=document.getElementById('submitBtn');
  submitBtn.disabled=true;
  document.getElementById('probQ').innerHTML='<span class="ldots"><span></span><span></span><span></span></span>';
  document.getElementById('probCtx').textContent='Generating AI problem...';
  document.getElementById('probType').textContent='LOADING...';
  document.getElementById('ansInput').value='';
  document.getElementById('ansInput').className='ans-input';
  document.getElementById('probCard').className='prob-card';
  hintUsed=false;aiUsed=false;startTime=Date.now();
  document.getElementById('hintBox').classList.remove('show');
  document.getElementById('aiHintBox').classList.remove('show');
  document.getElementById('hintBtn').disabled=false;
  document.getElementById('hintBtn').textContent='💡 Reveal Hint (−10 XP)';
  document.getElementById('aiBtn').disabled=false;
  document.getElementById('aiBtn').textContent='✨ Ask AI Oracle';

  const prompt=`Generate a ${currentDiff} ${currentSubject} math problem for a middle school student.
Return ONLY valid JSON:
{"type":"short label","question":"the problem","answer":"numeric answer only","hint":"one-sentence hint","explanation":"step-by-step 3-4 lines using \\n"}
No markdown, no extra text.`;

  try{
    const resp=await fetch('https://api.anthropic.com/v1/messages',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({model:'claude-sonnet-4-20250514',max_tokens:500,messages:[{role:'user',content:prompt}]})
    });
    const data=await resp.json();
    currentProblem=JSON.parse(data.content[0].text.replace(/```json|```/g,'').trim());
  }catch(e){
    const fallbacks={algebra:[{type:'Solve for x',question:'3x + 7 = 22',answer:'5',hint:'Subtract 7 first.',explanation:'3x+7=22\n3x=15\nx=5'}],arithmetic:[{type:'Calculate',question:'48 × 25 = ?',answer:'1200',hint:'48×100÷4',explanation:'4800÷4=1200'}],geometry:[{type:'Find the area',question:'Rectangle 12cm × 8cm. Area=?',answer:'96',hint:'length × width',explanation:'12×8=96cm²'}],fractions:[{type:'Simplify',question:'3/4 + 1/8 = ?',answer:'7/8',hint:'Common denominator of 8',explanation:'6/8+1/8=7/8'}],statistics:[{type:'Mean',question:'4,8,6,10,7. Mean=?',answer:'7',hint:'Sum÷count',explanation:'35÷5=7'}]};
    const list=fallbacks[currentSubject]||fallbacks.algebra;
    currentProblem=list[Math.floor(Math.random()*list.length)];
  }

  document.getElementById('probType').textContent=currentProblem.type.toUpperCase();
  document.getElementById('probQ').textContent=currentProblem.question;
  document.getElementById('probCtx').textContent='Answer precisely. Use decimals or fractions if needed.';
  submitBtn.disabled=false;
  resetTimer();
  document.getElementById('ansInput').focus();
  updateDots();
}

async function submitAnswer(){
  const userAns=document.getElementById('ansInput').value.trim();
  if(!userAns)return;
  clearInterval(timerInt);
  const timeTaken=Math.round((Date.now()-startTime)/1000);
  const correct=String(currentProblem.answer).toLowerCase().trim();
  const isCorrect=userAns.toLowerCase()===correct||parseFloat(userAns)===parseFloat(correct);
  const xpEarned=isCorrect?(hintUsed?25:50):0;

  // Save to DB
  const fd=new FormData();
  fd.append('action','save_attempt');
  fd.append('subject',currentSubject);
  fd.append('difficulty',currentDiff);
  fd.append('question',currentProblem.question);
  fd.append('user_answer',userAns);
  fd.append('correct_answer',currentProblem.answer);
  fd.append('is_correct',isCorrect?1:0);
  fd.append('xp_earned',xpEarned);
  fd.append('hint_used',hintUsed?1:0);
  fd.append('time_taken',timeTaken);
  fetch('api.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
    if(d.xp){userXP=d.xp;streak=d.streak;document.getElementById('navXP').textContent=userXP.toLocaleString();document.getElementById('navStreak').textContent=streak;}
  });

  // Store for feedback page
  sessionStorage.setItem('isCorrect',isCorrect);
  sessionStorage.setItem('userAnswer',userAns);
  sessionStorage.setItem('correctAnswer',currentProblem.answer);
  sessionStorage.setItem('problemExplanation',currentProblem.explanation);
  sessionStorage.setItem('problemQuestion',currentProblem.question);

  if(isCorrect){
    document.getElementById('ansInput').classList.add('correct');
    document.getElementById('probCard').classList.add('correct');
    const curHP=parseInt(document.getElementById('monHP').style.width)||75;
    const newHP=Math.max(0,curHP-25);
    document.getElementById('monHP').style.width=newHP+'%';
    document.getElementById('monHPVal').textContent=newHP;
    results.push('correct');playCorrect();showResult(true,xpEarned);
  }else{
    document.getElementById('ansInput').classList.add('wrong');
    document.getElementById('probCard').classList.add('wrong');
    results.push('wrong');playWrong();showResult(false,0);
  }
  updateDots();
}

function showResult(win,xp){
  document.getElementById('resEmoji').textContent=win?'🎉':'💀';
  const t=document.getElementById('resTitle');t.textContent=win?'Correct!':'Wrong!';t.className='res-title '+(win?'win':'lose');
  document.getElementById('resSub').textContent=win?'Great work! Moving to next problem.':('The answer was: '+currentProblem.answer);
  document.getElementById('resXP').textContent=win?`+${xp} XP Earned!`:'No XP this round.';
  document.getElementById('resBtnRetry').style.display=win?'none':'block';
  document.getElementById('resBtnNext').textContent=win?'Next Problem →':'Continue';
  document.getElementById('overlay').classList.add('show');
  if(win)spawnParticles();
}

function nextProblem(){
  document.getElementById('overlay').classList.remove('show');
  probIndex++;
  document.getElementById('curProbN').textContent=probIndex+1;
  const pct=Math.round((probIndex/10)*100);
  document.getElementById('progFill').style.width=pct+'%';
  document.getElementById('progPct').textContent=pct+'% COMPLETE';
  if(probIndex>=10){alert('🎉 Quest Complete! +200 XP Bonus!');window.location.href='dashboard.php';return;}
  fetchAIProblem();
}

function tryAgain(){document.getElementById('overlay').classList.remove('show');window.location.href='feedback.php';}

function useHint(){
  if(hintUsed||!currentProblem)return;hintUsed=true;
  document.getElementById('hintBox').textContent='💡 '+currentProblem.hint;
  document.getElementById('hintBox').classList.add('show');
  document.getElementById('hintBtn').disabled=true;
  document.getElementById('hintBtn').textContent='Hint revealed (−10 XP)';
  beep(440,0.15,0.07,'triangle');
}

async function getAIHint(){
  if(aiUsed||!currentProblem)return;aiUsed=true;
  document.getElementById('aiBtn').disabled=true;document.getElementById('aiBtn').textContent='Thinking...';
  document.getElementById('aiHintBox').classList.add('show');
  document.getElementById('aiHintText').innerHTML='<span class="ldots"><span></span><span></span><span></span></span>';
  try{
    const resp=await fetch('https://api.anthropic.com/v1/messages',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({model:'claude-sonnet-4-20250514',max_tokens:200,messages:[{role:'user',content:`Student solving: "${currentProblem.question}"\nGive a 2-3 sentence conceptual hint WITHOUT revealing the answer. Be encouraging.`}]})});
    const d=await resp.json();document.getElementById('aiHintText').textContent=d.content[0].text;
  }catch(e){document.getElementById('aiHintText').textContent='Think about what operation undoes the last step. Work backwards!';}
  document.getElementById('aiBtn').textContent='✨ AI hint revealed';
}

function resetTimer(){
  clearInterval(timerInt);timerSecs=150;updateTimerDisplay();
  timerInt=setInterval(()=>{timerSecs--;updateTimerDisplay();if(timerSecs===30)playWarning();if(timerSecs>0&&timerSecs%10===0)playTick();if(timerSecs<=0){clearInterval(timerInt);submitAnswer();}},1000);
}
function updateTimerDisplay(){
  const m=Math.floor(timerSecs/60),s=timerSecs%60;
  const el=document.getElementById('timerDisp');
  el.textContent=`${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  el.className='timer-display'+(timerSecs<=30?timerSecs<=10?' crit':' warn':'');
}

function updateDots(){
  const c=document.getElementById('probDots');c.innerHTML='';
  for(let i=0;i<10;i++){const d=document.createElement('div');d.className='pd';if(results[i]==='correct')d.classList.add('done');else if(results[i]==='wrong')d.classList.add('wrong');else if(i===probIndex)d.classList.add('cur');c.appendChild(d);}
}

function spawnParticles(){
  const colors=['#00e5ff','#00e676','#ffab00','#a78bfa','#ff6e40'];
  for(let i=0;i<35;i++){const p=document.createElement('div');const angle=(i/35)*360;const dist=60+Math.random()*180;p.style.cssText=`position:fixed;left:${45+Math.random()*10}%;top:20%;width:${3+Math.random()*5}px;height:${3+Math.random()*5}px;border-radius:50%;background:${colors[i%colors.length]};pointer-events:none;z-index:300;animation:pfly ${0.7+Math.random()*1}s ease-out forwards;animation-delay:${Math.random()*0.2}s;--tx:${Math.cos(angle*Math.PI/180)*dist}px;--ty:${Math.sin(angle*Math.PI/180)*dist}px`;document.body.appendChild(p);setTimeout(()=>p.remove(),2000);}
}
const pStyle=document.createElement('style');pStyle.textContent='@keyframes pfly{0%{opacity:1;transform:translate(0,0) scale(1)}100%{opacity:0;transform:translate(var(--tx),var(--ty)) scale(0)}}';document.head.appendChild(pStyle);

document.getElementById('ansInput').addEventListener('keypress',e=>{if(e.key==='Enter')submitAnswer();});
updateDots();
fetchAIProblem();
</script>
</body>
</html>