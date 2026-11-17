<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$activePage = 'unit-usaha';
$pageTitle = 'Unit Usaha | Moza Group';
$pageDescription = 'Kenali unit usaha dan layanan Moza Group.';
$canonicalUrl = url('unit-usaha.php');

$total = (int) $pdo->query(
    'SELECT COUNT(*) FROM unit_usaha WHERE aktif = 1'
)->fetchColumn();

$pagination = pagination($total, 9);

$statement = $pdo->prepare(
    'SELECT nama, slug, ringkasan, logo, foto
     FROM unit_usaha
     WHERE aktif = 1
     ORDER BY urutan ASC, id DESC
     LIMIT ? OFFSET ?'
);

$statement->bindValue(1, $pagination['per_page'], PDO::PARAM_INT);
$statement->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
$statement->execute();

$units = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<!-- Tailwind CDN + config + fonts (dipasang di body agar kelas Code 2 aktif) -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@500;600;700;800&amp;display=swap"
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
    id="tailwind-config">tailwind.config = { "darkMode": "class", "theme": { "extend": { "colors": { "on-primary-fixed": "#410002", "outline": "#926f6b", "tertiary": "#ab091b", "primary-fixed-dim": "#ffb4ab", "error-container": "#ffdad6", "surface-dim": "#dbd9dd", "secondary": "#b51b1e", "tertiary-fixed-dim": "#ffb3ad", "surface-container-high": "#e9e7eb", "on-secondary": "#ffffff", "background": "#faf9fd", "on-secondary-fixed": "#410003", "primary-container": "#d71920", "surface-bright": "#faf9fd", "surface": "#faf9fd", "surface-container-lowest": "#ffffff", "on-secondary-fixed-variant": "#93000e", "on-secondary-container": "#fffbff", "secondary-fixed": "#ffdad6", "on-primary-fixed-variant": "#93000d", "on-primary": "#ffffff", "error": "#ba1a1a", "primary": "#ae0011", "on-tertiary-container": "#ffebe9", "on-error-container": "#93000a", "secondary-fixed-dim": "#ffb4ac", "outline-variant": "#e6bdb8", "inverse-on-surface": "#f1f0f4", "on-tertiary-fixed": "#410004", "surface-container-low": "#f4f3f7", "on-surface-variant": "#5d3f3c", "primary-fixed": "#ffdad6", "tertiary-fixed": "#ffdad7", "surface-tint": "#c00014", "on-tertiary-fixed-variant": "#930013", "surface-container": "#efedf1", "on-surface": "#1a1b1e", "inverse-surface": "#2f3033", "secondary-container": "#d83734", "inverse-primary": "#ffb4ab", "on-tertiary": "#ffffff", "on-error": "#ffffff", "surface-variant": "#e3e2e6", "on-primary-container": "#ffece9", "surface-container-highest": "#e3e2e6", "tertiary-container": "#cf2b30", "on-background": "#1a1b1e" }, "borderRadius": { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" }, "spacing": { "margin-tablet": "2.5rem", "gutter-mobile": "1rem", "space-lg": "1.5rem", "margin": "1.5rem", "space-xl": "2.5rem", "gutter-desktop": "2rem", "space-md": "1rem", "gutter": "1.5rem", "space-xs": "0.25rem", "space-sm": "0.5rem", "margin-mobile": "1.25rem", "space-3xl": "6rem", "space-2xl": "4rem", "margin-desktop": "5rem" }, "fontFamily": { "headline-lg-mobile": ["Plus Jakarta Sans"], "headline-sm": ["Plus Jakarta Sans"], "headline-md-mobile": ["Plus Jakarta Sans"], "headline-md": ["Plus Jakarta Sans"], "display-hero-mobile": ["Plus Jakarta Sans"], "display-hero": ["Plus Jakarta Sans"], "body-sm": ["Inter"], "label-md": ["Inter"], "caption": ["Inter"], "label-sm": ["Inter"], "body-lg": ["Inter"], "headline-lg": ["Plus Jakarta Sans"], "title-lg": ["Plus Jakarta Sans"], "body-md": ["Inter"] }, "fontSize": { "headline-lg-mobile": ["28px", { "lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700" }], "headline-sm": ["22px", { "lineHeight": "30px", "letterSpacing": "0", "fontWeight": "600" }], "headline-md-mobile": ["24px", { "lineHeight": "32px", "letterSpacing": "0", "fontWeight": "600" }], "headline-md": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600" }], "display-hero-mobile": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.015em", "fontWeight": "700" }], "display-hero": ["56px", { "lineHeight": "64px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }], "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500" }], "caption": ["12px", { "lineHeight": "16px", "fontWeight": "400" }], "label-sm": ["12px", { "lineHeight": "16px", "letterSpacing": "0.04em", "fontWeight": "600" }], "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }], "headline-lg": ["40px", { "lineHeight": "48px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "title-lg": ["18px", { "lineHeight": "26px", "letterSpacing": "0", "fontWeight": "600" }], "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }] } } } };</script>

