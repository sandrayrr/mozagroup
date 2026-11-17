<?php

declare(strict_types=1);

$activeMenu = $activeMenu ?? '';

$menuGroups = [
    'Utama' => [
        ['dashboard', 'Dashboard', 'admin/index.php'],
        ['profil', 'Profil Perusahaan', 'admin/profil.php'],
        ['beranda', 'Konten Beranda', 'admin/beranda.php'],
    ],

    'Kelola Konten' => [
        ['unit-usaha', 'Unit Usaha', 'admin/unit-usaha/index.php'],
        ['produk', 'Produk', 'admin/produk/index.php'],
        ['berita', 'Berita', 'admin/berita/index.php'],
        ['galeri', 'Galeri', 'admin/galeri/index.php'],
        ['kemitraan', 'Kemitraan', 'admin/kemitraan.php'],
        ['mitra', 'Mitra Perusahaan', 'admin/mitra/index.php'],
    ],

    'Pengaturan' => [
        ['pengaturan', 'Pengaturan Website', 'admin/pengaturan.php'],
        ['akun', 'Akun Admin', 'admin/akun.php'],
    ],
];
?>

<aside class="admin-sidebar" id="admin-sidebar">
    <a class="admin-brand" href="<?= e(url('admin/index.php')) ?>">
        MOZA GROUP
        <small>Panel Administrator</small>
    </a>

    <?php foreach ($menuGroups as $group => $items): ?>
        <p class="sidebar-label"><?= e($group) ?></p>

        <nav class="sidebar-nav" aria-label="<?= e($group) ?>">
            <?php foreach ($items as [$key, $label, $path]): ?>
                <a
                    href="<?= e(url($path)) ?>"
                    class="<?= $activeMenu === $key ? 'active' : '' ?>"
                    <?= $activeMenu === $key
                        ? 'aria-current="page"'
                        : '' ?>
                >
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endforeach; ?>
</aside>