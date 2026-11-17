<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';

$statistik = [
    'Unit Usaha' => (int) $pdo
        ->query('SELECT COUNT(*) FROM unit_usaha')
        ->fetchColumn(),

    'Produk' => (int) $pdo
        ->query('SELECT COUNT(*) FROM produk')
        ->fetchColumn(),

    'Berita' => (int) $pdo
        ->query('SELECT COUNT(*) FROM berita')
        ->fetchColumn(),

    'Foto Galeri' => (int) $pdo
        ->query('SELECT COUNT(*) FROM galeri')
        ->fetchColumn(),

    'Mitra' => (int) $pdo
        ->query('SELECT COUNT(*) FROM mitra')
        ->fetchColumn(),
];

require __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Dashboard</h1>

    <p class="muted">
        Selamat datang, <?= e($admin['nama']) ?>.
        Kelola informasi Moza Group melalui menu di samping.
    </p>
</div>

<section class="stats-grid" aria-label="Ringkasan data">
    <?php foreach ($statistik as $label => $jumlah): ?>
        <article class="stat-card">
            <span class="stat-label">
                <?= e($label) ?>
            </span>

            <strong class="stat-number">
                <?= e($jumlah) ?>
            </strong>
        </article>
    <?php endforeach; ?>
</section>

<section class="panel">
    <h2>Pengelolaan Website</h2>

    <p class="muted">
        Lengkapi profil perusahaan dan informasi kontak,
        kemudian tambahkan unit usaha, produk, serta dokumentasi.
    </p>

    <div class="quick-links">
        <a
            class="button"
            href="<?= e(url('admin/profil.php')) ?>"
        >
            Profil Perusahaan
        </a>

        <a
            class="button button-outline"
            href="<?= e(url('admin/unit-usaha/index.php')) ?>"
        >
            Kelola Unit Usaha
        </a>

        <a
            class="button button-outline"
            href="<?= e(url('admin/pengaturan.php')) ?>"
        >
            Pengaturan Website
        </a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>