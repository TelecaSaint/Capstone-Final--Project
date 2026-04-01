<?php
require_once 'Config.php';

/**
 * 1. Authenticate the user
 * Redirects to login.php automatically if session is invalid.
 */
$user = require_login(); 

/**
 * 2. Map data from the $user session array
 */
$xp       = $user['xp'] ?? 0;
$lvl      = $user['level'] ?? 1;
$solved   = $user['problems_solved'] ?? 0;
$accuracy = $user['accuracy'] ?? 0;
$streak   = $user['streak'] ?? 0;
$name     = htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Explorer');

/**
 * 3. XP Progress Bar Logic (assuming 500 XP per level)
 */
$current_xp_in_lvl = $xp % 500;
$xp_percent = ($current_xp_in_lvl / 500) * 100;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathQuest — Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
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
            --chart-area-start:rgba(0,229,255,0.25);
            --chart-area-end:rgba(0,229,255,0);
        }

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
        }

        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;transition:0.3s}
        
        nav{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:var(--nav-bg);border-bottom:1px solid var(--b);position:sticky;top:0;z-index:100;backdrop-filter:blur(12px)}
        .nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15em;background:linear-gradient(135deg,var(--cyan),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
        .nav-link{padding:6px 12px;border-radius:7px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none;transition:0.2s}
        .nav-link.active{background:var(--cdim);color:var(--cyan)}

        .main{padding:24px 28px;max-width:1500px;margin:0 auto}
        .g4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
        .ghero{display:grid;grid-template-columns:290px 1fr;gap:20px;margin-bottom:20px}
        .panel{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:22px;position:relative;overflow:hidden;animation:pIn 0.45s ease backwards}
        .ptitle{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.14em;color:var(--tdim);text-transform:uppercase;margin-bottom:16px}
        .sc-val{font-family:'Syne',sans-serif;font-weight:800;font-size:2em;line-height:1;margin-bottom:3px}
        .bar{height:6px;background:var(--s2);border-radius:3px;overflow:hidden;margin-bottom:16px}
        .bar-fill{height:100%;border-radius:3px;transition:width 1s ease}

        .ct-line{fill:none;stroke:var(--cyan);stroke-width:2.5;stroke-linecap:round}
        .ct-area{fill:url(#areaGrad)}

        .chat-fab-wrap{position:fixed;bottom:24px;left:24px;z-index:400;}
        .chat-fab{width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#0099cc);border:none;cursor:pointer;font-size:1.4em;box-shadow:0 4px 20px rgba(124,58,237,0.35);display:grid;place-items:center;position:relative}
        .chat-fab-badge{position:absolute;top:-3px;right:-3px;background:#ff5252;color:#fff;font-size:0.6em;font-weight:800;border-radius:10px;padding:1px 5px;display:none;border:2px solid var(--bg);}

        @keyframes pIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
        @media(max-width:1100px){.ghero{grid-template-columns:1fr}}
        @media(max-width:600px){.g4,.ghero{grid-template-columns:1fr}}
    </style>
</head>
<body>

<nav>
    <a href="dashboard.php" class="nav-logo">MQ</a>
    <div class="nav-links">
        <a href="dashboard.php" class="nav-link active">🏠 Home</a>
        <a href="assignments.php" class="nav-link">📋 Assignments</a>
    </div>
    <div style="display:flex; align-items:center; gap:12px;">
        <div style="font-size:0.8em;">⭐ <b style="color:var(--cyan)"><?= number_format($xp) ?></b></div>
        <div style="font-size:0.8em;">🔥 <b style="color:var(--violet)"><?= $streak ?></b></div>
        <button onclick="toggleTheme()" style="background:var(--s2); border:1px solid var(--b); border-radius:20px; padding:5px 12px; cursor:pointer; color:var(--text); font-family:'Syne'; font-size:0.7em;">
            <span id="themeIcon">☀️</span> Toggle
        </button>
    </div>
</nav>

<main class="main">
    <div class="g4">
        <div class="panel">
            <div class="ptitle">TOTAL XP</div>
            <div class="sc-val" style="color:var(--cyan)"><?= number_format($xp) ?></div>
            <div style="font-size:0.75em; color:var(--tdim)">Level <?= $lvl ?> Scholar</div>
        </div>
        <div class="panel">
            <div class="ptitle">PROBLEMS</div>
            <div class="sc-val" style="color:var(--green)"><?= $solved ?></div>
            <div style="font-size:0.75em; color:var(--tdim)">Total Solved</div>
        </div>
        <div class="panel">
            <div class="ptitle">ACCURACY</div>
            <div class="sc-val" style="color:var(--amber)"><?= $accuracy ?>%</div>
            <div style="font-size:0.75em; color:var(--tdim)">Overall Success</div>
        </div>
        <div class="panel">
            <div class="ptitle">STREAK</div>
            <div class="sc-val" style="color:var(--violet)"><?= $streak ?></div>
            <div style="font-size:0.75em; color:var(--tdim)">Day Multiplier</div>
        </div>
    </div>

    <div class="ghero">
        <div class="panel" style="text-align:center;">
            <div class="ptitle" style="text-align:left;">CHARACTER</div>
            <div style="font-size:3em; margin:10px 0;">👨‍🎓</div>
            <div style="font-family:'Syne'; font-weight:700;"><?= $name ?></div>
            <div style="font-size:0.7em; color:var(--tdim); margin-bottom:15px;"><?= $current_xp_in_lvl ?> / 500 XP to Lvl <?= $lvl+1 ?></div>
            <div class="bar"><div class="bar-fill" style="width:<?= $xp_percent ?>%; background:var(--cyan)"></div></div>
            <button onclick="location.href='problem.php'" style="width:100%; padding:10px; border:none; border-radius:8px; background:linear-gradient(135deg,var(--cyan),#0099cc); color:#020d14; font-family:'Syne'; font-weight:800; cursor:pointer;">START BATTLE</button>
        </div>

        <div class="panel">
            <div class="ptitle">7-DAY PERFORMANCE</div>
            <div style="height:200px; width:100%;">
                <svg id="perfChart" viewBox="0 0 500 200" preserveAspectRatio="none" style="width:100%; height:100%;">
                    <defs>
                        <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="var(--chart-area-start)"/>
                            <stop offset="100%" stop-color="var(--chart-area-end)"/>
                        </linearGradient>
                    </defs>
                    <path id="chartArea" class="ct-area"></path>
                    <path id="chartLine" class="ct-line"></path>
                </svg>
            </div>
        </div>
    </div>
</main>

<div class="chat-fab-wrap">
    <button class="chat-fab" id="globalChatFab" onclick="window.location.href='messages.php'">
        💬
        <span class="chat-fab-badge" id="globalChatBadge"></span>
    </button>
</div>

<script>
function toggleTheme() {
    const html = document.documentElement;
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('mq_theme', next);
    document.getElementById('themeIcon').textContent = next === 'dark' ? '☀️' : '🌙';
}

const perfData = [60, 80, 45, 90, 70, 85, 95];
function buildChart() {
    const line = document.getElementById('chartLine');
    const area = document.getElementById('chartArea');
    const width = 500; const height = 200;
    const pts = perfData.map((v, i) => ({ x: (i/(perfData.length-1))*width, y: height - (v/100)*height }));
    const d = `M ${pts.map(p => `${p.x},${p.y}`).join(' L ')}`;
    line.setAttribute('d', d);
    area.setAttribute('d', `${d} L ${width},${height} L 0,${height} Z`);
}
buildChart();

(async function(){
    try {
        const r = await fetch('messages_api.php?action=unread_count');
        const d = await r.json();
        if(d.ok && d.count > 0){
            const badge = document.getElementById('globalChatBadge');
            badge.textContent = d.count > 99 ? '99+' : d.count;
            badge.style.display = 'block';
        }
    } catch(e) {}
})();
</script>
</body>
</html>