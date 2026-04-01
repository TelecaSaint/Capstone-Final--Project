<?php
require_once 'config.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
/* ── Dark mode (default) ───────────────────────────────────── */
:root, [data-theme="dark"] {
  --bg:#080b14;--s1:#0e1220;--s2:#141827;--s3:#1a2035;
  --b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.12);
  --cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);
  --violet:#7c3aed;--vdim:rgba(124,58,237,0.15);
  --amber:#ffab00;--adim:rgba(255,171,0,0.15);
  --green:#00e676;--gdim:rgba(0,230,118,0.12);
  --red:#ff5252;--text:#e8eaf2;
  --tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68);
  --nav-bg:rgba(8,11,20,0.97);
  --shadow:0 24px 60px rgba(0,0,0,0.5);
  --chart-area-start:rgba(0,229,255,0.25);
  --chart-area-end:rgba(0,229,255,0);
}

/* ── Light mode ────────────────────────────────────────────── */
[data-theme="light"] {
  --bg:#f0f4ff;--s1:#ffffff;--s2:#e8edf8;--s3:#d8e0f0;
  --b:rgba(0,0,0,0.08);--bb:rgba(0,0,0,0.15);
  --cyan:#0077cc;--cdim:rgba(0,119,204,0.10);
  --violet:#6d28d9;--vdim:rgba(109,40,217,0.10);
  --amber:#c47f00;--adim:rgba(196,127,0,0.12);
  --green:#00a854;--gdim:rgba(0,168,84,0.10);
  --red:#cc3333;--text:#0f1423;
  --tdim:rgba(15,20,35,0.45);--tmid:rgba(15,20,35,0.72);
  --nav-bg:rgba(240,244,255,0.97);
  --shadow:0 8px 32px rgba(0,0,0,0.10);
  --chart-area-start:rgba(0,119,204,0.18);
  --chart-area-end:rgba(0,119,204,0);
}

*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;transition:background 0.3s,color 0.3s}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:var(--nav-bg);border-bottom:1px solid var(--b);position:sticky;top:0;z-index:100;backdrop-filter:blur(12px);transition:background 0.3s,border-color 0.3s}
.nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15em;background:linear-gradient(135deg,var(--cyan),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
.nav-links{display:flex;align-items:center;gap:4px}
.nav-link{padding:6px 12px;border-radius:7px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;letter-spacing:0.06em;text-decoration:none;transition:all 0.2s}
.nav-link:hover{background:var(--s2);color:var(--tmid)}
.nav-link.active{background:var(--cdim);color:var(--cyan);border:1px solid rgba(0,119,204,0.2)}
.nav-right{display:flex;align-items:center;gap:12px}
.nav-stat{font-size:0.82em;color:var(--tmid);display:flex;align-items:center;gap:5px}
.nav-stat b{color:var(--cyan)}
.notif-btn{position:relative;width:34px;height:34px;border-radius:8px;background:var(--s2);border:1px solid var(--b);display:flex;align-items:center;justify-content:center;cursor:pointer;text-decoration:none;font-size:1em;transition:all 0.2s}
.notif-btn:hover{background:var(--s3)}
.notif-badge{position:absolute;top:-4px;right:-4px;width:16px;height:16px;border-radius:50%;background:var(--red);font-size:0.6em;font-family:'Syne',sans-serif;font-weight:700;display:flex;align-items:center;justify-content:center;color:#fff}
.nav-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--cdim),var(--vdim));border:1px solid var(--cyan);display:flex;align-items:center;justify-content:center;font-size:1.1em;text-decoration:none;opacity:0.8}
.logout{padding:6px 12px;background:rgba(255,82,82,0.08);border:1px solid rgba(255,82,82,0.2);border-radius:7px;color:var(--red);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none;transition:all 0.2s}
.logout:hover{background:rgba(255,82,82,0.16)}

/* ── Theme toggle ──────────────────────────────────────────── */
.theme-toggle{background:var(--s2);border:1px solid var(--b);border-radius:20px;padding:5px 12px;cursor:pointer;font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;color:var(--tmid);display:flex;align-items:center;gap:5px;transition:all 0.22s;letter-spacing:0.05em}
.theme-toggle:hover{border-color:var(--bb);color:var(--text)}

