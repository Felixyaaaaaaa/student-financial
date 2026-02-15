<?php
// includes/functions.php
require_once __DIR__ . '/config.php';

/**
 * Redirect helper
 */
function redirect(string $path = ''): void {
    $base = rtrim(BASE_URL, '/');
    header('Location: ' . $base . ($path ? '/' . ltrim($path, '/') : ''));
    exit;
}

/**
 * Flash messages (stored in session)
 */
function flash(string $key, $message = null) {
    if ($message === null) {
        if (!empty($_SESSION['_flash'][$key])) {
            $msg = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }
        return null;
    }
    $_SESSION['_flash'][$key] = $message;
}

/**
 * CSRF token generation & validation
 */
function csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_check($token): bool {
    return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], (string)$token);
}

/**
 * Sanitize output for HTML
 */
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Require login (redirect to login if not)
 */
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

/**
 * Find user by email
 */
function find_user_by_email(PDO $pdo, string $email) {
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    return $stmt->fetch();
}

/**
 * Create user
 */
function create_user(PDO $pdo, string $nama, string $email, string $password): int {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO mahasiswa (nama, email, kata_sandi) VALUES (:nama, :email, :kata_sandi)");
    $stmt->execute(['nama' => $nama, 'email' => $email, 'kata_sandi' => $hash]);
    return (int)$pdo->lastInsertId();
}
 