<div class="bg-surface font-body-md text-on-surface antialiased selection:bg-primary selection:text-on-primary">

    <main class="w-full pt-20 bg-surface">
        <div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop pb-space-xl">
            <div class="flex flex-col w-full">

                <!-- Breadcrumb -->
         
                <!-- Hero Header Section -->
                <section
                    class="relative w-full rounded-2xl bg-surface-container-lowest p-space-lg md:p-space-2xl shadow-sm overflow-hidden mb-space-2xl">
                    <div
                        class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-primary/5 blur-3xl pointer-events-none">
                    </div>
                    <div class="absolute bottom-0 right-1/4 w-64 h-32 bg-secondary/5 blur-2xl pointer-events-none">
                    </div>
                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end justify-between gap-space-xl">
                        <div class="max-w-3xl flex flex-col gap-space-md">
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed w-fit">
                                <span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
                                <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold">OUR
                                    BUSINESS</span>
                            </div>
                            <h1
                                class="font-display-hero text-headline-lg-mobile md:text-headline-lg text-on-surface font-bold tracking-tight">
                                Beragam Bisnis, <span class="text-primary-container">Satu Komitmen untuk
                                    Bertumbuh</span>
                            </h1>
                            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl leading-relaxed">
                                <?= e($companyName) ?> menaungi berbagai unit usaha yang bergerak dalam bidang bisnis
                                dan layanan berbeda, dengan komitmen untuk terus berkembang dan memberikan nilai tambah
                                bagi mitra dan masyarakat.
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-sm pt-space-md">
                                <div
                                    class="flex items-center gap-3 p-space-sm rounded-xl bg-surface-container-low shadow-sm">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-primary-container/10 flex items-center justify-center shrink-0">
                                        <span
                                            class="material-symbols-outlined text-primary-container text-[22px]">domain_verification</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-headline-sm text-title-lg text-on-surface font-bold leading-tight"><?= e($total) ?>
                                            Entitas</span>
                                        <span class="font-caption text-caption text-outline">Terverifikasi Resmi</span>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center gap-3 p-space-sm rounded-xl bg-surface-container-low shadow-sm">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-tertiary text-[22px]"
                                            style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-headline-sm text-title-lg text-on-surface font-bold leading-tight">100%
                                            Aktif</span>
                                        <span class="font-caption text-caption text-outline">Status Operasional</span>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center gap-3 p-space-sm rounded-xl bg-surface-container-low shadow-sm">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-primary-container/10 flex items-center justify-center shrink-0">
                                        <span
                                            class="material-symbols-outlined text-primary-container text-[22px]">mark_chat_unread</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-headline-sm text-title-lg text-on-surface font-bold leading-tight">Hotline
                                            Connect</span>
                                        <span class="font-caption text-caption text-outline">Direct WhatsApp</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="lg:w-72 shrink-0 flex flex-col justify-between p-space-lg rounded-2xl bg-surface-container-low shadow-sm relative overflow-hidden">
                            <div class="flex items-center justify-between pb-space-md">
                                <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Holding
                                    Entity</span>
                                <span
                                    class="material-symbols-outlined text-primary-container text-[20px]">corporate_fare</span>
                            </div>
                            <div class="space-y-1">
                                <p class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight">
                                    <?= e(strtoupper($companyName)) ?></p>
                                <p class="font-caption text-caption text-outline-variant font-medium">Enterprise
                                    Ecosystem Standard <?= e(date('Y')) ?></p>
                            </div>
                            <div class="mt-space-md pt-space-sm flex items-center gap-2">
                                <span class="inline-block w-2 h-2 rounded-full bg-primary-container"></span>
                                <span class="font-caption text-caption text-on-surface-variant">Katalog Portofolio
                                    Tervalidasi</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Filter & Search Console -->
                <section
                    class="w-full bg-surface-container-lowest rounded-2xl p-space-md md:p-space-lg shadow-sm mb-space-2xl">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md">
                        <div class="flex flex-col">
                            <h2 class="font-title-lg text-title-lg text-on-surface font-bold tracking-tight">Temukan
                                Bisnis Kami</h2>
                            <span class="font-caption text-caption text-outline">Pencarian cepat portofolio resmi unit
                                usaha <?= e($companyName) ?></span>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                            <div class="relative flex-1 sm:w-80">
                                <span
                                    class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                                <input autocomplete="off"
                                    class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-surface text-on-surface font-body-sm text-body-sm focus:outline-none focus:ring-2 focus:ring-primary-container shadow-inner transition-all placeholder:text-outline"
                                    id="search-input" placeholder="Cari nama unit usaha..." type="text" />
                            </div>
                            <button
                                class="inline-flex items-center justify-center gap-2 px-space-md py-2.5 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-semibold shadow-sm transition-all focus:outline-none"
                                id="btn-search-trigger" type="button">
                                <span class="material-symbols-outlined text-[18px]">manage_search</span>
                                <span>Cari</span>
                            </button>
                            <button
                                class="inline-flex items-center justify-center gap-1.5 px-space-md py-2.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md transition-colors focus:outline-none"
                                id="btn-reset-filter" type="button">
                                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                                <span>Reset</span>
                            </button>
                        </div>
                    </div>
                    <div
                        class="mt-space-md pt-space-sm flex items-center justify-between text-body-sm font-body-sm text-outline">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-primary-container"></span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant font-semibold"
                                id="counter-text">Menampilkan <?= e(count($units)) ?> Unit Usaha Aktif</span>
                        </div>
                        <span class="font-caption text-caption text-outline">Sinkronisasi Database Terkini</span>
                    </div>
                </section>

                <!-- Showcase Unit Usaha Section -->
                <section class="w-full flex flex-col gap-space-lg mb-space-2xl">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-sm">
                        <div>
                            <span
                                class="font-label-sm text-label-sm text-primary uppercase font-bold tracking-wider">Portofolio
                                Terdaftar</span>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
                                Kenali Unit Usaha Kami</h2>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Jelajahi berbagai bisnis
                                yang menjadi bagian dari ekosistem <?= e($companyName) ?>.</p>
                        </div>
                    </div>

                    <?php if ($units === []): ?>
                        <!-- Empty State -->
                        <div
                            class="w-full bg-surface-container-lowest rounded-2xl p-space-2xl text-center shadow-sm flex flex-col items-center justify-center">
                            <div
                                class="w-16 h-16 rounded-2xl bg-surface-container-high text-outline flex items-center justify-center mb-space-md">
                                <span class="material-symbols-outlined text-[32px]">domain</span>
                            </div>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight">Informasi
                                unit usaha belum tersedia.</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant max-w-md mt-2">
                                Mohon periksa kembali nanti untuk pembaruan portofolio unit usaha <?= e($companyName) ?>.
                            </p>
                        </div>
                    <?php else: ?>
                        <!-- Corporate Showcase Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter-desktop" id="units-container">
                            <?php foreach ($units as $index => $unit): ?>
                                <?php
                                $detailUrl = url('detail-unit.php?slug=' . rawurlencode($unit['slug']));
                                $unitInitial = mb_substr($unit['nama'], 0, 2, 'UTF-8');
                                $unitLabel = mb_substr($unit['nama'], 0, 4, 'UTF-8');
                                ?>
                                <article
                                    class="group relative flex flex-col justify-between bg-surface-container-lowest rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:-translate-y-1"
                                    data-name="<?= e(strtolower($unit['nama'])) ?>" data-unit-card="true">
                                    <div class="w-full h-1.5 bg-gradient-to-r from-primary via-primary-container to-secondary">
                                    </div>
                                    <?php if (!empty($unit['foto'])): ?>
                                        <div class="w-full h-40 overflow-hidden bg-surface-container">
                                            <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                                src="<?= e(upload_url($unit['foto'])) ?>" alt="<?= e($unit['nama']) ?>"
                                                loading="lazy" />
                                        </div>
                                    <?php endif; ?>
                                    <div class="p-space-lg flex flex-col flex-1">
                                        <div class="flex items-center justify-between mb-space-lg">
                                            <span
                                                class="font-label-sm text-label-sm text-outline bg-surface-container-low px-2.5 py-1 rounded-full font-bold">#<?= e(sprintf('%02d', $index + 1)) ?></span>
                                            <div
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-high text-on-surface">
                                                <span class="w-2 h-2 rounded-full bg-primary-container"></span>
                                                <span class="font-label-sm text-label-sm font-semibold tracking-wide">Aktif
                                                    Operasional</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 mb-space-md">
                                            <?php if (!empty($unit['logo'])): ?>
                                                <div
                                                    class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-fixed via-surface-container-low to-surface-container-lowest flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform duration-300 shrink-0">
                                                    <img class="w-12 h-12 object-contain" src="<?= e(upload_url($unit['logo'])) ?>"
                                                        alt="Logo <?= e($unit['nama']) ?>" loading="lazy" />
                                                </div>
                                            <?php else: ?>
                                                <div
                                                    class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-fixed via-surface-container-low to-surface-container-lowest flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform duration-300 shrink-0">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <span
                                                            class="font-headline-sm text-title-lg text-primary-container font-extrabold tracking-tighter leading-none"><?= e(strtoupper($unitInitial)) ?></span>
                                                        <span
                                                            class="font-caption text-[10px] text-on-surface font-black uppercase tracking-widest leading-none mt-0.5"><?= e(strtoupper($unitLabel)) ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex flex-col min-w-0">
                                                <span
                                                    class="font-caption text-caption uppercase text-primary font-bold tracking-wider">Unit
                                                    Usaha</span>
                                                <h3
                                                    class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight group-hover:text-primary-container transition-colors">
                                                    <a href="<?= e($detailUrl) ?>"><?= e($unit['nama']) ?></a>
                                                </h3>
                                            </div>
                                        </div>
                                        <?php if (!empty($unit['ringkasan'])): ?>
                                            <p
                                                class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed mb-space-lg line-clamp-3">
                                                <?= e($unit['ringkasan']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="mt-auto flex flex-col gap-2 pt-2">
                                            <a class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-semibold shadow-sm transition-all"
                                                href="<?= e($detailUrl) ?>">
                                                <span>Lihat Detail</span>
                                                <span class="material-symbols-outlined text-[18px]">north_east</span>
                                            </a>
                                            <a class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-surface hover:bg-surface-container text-on-surface font-label-md text-label-md font-semibold transition-all"
                                                href="<?= e(url('kontak.php')) ?>">
                                                <span class="material-symbols-outlined text-[18px]">support_agent</span>
                                                <span>Hubungi Kami</span>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <!-- Empty State (hidden by default, shown when search misses) -->
                        <div class="hidden w-full bg-surface-container-lowest rounded-2xl p-space-2xl text-center shadow-sm flex-col items-center justify-center"
                            id="search-empty-state">
                            <div
                                class="w-16 h-16 rounded-2xl bg-error-container text-error flex items-center justify-center mb-space-md">
                                <span class="material-symbols-outlined text-[32px]">manage_search</span>
                            </div>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight">Unit
                                usaha yang Anda cari belum ditemukan</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant max-w-md mt-2 mb-space-lg">
                                Mohon periksa kembali kata kunci pencarian Anda. Pastikan ejaan tepat atau gunakan tombol
                                reset untuk melihat kembali unit usaha aktif.
                            </p>
                            <button
                                class="inline-flex items-center gap-2 px-space-lg py-3 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-semibold transition-all shadow-md"
                                onclick="resetSearchQuery()" type="button">
                                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                                <span>Reset Pencarian</span>
                            </button>
                        </div>

                        <!-- Pagination -->
                        <?php
                        render_pagination(
                            $pagination,
                            'unit-usaha.php'
                        );
                        ?>
                    <?php endif; ?>
                </section>

                <!-- Section Brand Statement -->
                <section
                    class="relative w-full rounded-2xl bg-tertiary text-on-tertiary p-space-lg md:p-space-2xl overflow-hidden mb-space-2xl shadow-xl">
                    <div class="absolute inset-0 opacity-10 pointer-events-none">
                        <svg class="w-full h-full" fill="none" viewBox="0 0 1000 400"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 400L400 0H600L1000 400H0Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <div class="relative z-10 max-w-3xl flex flex-col gap-space-md">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-on-tertiary-fixed text-primary-fixed w-fit">
                            <span class="material-symbols-outlined text-[16px]">hub</span>
                            <span class="font-label-sm text-label-sm font-semibold tracking-wider uppercase">SOVEREIGN
                                SYNERGY</span>
                        </div>
                        <h2
                            class="font-headline-lg text-headline-lg-mobile md:text-headline-md font-bold tracking-tight leading-tight text-on-tertiary">
                            Satu Ekosistem, Beragam Peluang
                        </h2>
                        <p class="font-body-md text-body-md text-primary-fixed leading-relaxed">
                            Melalui berbagai unit usaha, <?= e($companyName) ?> terus mengembangkan potensi bisnis dan
                            membangun hubungan yang berkelanjutan demi menciptakan akselerasi ekonomi mandiri yang
                            tangguh.
                        </p>
                        <div
                            class="flex flex-wrap items-center gap-space-lg pt-space-md text-on-tertiary font-label-md text-label-md">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary-fixed">verified_user</span>
                                <span>Transparansi Korporasi</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary-fixed">trending_up</span>
                                <span>Skalabilitas Operasional</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary-fixed">handshake</span>
                                <span>Kemitraan Jangka Panjang</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- CTA Section -->
                <section
                    class="w-full rounded-2xl bg-surface-container-lowest p-space-lg md:p-space-2xl shadow-md mb-space-xl">
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-space-xl">
                        <div class="max-w-2xl flex flex-col gap-space-sm text-center lg:text-left">
                            <div
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm font-semibold w-fit mx-auto lg:mx-0">
                                <span class="material-symbols-outlined text-[16px]">support_agent</span>
                                <span>Konsultasi &amp; Kemitraan</span>
                            </div>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
                                Tertarik Mengenal Lebih Dekat Unit Usaha Kami?
                            </h2>
                            <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                                Temukan peluang kerja sama dan dapatkan informasi lebih lanjut mengenai bisnis yang
                                berada di bawah <?= e($companyName) ?>.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center gap-space-sm w-full lg:w-auto shrink-0">
                            <a class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-space-xl py-3.5 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-bold shadow-[0_4px_16px_rgba(215,25,32,0.28)] transition-all transform hover:-translate-y-0.5 focus:outline-none"
                                href="<?= e(url('kontak.php')) ?>">
                                <span class="material-symbols-outlined text-[20px]">mail</span>
                                <span>Hubungi Kami</span>
                            </a>
                            <a class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-space-lg py-3.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-semibold transition-all focus:outline-none"
                                href="<?= e(url('beranda.php')) ?>">
                                <span class="material-symbols-outlined text-[20px]">home</span>
                                <span>Kembali ke Beranda</span>
                            </a>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </main>

    <!-- Search & Filter JavaScript -->
    <script>
        (function () {
            const searchInput = document.getElementById('search-input');
            const searchTrigger = document.getElementById('btn-search-trigger');
            const resetBtn = document.getElementById('btn-reset-filter');
            const counterText = document.getElementById('counter-text');
            const emptyState = document.getElementById('search-empty-state');
            const cards = document.querySelectorAll('[data-unit-card="true"]');

            function filterUnits() {
                const query = (searchInput.value || '').trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(card => {
                    const nameAttr = card.getAttribute('data-name') || '';
                    if (nameAttr.toLowerCase().includes(query)) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    if (emptyState) {
                        emptyState.classList.remove('hidden');
                        emptyState.classList.add('flex');
                    }
                    if (counterText) counterText.textContent = 'Menampilkan 0 Unit Usaha';
                } else {
                    if (emptyState) {
                        emptyState.classList.add('hidden');
                        emptyState.classList.remove('flex');
                    }
                    if (counterText) counterText.textContent = `Menampilkan ${visibleCount} Unit Usaha Aktif`;
                }
            }

            window.resetSearchQuery = function () {
                if (searchInput) {
                    searchInput.value = '';
                    filterUnits();
                    searchInput.focus();
                }
            };

            if (searchInput) {
                searchInput.addEventListener('input', filterUnits);
                searchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        filterUnits();
                    }
                });
            }

            if (searchTrigger) {
                searchTrigger.addEventListener('click', filterUnits);
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', window.resetSearchQuery);
            }
        })();
    </script>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>