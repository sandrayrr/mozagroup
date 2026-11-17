<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script hanya dapat dijalankan melalui terminal.');
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';

function terminal_input(string $label): string
{
    echo $label;

    $value = fgets(STDIN);

    if ($value === false) {
        exit("Input dibatalkan.\n");
    }

    return rtrim($value, "\r\n");
}

// Hanya untuk membuat admin pertama.
$totalAdmin = (int) $pdo
    ->query('SELECT COUNT(*) FROM admins')
    ->fetchColumn();

if ($totalAdmin > 0) {
    exit("Admin sudah tersedia. Gunakan halaman Akun untuk mengubah akun.\n");
}

echo "=== Buat Admin Pertama Moza Group ===\n";
echo "Password akan terlihat saat diketik di terminal.\n\n";

$nama = trim(terminal_input('Nama: '));
$email = strtolower(trim(terminal_input('Email: ')));
$password = terminal_input('Password minimal 12 karakter: ');
$confirmation = terminal_input('Ulangi password: ');

$errors = array_filter([
    validate_required($nama, 'Nama', 100),
    validate_email($email),
    validate_password($password, $confirmation),
]);

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '- ' . $error . PHP_EOL;
    }

    exit(1);
}

try {
    $statement = $pdo->prepare(
        'INSERT INTO admins (nama, email, password)
         VALUES (?, ?, ?)'
    );

    $statement->execute([
        $nama,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
    ]);

    echo "\nAdmin berhasil dibuat.\n";
    echo 'Halaman login: ' . url('admin/login.php') . PHP_EOL;
} catch (PDOException $exception) {
    error_log('Pembuatan admin gagal: ' . $exception->getMessage());

    exit("Admin gagal dibuat. Periksa log aplikasi.\n");
}