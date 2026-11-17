<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$activePage = 'galeri';
$pageTitle = 'Galeri | Moza Group';
$pageDescription = 'Dokumentasi kegiatan perusahaan dan unit usaha Moza Group.';
$canonicalUrl = url('galeri.php');

$unitFilter = input_string($_GET, 'unit');

$units = $pdo->query(
    'SELECT id, nama
     FROM unit_usaha
     WHERE aktif = 1
     ORDER BY urutan ASC, nama ASC'
)->fetchAll();

$validUnitIds = array_map(
    static fn(array $unit): int => (int) $unit['id'],
    $units
);

$conditions = [
    '(g.unit_usaha_id IS NULL OR u.aktif = 1)',
];

$params = [];

if ($unitFilter === 'umum') {
    $conditions[] = 'g.unit_usaha_id IS NULL';
} elseif ($unitFilter !== '') {
    $unitId = positive_id($unitFilter);

    if ($unitId === null || !in_array($unitId, $validUnitIds, true)) {
        $conditions[] = '1 = 0';
    } else {
        $unitFilter = (string) $unitId;
        $conditions[] = 'g.unit_usaha_id = ?';
        $params[] = $unitId;
    }
}

$where = ' WHERE ' . implode(' AND ', $conditions);

$statement = $pdo->prepare(
    'SELECT COUNT(*)
     FROM galeri g
     LEFT JOIN unit_usaha u ON u.id = g.unit_usaha_id'
    . $where
);

$statement->execute($params);

$pagination = pagination(
    (int) $statement->fetchColumn(),
    12
);

$statement = $pdo->prepare(
    'SELECT g.judul, g.foto, u.nama AS nama_unit
     FROM galeri g
     LEFT JOIN unit_usaha u ON u.id = g.unit_usaha_id'
    . $where
    . ' ORDER BY g.urutan ASC, g.id DESC
        LIMIT ? OFFSET ?'
);

$position = 1;

foreach ($params as $value) {
    $statement->bindValue($position++, $value, PDO::PARAM_INT);
}

$statement->bindValue(
    $position++,
    $pagination['per_page'],
    PDO::PARAM_INT
);

$statement->bindValue(
    $position,
    $pagination['offset'],
    PDO::PARAM_INT
);

$statement->execute();

$photos = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<!-- Tailwind CDN + config + fonts (dipasang di body agar kelas Code 2 aktif) -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@600;700&amp;display=swap"
    rel="stylesheet" />
<link
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
    rel="stylesheet" />
<style>
    @layer base {

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            overscroll-behavior: none;
        }

        main>:first-child {
            margin-top: 0 !important;
        }

        main>:last-child {
            margin-bottom: 0 !important;
        }
    }

    ::-webkit-scrollbar {
        display: none;
    }
