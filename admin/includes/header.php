<?php

declare(strict_types=1);

// Setiap halaman tetap memanggil require_admin()
// sebelum menghasilkan HTML.
$pageTitle = $pageTitle ?? 'Dashboard';

$flash = get_flash();

$flashClass = ($flash['type'] ?? '') === 'success'
    ? 'alert-success'
    : 'alert-error';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>
        <?= e($pageTitle) ?> | <?= e(config('nama_aplikasi')) ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= e(asset('css/admin.css')) ?>"
    >
</head>
<body>
    <?php require __DIR__ . '/sidebar.php'; ?>

    <div class="sidebar-overlay" data-sidebar-overlay></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button
                    type="button"
                    class="menu-toggle"
                    data-sidebar-toggle
                    aria-label="Buka atau tutup navigasi"
                    aria-controls="admin-sidebar"
                    aria-expanded="false"
                >
                    ☰
                </button>

                <span class="admin-name">
                    <?= e($admin['nama']) ?>
                </span>
            </div>

            <div class="topbar-actions">
                <a
                    class="button button-outline"
                    href="<?= e(url()) ?>"
                >
                    Lihat Website
                </a>

                <form
                    method="post"
                    action="<?= e(url('admin/logout.php')) ?>"
                >
                    <?= csrf_field() ?>

                    <button class="button" type="submit">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <main class="admin-content">
            <?php if ($flash !== null): ?>
                <div
                    class="alert <?= e($flashClass) ?>"
                    role="status"
                >
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>