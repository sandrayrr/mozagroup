<?php

declare(strict_types=1);

$publicMenus = [
    'beranda' => ['Beranda', ''],
    'tentang' => ['Tentang Kami', 'tentang.php'],
    'unit-usaha' => ['Unit Usaha', 'unit-usaha.php'],
    'produk' => ['Produk', 'produk.php'],
    'kemitraan' => ['Kemitraan', 'kemitraan.php'],
    'berita' => ['Berita', 'berita.php'],
    'galeri' => ['Galeri', 'galeri.php'],
    'kontak' => ['Kontak', 'kontak.php'],
];
?>

<header class="site-header">
    <div class="container navbar">
        <a class="brand" href="<?= e(url()) ?>">
            <?php if (!empty($settings['logo'])): ?>
                <img
                    src="<?= e(upload_url($settings['logo'])) ?>"
                    alt="<?= e($companyName) ?>"
                    width="150"
                    height="48"
                >
            <?php else: ?>
                <span><?= e($companyName) ?></span>
            <?php endif; ?>
        </a>

        <button
            class="nav-toggle"
            type="button"
            aria-label="Buka navigasi"
            aria-controls="public-menu"
            aria-expanded="false"
            data-nav-toggle
            hidden
        >
            ☰
        </button>

        <nav
            class="nav-menu"
            id="public-menu"
            aria-label="Navigasi utama"
        >
            <?php foreach ($publicMenus as $key => [$label, $path]): ?>
                <a
                    href="<?= e(url($path)) ?>"
                    class="<?= $activePage === $key ? 'active' : '' ?>"
                    <?= $activePage === $key ? 'aria-current="page"' : '' ?>
                >
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>