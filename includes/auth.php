<?php

declare(strict_types=1);

/**
 * Mengambil admin yang sedang login.
 */
function current_admin(): ?array
{
    global $pdo;

    $adminId = positive_id($_SESSION['admin_id'] ?? null);

    if ($adminId === null) {
        return null;
    }

    $statement = $pdo->prepare(
        'SELECT id, nama, email
         FROM admins
         WHERE id = ?
         LIMIT 1'
    );

    $statement->execute([$adminId]);

    $admin = $statement->fetch();

    if (!$admin) {
        unset($_SESSION['admin_id']);

        return null;
    }

    return $admin;
}

/**
 * Melindungi halaman admin.
 *
 * Mengembalikan data admin yang sedang login.
 */
function require_admin(): array
{
    $admin = current_admin();

    if ($admin === null) {
        set_flash('error', 'Silakan login terlebih dahulu.');

        redirect('admin/login.php');
    }

    header('Cache-Control: no-store, private');

    return $admin;
}

/**
 * Mengalihkan admin yang sudah login.
 */
function guest_only(): void
{
    if (current_admin() !== null) {
        redirect('admin/index.php');
    }
}

/**
 * Identitas untuk pembatasan login.
 *
 * REMOTE_ADDR digunakan tanpa mempercayai header IP pengunjung.
 */
function login_identity(string $email): array
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    return [
        'email_hash' => hash('sha256', strtolower(trim($email))),
        'ip_hash' => hash('sha256', $ip),
    ];
}

/**
 * Menghitung percobaan gagal dalam 15 menit terakhir.
 */
function login_is_limited(string $email): bool
{
    global $pdo;

    $identity = login_identity($email);
    $since = date('Y-m-d H:i:s', time() - 900);

    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM login_attempts
         WHERE email_hash = ?
           AND attempted_at >= ?'
    );

    $statement->execute([
        $identity['email_hash'],
        $since,
    ]);

    $emailAttempts = (int) $statement->fetchColumn();

    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM login_attempts
         WHERE ip_hash = ?
           AND attempted_at >= ?'
    );

    $statement->execute([
        $identity['ip_hash'],
        $since,
    ]);

    $ipAttempts = (int) $statement->fetchColumn();

    return $emailAttempts >= 5 || $ipAttempts >= 20;
}

/**
 * Mencatat login gagal.
 */
function record_login_failure(string $email): void
{
    global $pdo;

    $identity = login_identity($email);

    $statement = $pdo->prepare(
        'INSERT INTO login_attempts (
            email_hash,
            ip_hash,
            attempted_at
         ) VALUES (?, ?, ?)'
    );

    $statement->execute([
        $identity['email_hash'],
        $identity['ip_hash'],
        date('Y-m-d H:i:s'),
    ]);

    // Bersihkan catatan yang lebih lama dari satu hari.
    $statement = $pdo->prepare(
        'DELETE FROM login_attempts
         WHERE attempted_at < ?'
    );

    $statement->execute([
        date('Y-m-d H:i:s', time() - 86400),
    ]);
}

/**
 * Memeriksa kredensial dan membuat sesi admin.
 *
 * Mengembalikan null jika berhasil atau pesan jika gagal.
 */
function attempt_login(string $email, string $password): ?string
{
    global $pdo;

    $email = strtolower(trim($email));

    if (login_is_limited($email)) {
        return 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.';
    }

    $statement = $pdo->prepare(
        'SELECT id, password
         FROM admins
         WHERE email = ?
         LIMIT 1'
    );

    $statement->execute([$email]);

    $admin = $statement->fetch();

    // Hash pengganti untuk tetap memeriksa password
    // ketika email tidak ditemukan.
    $dummyHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';

    $hash = $admin ? $admin['password'] : $dummyHash;

    $passwordValid = password_verify($password, $hash);

    if (!$admin || !$passwordValid) {
        record_login_failure($email);

        return 'Email atau password salah.';
    }

    if (password_needs_rehash($admin['password'], PASSWORD_DEFAULT)) {
        $statement = $pdo->prepare(
            'UPDATE admins SET password = ? WHERE id = ?'
        );

        $statement->execute([
            password_hash($password, PASSWORD_DEFAULT),
            $admin['id'],
        ]);
    }

    session_regenerate_id(true);

    $_SESSION = [
        'admin_id' => (int) $admin['id'],
        'csrf_token' => bin2hex(random_bytes(32)),
    ];

    // Hapus kegagalan dari pasangan email dan IP yang berhasil login.
    $identity = login_identity($email);

    $statement = $pdo->prepare(
        'DELETE FROM login_attempts
         WHERE email_hash = ? AND ip_hash = ?'
    );

    $statement->execute([
        $identity['email_hash'],
        $identity['ip_hash'],
    ]);

    return null;
}

/**
 * Menghapus sesi login.
 * Pemanggil wajib memeriksa POST dan token CSRF.
 */
function logout_admin(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}