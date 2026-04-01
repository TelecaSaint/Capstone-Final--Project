<?php
require_once 'config.php';
require_login();
$user = current_user();
$role = $user['role'];
$is_staff = in_array($role, ['admin','teacher']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Messages</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root,[data-theme="dark"]{
  --bg:#080b14;--s1:#0e1220;--s2:#141827;--s3:#1a2035;
  --b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.13);
  --cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--cglow:rgba(0,229,255,0.2);
  --violet:#7c3aed;--vdim:rgba(124,58,237,0.15);
  --amber:#ffab00;--green:#00e676;--gdim:rgba(0,230,118,0.1);
  --red:#ff5252;--rdim:rgba(255,82,82,0.1);
  --text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68);
  --nav-bg:rgba(8,11,20,0.97);
  --radius:12px;
}
[data-theme="light"]{
  --bg:#f0f4ff;--s1:#ffffff;--s2:#e8edf8;--s3:#d8e0f0;
  --b:rgba(0,0,0,0.08);--bb:rgba(0,0,0,0.15);
  --cyan:#0077cc;--cdim:rgba(0,119,204,0.10);--cglow:rgba(0,119,204,0.18);
  --violet:#6d28d9;--vdim:rgba(109,40,217,0.10);
  --amber:#c47f00;--green:#00a854;--gdim:rgba(0,168,84,0.10);
  --red:#cc3333;--rdim:rgba(204,51,51,0.10);
  --text:#0f1423;--tdim:rgba(15,20,35,0.45);--tmid:rgba(15,20,35,0.72);
  --nav-bg:rgba(240,244,255,0.97);
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column}

/* ── Nav ── */
nav{display:flex;align-items:center;justify-content:space-between;padding:0 26px;height:54px;background:var(--nav-bg);border-bottom:1px solid var(--b);backdrop-filter:blur(12px);position:sticky;top:0;z-index:50}
.nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.05rem;color:var(--cyan);text-decoration:none}
.nav-right{display:flex;align-items:center;gap:10px}
.back-btn{display:flex;align-items:center;gap:5px;color:var(--tdim);font-size:0.82em;cursor:pointer;background:none;border:none;padding:6px 10px;border-radius:7px;transition:all 0.2s;font-family:'DM Sans',sans-serif}
.back-btn:hover{color:var(--text);background:var(--s2)}
.theme-btn{background:var(--s2);border:1px solid var(--b);border-radius:20px;padding:4px 10px;cursor:pointer;font-family:'Syne',sans-serif;font-size:0.68em;font-weight:600;color:var(--tmid);transition:all 0.22s}
.theme-btn:hover{border-color:var(--bb);color:var(--text)}

/* ── Layout ── */
.page{display:grid;grid-template-columns:260px 1fr;gap:0;flex:1;max-width:1100px;margin:0 auto;width:100%;padding:24px 20px;gap:20px}