</style>
<script src="https://cdn.tailwindcss.com"></script>
<script
    id="tailwind-config">tailwind.config = { darkMode: "class", theme: { extend: { "colors": { "outline": "#926f6b", "on-primary-fixed-variant": "#93000d", "on-secondary-container": "#fffbff", "error-container": "#ffdad6", "surface": "#faf9fd", "background": "#faf9fd", "on-tertiary-fixed": "#410004", "error": "#ba1a1a", "surface-container": "#efedf1", "on-secondary": "#ffffff", "on-tertiary-fixed-variant": "#930013", "outline-variant": "#e6bdb8", "tertiary-fixed-dim": "#ffb3ad", "primary-fixed-dim": "#ffb4ab", "on-tertiary-container": "#ffebe9", "inverse-surface": "#2f3033", "secondary-fixed-dim": "#ffb4ac", "secondary-container": "#d83734", "surface-dim": "#dbd9dd", "on-tertiary": "#ffffff", "surface-tint": "#c00014", "primary-fixed": "#ffdad6", "on-secondary-fixed-variant": "#93000e", "inverse-on-surface": "#f1f0f4", "tertiary": "#ab091b", "on-primary-container": "#ffece9", "on-secondary-fixed": "#410003", "on-surface": "#1a1b1e", "on-primary-fixed": "#410002", "primary": "#ae0011", "secondary": "#b51b1e", "primary-container": "#d71920", "surface-container-lowest": "#ffffff", "on-primary": "#ffffff", "inverse-primary": "#ffb4ab", "tertiary-container": "#cf2b30", "on-error-container": "#93000a", "surface-container-high": "#e9e7eb", "tertiary-fixed": "#ffdad7", "surface-container-low": "#f4f3f7", "secondary-fixed": "#ffdad6", "on-error": "#ffffff", "surface-bright": "#faf9fd", "on-surface-variant": "#5d3f3c", "on-background": "#1a1b1e", "surface-variant": "#e3e2e6", "surface-container-highest": "#e3e2e6" }, "borderRadius": { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" }, "spacing": { "space-md": "1rem", "margin": "1.5rem", "margin-desktop": "5rem", "space-xs": "0.25rem", "space-xl": "2.5rem", "margin-mobile": "1.25rem", "space-3xl": "6rem", "gutter-mobile": "1rem", "margin-tablet": "2.5rem", "gutter": "1.5rem", "gutter-desktop": "2rem", "space-sm": "0.5rem", "space-lg": "1.5rem", "space-2xl": "4rem" }, "fontFamily": { "label-sm": ["Inter"], "body-sm": ["Inter"], "body-md": ["Inter"], "headline-lg": ["Plus Jakarta Sans"], "headline-sm": ["Plus Jakarta Sans"], "body-lg": ["Inter"], "headline-md-mobile": ["Plus Jakarta Sans"], "display-hero": ["Plus Jakarta Sans"], "label-md": ["Inter"], "caption": ["Inter"], "display-hero-mobile": ["Plus Jakarta Sans"], "title-lg": ["Plus Jakarta Sans"], "headline-lg-mobile": ["Plus Jakarta Sans"], "headline-md": ["Plus Jakarta Sans"] }, "fontSize": { "label-sm": ["12px", { "lineHeight": "16px", "letterSpacing": "0.04em", "fontWeight": "600" }], "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }], "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }], "headline-lg": ["40px", { "lineHeight": "48px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "headline-sm": ["22px", { "lineHeight": "30px", "letterSpacing": "0", "fontWeight": "600" }], "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }], "headline-md-mobile": ["24px", { "lineHeight": "32px", "letterSpacing": "0", "fontWeight": "600" }], "display-hero": ["56px", { "lineHeight": "64px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500" }], "caption": ["12px", { "lineHeight": "16px", "fontWeight": "400" }], "display-hero-mobile": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.015em", "fontWeight": "700" }], "title-lg": ["18px", { "lineHeight": "26px", "letterSpacing": "0", "fontWeight": "600" }], "headline-lg-mobile": ["28px", { "lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700" }], "headline-md": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600" }] } } } };</script>

