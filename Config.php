<?php
// ============================================================
//  config.php — Database & Global Helpers
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'mathquest');
define('DB_USER', 'root'); 
define('DB_PASS', ''); 
define('DB_CHARSET', 'utf8mb4');
define('SESSION_NAME', 'mq_session');

// 1. Session Bootstrap
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_name(SESSION_NAME);
    session_start();
}

// 2. Database connection 
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die("Database connection failed.");
        }
    }
    return $pdo;
}

// 3. Global Redirect Helper (FIXES THE ERROR)
function redirect(string $url): void {
    if (!headers_sent()) {
        header("Location: $url");
    } else {
        echo '<script>window.location.href="' . $url . '";</script>';
    }
    exit;
}

// 4. User Helpers
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(string $role = ''): array {
    $user = current_user();
    if (!$user) {
        redirect('login.php');
    }
    if ($role && ($user['role'] ?? '') !== $role) {
        redirect('login.php');
    }
    return $user;
}

function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}