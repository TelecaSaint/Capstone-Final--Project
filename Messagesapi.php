<?php
require_once 'config.php';
require_login();
header('Content-Type: application/json');

$user   = current_user();
$uid    = (int) $user['id'];
$role   = $user['role'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function json_out($data){ echo json_encode($data); exit; }
function is_staff($r){ return in_array($r, ['admin','teacher']); }

try {
    $pdo = db();

    // ── send ────────────────────────────────────────────────────
    if($action === 'send'){
        $body            = trim($_POST['body'] ?? '');
        $subject         = trim($_POST['subject'] ?? '(no subject)');
        $receiver_id     = $_POST['receiver_id'] !== '' ? (int)$_POST['receiver_id'] : null;
        $is_announcement = is_staff($role) && !empty($_POST['is_announcement']) ? 1 : 0;

        if($body === '') json_out(['ok'=>false,'error'=>'Message body is required.']);

        // Announcements: receiver_id = NULL
        if($is_announcement) $receiver_id = null;

        // Students can only message teachers/admins or other students (not broadcast)
        if(!is_staff($role) && $is_announcement)
            json_out(['ok'=>false,'error'=>'Students cannot send announcements.']);

        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, subject, body, is_announcement, created_at)
            VALUES (:sid, :rid, :sub, :body, :ann, NOW())
        ");
        $stmt->execute([
            ':sid'  => $uid,
            ':rid'  => $receiver_id,
            ':sub'  => $subject,
            ':body' => $body,
            ':ann'  => $is_announcement,
        ]);
        json_out(['ok'=>true,'id'=>(int)$pdo->lastInsertId()]);
    }

    // ── inbox ───────────────────────────────────────────────────
    if($action === 'inbox'){
        // Messages sent directly to me OR announcements (receiver_id NULL) not sent by me
        $stmt = $pdo->prepare("
            SELECT m.*, u.username AS sender_name, u.role AS sender_role
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (m.receiver_id = :uid OR (m.is_announcement = 1 AND m.sender_id != :uid2))
            ORDER BY m.created_at DESC
            LIMIT 100
        ");
        $stmt->execute([':uid'=>$uid, ':uid2'=>$uid]);
        json_out(['ok'=>true,'messages'=>$stmt->fetchAll()]);
    }

    // ── sent ────────────────────────────────────────────────────
    if($action === 'sent'){
        $stmt = $pdo->prepare("
            SELECT m.*, u.username AS receiver_name
            FROM messages m
            LEFT JOIN users u ON u.id = m.receiver_id
            WHERE m.sender_id = :uid
            ORDER BY m.created_at DESC
            LIMIT 100
        ");
        $stmt->execute([':uid'=>$uid]);
        json_out(['ok'=>true,'messages'=>$stmt->fetchAll()]);
    }

    // ── thread (conversation between two users) ─────────────────
    if($action === 'thread'){
        $other = (int)($_GET['with'] ?? 0);
        if(!$other) json_out(['ok'=>false,'error'=>'Missing user id.']);
        $stmt = $pdo->prepare("
            SELECT m.*, u.username AS sender_name, u.role AS sender_role
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (m.sender_id = :me AND m.receiver_id = :other)
               OR (m.sender_id = :other2 AND m.receiver_id = :me2)
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([':me'=>$uid,':other'=>$other,':other2'=>$other,':me2'=>$uid]);
        // Mark as read
        $pdo->prepare("UPDATE messages SET is_read=1 WHERE receiver_id=:uid AND sender_id=:other")
            ->execute([':uid'=>$uid,':other'=>$other]);
        json_out(['ok'=>true,'messages'=>$stmt->fetchAll()]);
    }

    // ── mark_read ───────────────────────────────────────────────
    if($action === 'mark_read'){
        $mid = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE messages SET is_read=1 WHERE id=:id AND receiver_id=:uid")
            ->execute([':id'=>$mid,':uid'=>$uid]);
        json_out(['ok'=>true]);
    }

    // ── unread_count ────────────────────────────────────────────
    if($action === 'unread_count'){
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM messages
            WHERE is_read=0
              AND sender_id != :uid
              AND (receiver_id = :uid2 OR (is_announcement=1))
        ");
        $stmt->execute([':uid'=>$uid,':uid2'=>$uid]);
        json_out(['ok'=>true,'count'=>(int)$stmt->fetchColumn()]);
    }

    // ── users list (for compose dropdown) ───────────────────────
    if($action === 'users'){
        if(is_staff($role)){
            // Teachers/admins see everyone
            $stmt = $pdo->query("SELECT id, username, role FROM users WHERE id != $uid ORDER BY role, username");
        } else {
            // Students see teachers/admins + other students
            $stmt = $pdo->query("SELECT id, username, role FROM users WHERE id != $uid ORDER BY role, username");
        }
        json_out(['ok'=>true,'users'=>$stmt->fetchAll()]);
    }

    // ── delete ──────────────────────────────────────────────────
    if($action === 'delete'){
        $mid = (int)($_POST['id'] ?? 0);
        // Can only delete own sent messages or messages received by you
        $pdo->prepare("DELETE FROM messages WHERE id=:id AND (sender_id=:uid OR receiver_id=:uid2)")
            ->execute([':id'=>$mid,':uid'=>$uid,':uid2'=>$uid]);
        json_out(['ok'=>true]);
    }

    json_out(['ok'=>false,'error'=>'Unknown action.']);

} catch(PDOException $e){
    error_log('[messages_api] '.$e->getMessage());
    json_out(['ok'=>false,'error'=>'Database error.']);
}