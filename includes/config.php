<?php
// includes/config.php
// Sesuaikan dengan environmentmu (XAMPP)
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    // secure session settings (ubah domain/path jika perlu)
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', // isi jika pakai domain custom
        'secure' => false, // set true jika pakai HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// BASE_URL: ganti sesuai folder projectmu atau biarkan kosong jika di root
define('BASE_URL', '/project-keuangan/public'); // contoh: '/project-keuangan/public'
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'keuangan_db'); // ganti sesuai db
define('DB_USER', 'root');
define('DB_PASS', '');

// PDO connection (throw exceptions on error)
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Jika di production, jangan tampilkan error details.
    exit('Database connection failed: ' . $e->getMessage());
}
