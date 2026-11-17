<?php

declare(strict_types=1);

// Mencegah bootstrap dimuat lebih dari satu kali.
if (defined('MOZA_BOOTSTRAPPED')) {
    return;
}

define('MOZA_BOOTSTRAPPED', true);
define('BASE_PATH', dirname(__DIR__));

// Konfigurasi aplikasi.
$app = require BASE_PATH . '/config/app.php';

date_default_timezone_set($app['timezone'] ?? 'Asia/Jakarta');

// Pengaturan error.
error_reporting(E_ALL);

ini_set(
    'display_errors',
    !empty($app['debug']) ? '1' : '0'
);

ini_set('log_errors', '1');

$logDirectory = BASE_PATH . '/storage/logs';

if (!is_dir($logDirectory)) {
    mkdir($logDirectory, 0755, true);
}

ini_set('error_log', $logDirectory . '/app.log');

// Sesi hanya diperlukan pada akses melalui browser.
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $isHttps = isset($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_name('moza_group_session');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

// Header keamanan dasar.
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Fungsi pembantu dan keamanan.
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/validation.php';
require_once BASE_PATH . '/includes/csrf.php';
require_once BASE_PATH . '/includes/auth.php';
require_once BASE_PATH . '/includes/upload.php';
require_once BASE_PATH . '/includes/pagination.php';
// Koneksi database.
try {
    $pdo = require BASE_PATH . '/config/database.php';
} catch (PDOException $exception) {
    error_log('Koneksi database gagal: ' . $exception->getMessage());

    if (PHP_SAPI !== 'cli') {
        http_response_code(503);
    }

    exit(
        'Koneksi database gagal. Periksa konfigurasi database '
        . 'dan pastikan MySQL sudah aktif.'
    );
}