<?php
require_once 'config.php';
$user = require_login('student');
$success='';$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'';
  if($action==='update_profile'){
    $name=trim($_POST['full_name']??'');
    if($name){
      db()->prepare('UPDATE users SET full_name=? WHERE id=?')->execute([$name,$user['id']]);
      $_SESSION['user']['full_name']=$name;
      $user['full_name']=$name;
      $success='Profile updated!';
    }
  } elseif($action==='change_password'){
    $cur=$_POST['current']??'';$new=$_POST['new_password']??'';$confirm=$_POST['confirm']??'';
    $row=db()->prepare('SELECT password FROM users WHERE id=?');$row->execute([$user['id']]);$row=$row->fetch();
    if(!password_verify($cur,$row['password'])){$error='Current password is incorrect.';}
    elseif(strlen($new)<6){$error='New password must be at least 6 characters.';}
    elseif($new!==$confirm){$error='Passwords do not match.';}
    else{db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new,PASSWORD_BCRYPT),$user['id']]);$success='Password changed successfully!';}
  }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MathQuest — Settings</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#080b14;--s1:#0e1220;--s2:#141827;--b:rgba(255,255,255,0.07);--bb:rgba(255,255,255,0.12);--cyan:#00e5ff;--cdim:rgba(0,229,255,0.1);--green:#00e676;--red:#ff5252;--text:#e8eaf2;--tdim:rgba(232,234,242,0.42);--tmid:rgba(232,234,242,0.68)}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
nav{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:rgba(8,11,20,0.97);border-bottom:1px solid var(--b);position:sticky;top:0;z-index:100;backdrop-filter:blur(12px)}
.nav-logo{font-family:'Syne',sans-serif;font-weight:800;font-size:1.15em;background:linear-gradient(135deg,var(--cyan),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none}
.nav-back{padding:6px 12px;border-radius:7px;color:var(--tdim);font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none;transition:all 0.2s}
.nav-back:hover{background:var(--s2);color:var(--tmid)}
.logout{padding:6px 12px;background:rgba(255,82,82,0.08);border:1px solid rgba(255,82,82,0.2);border-radius:7px;color:#ff8a80;font-family:'Syne',sans-serif;font-size:0.71em;font-weight:600;text-decoration:none}
.main{padding:28px;max-width:640px;margin:0 auto}
.page-title{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6em;margin-bottom:28px}
.panel{background:var(--s1);border:1px solid var(--b);border-radius:14px;padding:24px;margin-bottom:20px;animation:pIn 0.4s ease backwards}
@keyframes pIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.ptitle{font-family:'Syne',sans-serif;font-size:0.67em;font-weight:600;letter-spacing:0.14em;color:var(--tdim);text-transform:uppercase;margin-bottom:18px}
.field{margin-bottom:14px}
.field label{display:block;font-size:0.72em;font-weight:500;letter-spacing:0.07em;color:var(--tdim);text-transform:uppercase;margin-bottom:6px}
.field input{width:100%;padding:10px 13px;background:var(--s2);border:1px solid var(--b);border-radius:8px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.92em;outline:none;transition:all 0.2s}
.field input:focus{border-color:rgba(0,229,255,0.4);box-shadow:0 0 0 3px rgba(0,229,255,0.07)}
.btn{padding:11px 22px;background:linear-gradient(135deg,var(--cyan),#0099cc);border:none;border-radius:8px;color:#020d14;font-family:'Syne',sans-serif;font-size:0.82em;font-weight:700;letter-spacing:0.08em;cursor:pointer;transition:all 0.2s}
.btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,229,255,0.3)}
.alert{padding:10px 14px;border-radius:8px;font-size:0.84em;margin-bottom:16px}
.alert.success{background:rgba(0,230,118,0.08);border:1px solid rgba(0,230,118,0.25);color:var(--green)}
.alert.error{background:rgba(255,82,82,0.08);border:1px solid rgba(255,82,82,0.25);color:#ff8a80}
@media(max-width:600px){.main{padding:16px}}
</style>
</head>
<body>
<nav>
  <a href="dashboard.php" class="nav-logo">MQ</a>
  <a href="dashboard.php" class="nav-back">← Back to Dashboard</a>
  <a href="logout.php" class="logout">Sign Out</a>
</nav>
<main class="main">
  <div class="page-title">⚙ Settings</div>

  <?php if($success):?><div class="alert success">✓ <?=htmlspecialchars($success)?></div><?php endif;?>
  <?php if($error):?><div class="alert error">⚠ <?=htmlspecialchars($error)?></div><?php endif;?>

  <div class="panel" style="animation-delay:.04s">
    <div class="ptitle">👤 Profile</div>
    <form method="POST">
      <input type="hidden" name="action" value="update_profile">
      <div class="field"><label>Full Name</label><input type="text" name="full_name" value="<?=htmlspecialchars($user['full_name'])?>" required></div>
      <div class="field"><label>Username</label><input type="text" value="<?=htmlspecialchars($user['username'])?>" disabled style="opacity:0.5"></div>
      <div class="field"><label>Class</label><input type="text" value="<?=htmlspecialchars($user['class_name']??'')?>" disabled style="opacity:0.5"></div>
      <button type="submit" class="btn">Save Changes</button>
    </form>
  </div>

  <div class="panel" style="animation-delay:.08s">
    <div class="ptitle">🔒 Change Password</div>
    <form method="POST">
      <input type="hidden" name="action" value="change_password">
      <div class="field"><label>Current Password</label><input type="password" name="current" required></div>
      <div class="field"><label>New Password</label><input type="password" name="new_password" required></div>
      <div class="field"><label>Confirm New Password</label><input type="password" name="confirm" required></div>
      <button type="submit" class="btn">Change Password</button>
    </form>
  </div>

  <div class="panel" style="animation-delay:.12s">
    <div class="ptitle">🚪 Account</div>
    <p style="font-size:0.87em;color:var(--tdim);margin-bottom:14px">Sign out of your MathQuest account on this device.</p>
    <a href="logout.php" style="display:inline-block;padding:11px 22px;background:rgba(255,82,82,0.1);border:1px solid rgba(255,82,82,0.25);border-radius:8px;color:#ff8a80;font-family:'Syne',sans-serif;font-size:0.82em;font-weight:700;text-decoration:none">Sign Out</a>
  </div>
</main>
</body>
</html>