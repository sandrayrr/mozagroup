<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

guest_only();

header('Cache-Control: no-store, private');

$error = null;
$email = '';
$flash = get_flash();

if (is_post()) {
    csrf_verify();

    $email = strtolower(input_string($_POST, 'email'));

    $password = isset($_POST['password'])
        && is_string($_POST['password'])
        ? $_POST['password']
        : '';

    if (validate_email($email) !== null || $password === '') {
        $error = 'Masukkan email yang valid dan password.';
    } elseif (
        strlen($password) > 72
        || str_contains($password, "\0")
    ) {
        $error = 'Email atau password salah.';
    } else {
        $error = attempt_login($email, $password);

        if ($error === null) {
            redirect('admin/index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>Login Admin | <?= e(config('nama_aplikasi')) ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px;
            display: grid;
            place-items: center;
            background: #f8fafc;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 32px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            box-shadow: 0 16px 48px rgba(15, 23, 42, .07);
        }

        .brand {
            color: #dc2626;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        h1 {
            margin: 14px 0 8px;
            font-size: 28px;
        }

        .description {
            margin-bottom: 28px;
            color: #64748b;
            line-height: 1.6;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        input {
            width: 100%;
            min-height: 46px;
            margin-bottom: 20px;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font: inherit;
        }

        input:focus {
            outline: 3px solid #fee2e2;
            border-color: #dc2626;
        }

        button {
            width: 100%;
            min-height: 46px;
            padding: 12px;
            border: 0;
            border-radius: 10px;
            background: #dc2626;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            background: #991b1b;
        }

        .notice {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 10px;
            background: #f1f5f9;
            line-height: 1.5;
        }

        .error {
            color: #991b1b;
            background: #fef2f2;
        }

        .back {
            display: block;
            margin-top: 24px;
            color: #64748b;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <main class="login-card">
        <div class="brand">MOZA GROUP</div>

        <h1>Login Admin</h1>

        <p class="description">
            Masuk untuk mengelola konten website perusahaan.
        </p>

        <?php if ($flash !== null): ?>
            <div class="notice" role="status">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== null): ?>
            <div class="notice error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('admin/login.php')) ?>">
            <?= csrf_field() ?>

            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($email) ?>"
                maxlength="191"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Masuk Dashboard</button>
        </form>

        <a class="back" href="<?= e(url()) ?>">
            Kembali ke website
        </a>
    </main>
</body>
</html>