/* ── Sidebar ── */
.msg-sidebar{display:flex;flex-direction:column;gap:10px}
.compose-btn{width:100%;padding:12px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:var(--radius);color:#020d14;font-family:'Syne',sans-serif;font-size:0.82em;font-weight:700;letter-spacing:0.07em;cursor:pointer;transition:all 0.2s}
.compose-btn:hover{opacity:0.88;transform:translateY(-1px)}
.nav-tabs{display:flex;flex-direction:column;gap:4px}
.ntab{padding:10px 14px;border-radius:9px;border:none;background:transparent;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.78em;font-weight:600;letter-spacing:0.06em;cursor:pointer;text-align:left;transition:all 0.18s;display:flex;align-items:center;gap:8px}
.ntab:hover{background:var(--s2);color:var(--tmid)}
.ntab.active{background:var(--cdim);border:1px solid var(--cyan);color:var(--cyan)}
.ntab .badge{margin-left:auto;background:var(--red);color:#fff;font-size:0.7em;font-weight:700;border-radius:10px;padding:1px 6px;min-width:18px;text-align:center}

/* ── Main panel ── */
.msg-main{background:var(--s1);border:1px solid var(--b);border-radius:var(--radius);display:flex;flex-direction:column;min-height:500px;overflow:hidden}
.msg-header{padding:16px 20px;border-bottom:1px solid var(--b);display:flex;align-items:center;justify-content:space-between}
.msg-header h2{font-family:'Syne',sans-serif;font-size:0.95em;font-weight:700;color:var(--text)}
.msg-header-sub{font-size:0.75em;color:var(--tdim)}

/* ── Message list ── */
.msg-list{flex:1;overflow-y:auto;padding:8px}
.msg-item{padding:12px 14px;border-radius:9px;cursor:pointer;transition:background 0.15s;border-bottom:1px solid var(--b);display:flex;gap:12px;align-items:flex-start}
.msg-item:last-child{border-bottom:none}
.msg-item:hover{background:var(--s2)}
.msg-item.unread .msg-item-subject{color:var(--cyan);font-weight:700}
.msg-item.unread{background:var(--cdim)}
.msg-avatar{width:36px;height:36px;border-radius:50%;background:var(--s3);border:1px solid var(--b);display:grid;place-items:center;font-size:1em;flex-shrink:0}
.msg-item-body{flex:1;min-width:0}
.msg-item-meta{display:flex;align-items:center;gap:6px;margin-bottom:3px}
.msg-item-from{font-family:'Syne',sans-serif;font-size:0.78em;font-weight:700;color:var(--tmid)}
.msg-item-time{font-size:0.7em;color:var(--tdim);margin-left:auto;white-space:nowrap}
.msg-item-subject{font-size:0.82em;color:var(--text);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.msg-item-preview{font-size:0.75em;color:var(--tdim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ann-pill{display:inline-flex;align-items:center;gap:3px;background:rgba(255,171,0,0.12);border:1px solid rgba(255,171,0,0.3);border-radius:10px;padding:1px 7px;font-size:0.68em;font-weight:700;color:var(--amber);margin-left:4px}
.empty-state{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:var(--tdim);padding:40px}
.empty-state span{font-size:2.5em}
.empty-state p{font-size:0.85em;text-align:center}

/* ── Thread view ── */
.thread-view{display:flex;flex-direction:column;flex:1;overflow:hidden}
.thread-header{padding:14px 18px;border-bottom:1px solid var(--b);display:flex;align-items:center;gap:10px}
.thread-back{background:none;border:none;color:var(--tdim);cursor:pointer;font-size:1.1em;padding:4px;border-radius:6px;transition:all 0.15s}
.thread-back:hover{background:var(--s2);color:var(--text)}
.thread-title{font-family:'Syne',sans-serif;font-size:0.88em;font-weight:700}
.thread-with{font-size:0.75em;color:var(--tdim)}
.thread-messages{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px}
.bubble-wrap{display:flex;flex-direction:column;gap:2px}
.bubble-wrap.mine{align-items:flex-end}
.bubble-wrap.theirs{align-items:flex-start}
.bubble{max-width:72%;padding:10px 14px;border-radius:12px;font-size:0.88em;line-height:1.6;word-break:break-word}
.bubble.mine{background:linear-gradient(135deg,var(--cyan),#0099cc);color:#020d14;border-bottom-right-radius:4px}
.bubble.theirs{background:var(--s2);border:1px solid var(--b);color:var(--text);border-bottom-left-radius:4px}
.bubble.announcement{background:rgba(255,171,0,0.12);border:1px solid rgba(255,171,0,0.3);color:var(--text);max-width:90%;align-self:center;text-align:center;border-radius:10px}
.bubble-meta{font-size:0.68em;color:var(--tdim);padding:0 4px}
.thread-compose{padding:12px 16px;border-top:1px solid var(--b);display:flex;gap:8px}
.thread-input{flex:1;background:var(--s2);border:1px solid var(--b);border-radius:9px;padding:10px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.88em;outline:none;resize:none;transition:border-color 0.2s;min-height:42px;max-height:120px}
.thread-input:focus{border-color:var(--cyan)}
.thread-send{padding:10px 18px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:9px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.78em;font-weight:700;cursor:pointer;transition:opacity 0.15s;white-space:nowrap}
.thread-send:hover{opacity:0.88}

/* ── Compose modal ── */
.modal-overlay{position:fixed;inset:0;z-index:200;background:rgba(8,11,20,0.7);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity 0.2s}
.modal-overlay.show{opacity:1;pointer-events:all}
.modal{background:var(--s1);border:1px solid var(--b);border-radius:16px;padding:28px;width:100%;max-width:480px;transform:scale(0.95);transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1)}
.modal-overlay.show .modal{transform:scale(1)}
.modal h3{font-family:'Syne',sans-serif;font-size:1em;font-weight:800;margin-bottom:18px;color:var(--text)}
.form-row{margin-bottom:14px}
.form-label{font-family:'Syne',sans-serif;font-size:0.7em;font-weight:700;letter-spacing:0.1em;color:var(--tdim);text-transform:uppercase;display:block;margin-bottom:6px}
.form-input,.form-select,.form-textarea{width:100%;background:var(--s2);border:1px solid var(--b);border-radius:8px;padding:10px 14px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.88em;outline:none;transition:border-color 0.2s}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--cyan)}
.form-select option{background:var(--s2);color:var(--text)}
.form-textarea{resize:vertical;min-height:100px}
.ann-toggle{display:flex;align-items:center;gap:8px;font-size:0.82em;color:var(--tmid);cursor:pointer;margin-bottom:14px}
.ann-toggle input{accent-color:var(--amber);width:15px;height:15px}
.modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:18px}
.btn-cancel{padding:10px 20px;background:var(--s2);border:1px solid var(--b);border-radius:8px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.8em;font-weight:600;cursor:pointer;transition:all 0.15s}
.btn-cancel:hover{color:var(--text);border-color:var(--bb)}
.btn-send-modal{padding:10px 24px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:8px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.8em;font-weight:700;cursor:pointer;transition:opacity 0.15s}
.btn-send-modal:hover{opacity:0.88}
.toast{position:fixed;bottom:80px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--s1);border:1px solid var(--b);border-radius:10px;padding:10px 20px;font-size:0.84em;font-weight:600;opacity:0;transition:all 0.3s;z-index:300;white-space:nowrap}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.toast.success{border-color:var(--green);color:var(--green)}
.toast.error{border-color:var(--red);color:var(--red)}

@media(max-width:700px){
  .page{grid-template-columns:1fr;padding:12px}
  .msg-sidebar{flex-direction:row;flex-wrap:wrap}
  .nav-tabs{flex-direction:row}
  .compose-btn{width:auto}
}
</style>
</head>
<body>
<nav>
  <a class="nav-logo" href="dashboard.php">MathQuest</a>
  <div class="nav-right">
    <button class="back-btn" onclick="history.back()">← Back</button>
    <button class="theme-btn" id="themeBtn" onclick="toggleTheme()">☀️ Light</button>
  </div>
</nav>

<div class="page">
  <!-- Sidebar -->
  <aside class="msg-sidebar">
    <button class="compose-btn" onclick="openCompose()">✏️ Compose</button>
    <nav class="nav-tabs">
      <button class="ntab active" id="tab-inbox"  onclick="switchTab('inbox')"> 📥 Inbox   <span class="badge" id="unreadBadgeSide" style="display:none"></span></button>
      <button class="ntab"        id="tab-sent"   onclick="switchTab('sent')">  📤 Sent</button>
      <?php if($is_staff): ?>
      <button class="ntab"        id="tab-announce" onclick="switchTab('announce')">📢 Announce</button>
      <?php endif; ?>
    </nav>
  </aside>

  <!-- Main -->
  <main class="msg-main" id="msgMain">
    <div class="msg-header">
      <div>
        <h2 id="panelTitle">Inbox</h2>
        <div class="msg-header-sub" id="panelSub">Your messages</div>
      </div>
    </div>
    <div id="msgContent" style="flex:1;display:flex;flex-direction:column;overflow:hidden"></div>
  </main>
</div>

<!-- Compose modal -->
<div class="modal-overlay" id="composeModal">
  <div class="modal">
    <h3>✏️ New Message</h3>
    <?php if($is_staff): ?>
    <label class="ann-toggle">
      <input type="checkbox" id="annCheck" onchange="toggleAnnouncement(this.checked)">
      📢 Send as announcement to all students
    </label>
    <?php endif; ?>
    <div class="form-row" id="toRow">
      <label class="form-label">To</label>
      <select class="form-select" id="composeTo">
        <option value="">Loading users…</option>
      </select>
    </div>
    <div class="form-row">
      <label class="form-label">Subject</label>
      <input class="form-input" id="composeSubject" type="text" placeholder="Subject…">
    </div>
    <div class="form-row">
      <label class="form-label">Message</label>
      <textarea class="form-textarea" id="composeBody" placeholder="Write your message…"></textarea>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeCompose()">Cancel</button>
      <button class="btn-send-modal" onclick="sendMessage()">Send →</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
// ── Theme ────────────────────────────────────────────────────
const html = document.documentElement;
applyTheme(localStorage.getItem('mq_theme') || 'dark');
function applyTheme(t){
  html.setAttribute('data-theme', t);
  localStorage.setItem('mq_theme', t);
  document.getElementById('themeBtn').textContent = t==='dark' ? '☀️ Light' : '🌙 Dark';
}
function toggleTheme(){ applyTheme(html.getAttribute('data-theme')==='dark'?'light':'dark'); }

// ── State ────────────────────────────────────────────────────
let currentTab    = 'inbox';
let allUsers      = [];
let currentThread = null; // { with_id, with_name }
const ME_ID       = <?= (int)$user['id'] ?>;
const ME_NAME     = <?= json_encode($user['username']) ?>;
const IS_STAFF    = <?= $is_staff ? 'true' : 'false' ?>;

// ── API helper ───────────────────────────────────────────────
async function api(action, params={}, method='POST'){
  if(method === 'GET'){
    const qs = new URLSearchParams({action,...params}).toString();
    const r  = await fetch(`messages_api.php?${qs}`);
    return r.json();
  }
  const fd = new FormData();
  fd.append('action', action);
  for(const [k,v] of Object.entries(params)) fd.append(k, v);
  const r = await fetch('messages_api.php', {method:'POST', body:fd});
  return r.json();
}

// ── Toast ────────────────────────────────────────────────────
function toast(msg, type='success'){
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className   = `toast ${type} show`;
  setTimeout(() => el.classList.remove('show'), 2800);
}

// ── Role avatar ──────────────────────────────────────────────
function roleAvatar(role){
  return role==='admin'?'👑':role==='teacher'?'🧑‍🏫':'🎮';
}

// ── Time format ──────────────────────────────────────────────
function fmtTime(ts){
  const d = new Date(ts.replace(' ','T'));
  const now = new Date();
  if(d.toDateString()===now.toDateString()) return d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
  return d.toLocaleDateString([],{month:'short',day:'numeric'});
}

// ── Tab switch ───────────────────────────────────────────────
function switchTab(tab){
  currentTab = tab;
  currentThread = null;
  document.querySelectorAll('.ntab').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-'+tab)?.classList.add('active');
  const titles = {inbox:'Inbox', sent:'Sent', announce:'Announcements'};
  const subs   = {inbox:'Messages sent to you', sent:'Messages you sent', announce:'Broadcast to all students'};
  document.getElementById('panelTitle').textContent = titles[tab] || 'Messages';
  document.getElementById('panelSub').textContent   = subs[tab]  || '';
  if(tab === 'announce') renderCompose(true);
  else loadList(tab);
}

// ── Load list ────────────────────────────────────────────────
async function loadList(tab){
  const content = document.getElementById('msgContent');
  content.innerHTML = '<div class="empty-state"><p>Loading…</p></div>';

  const action = tab === 'sent' ? 'sent' : 'inbox';
  const res    = await api(action, {}, 'GET');
  if(!res.ok){ content.innerHTML='<div class="empty-state"><span>⚠️</span><p>Could not load messages.</p></div>'; return; }

  const msgs = res.messages || [];
  if(!msgs.length){
    content.innerHTML='<div class="empty-state"><span>📭</span><p>Nothing here yet.</p></div>';
    return;
  }

  const list = document.createElement('div');
  list.className = 'msg-list';

  msgs.forEach(m => {
    const isUnread = !parseInt(m.is_read) && m.sender_id != ME_ID;
    const isAnn    = parseInt(m.is_announcement);
    const fromName = tab==='sent' ? (m.receiver_name || 'All Students') : m.sender_name;
    const fromRole = tab==='sent' ? '' : m.sender_role;

    const item = document.createElement('div');
    item.className = 'msg-item' + (isUnread?' unread':'');
    item.innerHTML = `
      <div class="msg-avatar">${roleAvatar(fromRole)}</div>
      <div class="msg-item-body">
        <div class="msg-item-meta">
          <span class="msg-item-from">${escHtml(fromName)}</span>
          ${isAnn ? '<span class="ann-pill">📢 Announcement</span>' : ''}
          <span class="msg-item-time">${fmtTime(m.created_at)}</span>
        </div>
        <div class="msg-item-subject">${escHtml(m.subject)}</div>
        <div class="msg-item-preview">${escHtml(m.body.slice(0,80))}${m.body.length>80?'…':''}</div>
      </div>`;

    item.addEventListener('click', () => {
      if(isAnn) openAnnouncement(m);
      else openThread(tab==='sent' ? m.receiver_id : m.sender_id,
                      tab==='sent' ? (m.receiver_name||'All') : m.sender_name,
                      m.subject);
    });
    list.appendChild(item);
  });

  content.innerHTML = '';
  content.appendChild(list);
  updateUnreadBadge();
}

// ── Open thread ──────────────────────────────────────────────
async function openThread(withId, withName, subject){
  currentThread = {with_id: withId, with_name: withName, subject};
  const content = document.getElementById('msgContent');

  // Mark as read
  await api('unread_count', {}, 'GET'); // refresh

  const res = await api('thread', {with: withId}, 'GET');
  if(!res.ok){ toast('Could not load thread.','error'); return; }

  content.innerHTML = '';
  const view = document.createElement('div');
  view.className = 'thread-view';

  // Header
  const hdr = document.createElement('div');
  hdr.className = 'thread-header';
  hdr.innerHTML = `
    <button class="thread-back" onclick="switchTab('${currentTab}')">←</button>
    <div>
      <div class="thread-title">${escHtml(subject)}</div>
      <div class="thread-with">with ${escHtml(withName)}</div>
    </div>`;
  view.appendChild(hdr);

  // Messages
  const msgs = document.createElement('div');
  msgs.className = 'thread-messages';
  msgs.id = 'threadMessages';
  renderBubbles(res.messages, msgs);
  view.appendChild(msgs);

  // Compose bar
  const bar = document.createElement('div');
  bar.className = 'thread-compose';
  bar.innerHTML = `
    <textarea class="thread-input" id="threadInput" placeholder="Reply…" rows="1"
      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();threadReply();}"></textarea>
    <button class="thread-send" onclick="threadReply()">Send →</button>`;
  view.appendChild(bar);

  content.appendChild(view);
  msgs.scrollTop = msgs.scrollHeight;
  document.getElementById('threadInput').focus();
  updateUnreadBadge();
}

function renderBubbles(messages, container){
  container.innerHTML = '';
  messages.forEach(m => {
    const mine = (m.sender_id == ME_ID);
    const wrap = document.createElement('div');
    wrap.className = 'bubble-wrap ' + (mine ? 'mine' : 'theirs');
    wrap.innerHTML = `
      <div class="bubble ${mine?'mine':'theirs'}">${escHtml(m.body)}</div>
      <div class="bubble-meta">${mine?'You':escHtml(m.sender_name)} · ${fmtTime(m.created_at)}</div>`;
    container.appendChild(wrap);
  });
}

async function threadReply(){
  const input = document.getElementById('threadInput');
  const body  = input.value.trim();
  if(!body || !currentThread) return;
  input.value = '';

  const res = await api('send', {
    receiver_id: currentThread.with_id,
    subject:     'Re: ' + currentThread.subject,
    body,
    is_announcement: 0,
  });
  if(!res.ok){ toast(res.error||'Failed to send.','error'); return; }

  // Refresh thread
  const r2 = await api('thread', {with: currentThread.with_id}, 'GET');
  if(r2.ok){
    const msgs = document.getElementById('threadMessages');
    renderBubbles(r2.messages, msgs);
    msgs.scrollTop = msgs.scrollHeight;
  }
}

// ── Announcement view ────────────────────────────────────────
function openAnnouncement(m){
  const content = document.getElementById('msgContent');
  content.innerHTML = `
    <div class="thread-view">
      <div class="thread-header">
        <button class="thread-back" onclick="switchTab('inbox')">←</button>
        <div>
          <div class="thread-title">📢 ${escHtml(m.subject)}</div>
          <div class="thread-with">from ${escHtml(m.sender_name)} · ${fmtTime(m.created_at)}</div>
        </div>
      </div>
      <div class="thread-messages">
        <div class="bubble-wrap" style="align-items:center">
          <div class="bubble announcement">${escHtml(m.body)}</div>
        </div>
      </div>
    </div>`;
}

// ── Announce compose (staff only) ─────────────────────────────
function renderCompose(announce=false){
  const content = document.getElementById('msgContent');
  content.innerHTML = `
    <div style="padding:28px;display:flex;flex-direction:column;gap:14px;flex:1">
      <p style="font-family:'Syne',sans-serif;font-size:0.8em;color:var(--tdim)">
        📢 This message will be sent to <strong style="color:var(--amber)">all students</strong>.
      </p>
      <div class="form-row">
        <label class="form-label">Subject</label>
        <input class="form-input" id="annSubject" type="text" placeholder="Announcement subject…">
      </div>
      <div class="form-row" style="flex:1">
        <label class="form-label">Message</label>
        <textarea class="form-textarea" id="annBody" placeholder="Write your announcement…" style="min-height:140px"></textarea>
      </div>
      <div>
        <button class="compose-btn" onclick="sendAnnouncement()" style="width:auto;padding:12px 28px">
          📢 Send Announcement
        </button>
      </div>
    </div>`;
}

async function sendAnnouncement(){
  const subject = document.getElementById('annSubject').value.trim();
  const body    = document.getElementById('annBody').value.trim();
  if(!body){ toast('Please write a message.','error'); return; }
  const res = await api('send',{subject:subject||'Announcement',body,is_announcement:1,receiver_id:''});
  if(res.ok){ toast('Announcement sent! 📢'); renderCompose(); }
  else toast(res.error||'Failed.','error');
}

// ── Compose modal ────────────────────────────────────────────
async function openCompose(){
  document.getElementById('annCheck') && (document.getElementById('annCheck').checked=false);
  document.getElementById('toRow').style.display = '';
  document.getElementById('composeSubject').value = '';
  document.getElementById('composeBody').value    = '';

  // Load users
  if(!allUsers.length){
    const res = await api('users',{},'GET');
    if(res.ok) allUsers = res.users;
  }
  const sel = document.getElementById('composeTo');
  sel.innerHTML = '<option value="">— Select recipient —</option>';
  allUsers.forEach(u => {
    const opt = document.createElement('option');
    opt.value = u.id;
    opt.textContent = `${roleAvatar(u.role)} ${u.username} (${u.role})`;
    sel.appendChild(opt);
  });

  document.getElementById('composeModal').classList.add('show');
}

function closeCompose(){ document.getElementById('composeModal').classList.remove('show'); }

function toggleAnnouncement(on){
  document.getElementById('toRow').style.display = on ? 'none' : '';
}

async function sendMessage(){
  const isAnn     = document.getElementById('annCheck')?.checked || false;
  const to        = document.getElementById('composeTo').value;
  const subject   = document.getElementById('composeSubject').value.trim();
  const body      = document.getElementById('composeBody').value.trim();

  if(!body){ toast('Write a message first.','error'); return; }
  if(!isAnn && !to){ toast('Select a recipient.','error'); return; }

  const res = await api('send',{
    receiver_id: isAnn ? '' : to,
    subject: subject || '(no subject)',
    body,
    is_announcement: isAnn ? 1 : 0,
  });

  if(res.ok){
    closeCompose();
    toast('Message sent! ✓');
    loadList(currentTab);
  } else {
    toast(res.error||'Failed to send.','error');
  }
}

// ── Unread badge ─────────────────────────────────────────────
async function updateUnreadBadge(){
  const res = await api('unread_count',{},'GET');
  if(!res.ok) return;
  const n   = res.count;
  const el  = document.getElementById('unreadBadgeSide');
  if(n > 0){ el.textContent=n; el.style.display=''; }
  else { el.style.display='none'; }
}

// ── Escape html ──────────────────────────────────────────────
function escHtml(s){
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Close modal on overlay click
document.getElementById('composeModal').addEventListener('click', function(e){
  if(e.target === this) closeCompose();
});

// ── Boot ─────────────────────────────────────────────────────
loadList('inbox');
updateUnreadBadge();
</script>
</body>
</html>