<?php
// Include this file on any page where you want the floating chat bubble.
// Usage: <?php require_once 'chat_bubble.php'; ?>
// Must be included AFTER require_login() and config.php.
if(!isset($user)) $user = current_user();
?>
<!-- ── Floating Chat Bubble ───────────────────────────────── -->
<style>
.chat-fab-wrap{position:fixed;bottom:24px;left:24px;z-index:400;display:flex;flex-direction:column;align-items:flex-start;gap:8px}
.chat-fab{width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#0099cc);border:none;cursor:pointer;font-size:1.4em;box-shadow:0 4px 20px rgba(124,58,237,0.35);transition:transform 0.2s,box-shadow 0.2s;display:grid;place-items:center;position:relative}
.chat-fab:hover{transform:translateY(-3px) scale(1.06);box-shadow:0 8px 28px rgba(124,58,237,0.45)}
.chat-fab-badge{position:absolute;top:-3px;right:-3px;background:#ff5252;color:#fff;font-family:'Syne',sans-serif;font-size:0.58em;font-weight:800;border-radius:10px;padding:1px 5px;min-width:17px;text-align:center;display:none;border:2px solid var(--bg,#080b14);line-height:1.4}
.chat-fab-tooltip{background:rgba(8,11,20,0.9);color:#e8eaf2;font-family:'Syne',sans-serif;font-size:0.7em;font-weight:600;letter-spacing:0.06em;padding:5px 10px;border-radius:6px;pointer-events:none;opacity:0;transform:translateX(-4px);transition:all 0.18s;white-space:nowrap;position:absolute;left:58px;top:50%;transform:translateY(-50%);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,0.08)}
.chat-fab:hover .chat-fab-tooltip{opacity:1;transform:translateY(-50%) translateX(0)}
</style>

<div class="chat-fab-wrap">
  <button class="chat-fab" id="globalChatFab" onclick="window.location.href='messages.php'" title="Messages">
    💬
    <span class="chat-fab-badge" id="globalChatBadge"></span>
    <span class="chat-fab-tooltip">Messages</span>
  </button>
</div>

<script>
// Fetch unread count and show badge
(async function(){
  try{
    const fd = new FormData();
    fd.append('action','unread_count');
    const r = await fetch('messages_api.php?action=unread_count');
    const d = await r.json();
    if(d.ok && d.count > 0){
      const badge = document.getElementById('globalChatBadge');
      badge.textContent = d.count > 99 ? '99+' : d.count;
      badge.style.display = 'block';
    }
  }catch(e){}
})();
</script>