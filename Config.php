<?php
// ============================================================
//  config.php — Database connection
//  Edit DB_HOST / DB_NAME / DB_USER / DB_PASS to match your
//  cPanel MySQL settings, then upload to your server root.
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'mathquest');
define('DB_USER', 'root');        // ← change to your MySQL user
define('DB_PASS', '');            // ← change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL', '');           // e.g. 'https://yoursite.com' — leave blank for relative URLs
define('SESSION_NAME', 'mq_session');

// ── PDO connection ────────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ── Session bootstrap ─────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_name(SESSION_NAME);
    session_start();
}

// ── Helper: JSON response ─────────────────────────────────────
function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ── Helper: redirect ──────────────────────────────────────────
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ── Helper: current user ──────────────────────────────────────
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(string $role = ''): array {
    $user = current_user();
    if (!$user) redirect('login.php');
    if ($role && $user['role'] !== $role) redirect('login.php');
    return $user;
}