.main{padding:24px 28px;max-width:1500px;margin:0 auto}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:20px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.ghero{display:grid;grid-template-columns:290px 1fr;gap:20px;margin-bottom:20px}
.panel{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:22px;position:relative;overflow:hidden;transition:border-color 0.25s,background 0.3s;animation:pIn 0.45s ease backwards}
.panel:hover{border-color:var(--bb)}
.panel::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,0.013) 0%,transparent 60%);pointer-events:none}
@keyframes pIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
.ptitle{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.14em;color:var(--tdim);text-transform:uppercase;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between}
.ptitle a{font-size:0.88em;color:var(--cyan);text-decoration:none;opacity:0.7;letter-spacing:0;font-weight:500;text-transform:none}
.ptitle a:hover{opacity:1}
.scard{padding:18px 20px}
.sc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.sc-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1em}
.sc-icon.cyan{background:var(--cdim);border:1px solid rgba(0,119,204,0.2)}
.sc-icon.violet{background:var(--vdim);border:1px solid rgba(124,58,237,0.25)}
.sc-icon.amber{background:var(--adim);border:1px solid rgba(255,171,0,0.25)}
.sc-icon.green{background:var(--gdim);border:1px solid rgba(0,230,118,0.2)}
.sc-delta{font-size:0.74em;color:var(--green);font-weight:500}
.sc-val{font-family:'Syne',sans-serif;font-weight:800;font-size:2em;line-height:1;margin-bottom:3px}
.sc-val.cyan{color:var(--cyan)}.sc-val.violet{color:#a78bfa}.sc-val.amber{color:var(--amber)}.sc-val.green{color:var(--green)}
.sc-label{font-size:0.78em;color:var(--tdim)}
.char-av{width:76px;height:76px;margin:0 auto 12px;border-radius:50%;background:linear-gradient(135deg,var(--cdim),var(--vdim));border:2px solid var(--cyan);display:flex;align-items:center;justify-content:center;font-size:2.4em;animation:avPulse 3s ease-in-out infinite}
@keyframes avPulse{0%,100%{box-shadow:0 0 16px var(--cdim)}50%{box-shadow:0 0 30px var(--cdim)}}
.char-name{text-align:center;font-family:'Syne',sans-serif;font-weight:700;font-size:1.05em;margin-bottom:3px}
.char-title{text-align:center;font-size:0.79em;color:var(--cyan);margin-bottom:16px}
.xp-row{display:flex;justify-content:space-between;font-size:0.74em;color:var(--tdim);margin-bottom:5px}
.bar{height:6px;background:var(--s2);border-radius:3px;overflow:hidden;margin-bottom:16px}
.bar-fill{height:100%;border-radius:3px;transition:width 1.4s cubic-bezier(0.25,1,0.5,1)}
.bar-fill.cyan{background:linear-gradient(90deg,#0099cc,var(--cyan));box-shadow:0 0 8px var(--cdim)}
.mini-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px}
.ms-box{background:var(--s2);border:1px solid var(--b);border-radius:8px;padding:9px 6px;text-align:center}
.ms-val{font-family:'Syne',sans-serif;font-weight:700;font-size:1.05em;color:var(--cyan)}
.ms-lbl{font-size:0.67em;color:var(--tdim);margin-top:2px}
.cta{width:100%;padding:11px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:9px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.81em;font-weight:700;letter-spacing:0.09em;cursor:pointer;transition:all 0.22s;box-shadow:0 4px 18px var(--cdim)}
.cta:hover{transform:translateY(-2px)}
.cta.amber{background:linear-gradient(135deg,var(--amber),#ff6f00);box-shadow:0 4px 18px var(--adim)}
.streak-row{display:flex;gap:5px;justify-content:center;margin:10px 0}
.sk-dot{width:27px;height:27px;border-radius:50%;border:1px solid var(--b);background:var(--s2);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-size:0.59em;color:var(--tdim);font-weight:600}
.sk-dot.on{background:var(--cdim);border-color:var(--cyan);color:var(--cyan)}
.sk-dot.now{background:var(--adim);border-color:var(--amber);color:var(--amber);animation:skPulse 1.5s ease infinite}
@keyframes skPulse{0%,100%{box-shadow:0 0 0 0 var(--adim)}50%{box-shadow:0 0 0 5px transparent}}
.quest-active{background:linear-gradient(135deg,var(--cdim),var(--vdim));border-color:var(--cyan)}
.qa-badge{display:inline-block;padding:3px 9px;border-radius:5px;font-family:'Syne',sans-serif;font-size:0.63em;font-weight:600;letter-spacing:0.08em;background:var(--cdim);color:var(--cyan);border:1px solid var(--cyan)}
.qi{display:flex;align-items:center;gap:13px;padding:12px;background:var(--s2);border:1px solid var(--b);border-radius:10px;margin-bottom:8px;cursor:pointer;transition:all 0.2s;text-decoration:none;color:inherit}
.qi:hover{background:var(--s3);border-color:var(--bb);transform:translateX(3px)}
.qi.locked{opacity:0.4;cursor:not-allowed;pointer-events:none}
.qi-ico{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2em;flex-shrink:0}
.qi-name{font-family:'Syne',sans-serif;font-weight:600;font-size:0.88em;margin-bottom:2px}
.qi-sub{font-size:0.75em;color:var(--tdim)}
.qi-badge{padding:3px 8px;border-radius:4px;font-family:'Syne',sans-serif;font-size:0.63em;font-weight:600;flex-shrink:0}
.bi-easy{background:var(--gdim);color:var(--green);border:1px solid rgba(0,230,118,0.22)}
.bi-med{background:var(--adim);color:var(--amber);border:1px solid rgba(255,171,0,0.22)}
.bi-hard{background:rgba(255,82,82,0.1);color:var(--red);border:1px solid rgba(255,82,82,0.2)}
.bi-lock{background:var(--s3);color:var(--tdim)}
.ct-line{fill:none;stroke:var(--cyan);stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.ct-area{fill:url(#areaGrad)}
.ct-dot{fill:var(--cyan);stroke:var(--s1);stroke-width:2}
.ct-label{fill:var(--tdim);font-family:'DM Sans',sans-serif;font-size:11px}
.ct-grid{stroke:var(--b);stroke-width:1}
.lb-item{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:8px;margin-bottom:6px;transition:background 0.2s}
.lb-item:hover{background:var(--s2)}
.lb-item.you{background:var(--cdim);border:1px solid var(--cyan)}
.lb-rank{font-family:'Syne',sans-serif;font-size:0.77em;font-weight:700;width:20px;color:var(--tdim);flex-shrink:0}
.lb-rank.top{color:var(--amber)}
.lb-av{width:28px;height:28px;border-radius:50%;background:var(--s3);border:1px solid var(--b);display:flex;align-items:center;justify-content:center;font-size:0.85em;flex-shrink:0}
.lb-name{flex:1;font-size:0.87em}
.lb-pts{font-family:'Syne',sans-serif;font-size:0.77em;color:var(--tdim);font-weight:600}
.badges-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:9px}
.badge-box{aspect-ratio:1;background:var(--s2);border:1px solid var(--b);border-radius:9px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;font-size:1.4em;transition:all 0.2s}
.badge-box:hover{background:var(--s3);border-color:var(--bb);transform:scale(1.05)}
.badge-box span{font-family:'Syne',sans-serif;font-size:0.21em;color:var(--tdim)}
.badge-box.locked{opacity:0.2}
.daily-panel{background:linear-gradient(135deg,var(--adim),var(--bg));border-color:var(--amber);text-align:center}
.daily-timer{font-family:'Syne',sans-serif;font-weight:800;font-size:1.55em;color:var(--amber);margin:8px 0;letter-spacing:0.04em}
.asgn-item{display:flex;align-items:center;gap:12px;padding:11px;background:var(--s2);border:1px solid var(--b);border-radius:9px;margin-bottom:8px;text-decoration:none;color:inherit;transition:all 0.2s}
.asgn-item:hover{background:var(--s3);border-color:var(--bb)}
.asgn-icon{width:34px;height:34px;border-radius:8px;background:var(--cdim);border:1px solid var(--cyan);display:flex;align-items:center;justify-content:center;font-size:1.1em;flex-shrink:0}
.asgn-name{font-family:'Syne',sans-serif;font-weight:600;font-size:0.87em;margin-bottom:2px}
.asgn-meta{font-size:0.74em;color:var(--tdim)}
.notif-item{display:flex;gap:10px;padding:10px;background:rgba(255,82,82,0.05);border:1px solid rgba(255,82,82,0.2);border-radius:9px;margin-bottom:7px}
.notif-text{font-size:0.82em;color:var(--tmid);line-height:1.4}
.notif-time{font-size:0.72em;color:var(--tdim);margin-top:3px}
@media(max-width:1100px){.ghero{grid-template-columns:1fr}}
@media(max-width:900px){.g3{grid-template-columns:1fr 1fr}.g4{grid-template-columns:1fr 1fr}.nav-links{display:none}}
@media(max-width:600px){.main{padding:14px}.g2,.g3,.g4,.ghero{grid-template-columns:1fr}}
</style>
</head>
<body>
<nav>
  <a href="dashboard.php" class="nav-logo">MQ</a>
  <div class="nav-links">
    <a href="dashboard.php"   class="nav-link active">🏠 Home</a>
    <a href="assignments.php" class="nav-link">📋 Assignments</a>
    <a href="leaderboard.php" class="nav-link">🏅 Leaderboard</a>
    <a href="progress.php"    class="nav-link">📈 Progress</a>
    <a href="profile.php"     class="nav-link">🧙 Profile</a>
  </div>
  <div class="nav-right">
    <div class="nav-stat">⭐ <b>0</b></div>
    <div class="nav-stat">🔥 <b>0</b></div>
    <a href="notifications.php" class="notif-btn">🔔</a>
    <a href="profile.php" class="nav-avatar">👨‍🎓</a>
    <a href="settings.php" class="nav-link" style="padding:6px 10px">⚙</a>
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">
      <span id="themeIcon">☀️</span><span id="themeLabel">Light</span>
    </button>
    <a href="logout.php" class="logout">Sign Out</a>
  </div>
</nav>

<main class="main">
  <div class="g4">
    <div class="panel scard" style="animation-delay:.04s">
      <div class="sc-top"><div class="sc-icon cyan">⭐</div><div class="sc-delta">Level 1</div></div>
      <div class="sc-val cyan">0</div><div class="sc-label">Total XP Earned</div>
    </div>
    <div class="panel scard" style="animation-delay:.08s">
      <div class="sc-top"><div class="sc-icon green">✓</div><div class="sc-delta">All time</div></div>
      <div class="sc-val green">0</div><div class="sc-label">Problems Solved</div>
    </div>
    <div class="panel scard" style="animation-delay:.12s">
      <div class="sc-top"><div class="sc-icon amber">🎯</div><div class="sc-delta">Overall</div></div>
      <div class="sc-val amber">0%</div><div class="sc-label">Accuracy Rate</div>
    </div>
    <div class="panel scard" style="animation-delay:.16s">
      <div class="sc-top"><div class="sc-icon violet">🔥</div><div class="sc-delta">Current</div></div>
      <div class="sc-val violet">0</div><div class="sc-label">Day Streak</div>
    </div>
  </div>

  <div class="ghero">
    <div class="panel" style="animation-delay:.2s">
      <div class="ptitle">🧙 Profile <a href="profile.php">View →</a></div>
      <div class="char-av">👨‍🎓</div>
      <div class="char-name">Alex M.</div>
      <div class="char-title">Level 1 · </div>
      <div class="xp-row"><span>XP to next level</span><span>0 / 500</span></div>
      <div class="bar"><div class="bar-fill cyan" style="width:0%"></div></div>
      <div class="mini-stats">
        <div class="ms-box"><div class="ms-val">0</div><div class="ms-lbl">Solved</div></div>
        <div class="ms-box"><div class="ms-val">0%</div><div class="ms-lbl">Accuracy</div></div>
        <div class="ms-box"><div class="ms-val">0🔥</div><div class="ms-lbl">Streak</div></div>
      </div>
      <div style="font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.14em;color:var(--tdim);text-transform:uppercase;margin-bottom:10px">📅 This Week</div>
      <div class="streak-row">
        <div class="sk-dot on">M</div>
        <div class="sk-dot on">T</div>
        <div class="sk-dot now">W</div>
        <div class="sk-dot ">T</div>
        <div class="sk-dot ">F</div>
        <div class="sk-dot ">S</div>
        <div class="sk-dot ">S</div>
      </div>
      <br><button class="cta" onclick="location.href='problem.php'">⚔ START BATTLE</button>
    </div>

    <div style="display:flex;flex-direction:column;gap:18px">
      <div class="panel quest-active" style="animation-delay:.24s">
        <div class="ptitle">🏆 Active Quest <span class="qa-badge">IN PROGRESS</span></div>
        <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:1.2em;margin-bottom:4px">Algebra Castle</div>
        <div style="font-size:0.84em;color:var(--tdim);margin-bottom:12px;font-style:italic">Defeat the Equation Dragon by solving 10 algebra problems</div>
        <div style="display:flex;gap:14px;margin-bottom:12px;flex-wrap:wrap;font-size:0.77em;color:var(--tmid)">
          <span>🐉 Boss: <b>Equation Dragon</b></span>
          <span>📝 Progress: <b>8 / 10</b></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.74em;color:var(--tdim);margin-bottom:5px"><span>Quest Progress</span><span>80%</span></div>
        <div class="bar" style="height:7px;margin-bottom:10px"><div class="bar-fill cyan" style="width:80%"></div></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <div style="padding:4px 10px;background:var(--s2);border:1px solid var(--b);border-radius:20px;font-size:0.73em;color:var(--tmid)">⭐ +200 XP</div>
          <div style="padding:4px 10px;background:var(--s2);border:1px solid var(--b);border-radius:20px;font-size:0.73em;color:var(--tmid)">🏆 Dragon Slayer Badge</div>
        </div>
      </div>

      <div class="panel" style="animation-delay:.28s">
        <div class="ptitle">📚 Quest Library</div>
        <a class="qi" href="problem.php?subject=algebra"><div class="qi-ico" style="background:var(--cdim);border:1px solid var(--cyan)">∑</div><div><div class="qi-name">Algebra Castle</div><div class="qi-sub">Equations, variables</div></div><div class="qi-badge bi-med">MEDIUM</div></a>
        <a class="qi" href="problem.php?subject=arithmetic"><div class="qi-ico" style="background:var(--gdim);border:1px solid var(--green)">🔢</div><div><div class="qi-name">Arithmetic Arena</div><div class="qi-sub">Addition, multiplication</div></div><div class="qi-badge bi-easy">EASY</div></a>
        <a class="qi" href="problem.php?subject=fractions"><div class="qi-ico" style="background:var(--vdim);border:1px solid var(--violet)">½</div><div><div class="qi-name">Fraction Dungeon</div><div class="qi-sub">Fractions, decimals</div></div><div class="qi-badge bi-med">MEDIUM</div></a>
        <a class="qi" href="problem.php?subject=geometry"><div class="qi-ico" style="background:var(--adim);border:1px solid var(--amber)">📐</div><div><div class="qi-name">Geometry Galaxy</div><div class="qi-sub">Shapes, angles</div></div><div class="qi-badge bi-hard">HARD</div></a>
        <div class="qi locked"><div class="qi-ico" style="background:var(--s3)">🔬</div><div><div class="qi-name">Calculus Cavern</div><div class="qi-sub">Unlock at Level 10</div></div><div class="qi-badge bi-lock">LOCKED</div></div>
      </div>
    </div>
  </div>

  <div class="g2">
    <div class="panel" style="animation-delay:.32s">
      <div class="ptitle">📈 7-Day Performance <a href="progress.php">Full Report →</a></div>
      <div style="text-align:center;padding:30px 0;color:var(--tdim);font-size:0.85em;font-style:italic">🎮 Solve some problems to see your chart!</div>
    </div>

    <div class="panel" style="animation-delay:.36s">
      <div class="ptitle">📋 Upcoming Assignments <a href="assignments.php">View All →</a></div>
      <a class="asgn-item" href="assignments.php">
        <div class="asgn-icon">📝</div>
        <div style="flex:1;min-width:0">
          <div class="asgn-name">Algebra Basics</div>
          <div class="asgn-meta">Algebra · <span style="color:var(--green)">Easy</span></div>
        </div>
        <div style="font-family:'Syne',sans-serif;font-size:0.7em;font-weight:600;color:var(--amber);flex-shrink:0">Due today</div>
      </a>
      <a class="asgn-item" href="assignments.php">
        <div class="asgn-icon">📝</div>
        <div style="flex:1;min-width:0">
          <div class="asgn-name">Fraction Challenge</div>
          <div class="asgn-meta">Fractions · <span style="color:var(--amber)">Medium</span></div>
        </div>
        <div style="font-family:'Syne',sans-serif;font-size:0.7em;font-weight:600;color:var(--amber);flex-shrink:0">4d left</div>
      </a>
      <a class="asgn-item" href="assignments.php">
        <div class="asgn-icon">📝</div>
        <div style="flex:1;min-width:0">
          <div class="asgn-name">Geometry Quiz</div>
          <div class="asgn-meta">Geometry · <span style="color:var(--amber)">Medium</span></div>
        </div>
        <div style="font-family:'Syne',sans-serif;font-size:0.7em;font-weight:600;color:var(--amber);flex-shrink:0">9d left</div>
      </a>
    </div>
  </div>

  <div class="g3">
    <div class="panel" style="animation-delay:.4s">
      <div class="ptitle">🏅 Leaderboard <a href="leaderboard.php">Full →</a></div>
    </div>

    <div class="panel" style="animation-delay:.44s">
      <div class="ptitle">🏆 Badges <a href="profile.php">All →</a></div>
      <div class="badges-grid">
        <div class="badge-box locked" title="First Win">🥇<span>First Win</span></div>
        <div class="badge-box locked" title="Sharp">🎯<span>Sharp</span></div>
        <div class="badge-box locked" title="Quick">⚡<span>Quick</span></div>
        <div class="badge-box locked" title="Champ">🏆<span>Champ</span></div>
        <div class="badge-box locked" title="Streak">🔥<span>Streak</span></div>
        <div class="badge-box locked" title="Scholar">📚<span>Scholar</span></div>
        <div class="badge-box locked" title="Dragon">🐉<span>Dragon</span></div>
        <div class="badge-box locked" title="Wizard">⚗<span>Wizard</span></div>
      </div>
    </div>

    <div class="panel daily-panel" style="animation-delay:.48s">
      <div class="ptitle">⚡ Daily Challenge</div>
      <div style="font-size:2em;margin:4px 0">🎁</div>
      <div style="font-family:'Syne',sans-serif;font-weight:700;margin-bottom:5px">Arcane Trial</div>
      <div style="font-size:0.81em;color:var(--tdim);font-style:italic;margin-bottom:8px">"Solve 5 problems in 10 minutes"</div>
      <div style="font-family:'Syne',sans-serif;font-size:0.77em;color:var(--amber);margin-bottom:4px">⭐ +100 XP · 🏆 Badge</div>
      <div class="daily-timer" id="dailyTimer">--:--:--</div>
      <button class="cta amber" onclick="location.href='problem.php'">ACCEPT CHALLENGE</button>
    </div>
  </div>

  <div class="g2">
    <div class="panel" style="animation-delay:.52s">
      <div class="ptitle">📊 Subject Mastery <a href="progress.php">Details →</a></div>
      <div style="margin-bottom:13px">
        <div style="display:flex;justify-content:space-between;font-size:0.81em;margin-bottom:5px"><span>Algebra</span><span style="font-family:'Syne',sans-serif;font-weight:600;color:var(--tdim)">0%</span></div>
        <div class="bar" style="height:6px;margin-bottom:0"><div class="bar-fill" style="width:0%;background:var(--cyan)"></div></div>
      </div>
      <div style="margin-bottom:13px">
        <div style="display:flex;justify-content:space-between;font-size:0.81em;margin-bottom:5px"><span>Arithmetic</span><span style="font-family:'Syne',sans-serif;font-weight:600;color:var(--tdim)">0%</span></div>
        <div class="bar" style="height:6px;margin-bottom:0"><div class="bar-fill" style="width:0%;background:var(--green)"></div></div>
      </div>
      <div style="margin-bottom:13px">
        <div style="display:flex;justify-content:space-between;font-size:0.81em;margin-bottom:5px"><span>Fractions</span><span style="font-family:'Syne',sans-serif;font-weight:600;color:var(--tdim)">0%</span></div>
        <div class="bar" style="height:6px;margin-bottom:0"><div class="bar-fill" style="width:0%;background:var(--amber)"></div></div>
      </div>
      <div style="margin-bottom:13px">
        <div style="display:flex;justify-content:space-between;font-size:0.81em;margin-bottom:5px"><span>Geometry</span><span style="font-family:'Syne',sans-serif;font-weight:600;color:var(--tdim)">0%</span></div>
        <div class="bar" style="height:6px;margin-bottom:0"><div class="bar-fill" style="width:0%;background:#a78bfa"></div></div>
      </div>
      <div style="margin-bottom:13px">
        <div style="display:flex;justify-content:space-between;font-size:0.81em;margin-bottom:5px"><span>Statistics</span><span style="font-family:'Syne',sans-serif;font-weight:600;color:var(--tdim)">0%</span></div>
        <div class="bar" style="height:6px;margin-bottom:0"><div class="bar-fill" style="width:0%;background:var(--red)"></div></div>
      </div>
    </div>

    <div class="panel" style="animation-delay:.56s">
      <div class="ptitle">🔔 Notifications <a href="notifications.php">All →</a></div>
      <div style="color:var(--tdim);font-size:0.85em;font-style:italic;padding:10px 0">✅ You're all caught up!</div>
    </div>
  </div>
</main>

<script>
// ── Theme toggle ──────────────────────────────────────────────
const html  = document.documentElement;
const saved = localStorage.getItem('mq_theme') || 'dark';
applyTheme(saved);

function applyTheme(theme) {
  html.setAttribute('data-theme', theme);
  localStorage.setItem('mq_theme', theme);
  document.getElementById('themeIcon').textContent  = theme === 'dark' ? '☀️' : '🌙';
  document.getElementById('themeLabel').textContent = theme === 'dark' ? 'Light' : 'Dark';
}

function toggleTheme() {
  applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
}

// ── Performance chart ─────────────────────────────────────────
const perfData=[];
const xs=[80,133,186,239,292,345,398];
function dataToY(v){return 105-(v/100)*90;}
function buildChart(){
  if(!perfData.length)return;
  const step=perfData.length>1?Math.floor(6/(perfData.length-1)):0;
  const pts=perfData.map((v,i)=>({x:xs[Math.min(i*step,6)],y:dataToY(v)}));
  document.getElementById('chartLine').setAttribute('d','M'+pts.map(p=>`${p.x},${p.y}`).join(' L'));
  document.getElementById('chartArea').setAttribute('d',`M${pts[0].x},105 `+pts.map(p=>`L${p.x},${p.y}`).join(' ')+` L${pts[pts.length-1].x},105 Z`);
  const svg=document.getElementById('perfChart');
  pts.forEach(p=>{const c=document.createElementNS('http://www.w3.org/2000/svg','circle');c.setAttribute('class','ct-dot');c.setAttribute('cx',p.x);c.setAttribute('cy',p.y);c.setAttribute('r','4');svg.appendChild(c);});
}
buildChart();

// ── Daily timer ───────────────────────────────────────────────
function updateTimer(){const now=new Date(),mid=new Date(now);mid.setHours(24,0,0,0);const d=mid-now,h=Math.floor(d/3600000),m=Math.floor((d%3600000)/60000),s=Math.floor((d%60000)/1000);document.getElementById('dailyTimer').textContent=`${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;}
updateTimer();setInterval(updateTimer,1000);
</script>

<?php require_once 'chat_bubble.php'; ?>
</body>
</html>