<div class="bg-surface font-body-md text-on-surface antialiased">

    <main class="w-full pt-20 bg-surface min-h-[calc(100vh-80px)]">
        <div class="flex flex-col w-full">

            <!-- Breadcrumb & Hero Header -->
            <section class="w-full bg-surface-container-lowest py-space-lg md:py-space-xl">
                <div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-center">
                        <div class="lg:col-span-6 flex flex-col items-start">
                            <div
                                class="inline-flex items-center gap-space-xs px-3 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm uppercase tracking-wider mb-space-md shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
                                Dokumentasi <?= e($companyName) ?>
                            </div>
                            <h1
                                class="font-display-hero text-headline-md-mobile md:text-headline-lg text-on-surface tracking-tight leading-tight mb-space-md font-bold">
                                Cerita dan Momen <span class="text-primary-container">Bersama
                                    <?= e($companyName) ?></span>
                            </h1>
                            <p
                                class="font-body-md text-body-md text-on-surface-variant mb-space-lg max-w-xl leading-relaxed">
                                Jelajahi berbagai dokumentasi kegiatan, perjalanan, dan momen yang menjadi bagian dari
                                perkembangan <?= e($companyName) ?> serta unit usaha nasional kami.
                            </p>
                            <div class="flex flex-wrap items-center gap-space-sm pt-space-xs">
                                <div
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-caption">
                                    <span class="material-symbols-outlined text-[16px] text-primary">verified</span>
                                    <span>Arsip Terverifikasi</span>
                                </div>
                                <div
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-caption">
                                    <span class="material-symbols-outlined text-[16px] text-primary">sync</span>
                                    <span>Integrasi Database Aktif</span>
                                </div>
                                <div
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-caption">
                                    <span class="material-symbols-outlined text-[16px] text-primary">storage</span>
                                    <span>Katalog Multi-Unit</span>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-6 relative">
                            <div class="relative rounded-2xl overflow-hidden shadow-xl bg-surface-container group">
                                <div class="relative w-full aspect-[16/9] overflow-hidden">
                                    <?php if ($photos !== [] && !empty($photos[0]['foto'])): ?>
                                        <img alt="<?= e($photos[0]['judul']) ?>"
                                            class="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105"
                                            src="<?= e(upload_url($photos[0]['foto'])) ?>" loading="lazy" />
                                    <?php else: ?>
                                        <div
                                            class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/30 to-surface-container">
                                            <span
                                                class="font-title-lg text-title-lg font-bold text-primary"><?= e($companyName) ?></span>
                                            <small class="font-body-sm text-body-sm text-on-surface-variant">Galeri
                                                Dokumentasi</small>
                                        </div>
                                    <?php endif; ?>
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-inverse-surface/90 via-inverse-surface/20 to-transparent">
                                    </div>
                                </div>
                                <?php if ($photos !== []): ?>
                                    <div
                                        class="absolute bottom-4 left-4 right-4 flex items-end justify-between text-on-secondary">
                                        <div class="space-y-1">
                                            <span
                                                class="px-2.5 py-0.5 rounded-md bg-primary-container text-on-primary font-label-sm text-caption uppercase tracking-wider font-semibold">
                                                <?= e($photos[0]['nama_unit'] ?? 'Umum / Perusahaan') ?>
                                            </span>
                                            <p
                                                class="font-headline-sm text-headline-sm text-on-secondary drop-shadow-sm font-bold">
                                                <?= e($photos[0]['judul']) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filter & Search Section -->
            <section class="w-full bg-surface py-space-lg">
                <div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md mb-space-lg">
                        <div>
                            <span
                                class="font-label-sm text-caption text-primary uppercase font-bold tracking-widest block mb-1">Katalog
                                Arsip</span>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
                                Dokumentasi Kami</h2>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                                Berbagai momen dan dokumentasi yang menggambarkan aktivitas <?= e($companyName) ?> dan
                                unit usaha.
                            </p>
                        </div>
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-container-low text-on-surface-variant font-label-sm text-caption shrink-0">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>Ditemukan <?= e($pagination['total']) ?> foto</span>
                        </div>
                    </div>
                    <form method="get" action="<?= e(url('galeri.php')) ?>"
                        class="bg-surface-container-lowest p-space-md md:p-space-lg rounded-2xl shadow-sm mb-space-xl">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md">
                            <div class="flex flex-col gap-2 flex-1">
                                <label for="unit"
                                    class="font-label-sm text-label-sm font-semibold text-on-surface-variant uppercase tracking-wider">Kategori
                                    Foto</label>
                                <select id="unit" name="unit"
                                    class="px-4 py-3 rounded-xl bg-surface-container-low text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container transition-all border-0 cursor-pointer">
                                    <option value="">Semua Foto</option>
                                    <option value="umum" <?= $unitFilter === 'umum' ? 'selected' : '' ?>>Umum / Perusahaan
                                    </option>
                                    <?php foreach ($units as $unit): ?>
                                        <option value="<?= e($unit['id']) ?>" <?= $unitFilter === (string) $unit['id'] ? 'selected' : '' ?>>
                                            <?= e($unit['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 px-space-xl py-3 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-semibold shadow-sm hover:shadow-md transition-all">
                                    <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                                    <span>Tampilkan</span>
                                </button>
                                <a class="inline-flex items-center justify-center gap-2 px-space-md py-3 rounded-xl bg-surface-container-low hover:bg-surface-container text-on-surface font-label-md text-label-md font-semibold transition-all"
                                    href="<?= e(url('galeri.php')) ?>">
                                    <span class="material-symbols-outlined text-[18px]">refresh</span>
                                    <span>Reset</span>
                                </a>
                            </div>
                        </div>
                    </form>

                    <?php if ($photos === []): ?>
                        <!-- Empty State -->
                        <div
                            class="py-space-2xl text-center bg-surface-container-lowest rounded-2xl shadow-sm flex flex-col items-center justify-center">
                            <div
                                class="w-14 h-14 rounded-full bg-surface-container text-on-surface-variant flex items-center justify-center mx-auto mb-3">
                                <span class="material-symbols-outlined text-[30px]">image_not_supported</span>
                            </div>
                            <h4 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Belum ada foto yang
                                sesuai.</h4>
                            <p class="font-body-md text-body-md text-on-surface-variant max-w-md mx-auto mt-1 mb-space-md">
                                Coba ubah filter kategori atau reset untuk melihat semua foto.
                            </p>
                            <a class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-container text-on-primary font-label-md text-label-md font-semibold hover:bg-primary transition-colors"
                                href="<?= e(url('galeri.php')) ?>">
                                <span class="material-symbols-outlined text-[18px]">refresh</span>
                                <span>Tampilkan Seluruh Foto</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Gallery Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter-desktop">
                            <?php foreach ($photos as $index => $photo): ?>
                                <div
                                    class="group relative bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col">
                                    <div class="relative w-full aspect-[4/3] overflow-hidden bg-surface-container">
                                        <a href="<?= e(upload_url($photo['foto'])) ?>" data-lightbox
                                            data-caption="<?= e($photo['judul']) ?>"
                                            aria-label="<?= e('Perbesar foto: ' . $photo['judul']) ?>"
                                            class="block w-full h-full">
                                            <img alt="<?= e($photo['judul']) ?>"
                                                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500 ease-out cursor-pointer"
                                                src="<?= e(upload_url($photo['foto'])) ?>" loading="lazy" />
                                        </a>
                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-inverse-surface/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-space-md pointer-events-none">
                                            <span
                                                class="inline-flex items-center gap-1 text-on-secondary font-label-sm text-caption">
                                                <span class="material-symbols-outlined text-[18px]">zoom_in</span>
                                                Klik untuk memperbesar
                                            </span>
                                        </div>
                                        <div class="absolute top-3 right-3">
                                            <span
                                                class="px-2.5 py-1 rounded-full bg-surface-container-lowest/90 backdrop-blur-md text-primary font-label-sm text-caption font-bold shadow-sm">
                                                Urutan #<?= e(sprintf('%02d', $index + 1)) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-space-lg flex flex-col flex-1 justify-between bg-surface-container-lowest">
                                        <div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <span
                                                    class="px-2.5 py-0.5 rounded-full bg-primary-fixed text-primary font-label-sm text-caption uppercase tracking-wider font-semibold">
                                                    <?= e($photo['nama_unit'] ?? 'Umum / Perusahaan') ?>
                                                </span>
                                                <span
                                                    class="text-caption font-caption text-on-surface-variant flex items-center gap-1">
                                                    <span
                                                        class="material-symbols-outlined text-[14px] text-emerald-600">check_circle</span>
                                                    Dokumentasi Resmi
                                                </span>
                                            </div>
                                            <h3
                                                class="font-headline-sm text-headline-sm text-on-surface font-bold group-hover:text-primary transition-colors">
                                                <?= e($photo['judul']) ?>
                                            </h3>
                                        </div>
                                        <div class="mt-space-md pt-space-sm flex items-center justify-between">
                                            <span class="font-caption text-caption text-on-surface-variant">ID:
                                                <?= e(sprintf('%03d', $index + 1)) ?>/MG</span>
                                            <a href="<?= e(upload_url($photo['foto'])) ?>" data-lightbox
                                                data-caption="<?= e($photo['judul']) ?>"
                                                aria-label="<?= e('Perbesar foto: ' . $photo['judul']) ?>"
                                                class="inline-flex items-center gap-1 text-primary hover:text-primary-container font-label-sm text-label-sm font-semibold transition-colors cursor-pointer">
                                                <span>Detail Foto</span>
                                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php
                        render_pagination(
                            $pagination,
                            'galeri.php',
                            ['unit' => $unitFilter]
                        );
                        ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Section: Dokumentasi Setiap Unit Usaha -->
            <?php if ($units !== []): ?>
                <section class="w-full bg-surface-container-low py-space-xl">
                    <div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
                        <div class="max-w-2xl mb-space-lg">
                            <span
                                class="font-label-sm text-caption text-primary uppercase font-bold tracking-widest block mb-1">Struktur
                                Portofolio</span>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
                                Dokumentasi Setiap Unit Usaha</h2>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-2 leading-relaxed">
                                Kenali berbagai aktivitas dan dokumentasi melalui unit usaha yang menjadi bagian dari
                                <?= e($companyName) ?>.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter-desktop">
                            <?php foreach ($units as $unitIndex => $unit): ?>
                                <?php
                                $unitSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $unit['nama']), '-'));
                                $unitInitial = mb_substr($unit['nama'], 0, 2, 'UTF-8');
                                ?>
                                <div
                                    class="bg-surface-container-lowest rounded-2xl p-space-lg shadow-sm hover:shadow-lg transition-all flex flex-col justify-between group">
                                    <div>
                                        <div class="flex items-start justify-between mb-space-md">
                                            <div
                                                class="w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center font-bold text-headline-sm shadow-md">
                                                <?= e(strtoupper($unitInitial)) ?>
                                            </div>
                                            <span
                                                class="px-3 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-caption font-bold">
                                                Unit Aktif
                                            </span>
                                        </div>
                                        <h3
                                            class="font-headline-sm text-headline-sm text-on-surface font-bold group-hover:text-primary transition-colors">
                                            <?= e($unit['nama']) ?>
                                        </h3>
                                    </div>
                                    <div class="mt-space-lg pt-space-md flex items-center justify-between">
                                        <span
                                            class="font-label-sm text-caption text-on-surface-variant font-medium">Terverifikasi
                                            di CMS</span>
                                        <a class="inline-flex items-center gap-1 font-label-sm text-label-sm text-primary font-bold hover:underline"
                                            href="<?= e(url('galeri.php?unit=' . $unit['id'])) ?>">
                                            <span>Lihat Foto Unit</span>
                                            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <!-- Umum / Perusahaan card -->
                            <div
                                class="bg-surface-container-lowest rounded-2xl p-space-lg shadow-sm hover:shadow-lg transition-all flex flex-col justify-between group">
                                <div>
                                    <div class="flex items-start justify-between mb-space-md">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-surface-container text-on-surface font-bold text-headline-sm flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[24px]">corporate_fare</span>
                                        </div>
                                        <span
                                            class="px-3 py-1 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-caption">
                                            Holding Pusat
                                        </span>
                                    </div>
                                    <h3
                                        class="font-headline-sm text-headline-sm text-on-surface font-bold group-hover:text-primary transition-colors">
                                        Umum / Perusahaan
                                    </h3>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-2">
                                        Dokumentasi kegiatan umum dan korporat <?= e($companyName) ?>.
                                    </p>
                                </div>
                                <div class="mt-space-lg pt-space-md flex items-center justify-between">
                                    <span class="font-label-sm text-caption text-on-surface-variant font-medium">Kategori
                                        Umum</span>
                                    <a class="inline-flex items-center gap-1 font-label-sm text-label-sm text-primary font-bold hover:underline"
                                        href="<?= e(url('galeri.php?unit=umum')) ?>">
                                        <span>Lihat Foto Umum</span>
                                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Lightbox Dialog (Code 1 - tetap dipertahankan) -->
            <dialog class="lightbox" id="gallery-lightbox" aria-label="Pratinjau foto">
                <div class="lightbox-content">
                    <button class="lightbox-close" type="button" aria-label="Tutup pratinjau" data-lightbox-close
                        autofocus>
                        Tutup ✕
                    </button>
                    <img class="lightbox-image" alt="">
                    <p class="lightbox-caption" aria-live="polite"></p>
                    <div class="lightbox-controls">
                        <button class="button button-outline" type="button" data-lightbox-prev>
                            ← Sebelumnya
                        </button>
                        <button class="button button-outline" type="button" data-lightbox-next>
                            Berikutnya →
                        </button>
                    </div>
                </div>
            </dialog>

            <!-- CTA Section -->
            <section class="w-full bg-[#A30F18] text-on-primary py-space-xl relative overflow-hidden">
                <div class="absolute -right-24 -bottom-24 w-96 h-96 rounded-full bg-white/5 pointer-events-none"></div>
                <div class="absolute -left-12 -top-12 w-64 h-64 rounded-full bg-white/5 pointer-events-none"></div>
                <div
                    class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop relative z-10">
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-space-xl">
                        <div class="max-w-2xl text-center lg:text-left">
                            <span
                                class="inline-block px-3 py-1 rounded-full bg-white/10 text-white font-label-sm text-caption uppercase tracking-wider mb-space-sm font-semibold">
                                Kolaborasi &amp; Pertumbuhan
                            </span>
                            <h2
                                class="font-display-hero text-headline-md-mobile md:text-headline-lg text-white font-bold tracking-tight leading-tight">
                                Ingin Mengenal <?= e($companyName) ?> Lebih Dekat?
                            </h2>
                            <p class="font-body-md text-body-md text-white/90 mt-space-sm max-w-xl leading-relaxed">
                                Temukan informasi lebih lanjut mengenai perusahaan, unit usaha, produk, dan berbagai
                                kegiatan <?= e($companyName) ?> yang mendorong kemajuan bersama.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center gap-space-md shrink-0">
                            <a class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-surface-container-lowest text-[#A30F18] font-label-md text-label-md font-bold hover:bg-surface-container-low transition-all shadow-lg text-center"
                                href="<?= e(url('kontak.php')) ?>">
                                Hubungi Kami
                            </a>
                            <a class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-transparent text-white font-label-md text-label-md font-semibold hover:bg-white/10 transition-all text-center flex items-center justify-center gap-1.5"
                                href="<?= e(url('unit-usaha.php')) ?>">
                                <span>Jelajahi Unit Usaha</span>
                                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>