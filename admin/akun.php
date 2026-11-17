<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Akun Admin';
$activeMenu = 'akun';

$errors = [];

$nama = $admin['nama'];
$email = $admin['email'];

if (is_post()) {
    csrf_verify();

    $nama = input_string($_POST, 'nama');
    $email = strtolower(input_string($_POST, 'email'));

    // Password tidak di-trim agar karakter aslinya dipertahankan.
    $currentPassword = is_string($_POST['current_password'] ?? null)
        ? $_POST['current_password']
        : '';

    $newPassword = is_string($_POST['new_password'] ?? null)
        ? $_POST['new_password']
        : '';

    $confirmation = is_string($_POST['password_confirmation'] ?? null)
        ? $_POST['password_confirmation']
        : '';

    $nameError = validate_required($nama, 'Nama', 100);

    if ($nameError !== null) {
        $errors[] = $nameError;
    }

    $emailError = validate_email($email);

    if ($emailError !== null) {
        $errors[] = $emailError;
    }

    if (
        $currentPassword === ''
        || strlen($currentPassword) > 72
        || str_contains($currentPassword, "\0")
    ) {
        $errors[] = 'Masukkan password saat ini yang valid.';
    }

    $changePassword = $newPassword !== '' || $confirmation !== '';

    if ($changePassword) {
        $passwordError = validate_password(
            $newPassword,
            $confirmation
        );

        if ($passwordError !== null) {
            $errors[] = $passwordError;
        }
    }

    if ($errors === []) {
        // Batasi percobaan verifikasi password menggunakan fungsi
        // pembatasan login yang sudah tersedia.
        if (login_is_limited($admin['email'])) {
            $errors[] = 'Terlalu banyak percobaan. Coba lagi dalam 15 menit.';
        } else {
            $statement = $pdo->prepare(
                'SELECT password FROM admins WHERE id = ?'
            );

            $statement->execute([$admin['id']]);

            $storedHash = $statement->fetchColumn();

            if (
                !is_string($storedHash)
                || !password_verify($currentPassword, $storedHash)
            ) {
                record_login_failure($admin['email']);

                $errors[] = 'Password saat ini salah.';
            }
        }
    }

    if ($errors === []) {
        $statement = $pdo->prepare(
            'SELECT id
             FROM admins
             WHERE email = ? AND id <> ?
             LIMIT 1'
        );

        $statement->execute([$email, $admin['id']]);

        if ($statement->fetch()) {
            $errors[] = 'Email sudah digunakan akun lain.';
        }
    }

    if ($errors === []) {
        try {
            $passwordHash = $storedHash;

            if (
                $changePassword
                || password_needs_rehash($storedHash, PASSWORD_DEFAULT)
            ) {
                $passwordHash = password_hash(
                    $changePassword ? $newPassword : $currentPassword,
                    PASSWORD_DEFAULT
                );
            }

            $statement = $pdo->prepare(
                'UPDATE admins
                 SET nama = ?, email = ?, password = ?
                 WHERE id = ?'
            );

            $statement->execute([
                $nama,
                $email,
                $passwordHash,
                $admin['id'],
            ]);
        } catch (PDOException $exception) {
            error_log(
                'Pembaruan akun gagal: ' . $exception->getMessage()
            );

            $errors[] = 'Akun gagal diperbarui. Silakan coba kembali.';
        }

        if ($errors === []) {
            session_regenerate_id(true);

            // Form lama harus dimuat ulang setelah perubahan akun.
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            set_flash(
                'success',
                $changePassword
                    ? 'Akun dan password berhasil diperbarui.'
                    : 'Informasi akun berhasil diperbarui.'
            );

            redirect('admin/akun.php');
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Akun Admin</h1>

    <p class="muted">
        Perbarui identitas dan password akun kamu.
    </p>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert-error" role="alert">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form
    method="post"
    action="<?= e(url('admin/akun.php')) ?>"
>
    <?= csrf_field() ?>

    <section class="panel">
        <h2>Informasi Akun</h2>

        <div class="form-group">
            <label for="nama">Nama Admin *</label>

            <input
                type="text"
                id="nama"
                name="nama"
                value="<?= e($nama) ?>"
                maxlength="100"
                autocomplete="name"
                required
            >
        </div>

        <div class="form-group">
            <label for="email">Email Login *</label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($email) ?>"
                maxlength="191"
                autocomplete="username"
                required
            >
        </div>
    </section>

    <section class="panel">
        <h2>Ganti Password</h2>

        <p class="muted">
            Kosongkan kedua kolom berikut jika tidak ingin mengganti password.
        </p>

        <div class="form-grid">
            <div class="form-group">
                <label for="new_password">Password Baru</label>

                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    autocomplete="new-password"
                >

                <small class="form-help">
                    Minimal 12 karakter dan maksimal 72 byte.
                </small>
            </div>

            <div class="form-group">
                <label for="password_confirmation">
                    Konfirmasi Password Baru
                </label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                >
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Konfirmasi Perubahan</h2>

        <div class="form-group">
            <label for="current_password">Password Saat Ini *</label>

            <input
                type="password"
                id="current_password"
                name="current_password"
                autocomplete="current-password"
                required
            >

            <small class="form-help">
                Wajib diisi untuk menyimpan perubahan akun.
            </small>
        </div>

        <button type="submit" class="button">
            Simpan Perubahan
        </button>
    </section>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>