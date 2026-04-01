<?php
require_once 'config.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Battle</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ── Dark mode ─────────────────────────────────────────────── */
:root,[data-theme="dark"]{
  --bg:#080b14;--s1:#0e1220;--s2:#141827;--s3:#1a2035;
  --b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.13);
  --cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--cglow:rgba(0,229,255,0.2);
  --violet:#7c3aed;--vdim:rgba(124,58,237,0.15);
  --amber:#ffab00;--green:#00e676;--gdim:rgba(0,230,118,0.1);
  --red:#ff5252;--rdim:rgba(255,82,82,0.1);
  --text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68);
  --nav-bg:rgba(8,11,20,0.97);
  --overlay-bg:rgba(8,11,20,0.85);
  --body-grad1:rgba(0,229,255,0.05);--body-grad2:rgba(124,58,237,0.06);
}
/* ── Light mode ────────────────────────────────────────────── */
[data-theme="light"]{
  --bg:#f0f4ff;--s1:#ffffff;--s2:#e8edf8;--s3:#d8e0f0;
  --b:rgba(0,0,0,0.08);--bb:rgba(0,0,0,0.15);
  --cyan:#0077cc;--cdim:rgba(0,119,204,0.10);--cglow:rgba(0,119,204,0.18);
  --violet:#6d28d9;--vdim:rgba(109,40,217,0.10);
  --amber:#c47f00;--green:#00a854;--gdim:rgba(0,168,84,0.10);
  --red:#cc3333;--rdim:rgba(204,51,51,0.10);
  --text:#0f1423;--tdim:rgba(15,20,35,0.45);--tmid:rgba(15,20,35,0.72);
  --nav-bg:rgba(240,244,255,0.97);
  --overlay-bg:rgba(240,244,255,0.85);
  --body-grad1:rgba(0,119,204,0.04);--body-grad2:rgba(109,40,217,0.04);
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:grid;grid-template-rows:auto auto 1fr;background-image:radial-gradient(ellipse at 20% 0%,var(--body-grad1) 0%,transparent 45%),radial-gradient(ellipse at 80% 100%,var(--body-grad2) 0%,transparent 45%);transition:background 0.3s,color 0.3s}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 26px;height:54px;background:var(--nav-bg);border-bottom:1px solid var(--b);backdrop-filter:blur(12px);transition:background 0.3s}
.nav-left{display:flex;align-items:center;gap:14px}
.back-btn{display:flex;align-items:center;gap:5px;color:var(--tdim);font-size:0.82em;cursor:pointer;background:none;border:none;padding:6px 10px;border-radius:7px;transition:all 0.2s;font-family:'DM Sans',sans-serif}
.back-btn:hover{color:var(--text);background:var(--s2)}
.nav-quest{font-family:'Syne',sans-serif;font-size:0.72em;font-weight:600;letter-spacing:0.08em;color:var(--tdim)}
.nav-quest b{color:var(--tmid)}
.nav-right{display:flex;align-items:center;gap:12px}
.nav-stat{font-size:0.82em;color:var(--tmid);display:flex;align-items:center;gap:4px}
.nav-stat b{color:var(--cyan)}
.theme-toggle{background:var(--s2);border:1px solid var(--b);border-radius:20px;padding:4px 10px;cursor:pointer;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;color:var(--tmid);display:flex;align-items:center;gap:4px;transition:all 0.22s}
.theme-toggle:hover{border-color:var(--bb);color:var(--text)}
.prog-strip{background:var(--s1);border-bottom:1px solid var(--b);padding:10px 26px;transition:background 0.3s}
.prog-meta{display:flex;justify-content:space-between;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.08em;color:var(--tdim);margin-bottom:7px}
.prog-bar{height:5px;background:var(--s2);border-radius:3px;overflow:hidden}
.prog-fill{height:100%;background:linear-gradient(90deg,#0099cc,var(--cyan));border-radius:3px;transition:width 0.8s ease;box-shadow:0 0 8px var(--cglow)}
.prob-dots{display:flex;gap:4px;margin-top:7px}
.pd{width:22px;height:5px;border-radius:2px;background:var(--s2);border:1px solid var(--b);transition:all 0.3s}
.pd.done{background:var(--cyan);border-color:var(--cyan);box-shadow:0 0 6px var(--cglow)}
.pd.wrong{background:var(--red);border-color:var(--red)}
.pd.cur{background:var(--cdim);border-color:var(--cyan);animation:curPulse 1.5s ease infinite}
@keyframes curPulse{0%,100%{opacity:1}50%{opacity:0.55}}
.battle{display:grid;grid-template-columns:1fr 340px;gap:22px;padding:22px 26px;max-width:1200px;margin:0 auto;width:100%}
.arena{display:flex;flex-direction:column;gap:18px}
.ai-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--cdim);border:1px solid var(--cglow);border-radius:20px;font-size:0.72em;color:var(--cyan);font-family:'Syne',sans-serif;font-weight:600;letter-spacing:0.06em;margin-bottom:6px}
.ai-dot{width:5px;height:5px;border-radius:50%;background:var(--cyan);animation:aiPulse 1s ease infinite}
@keyframes aiPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.5;transform:scale(0.8)}}
.subject-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:4px}
.stab{padding:6px 14px;background:var(--s2);border:1px solid var(--b);border-radius:7px;font-family:'Syne',sans-serif;font-size:0.72em;font-weight:600;letter-spacing:0.06em;color:var(--tdim);cursor:pointer;transition:all 0.2s}
.stab.active{background:var(--cdim);border-color:var(--cyan);color:var(--cyan)}
.stab:hover:not(.active){background:var(--s3);color:var(--tmid)}
.prob-card{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:32px;text-align:center;transition:border-color 0.3s,background 0.3s;position:relative;overflow:hidden}
.prob-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,0.015) 0%,transparent 60%);pointer-events:none}
.prob-card.correct{border-color:rgba(0,230,118,0.45);box-shadow:0 0 32px rgba(0,230,118,0.1)}
.prob-card.wrong{border-color:rgba(255,82,82,0.45);box-shadow:0 0 32px rgba(255,82,82,0.1)}
.prob-type{display:inline-block;padding:4px 12px;background:var(--s2);border:1px solid var(--b);border-radius:5px;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.1em;color:var(--tdim);text-transform:uppercase;margin-bottom:18px}
.prob-q{font-family:'Inter',Arial,sans-serif;font-weight:600;font-size:var(--prob-q-size,1.55em);color:var(--text);margin-bottom:6px;line-height:1.45;transition:font-size 0.15s ease}
.prob-q-controls{display:flex;align-items:center;justify-content:flex-end;gap:6px;margin-bottom:8px}
.pq-size-btn{width:26px;height:26px;border-radius:6px;border:1px solid var(--b);background:var(--s2);color:var(--tmid);font-size:0.95em;font-weight:700;cursor:pointer;display:grid;place-items:center;transition:all 0.18s;font-family:'DM Sans',sans-serif;line-height:1}
.pq-size-btn:hover{border-color:var(--cyan);color:var(--cyan);background:var(--cdim)}
.pq-size-label{font-family:'Syne',sans-serif;font-size:0.66em;font-weight:600;letter-spacing:0.08em;color:var(--tdim);min-width:28px;text-align:center}
.prob-ctx{font-size:0.84em;color:var(--tdim);font-style:italic;margin-bottom:24px}
.ans-label{font-family:'Syne',sans-serif;font-size:0.7em;font-weight:600;letter-spacing:0.1em;color:var(--tdim);text-transform:uppercase;margin-bottom:8px;text-align:left}
.ans-wrap{position:relative;margin-bottom:16px}
.ans-input{width:100%;padding:16px 20px;background:var(--s2);border:1.5px solid var(--b);border-radius:10px;color:var(--text);font-family:'Syne',sans-serif;font-size:1.4em;font-weight:700;text-align:center;letter-spacing:0.08em;outline:none;transition:all 0.25s}
.ans-input::placeholder{color:var(--tdim);font-weight:400;font-size:0.8em}
.ans-input:focus{border-color:var(--cyan);background:var(--cdim);box-shadow:0 0 0 3px var(--cdim)}
.ans-input.correct{border-color:rgba(0,230,118,0.6);background:var(--gdim);color:var(--green)}
.ans-input.wrong{border-color:rgba(255,82,82,0.6);background:var(--rdim);color:var(--red);animation:shake 0.35s ease}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-8px)}75%{transform:translateX(8px)}}
.btn-row{display:flex;gap:10px}
.btn{flex:1;padding:14px;border:none;border-radius:9px;font-family:'Syne',sans-serif;font-size:0.82em;font-weight:700;letter-spacing:0.08em;cursor:pointer;transition:all 0.22s}
.btn-clear{background:var(--rdim);border:1px solid rgba(255,82,82,0.2);color:var(--red)}
.btn-clear:hover{background:rgba(255,82,82,0.18);border-color:rgba(255,82,82,0.4)}
.btn-submit{background:linear-gradient(135deg,var(--cyan) 0%,#0099cc 100%);color:#020d14;flex:2.5;box-shadow:0 4px 18px var(--cdim)}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 26px var(--cglow)}
.btn-submit:disabled{opacity:0.4;cursor:not-allowed;transform:none}
.sidebar{display:flex;flex-direction:column;gap:16px}
.s-panel{background:var(--s1);border:1px solid var(--b);border-radius:12px;padding:18px;transition:background 0.3s}
.sp-title{font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.12em;color:var(--tdim);text-transform:uppercase;margin-bottom:12px;padding-bottom:9px;border-bottom:1px solid var(--b)}
.timer-display{text-align:center;font-family:'Syne',sans-serif;font-weight:800;font-size:2.2em;letter-spacing:0.04em;color:var(--cyan)}
.timer-display.warn{color:var(--amber);animation:timerWarn 0.6s ease infinite}
.timer-display.crit{color:var(--red);animation:timerWarn 0.3s ease infinite}
@keyframes timerWarn{0%,100%{opacity:1}50%{opacity:0.5}}
.timer-sub{text-align:center;font-size:0.72em;color:var(--tdim);margin-top:4px}
.hint-cost{font-size:0.75em;color:var(--tdim);margin-bottom:9px}
.hint-box{background:var(--cdim);border:1px solid var(--cyan);border-radius:8px;padding:12px;font-size:0.88em;color:var(--tmid);line-height:1.6;margin-bottom:10px;display:none;font-style:italic}
.hint-box.show{display:block}
.hint-btn{width:100%;padding:10px;background:var(--s2);border:1px solid var(--b);border-radius:8px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.73em;font-weight:600;letter-spacing:0.07em;cursor:pointer;transition:all 0.2s}
.hint-btn:hover{background:var(--s3);color:var(--tmid);border-color:var(--bb)}
.hint-btn:disabled{opacity:0.35;cursor:not-allowed}
.ai-hint-box{background:linear-gradient(135deg,var(--cdim),var(--vdim));border:1px solid var(--cyan);border-radius:8px;padding:12px;font-size:0.86em;color:var(--tmid);line-height:1.7;margin-bottom:10px;display:none}
.ai-hint-box.show{display:block}
.ai-hint-box-header{font-family:'Syne',sans-serif;font-size:0.68em;font-weight:700;letter-spacing:0.1em;color:var(--cyan);margin-bottom:7px;display:flex;align-items:center;gap:5px}
.ai-btn{width:100%;padding:10px;background:linear-gradient(135deg,var(--cdim),var(--vdim));border:1px solid var(--cyan);border-radius:8px;color:var(--cyan);font-family:'Syne',sans-serif;font-size:0.73em;font-weight:600;letter-spacing:0.07em;cursor:pointer;transition:all 0.2s}
.ai-btn:hover{background:linear-gradient(135deg,var(--cglow),rgba(124,58,237,0.2));border-color:var(--cyan)}
.ai-btn:disabled{opacity:0.35;cursor:not-allowed}
.ldots{display:inline-flex;gap:4px;align-items:center}
.ldots span{width:5px;height:5px;border-radius:50%;background:var(--cyan);animation:ldot 1s ease infinite}
.ldots span:nth-child(2){animation-delay:.2s}.ldots span:nth-child(3){animation-delay:.4s}
@keyframes ldot{0%,80%,100%{transform:scale(0.5);opacity:0.3}40%{transform:scale(1);opacity:1}}
.overlay{position:fixed;inset:0;z-index:200;display:flex;align-items:center;justify-content:center;background:var(--overlay-bg);backdrop-filter:blur(8px);opacity:0;pointer-events:none;transition:opacity 0.3s}
.overlay.show{opacity:1;pointer-events:all}
.result-box{background:var(--s1);border:1px solid var(--b);border-radius:18px;padding:40px 48px;text-align:center;max-width:400px;transform:scale(0.85);transition:transform 0.4s cubic-bezier(0.34,1.56,0.64,1)}
.overlay.show .result-box{transform:scale(1)}
.res-emoji{font-size:3.5em;display:block;margin-bottom:10px}
.res-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.7em;margin-bottom:6px}
.res-title.win{color:var(--green)}.res-title.lose{color:var(--red)}
.res-sub{font-size:0.88em;color:var(--tdim);margin-bottom:12px;font-style:italic}
.res-xp{font-family:'Syne',sans-serif;font-size:1em;font-weight:700;color:var(--cyan);margin:10px 0}
.res-btn{padding:13px 32px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:9px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.85em;font-weight:700;letter-spacing:0.09em;cursor:pointer;transition:all 0.22s;box-shadow:0 4px 18px var(--cdim);margin-top:6px}
.res-btn:hover{transform:translateY(-2px);box-shadow:0 8px 26px var(--cglow)}
.res-btn.retry{background:linear-gradient(135deg,#e65100,var(--amber));box-shadow:0 4px 18px rgba(255,171,0,0.25)}
.diff-row{display:flex;gap:6px;margin-bottom:14px}
.diff-btn{flex:1;padding:7px;background:var(--s2);border:1px solid var(--b);border-radius:7px;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;letter-spacing:0.06em;color:var(--tdim);cursor:pointer;transition:all 0.2s;text-align:center}
.diff-btn.active.e{background:var(--gdim);border-color:var(--green);color:var(--green)}
.diff-btn.active.m{background:rgba(255,171,0,0.12);border-color:var(--amber);color:var(--amber)}
.diff-btn.active.h{background:var(--rdim);border-color:var(--red);color:var(--red)}
.diff-btn:hover:not(.active){background:var(--s3);color:var(--tmid)}
.mon-row{display:flex;align-items:center;gap:12px;margin-bottom:16px;padding:12px 14px;background:var(--s2);border:1px solid var(--b);border-radius:10px}
.mon-ico{font-size:2em;flex-shrink:0}
.mon-info{flex:1;min-width:0}
.mon-name{font-family:'Syne',sans-serif;font-weight:700;font-size:0.88em;margin-bottom:4px}
.mon-hp-label{font-size:0.72em;color:var(--tdim);margin-bottom:4px}
.mon-hp-bar{height:5px;background:var(--s3);border-radius:3px;overflow:hidden}
.mon-hp-fill{height:100%;background:linear-gradient(90deg,var(--red),#ff7043);transition:width 0.8s ease}
@media(max-width:900px){.battle{grid-template-columns:1fr;padding:16px}.sidebar{display:grid;grid-template-columns:1fr 1fr}}
@media(max-width:600px){.sidebar{grid-template-columns:1fr}nav{padding:0 14px}}

/* ── Floating calculator ────────────────────────────────────── */
.calc-fab{position:fixed;bottom:24px;right:24px;z-index:150;width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;cursor:pointer;font-size:1.35em;box-shadow:0 4px 18px var(--cdim);transition:transform 0.2s,box-shadow 0.2s;display:grid;place-items:center}
.calc-fab:hover{transform:translateY(-3px);box-shadow:0 8px 26px var(--cglow)}
.calc-fab.open{background:linear-gradient(135deg,var(--red),#cc2200);box-shadow:0 4px 18px var(--rdim)}

.calc-popup{position:fixed;bottom:84px;right:24px;z-index:149;width:300px;background:var(--s1);border:1px solid var(--b);border-radius:16px;padding:16px;box-shadow:0 12px 48px rgba(0,0,0,0.35);opacity:0;pointer-events:none;transform:translateY(12px) scale(0.97);transform-origin:bottom right;transition:opacity 0.22s ease,transform 0.22s cubic-bezier(0.34,1.56,0.64,1)}
.calc-popup.show{opacity:1;pointer-events:all;transform:translateY(0) scale(1)}

.calc-screen{background:var(--s2);border:1px solid var(--b);border-radius:10px;padding:10px 14px;margin-bottom:12px;text-align:right}
.calc-expr{font-family:'DM Sans',sans-serif;font-size:0.78em;color:var(--tdim);min-height:1.2em;word-break:break-all;letter-spacing:0.02em}
.calc-result{font-family:'Syne',sans-serif;font-weight:800;font-size:1.7em;color:var(--text);min-height:1.2em;letter-spacing:0.02em}
.calc-result.error{color:var(--red);font-size:1em}

.calc-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:6px}
.ck{padding:9px 4px;border:1px solid var(--b);border-radius:8px;background:var(--s2);color:var(--tmid);font-family:'Syne',sans-serif;font-size:0.72em;font-weight:600;cursor:pointer;text-align:center;transition:all 0.15s;letter-spacing:0.02em;line-height:1.2}
.ck:hover{background:var(--s3);border-color:var(--bb);color:var(--text)}
.ck:active{transform:scale(0.93)}
.ck.op{color:var(--cyan);border-color:var(--cdim);background:var(--cdim)}
.ck.op:hover{background:rgba(0,229,255,0.18);border-color:var(--cyan)}
.ck.eq{background:linear-gradient(135deg,var(--cyan),#0099cc);color:#020d14;border-color:transparent;grid-column:span 2}
.ck.eq:hover{opacity:0.88}
.ck.clr{color:var(--red);border-color:var(--rdim);background:var(--rdim)}
.ck.clr:hover{background:rgba(255,82,82,0.18)}
.ck.fn{color:var(--amber);border-color:rgba(255,171,0,0.2);background:rgba(255,171,0,0.08);font-size:0.66em}
.ck.fn:hover{background:rgba(255,171,0,0.16);border-color:var(--amber)}
.ck.use{background:linear-gradient(135deg,var(--green),#00aa44);color:#020d14;border-color:transparent;font-size:0.7em}
.ck.use:hover{opacity:0.88}
</style>
</head>
<body>
<nav>
  <div class="nav-left">
    <button class="back-btn" onclick="window.location.href='dashboard.php'">← Back</button>
    <span class="nav-quest">Quest: <b id="questName">Algebra Castle</b></span>
  </div>
  <div class="nav-right">
    <div class="nav-stat">⭐ <b id="navXP">0</b></div>
    <div class="nav-stat">🔥 <b id="navStreak">0</b></div>
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn"><span id="themeIcon">☀️</span><span id="themeLabel">Light</span></button>
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
      <!-- ── Subject tabs (Word Problems added) ── -->
      <div class="subject-tabs">
        <button class="stab active"  onclick="setSubject('algebra',this)">∑ Algebra</button>
        <button class="stab"         onclick="setSubject('arithmetic',this)">+ Arithmetic</button>
        <button class="stab"         onclick="setSubject('geometry',this)">📐 Geometry</button>
        <button class="stab"         onclick="setSubject('fractions',this)">½ Fractions</button>
        <button class="stab"         onclick="setSubject('wordproblems',this)">📝 Word Problems</button>
        <button class="stab"         onclick="setSubject('statistics',this)">📊 Statistics</button>
      </div>
    </div>
    <div class="mon-row">
      <div class="mon-ico" id="monIco">🐉</div>
      <div class="mon-info">
        <div class="mon-name" id="monName">Equation Dragon</div>
        <div class="mon-hp-label">HP: <span id="monHPVal">100</span>/100</div>
        <div class="mon-hp-bar"><div class="mon-hp-fill" id="monHP" style="width:100%"></div></div>
      </div>
      <div style="font-size:0.75em;color:var(--tdim);text-align:right;flex-shrink:0">
        <span class="ai-pill"><span class="ai-dot"></span>AI</span>
      </div>
    </div>
    <div class="prob-card" id="probCard">
      <div class="prob-type" id="probType">LOADING...</div>
      <div class="prob-q-controls">
        <span class="pq-size-label" id="pqSizeLabel">A</span>
        <button class="pq-size-btn" onclick="changeQSize(-1)" title="Decrease text size">−</button>
        <button class="pq-size-btn" onclick="changeQSize(1)"  title="Increase text size">+</button>
      </div>
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
        <button class="diff-btn e"   onclick="setDiff('easy',this)">Easy</button>
        <button class="diff-btn m active" onclick="setDiff('medium',this)">Med</button>
        <button class="diff-btn h"   onclick="setDiff('hard',this)">Hard</button>
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
// ── Theme ─────────────────────────────────────────────────────
const html = document.documentElement;
applyTheme(localStorage.getItem('mq_theme') || 'dark');
function applyTheme(t){
  html.setAttribute('data-theme',t);
  localStorage.setItem('mq_theme',t);
  document.getElementById('themeIcon').textContent = t==='dark'?'☀️':'🌙';
  document.getElementById('themeLabel').textContent = t==='dark'?'Light':'Dark';
}
function toggleTheme(){applyTheme(html.getAttribute('data-theme')==='dark'?'light':'dark');}

// ── Audio ─────────────────────────────────────────────────────
const AudioCtx=window.AudioContext||window.webkitAudioContext;let actx;
function ac(){if(!actx)actx=new AudioCtx();return actx;}
function beep(f,d=0.15,vol=0.08,type='sine'){try{const a=ac(),o=a.createOscillator(),g=a.createGain();o.connect(g);g.connect(a.destination);o.type=type;o.frequency.value=f;g.gain.setValueAtTime(vol,a.currentTime);g.gain.exponentialRampToValueAtTime(0.001,a.currentTime+d);o.start();o.stop(a.currentTime+d);}catch(e){}}
function playCorrect(){[523,659,784,1047].forEach((f,i)=>setTimeout(()=>beep(f,0.2,0.09),i*80));}
function playWrong(){[300,200].forEach((f,i)=>setTimeout(()=>beep(f,0.25,0.1,'sawtooth'),i*150));}
function playTick(){beep(800,0.05,0.04);}
function playWarning(){beep(440,0.1,0.08,'triangle');}
function playClick(){beep(600,0.06,0.04);}

// ── State ─────────────────────────────────────────────────────
let currentSubject='algebra', currentDiff='medium', currentProblem=null;
let probIndex=0, userXP=0, streak=0;
let hintUsed=false, aiUsed=false, timerSecs=150, timerInt=null, startTime=Date.now();
const results=[];
let answered = false;  // prevents double-submit on same question
let loading   = false; // prevents re-entrant fetchAIProblem calls

// ── Anti-repeat tracking ──────────────────────────────────────
// ── Question queue (shuffle-based, no repeats) ───────────────
let questionQueue = [];   // shuffled pool for current subject+difficulty
let queueIndex    = 0;    // current position in queue

function shuffle(arr){
  const a = [...arr];
  for(let i=a.length-1;i>0;i--){
    const j=Math.floor(Math.random()*(i+1));
    [a[i],a[j]]=[a[j],a[i]];
  }
  return a;
}

function buildQueue(subject, difficulty){
  // Combine all difficulties for more variety, weight selected difficulty 3x
  const bank = fallbacks[subject] || fallbacks.algebra;
  const main  = bank[difficulty]  || [];
  const easy  = bank.easy   || [];
  const med   = bank.medium || [];
  const hard  = bank.hard   || [];

  // Pool = selected difficulty x3 + others x1 (more variety, still focused)
  const pool = [
    ...main, ...main, ...main,
    ...(difficulty !== 'easy'   ? easy  : []),
    ...(difficulty !== 'medium' ? med   : []),
    ...(difficulty !== 'hard'   ? hard  : []),
  ];
  questionQueue = shuffle(pool);
  queueIndex    = 0;
}

function nextFromQueue(){
  if(queueIndex >= questionQueue.length){
    // Reshuffled when exhausted — guaranteed fresh order
    questionQueue = shuffle(questionQueue);
    queueIndex    = 0;
  }
  return questionQueue[queueIndex++];
}

// ── Monster + quest config (word problems added) ──────────────
const monsters={
  algebra:      {ico:'🐉', name:'Equation Dragon'},
  arithmetic:   {ico:'👾', name:'Number Goblin'},
  geometry:     {ico:'🔷', name:'Shape Specter'},
  fractions:    {ico:'🌀', name:'Fraction Phantom'},
  wordproblems: {ico:'📖', name:'Word Wizard'},
  statistics:   {ico:'📡', name:'Data Wraith'},
};
const questNames={
  algebra:      'Algebra Castle',
  arithmetic:   'Arithmetic Arena',
  geometry:     'Geometry Galaxy',
  fractions:    'Fraction Dungeon',
  wordproblems: 'Word Problem Dungeon',
  statistics:   'Stats Stronghold',
};

// ── Expanded fallback bank ─────────────────────────────────────
const fallbacks = {
  algebra: {
    easy: [
      {type:'Solve for x',   question:'x + 9 = 15',                           answer:'6',      hint:'Subtract 9 from both sides.',          explanation:'x+9=15\nx=15−9\nx=6'},
      {type:'Solve for x',   question:'2x = 14',                               answer:'7',      hint:'Divide both sides by 2.',              explanation:'2x=14\nx=14÷2\nx=7'},
      {type:'Solve for x',   question:'x − 4 = 11',                           answer:'15',     hint:'Add 4 to both sides.',                 explanation:'x−4=11\nx=11+4\nx=15'},
      {type:'Evaluate',      question:'If y = 3, what is 4y + 2?',             answer:'14',     hint:'Substitute y=3 first.',                explanation:'4(3)+2=12+2=14'},
      {type:'Solve for x',   question:'x ÷ 5 = 6',                            answer:'30',     hint:'Multiply both sides by 5.',            explanation:'x÷5=6\nx=6×5\nx=30'},
    ],
    medium: [
      {type:'Solve for x',   question:'3x + 7 = 22',                          answer:'5',      hint:'Subtract 7 first, then divide.',       explanation:'3x+7=22\n3x=15\nx=5'},
      {type:'Solve for x',   question:'2x − 5 = 13',                          answer:'9',      hint:'Add 5 to both sides first.',           explanation:'2x−5=13\n2x=18\nx=9'},
      {type:'Solve for x',   question:'4x + 3 = 19',                          answer:'4',      hint:'Subtract 3, then divide by 4.',        explanation:'4x+3=19\n4x=16\nx=4'},
      {type:'Evaluate',      question:'Simplify: 3(x+4) when x=2',            answer:'18',     hint:'Distribute first or substitute.',      explanation:'3(2+4)=3(6)=18'},
      {type:'Solve for x',   question:'5x − 10 = 20',                         answer:'6',      hint:'Add 10 to both sides first.',          explanation:'5x−10=20\n5x=30\nx=6'},
    ],
    hard: [
      {type:'Two-step',      question:'2(x + 3) = 14',                        answer:'4',      hint:'Distribute 2, then solve.',            explanation:'2x+6=14\n2x=8\nx=4'},
      {type:'Two variables', question:'If 2x + y = 10 and x = 3, find y',    answer:'4',      hint:'Substitute x=3.',                      explanation:'2(3)+y=10\n6+y=10\ny=4'},
      {type:'Solve for x',   question:'3(2x − 1) = 15',                       answer:'3',      hint:'Distribute then isolate x.',           explanation:'6x−3=15\n6x=18\nx=3'},
      {type:'Inequality',    question:'Smallest integer where 4x > 20?',      answer:'6',      hint:'Solve 4x>20 then find integer.',       explanation:'4x>20\nx>5\nSmallest integer=6'},
      {type:'Systems',       question:'x + y = 9 and x − y = 3. Find x.',    answer:'6',      hint:'Add the two equations together.',      explanation:'2x=12\nx=6'},
    ],
  },
  geometry: {
    easy: [
      {type:'Area',          question:'Rectangle 12cm × 8cm. Area=?',         answer:'96',     hint:'Area = length × width.',               explanation:'12×8=96 cm²'},
      {type:'Area',          question:'Square with side 7cm. Area=?',          answer:'49',     hint:'Area = side².',                        explanation:'7²=49 cm²'},
      {type:'Perimeter',     question:'Rectangle 10m × 4m. Perimeter=?',      answer:'28',     hint:'P = 2(l+w).',                          explanation:'2(10+4)=2(14)=28 m'},
      {type:'Angles',        question:'Triangle has angles 60° and 70°. Third angle=?', answer:'50', hint:'Angles sum to 180°.',          explanation:'180−60−70=50°'},
      {type:'Perimeter',     question:'Equilateral triangle, side 9cm. Perimeter=?', answer:'27', hint:'All 3 sides are equal.',          explanation:'9×3=27 cm'},
    ],
    medium: [
      {type:'Area',          question:'Triangle: base 10cm, height 6cm. Area=?', answer:'30',  hint:'Area = ½ × base × height.',          explanation:'½×10×6=30 cm²'},
      {type:'Circles',       question:'Circle radius 7cm. Circumference=? (π≈3.14, nearest whole)', answer:'44', hint:'C = 2πr.',        explanation:'2×3.14×7≈43.96≈44 cm'},
      {type:'Pythagorean',   question:'Right triangle: legs 3cm and 4cm. Hypotenuse=?', answer:'5', hint:'a²+b²=c².',                  explanation:'3²+4²=9+16=25\n√25=5 cm'},
      {type:'Area',          question:'Parallelogram: base 8m, height 5m. Area=?', answer:'40', hint:'Area = base × height.',           explanation:'8×5=40 m²'},
      {type:'Angles',        question:'Supplementary angles. One is 65°. Other=?', answer:'115', hint:'They sum to 180°.',              explanation:'180−65=115°'},
    ],
    hard: [
      {type:'Circles',       question:'Circle area = 78.5 cm². Radius=? (π≈3.14)', answer:'5', hint:'A=πr², solve for r.',             explanation:'78.5=3.14×r²\nr²=25\nr=5 cm'},
      {type:'Composite',     question:'L-shape: 10×8 minus 4×3 cut from corner. Area=?', answer:'68', hint:'Subtract the cut-out.',    explanation:'10×8=80\n4×3=12\n80−12=68 cm²'},
      {type:'Pythagorean',   question:'Hypotenuse=13, one leg=5. Other leg=?',  answer:'12',    hint:'5²+b²=13².',                        explanation:'25+b²=169\nb²=144\nb=12'},
      {type:'Volume',        question:'Box: 4cm × 3cm × 5cm. Volume=?',         answer:'60',    hint:'V = l × w × h.',                    explanation:'4×3×5=60 cm³'},
      {type:'Scale',         question:'Triangle sides 3,4,5. Similar triangle hypotenuse=20. Shortest side=?', answer:'12', hint:'Scale=20÷5=4.', explanation:'Scale=4\nShortest=3×4=12'},
    ],
  },
  fractions: {
    easy: [
      {type:'Simplify',      question:'Simplify: 6/8',                         answer:'3/4',   hint:'Divide top and bottom by 2.',          explanation:'GCF=2\n6÷2=3, 8÷2=4\n=3/4'},
      {type:'Add',           question:'1/4 + 1/4 = ?',                         answer:'1/2',   hint:'Same denominator — add the tops.',     explanation:'2/4=1/2'},
      {type:'Compare',       question:'Which is bigger: 3/4 or 2/3?',          answer:'3/4',   hint:'Convert to common denominator.',       explanation:'3/4=9/12, 2/3=8/12\n9/12>8/12 → 3/4'},
      {type:'Fraction of',   question:'What is 1/2 of 20?',                    answer:'10',    hint:'Multiply 20 × 1/2.',                   explanation:'20×½=10'},
      {type:'Subtract',      question:'3/5 − 1/5 = ?',                         answer:'2/5',   hint:'Same denominator — subtract tops.',    explanation:'3/5−1/5=2/5'},
    ],
    medium: [
      {type:'Add',           question:'3/4 + 1/8 = ?',                         answer:'7/8',   hint:'Common denominator is 8.',             explanation:'6/8+1/8=7/8'},
      {type:'Multiply',      question:'2/3 × 3/4 = ?',                         answer:'1/2',   hint:'Multiply tops, multiply bottoms.',     explanation:'6/12=1/2'},
      {type:'Divide',        question:'3/4 ÷ 1/2 = ?',                         answer:'3/2',   hint:'Multiply by the reciprocal.',          explanation:'3/4×2/1=6/4=3/2'},
      {type:'Mixed number',  question:'Convert 7/4 to a mixed number.',         answer:'1 3/4', hint:'Divide 7÷4.',                         explanation:'7÷4=1 remainder 3\n=1 3/4'},
      {type:'Subtract',      question:'5/6 − 1/4 = ?',                         answer:'7/12',  hint:'Common denominator is 12.',            explanation:'10/12−3/12=7/12'},
    ],
    hard: [
      {type:'Complex',       question:'(1/2 + 1/3) ÷ 5/6 = ?',                answer:'1',     hint:'Add first, then divide.',              explanation:'5/6÷5/6=1'},
      {type:'Percentage',    question:'What percent is 3/8?',                   answer:'37.5',  hint:'Divide 3÷8 × 100.',                   explanation:'3÷8=0.375\n×100=37.5%'},
      {type:'Scaling',       question:'Recipe needs 2/3 cup sugar. Making 1.5×. Sugar needed=?', answer:'1', hint:'Multiply 2/3 × 3/2.', explanation:'2/3×3/2=6/6=1 cup'},
      {type:'Ratio',         question:'Ratio 3:5. Total is 40. Larger share=?', answer:'25',   hint:'5 parts out of 8 total.',             explanation:'1 part=5\nLarger=5×5=25'},
      {type:'Mixed ops',     question:'2 1/2 + 1 3/4 = ?',                     answer:'4 1/4', hint:'Convert to improper fractions.',      explanation:'5/2+7/4=10/4+7/4=17/4=4 1/4'},
    ],
  },
  wordproblems: {
    easy: [
      {type:'Word problem',  question:'Jake has 24 apples. He gives 9 to friends. How many remain?', answer:'15', hint:'Subtract.',       explanation:'24−9=15 apples'},
      {type:'Word problem',  question:'A bus has 8 rows with 4 seats each. Total seats=?', answer:'32', hint:'Multiply rows × seats.',    explanation:'8×4=32 seats'},
      {type:'Word problem',  question:'Maria earns $6/hr. She works 5 hours. Total earned=?', answer:'30', hint:'Rate × time.',          explanation:'$6×5=$30'},
      {type:'Word problem',  question:'Tom reads 12 pages/day. Days needed for 60 pages=?', answer:'5', hint:'Divide total by daily rate.', explanation:'60÷12=5 days'},
      {type:'Word problem',  question:'Bag has 5 red and 7 blue marbles. Total=?', answer:'12', hint:'Just add them.',                   explanation:'5+7=12 marbles'},
    ],
    medium: [
      {type:'Word problem',  question:'Shirt costs $15 with 20% off. New price=?', answer:'12', hint:'20% of $15=$3 discount.',          explanation:'$15−$3=$12'},
      {type:'Word problem',  question:'Train travels 60 mph for 2.5 hours. Distance=?', answer:'150', hint:'Distance = speed × time.',  explanation:'60×2.5=150 miles'},
      {type:'Word problem',  question:'Class of 30. 2/5 are girls. How many girls=?', answer:'12', hint:'Multiply 30 × 2/5.',           explanation:'30×2/5=12 girls'},
      {type:'Word problem',  question:'$12 pizza split equally by 4 people. Each pays=?', answer:'3', hint:'Divide by 4.',              explanation:'$12÷4=$3'},
      {type:'Word problem',  question:'Temp at 8am: −3°C. Rose 11°C by noon. Noon temp=?', answer:'8', hint:'Add rise to start.',      explanation:'−3+11=8°C'},
    ],
    hard: [
      {type:'Word problem',  question:'Pool holds 2400L. Fills at 80L/min, leaks 20L/min. Minutes to fill=?', answer:'40', hint:'Net rate=80−20.', explanation:'Net=60L/min\n2400÷60=40 min'},
      {type:'Word problem',  question:'Two numbers sum to 50 and differ by 14. Larger number=?', answer:'32', hint:'Add both equations.', explanation:'2x=64\nx=32'},
      {type:'Word problem',  question:"Rectangle's length is 3× its width. Perimeter=48cm. Width=?", answer:'6', hint:'Express l=3w.', explanation:'2(3w+w)=48\n8w=48\nw=6'},
      {type:'Word problem',  question:'$500 at 4% simple interest/year. Total after 3 years=?', answer:'560', hint:'I=P×r×t.',          explanation:'I=500×0.04×3=$60\nTotal=$560'},
      {type:'Percent change',question:'Price went from $40 to $52. Percent increase=?', answer:'30', hint:'(change÷original)×100.',    explanation:'12÷40×100=30%'},
    ],
  },
  arithmetic: {
    easy: [
      {type:'Calculate',     question:'256 + 189 = ?',                          answer:'445',   hint:'Add column by column.',              explanation:'256+189=445'},
      {type:'Calculate',     question:'500 − 237 = ?',                          answer:'263',   hint:'Borrow from the hundreds.',          explanation:'500−237=263'},
      {type:'Calculate',     question:'17 × 6 = ?',                             answer:'102',   hint:'Think 10×6 + 7×6.',                 explanation:'60+42=102'},
      {type:'Calculate',     question:'144 ÷ 12 = ?',                           answer:'12',    hint:'12 × ? = 144.',                      explanation:'12×12=144'},
      {type:'Order of ops',  question:'6 + 4 × 2 = ?',                          answer:'14',    hint:'Multiply before adding.',            explanation:'4×2=8\n6+8=14'},
    ],
    medium: [
      {type:'Calculate',     question:'48 × 25 = ?',                            answer:'1200',  hint:'48 × 100 ÷ 4.',                     explanation:'4800÷4=1200'},
      {type:'Order of ops',  question:'(3 + 5)² − 10 = ?',                     answer:'54',    hint:'Brackets first, then exponent.',     explanation:'8²−10=64−10=54'},
      {type:'Percentage',    question:'15% of 80 = ?',                          answer:'12',    hint:'10% + 5% of 80.',                   explanation:'8+4=12'},
      {type:'Square root',   question:'√144 = ?',                               answer:'12',    hint:'What squared = 144?',               explanation:'12×12=144\n√144=12'},
      {type:'Prime',         question:'Is 97 prime? (yes/no)',                  answer:'yes',   hint:'Try dividing by 2,3,5,7.',          explanation:'No factor found\n97 is prime'},
    ],
    hard: [
      {type:'Order of ops',  question:'3² + (12 ÷ 4) × 5 − 1 = ?',           answer:'23',    hint:'PEMDAS: exponent, brackets, ×, +−.', explanation:'9+(3×5)−1=9+15−1=23'},
      {type:'LCM',           question:'LCM of 12 and 18 = ?',                   answer:'36',    hint:'List multiples of each.',            explanation:'12:12,24,36\n18:18,36\nLCM=36'},
      {type:'GCF',           question:'GCF of 48 and 60 = ?',                   answer:'12',    hint:'Factor both numbers.',               explanation:'48=2⁴×3\n60=2²×3×5\nGCF=12'},
      {type:'Sci notation',  question:'Write 45,000 in scientific notation.',   answer:'4.5 × 10^4', hint:'Move decimal left.',           explanation:'4.5×10⁴'},
      {type:'Powers',        question:'2³ × 5² = ?',                            answer:'200',   hint:'Calculate each power separately.',   explanation:'8×25=200'},
    ],
  },
  statistics: {
    easy: [
      {type:'Mean',          question:'4, 8, 6, 10, 7. Mean=?',                answer:'7',     hint:'Sum all, divide by count.',           explanation:'35÷5=7'},
      {type:'Median',        question:'Median of: 3, 7, 2, 9, 5',              answer:'5',     hint:'Sort first, find the middle.',        explanation:'Sorted: 2,3,5,7,9\nMedian=5'},
      {type:'Mode',          question:'Mode of: 4, 2, 4, 7, 2, 4',             answer:'4',     hint:'Most frequent value.',                explanation:'4 appears 3 times'},
      {type:'Range',         question:'Range of: 12, 5, 18, 9, 3',             answer:'15',    hint:'Max − Min.',                         explanation:'18−3=15'},
      {type:'Probability',   question:'Bag: 3 red, 2 blue. P(red) as fraction=?', answer:'3/5', hint:'Favorable ÷ total.',              explanation:'3÷5=3/5'},
    ],
    medium: [
      {type:'Mean',          question:'Scores: 72, 85, 90, 68, 95. Mean=?',    answer:'82',    hint:'Sum÷5.',                             explanation:'410÷5=82'},
      {type:'Median',        question:'Median of: 14, 22, 8, 31, 19, 27',      answer:'20.5',  hint:'Even count — average the two middle.', explanation:'Sorted: 8,14,19,22,27,31\n(19+22)÷2=20.5'},
      {type:'Probability',   question:'Roll a die. P(even)=?',                  answer:'1/2',   hint:'Count evens on a die.',              explanation:'2,4,6 → 3/6=1/2'},
      {type:'Probability',   question:'P(A)=0.4, P(B)=0.3, independent. P(A and B)=?', answer:'0.12', hint:'Multiply independents.',   explanation:'0.4×0.3=0.12'},
      {type:'Mean',          question:'Mean of 5 numbers is 14. Four are: 10,12,16,18. Fifth=?', answer:'14', hint:'Total=mean×count.', explanation:'70−(10+12+16+18)=70−56=14'},
    ],
    hard: [
      {type:'Weighted mean', question:'Scores: 80(×2), 90(×3), 70(×1). Weighted mean=?', answer:'84', hint:'Sum(score×weight)÷sum(weights).', explanation:'(160+270+70)÷6≈84'},
      {type:'Probability',   question:'Flip 3 fair coins. P(all heads)=?',     answer:'1/8',   hint:'½ × ½ × ½.',                        explanation:'(1/2)³=1/8'},
      {type:'Std deviation', question:'Data: 5,5,5,5,5. Standard deviation=?', answer:'0',     hint:'No spread = no deviation.',          explanation:'All values equal\nSD=0'},
      {type:'Compound prob', question:'Draw 2 cards from 52 without replacement. P(both aces)?  (nearest hundredth)', answer:'0.00', hint:'(4/52)×(3/51).', explanation:'12/2652≈0.0045≈0.00'},
      {type:'Outlier',       question:'Data: 10,12,11,13,100. Removing 100 will: (increase/decrease) the mean?', answer:'decrease', hint:'100 pulls the mean up.', explanation:'Mean with 100: 29.2\nWithout: 11.5\n→ decreases'},
    ],
  },
};

// ── Helper: pick problems by subject + difficulty ─────────────
function getFallback(subject, difficulty) {
  const bank = fallbacks[subject] || fallbacks.algebra;
  const pool = bank[difficulty] || bank.medium || Object.values(bank)[0];
  return pool;
}

// ── Question font-size control ────────────────────────────────
const Q_SIZES  = [1.1, 1.3, 1.55, 1.85, 2.2];  // em steps
const Q_LABELS = ['XS', 'S', 'M', 'L', 'XL'];
let   qSizeIdx = parseInt(localStorage.getItem('mq_qsize') || '2'); // default M

function applyQSize(){
  document.documentElement.style.setProperty('--prob-q-size', Q_SIZES[qSizeIdx] + 'em');
  document.getElementById('pqSizeLabel').textContent = Q_LABELS[qSizeIdx];
}
function changeQSize(dir){
  qSizeIdx = Math.min(Math.max(qSizeIdx + dir, 0), Q_SIZES.length - 1);
  localStorage.setItem('mq_qsize', qSizeIdx);
  applyQSize();
  playClick();
}
applyQSize(); // apply saved preference on load

// ── Read subject from URL param ───────────────────────────────
const urlSubject = new URLSearchParams(window.location.search).get('subject');
if(urlSubject && monsters[urlSubject]){
  currentSubject = urlSubject;
  document.querySelectorAll('.stab').forEach(b=>b.classList.remove('active'));
  document.querySelectorAll('.stab').forEach(b=>{
    if(b.textContent.toLowerCase().includes(urlSubject.slice(0,4))) b.classList.add('active');
  });
  const m = monsters[urlSubject];
  document.getElementById('monIco').textContent  = m.ico;
  document.getElementById('monName').textContent = m.name;
  document.getElementById('questName').textContent = questNames[urlSubject] || 'Battle';
}

function setSubject(s, el){
  currentSubject = s;
  document.querySelectorAll('.stab').forEach(b=>b.classList.remove('active'));
  el.classList.add('active');
  const m = monsters[s];
  document.getElementById('monIco').textContent  = m.ico;
  document.getElementById('monName').textContent = m.name;
  document.getElementById('monHP').style.width   = '100%';
  document.getElementById('monHPVal').textContent = '100';
  document.getElementById('questName').textContent = questNames[s] || 'Battle';
  buildQueue(s, currentDiff);
  playClick(); fetchAIProblem();
}

const diffDescs={easy:'Simple 1-step problems',medium:'Medium problems with 2-step equations',hard:'Multi-step challenge problems'};
function setDiff(d, el){
  currentDiff = d;
  document.querySelectorAll('.diff-btn').forEach(b=>b.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('diffDesc').textContent = diffDescs[d];
  buildQueue(currentSubject, d);
  playClick(); fetchAIProblem();
}

function clearAns(){ document.getElementById('ansInput').value = ''; }

// ── Fetch AI problem ──────────────────────────────────────────
function fetchAIProblem(){
  if(loading) return;
  loading  = true;
  answered = false;
  clearInterval(timerInt);

  const submitBtn = document.getElementById('submitBtn');
  submitBtn.disabled = true;

  // Reset UI
  document.getElementById('ansInput').value     = '';
  document.getElementById('ansInput').className = 'ans-input';
  document.getElementById('probCard').className = 'prob-card';
  document.getElementById('hintBox').classList.remove('show');
  document.getElementById('hintBox').textContent = '';
  document.getElementById('aiHintBox').classList.remove('show');
  document.getElementById('aiHintText').textContent = '';
  document.getElementById('hintBtn').disabled    = false;
  document.getElementById('hintBtn').textContent = '💡 Reveal Hint (−10 XP)';
  document.getElementById('aiBtn').disabled      = false;
  document.getElementById('aiBtn').textContent   = '✨ Ask AI Oracle';
  hintUsed = false; aiUsed = false; startTime = Date.now();

  // Get next problem from shuffled queue (instant, no API)
  currentProblem = nextFromQueue();

  // Render
  document.getElementById('probType').textContent = currentProblem.type.toUpperCase();
  document.getElementById('probQ').textContent    = currentProblem.question;
  document.getElementById('probCtx').textContent  = 'Answer precisely. Use decimals or fractions if needed.';
  submitBtn.disabled = false;
  loading = false;
  resetTimer();
  updateDots();
  document.getElementById('ansInput').focus();
}

// ── Submit answer ─────────────────────────────────────────────
async function submitAnswer(){
  if(loading || answered || !currentProblem) return; // guards
  const userAns = document.getElementById('ansInput').value.trim();
  if(!userAns) return;
  answered = true;             // lock — no more submits for this question
  clearInterval(timerInt);
  const timeTaken = Math.round((Date.now()-startTime)/1000);
  const correct   = String(currentProblem.answer).toLowerCase().trim();
  const isCorrect = userAns.toLowerCase()===correct || parseFloat(userAns)===parseFloat(correct);
  const xpEarned  = isCorrect ? (hintUsed?25:50) : 0;

  const fd = new FormData();
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
    if(d.new_xp !== undefined){ userXP=d.new_xp; streak=d.new_streak; }
    else if(d.xp){ userXP=d.xp; streak=d.streak; }
    document.getElementById('navXP').textContent     = userXP.toLocaleString();
    document.getElementById('navStreak').textContent = streak;
  }).catch(()=>{});

  sessionStorage.setItem('isCorrect',isCorrect);
  sessionStorage.setItem('userAnswer',userAns);
  sessionStorage.setItem('correctAnswer',currentProblem.answer);
  sessionStorage.setItem('problemExplanation',currentProblem.explanation);
  sessionStorage.setItem('problemQuestion',currentProblem.question);

  if(isCorrect){
    document.getElementById('ansInput').classList.add('correct');
    document.getElementById('probCard').classList.add('correct');
    const curHP = parseInt(document.getElementById('monHP').style.width)||100;
    const newHP = Math.max(0, curHP-25);
    document.getElementById('monHP').style.width    = newHP+'%';
    document.getElementById('monHPVal').textContent = newHP;
    results.push('correct'); playCorrect(); showResult(true, xpEarned);
  } else {
    document.getElementById('ansInput').classList.add('wrong');
    document.getElementById('probCard').classList.add('wrong');
    results.push('wrong'); playWrong(); showResult(false, 0);
  }
  updateDots();
}

function showResult(win, xp){
  document.getElementById('resEmoji').textContent = win ? '🎉' : '💀';
  const t = document.getElementById('resTitle');
  t.textContent  = win ? 'Correct!' : 'Wrong!';
  t.className    = 'res-title ' + (win?'win':'lose');
  document.getElementById('resSub').textContent = win ? 'Great work! Moving to next problem.' : ('The answer was: '+currentProblem.answer);
  document.getElementById('resXP').textContent  = win ? `+${xp} XP Earned!` : 'No XP this round.';
  document.getElementById('resBtnRetry').style.display = win ? 'none' : 'block';
  document.getElementById('resBtnNext').textContent    = win ? 'Next Problem →' : 'Continue';
  document.getElementById('overlay').classList.add('show');
  if(win) spawnParticles();
}

function nextProblem(){
  if(loading) return;          // wait for fetch to finish
  document.getElementById('overlay').classList.remove('show');
  probIndex++;
  document.getElementById('curProbN').textContent = probIndex+1;
  const pct = Math.round((probIndex/10)*100);
  document.getElementById('progFill').style.width  = pct+'%';
  document.getElementById('progPct').textContent   = pct+'% COMPLETE';
  if(probIndex>=10){ alert('🎉 Quest Complete! +200 XP Bonus!'); window.location.href='dashboard.php'; return; }
  fetchAIProblem();
}

function tryAgain(){ document.getElementById('overlay').classList.remove('show'); window.location.href='feedback.php'; }

function useHint(){
  if(hintUsed || !currentProblem || answered) return;
  hintUsed = true;
  document.getElementById('hintBox').textContent = '💡 '+currentProblem.hint;
  document.getElementById('hintBox').classList.add('show');
  document.getElementById('hintBtn').disabled    = true;
  document.getElementById('hintBtn').textContent = 'Hint revealed (−10 XP)';
  beep(440,0.15,0.07,'triangle');
}

async function getAIHint(){
  if(aiUsed || !currentProblem || answered) return;
  aiUsed = true;
  document.getElementById('aiBtn').disabled    = true;
  document.getElementById('aiBtn').textContent = 'Thinking...';
  document.getElementById('aiHintBox').classList.add('show');
  document.getElementById('aiHintText').innerHTML = '<span class="ldots"><span></span><span></span><span></span></span>';
  try {
    const resp = await fetch('https://api.anthropic.com/v1/messages',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({model:'claude-sonnet-4-20250514',max_tokens:200,messages:[{role:'user',content:`Student solving: "${currentProblem.question}"\nGive a 2-3 sentence conceptual hint WITHOUT revealing the answer. Be encouraging.`}]})});
    const d = await resp.json();
    document.getElementById('aiHintText').textContent = d.content[0].text;
  } catch(e) {
    document.getElementById('aiHintText').textContent = 'Think about what operation undoes the last step. Work backwards!';
  }
  document.getElementById('aiBtn').textContent = '✨ AI hint revealed';
}

// ── Timer ─────────────────────────────────────────────────────
function resetTimer(){
  clearInterval(timerInt); timerSecs=150; updateTimerDisplay();
  timerInt = setInterval(()=>{
    timerSecs--;
    updateTimerDisplay();
    if(timerSecs===30) playWarning();
    if(timerSecs>0 && timerSecs%10===0) playTick();
    if(timerSecs<=0){ clearInterval(timerInt); submitAnswer(); }
  },1000);
}
function updateTimerDisplay(){
  const m=Math.floor(timerSecs/60), s=timerSecs%60;
  const el=document.getElementById('timerDisp');
  el.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  el.className   = 'timer-display'+(timerSecs<=30 ? timerSecs<=10?' crit':' warn' : '');
}

// ── Dots ──────────────────────────────────────────────────────
function updateDots(){
  const c=document.getElementById('probDots'); c.innerHTML='';
  for(let i=0;i<10;i++){
    const d=document.createElement('div'); d.className='pd';
    if(results[i]==='correct') d.classList.add('done');
    else if(results[i]==='wrong') d.classList.add('wrong');
    else if(i===probIndex) d.classList.add('cur');
    c.appendChild(d);
  }
}

// ── Particles ─────────────────────────────────────────────────
function spawnParticles(){
  const colors=['#00e5ff','#00e676','#ffab00','#a78bfa','#ff6e40'];
  for(let i=0;i<35;i++){
    const p=document.createElement('div');
    const angle=(i/35)*360, dist=60+Math.random()*180;
    p.style.cssText=`position:fixed;left:${45+Math.random()*10}%;top:20%;width:${3+Math.random()*5}px;height:${3+Math.random()*5}px;border-radius:50%;background:${colors[i%colors.length]};pointer-events:none;z-index:300;animation:pfly ${0.7+Math.random()*1}s ease-out forwards;animation-delay:${Math.random()*0.2}s;--tx:${Math.cos(angle*Math.PI/180)*dist}px;--ty:${Math.sin(angle*Math.PI/180)*dist}px`;
    document.body.appendChild(p);
    setTimeout(()=>p.remove(),2000);
  }
}
const pStyle=document.createElement('style');
pStyle.textContent='@keyframes pfly{0%{opacity:1;transform:translate(0,0) scale(1)}100%{opacity:0;transform:translate(var(--tx),var(--ty)) scale(0)}}';
document.head.appendChild(pStyle);

// ── Init ──────────────────────────────────────────────────────
document.getElementById('ansInput').addEventListener('keypress',e=>{ if(e.key==='Enter') submitAnswer(); });
updateDots();
buildQueue(currentSubject, currentDiff); // build shuffled queue on load
fetchAIProblem();
</script>
<!-- ── Floating Calculator ───────────────────────────────────── -->
<button class="calc-fab" id="calcFab" onclick="toggleCalc()" title="Calculator">🧮</button>

<div class="calc-popup" id="calcPopup">
  <div class="calc-screen">
    <div class="calc-expr"   id="calcExpr"></div>
    <div class="calc-result" id="calcResult">0</div>
  </div>
  <div class="calc-grid">
    <!-- Row 1: sci functions -->
    <button class="ck fn" onclick="calcFn('sin')">sin</button>
    <button class="ck fn" onclick="calcFn('cos')">cos</button>
    <button class="ck fn" onclick="calcFn('tan')">tan</button>
    <button class="ck fn" onclick="calcFn('log')">log</button>
    <button class="ck fn" onclick="calcFn('ln')">ln</button>
    <!-- Row 2: sci functions cont -->
    <button class="ck fn" onclick="calcFn('sqrt')">√</button>
    <button class="ck fn" onclick="calcInsert('^')">xʸ</button>
    <button class="ck fn" onclick="calcInsert('Math.PI')">π</button>
    <button class="ck fn" onclick="calcInsert('Math.E')">e</button>
    <button class="ck fn" onclick="calcFn('abs')">|x|</button>
    <!-- Row 3: digits + ops -->
    <button class="ck"    onclick="calcInsert('7')">7</button>
    <button class="ck"    onclick="calcInsert('8')">8</button>
    <button class="ck"    onclick="calcInsert('9')">9</button>
    <button class="ck op" onclick="calcInsert('÷')">÷</button>
    <button class="ck clr" onclick="calcDel()">⌫</button>
    <!-- Row 4 -->
    <button class="ck"    onclick="calcInsert('4')">4</button>
    <button class="ck"    onclick="calcInsert('5')">5</button>
    <button class="ck"    onclick="calcInsert('6')">6</button>
    <button class="ck op" onclick="calcInsert('×')">×</button>
    <button class="ck clr" onclick="calcClear()">C</button>
    <!-- Row 5 -->
    <button class="ck"    onclick="calcInsert('1')">1</button>
    <button class="ck"    onclick="calcInsert('2')">2</button>
    <button class="ck"    onclick="calcInsert('3')">3</button>
    <button class="ck op" onclick="calcInsert('−')">−</button>
    <button class="ck op" onclick="calcInsert('(')"> ( </button>
    <!-- Row 6 -->
    <button class="ck"    onclick="calcInsert('0')">0</button>
    <button class="ck"    onclick="calcInsert('.')">.</button>
    <button class="ck op" onclick="calcInsert('%')">%</button>
    <button class="ck op" onclick="calcInsert('+')">+</button>
    <button class="ck op" onclick="calcInsert(')')"> ) </button>
    <!-- Row 7: = and use -->
    <button class="ck eq" onclick="calcEval()">=</button>
    <button class="ck use" onclick="calcUseAnswer()" id="calcUseBtn" style="display:none">→ Use</button>
  </div>
</div>

<script>
// ── Calculator logic ──────────────────────────────────────────
let calcExprStr = '';
let calcLastResult = null;

function toggleCalc(){
  const popup = document.getElementById('calcPopup');
  const fab   = document.getElementById('calcFab');
  const open  = popup.classList.toggle('show');
  fab.classList.toggle('open', open);
  fab.textContent = open ? '✕' : '🧮';
}

function calcInsert(val){
  calcExprStr += val;
  renderCalc();
}

function calcFn(fn){
  calcExprStr += fn + '(';
  renderCalc();
}

function calcDel(){
  // Remove last character (handles multi-char tokens like 'sin(')
  calcExprStr = calcExprStr.replace(/(?:sin\(|cos\(|tan\(|log\(|ln\(|abs\(|sqrt\(|Math\.PI|Math\.E|.)$/, '');
  renderCalc();
}

function calcClear(){
  calcExprStr = '';
  calcLastResult = null;
  document.getElementById('calcResult').textContent = '0';
  document.getElementById('calcResult').className   = 'calc-result';
  document.getElementById('calcExpr').textContent   = '';
  document.getElementById('calcUseBtn').style.display = 'none';
}

function calcEval(){
  if(!calcExprStr.trim()) return;
  try {
    // Replace display symbols with JS equivalents
    let expr = calcExprStr
      .replace(/×/g, '*')
      .replace(/÷/g, '/')
      .replace(/−/g, '-')
      .replace(/\^/g, '**')
      .replace(/sqrt\(/g,  'Math.sqrt(')
      .replace(/sin\(/g,   'Math.sin(')
      .replace(/cos\(/g,   'Math.cos(')
      .replace(/tan\(/g,   'Math.tan(')
      .replace(/log\(/g,   'Math.log10(')
      .replace(/ln\(/g,    'Math.log(')
      .replace(/abs\(/g,   'Math.abs(')
      .replace(/%/g,       '/100');

    // eslint-disable-next-line no-new-func
    const result = Function('"use strict"; return (' + expr + ')')();

    if(!isFinite(result)) throw new Error('Not finite');

    // Round to 10 sig figs to avoid floating point noise
    const rounded = parseFloat(result.toPrecision(10));
    calcLastResult = rounded;
    document.getElementById('calcExpr').textContent   = calcExprStr + ' =';
    document.getElementById('calcResult').textContent = rounded;
    document.getElementById('calcResult').className   = 'calc-result';
    document.getElementById('calcUseBtn').style.display = 'block';
    calcExprStr = String(rounded); // continue from result
    playCorrect();
  } catch(e){
    document.getElementById('calcResult').textContent = 'Error';
    document.getElementById('calcResult').className   = 'calc-result error';
    document.getElementById('calcUseBtn').style.display = 'none';
    beep(200, 0.2, 0.08, 'sawtooth');
  }
}

function calcUseAnswer(){
  if(calcLastResult === null) return;
  const input = document.getElementById('ansInput');
  input.value = calcLastResult;
  input.focus();
  // Flash the input
  input.style.transition = 'background 0.1s';
  input.style.background = 'var(--cdim)';
  setTimeout(() => { input.style.background = ''; }, 400);
  toggleCalc();
  beep(600, 0.1, 0.06);
}

function renderCalc(){
  document.getElementById('calcExpr').textContent = calcExprStr || '';
  if(!calcExprStr) {
    document.getElementById('calcResult').textContent = '0';
    document.getElementById('calcResult').className   = 'calc-result';
  }
}

// Close on Escape
document.addEventListener('keydown', e => {
  if(e.key === 'Escape' && document.getElementById('calcPopup').classList.contains('show')){
    toggleCalc();
  }
});
</script>
<?php require_once 'chat_bubble.php'; ?>
</body>
</html>