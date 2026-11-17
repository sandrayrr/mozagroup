<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$activePage = 'tentang';
$pageTitle = 'Tentang Kami | Moza Group';
$pageDescription = 'Kenali profil, sejarah, visi, dan misi Moza Group.';
$canonicalUrl = url('tentang.php');

require __DIR__ . '/includes/header.php';

// Setiap baris misi menjadi satu poin.
$missionLines = preg_split(
    '/\R/u',
    (string) ($company['misi'] ?? '')
) ?: [];

$missions = [];

foreach ($missionLines as $line) {
    $line = trim($line);

    if ($line !== '') {
        $missions[] = $line;
    }
}

// Ikon yang dipakai berputar untuk kartu misi.
$missionIcons = ['public', 'eco', 'handshake', 'gavel', 'diversity_3', 'visibility'];
?>

<!-- Tailwind CDN + config + fonts (dipasang di body agar kelas Code 2 aktif) -->
<link
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    rel="stylesheet" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@600;700;800&amp;display=swap"
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
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script
    id="tailwind-config">tailwind.config = { darkMode: "class", theme: { extend: { colors: { "on-error": "#ffffff", "tertiary-container": "#cf2b30", "on-primary": "#ffffff", "surface": "#faf9fd", "surface-bright": "#faf9fd", "on-primary-container": "#ffece9", "on-error-container": "#93000a", "inverse-primary": "#ffb4ab", "surface-container": "#efedf1", "on-secondary": "#ffffff", "secondary-fixed-dim": "#ffb4ac", "tertiary-fixed-dim": "#ffb3ad", "on-secondary-fixed": "#410003", "error": "#ba1a1a", "primary-fixed": "#ffdad6", "secondary-container": "#d83734", "primary-fixed-dim": "#ffb4ab", "primary": "#ae0011", "surface-container-lowest": "#ffffff", "on-secondary-fixed-variant": "#93000e", "surface-container-low": "#f4f3f7", "on-primary-fixed": "#410002", "on-surface-variant": "#5d3f3c", "on-surface": "#1a1b1e", "on-tertiary-fixed-variant": "#930013", "outline": "#926f6b", "on-secondary-container": "#fffbff", "surface-tint": "#c00014", "primary-container": "#d71920", "surface-container-high": "#e9e7eb", "outline-variant": "#e6bdb8", "inverse-on-surface": "#f1f0f4", "on-primary-fixed-variant": "#93000d", "on-tertiary": "#ffffff", "background": "#faf9fd", "inverse-surface": "#2f3033", "on-tertiary-fixed": "#410004", "on-background": "#1a1b1e", "surface-dim": "#dbd9dd", "tertiary-fixed": "#ffdad7", "surface-variant": "#e3e2e6", "on-tertiary-container": "#ffebe9", "secondary-fixed": "#ffdad6", "secondary": "#b51b1e", "tertiary": "#ab091b", "error-container": "#ffdad6", "surface-container-highest": "#e3e2e6" }, borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" }, spacing: { "margin-mobile": "1.25rem", "margin": "1.5rem", "space-3xl": "6rem", "space-xl": "2.5rem", "space-sm": "0.5rem", "margin-tablet": "2.5rem", "margin-desktop": "5rem", "space-lg": "1.5rem", "gutter-mobile": "1rem", "gutter-desktop": "2rem", "space-2xl": "4rem", "space-md": "1rem", "gutter": "1.5rem", "space-xs": "0.25rem" }, fontFamily: { "label-md": ["Inter"], "headline-lg-mobile": ["Plus Jakarta Sans"], "body-md": ["Inter"], "headline-sm": ["Plus Jakarta Sans"], "headline-md-mobile": ["Plus Jakarta Sans"], "display-hero-mobile": ["Plus Jakarta Sans"], "title-lg": ["Plus Jakarta Sans"], "body-lg": ["Inter"], "headline-md": ["Plus Jakarta Sans"], "body-sm": ["Inter"], "display-hero": ["Plus Jakarta Sans"], "headline-lg": ["Plus Jakarta Sans"], "label-sm": ["Inter"], "caption": ["Inter"] }, fontSize: { "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.01em", fontWeight: "500" }], "headline-lg-mobile": ["28px", { lineHeight: "36px", letterSpacing: "-0.01em", fontWeight: "700" }], "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }], "headline-sm": ["22px", { lineHeight: "30px", letterSpacing: "0", fontWeight: "600" }], "headline-md-mobile": ["24px", { lineHeight: "32px", letterSpacing: "0", fontWeight: "600" }], "display-hero-mobile": ["36px", { lineHeight: "44px", letterSpacing: "-0.015em", fontWeight: "700" }], "title-lg": ["18px", { lineHeight: "26px", letterSpacing: "0", fontWeight: "600" }], "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }], "headline-md": ["32px", { lineHeight: "40px", letterSpacing: "-0.01em", fontWeight: "600" }], "body-sm": ["14px", { lineHeight: "20px", fontWeight: "400" }], "display-hero": ["56px", { lineHeight: "64px", letterSpacing: "-0.02em", fontWeight: "700" }], "headline-lg": ["40px", { lineHeight: "48px", letterSpacing: "-0.02em", fontWeight: "700" }], "label-sm": ["12px", { lineHeight: "16px", letterSpacing: "0.04em", fontWeight: "600" }], "caption": ["12px", { lineHeight: "16px", fontWeight: "400" }] } } } };</script>

<div class="bg-surface font-body-md text-on-surface antialiased">

    <main class="w-full pt-20 bg-surface flex-1">
        <div class="flex flex-col w-full">

            <!-- 1. HERO SECTION: PROFIL -->
            <section class="relative w-full bg-surface-container-lowest overflow-hidden py-10 lg:py-16">
                <div
                    class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-primary-fixed/30 blur-3xl pointer-events-none">
                </div>
                <div
                    class="absolute left-1/3 -bottom-20 w-80 h-80 rounded-full bg-surface-container/60 blur-2xl pointer-events-none">
                </div>
                <div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="lg:col-span-7 flex flex-col items-start gap-6">
                            <div
                                class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-primary-fixed/50 text-primary">
                                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                                <span class="font-label-sm text-label-sm font-semibold tracking-wider uppercase">Tentang
                                    <?= e($companyName) ?></span>
                            </div>
                            <h1
                                class="font-headline-lg text-headline-lg-mobile lg:text-headline-lg text-on-surface tracking-tight">
                                Mengenal Lebih Dekat <br />
                                <span class="text-primary"><?= e($companyName) ?></span>
                            </h1>
                            <?php if (!empty($company['ringkasan'])): ?>
                                <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
                                    <?= e($company['ringkasan']) ?>
                                </p>
                            <?php endif; ?>
                            <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                                <div
                                    class="p-5 rounded-xl bg-surface-container-low transition-all duration-300 hover:shadow-md">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-primary-fixed/60 flex items-center justify-center text-primary mb-3">
                                        <span class="material-symbols-outlined text-[24px]">corporate_fare</span>
                                    </div>
                                    <span
                                        class="font-caption text-caption text-on-surface-variant uppercase tracking-wider block">Identitas
                                        Korporasi</span>
                                    <p class="font-title-lg text-title-lg font-bold text-on-surface mt-0.5">Profil Resmi
                                        Perusahaan</p>
                                </div>
                                <div
                                    class="p-5 rounded-xl bg-surface-container-low transition-all duration-300 hover:shadow-md">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-primary-fixed/60 flex items-center justify-center text-primary mb-3">
                                        <span class="material-symbols-outlined text-[24px]">verified_user</span>
                                    </div>
                                    <span
                                        class="font-caption text-caption text-on-surface-variant uppercase tracking-wider block">Komitmen</span>
                                    <p class="font-title-lg text-title-lg font-bold text-on-surface mt-0.5">Tata Kelola
                                        &amp; Integritas</p>
                                </div>
                            </div>
                            <div class="pt-4 flex flex-wrap items-center gap-4">
                                <a class="inline-flex items-center gap-3 px-8 py-3.5 rounded-xl bg-primary hover:bg-secondary text-on-primary font-label-md text-label-md shadow-md hover:shadow-lg transition-all duration-200"
                                    href="#sejarah">
                                    <span>Jelajahi Perjalanan Kami</span>
                                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                                </a>
                                <a class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md transition-all"
                                    href="#visi-misi">
                                    <span>Visi &amp; Misi</span>
                                    <span class="material-symbols-outlined text-[18px]">south</span>
                                </a>
                            </div>
                        </div>
                        <div class="lg:col-span-5 relative">
                            <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-surface-container">
                                <?php if (!empty($company['foto'])): ?>
                                    <img class="w-full h-64 sm:h-80 lg:h-[400px] object-cover"
                                        src="<?= e(upload_url($company['foto'])) ?>" alt="Profil <?= e($companyName) ?>"
                                        loading="lazy" />
                                <?php else: ?>
                                    <div
                                        class="w-full h-64 sm:h-80 lg:h-[400px] flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/30 to-surface-container">
                                        <span
                                            class="font-title-lg text-title-lg font-bold text-primary"><?= e($companyName) ?></span>
                                        <small class="font-body-sm text-body-sm text-on-surface-variant">Profil
                                            Perusahaan</small>
                                    </div>
                                <?php endif; ?>
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent">
                                </div>
                                <div
                                    class="absolute bottom-5 left-5 right-5 p-4 rounded-xl bg-surface-container-lowest/95 backdrop-blur-md shadow-lg flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-on-primary">
                                            <span class="material-symbols-outlined text-[20px]">domain</span>
                                        </div>
                                        <div>
                                            <p class="font-label-md text-label-md font-bold text-on-surface">
                                                <?= e($companyName) ?></p>
                                            <p class="font-caption text-caption text-on-surface-variant">Profil &amp;
                                                Kantor Operasional</p>
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm">Resmi</span>
                                </div>
                            </div>
                            <div
                                class="absolute -top-4 -left-4 px-4 py-2.5 rounded-xl bg-primary text-on-primary shadow-xl hidden sm:flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">stars</span>
                                <span class="font-label-sm text-label-sm font-semibold tracking-wide">Profil Resmi
                                    Korporat</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. SECTION TENTANG (WHO WE ARE) -->
            <section class="w-full bg-surface-container-low py-12 lg:py-20 relative">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-1 bg-primary rounded-full"></div>
                        <span
                            class="font-label-md text-label-md text-primary uppercase font-bold tracking-widest">Mengenal
                            Kami</span>
                    </div>
                    <h2
                        class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface mb-8 lg:mb-12 max-w-2xl font-bold">
                        Lebih dari Sekadar Sebuah Perusahaan
                    </h2>
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div
                            class="lg:col-span-7 flex flex-col gap-6 text-on-surface-variant font-body-md text-body-md">
                            <?php if (!empty($company['deskripsi'])): ?>
                                <div class="prose"><?= text_to_html($company['deskripsi']) ?></div>
                            <?php elseif (!empty($company['ringkasan'])): ?>
                                <div class="prose"><?= text_to_html($company['ringkasan']) ?></div>
                            <?php else: ?>
                                <p class="leading-relaxed">Temukan informasi unit usaha dan layanan <?= e($companyName) ?>
                                    melalui website ini.</p>
                            <?php endif; ?>
                            <div class="button-group flex flex-wrap items-center gap-4 pt-4">
                                <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary hover:bg-secondary text-on-primary font-label-md text-label-md transition-all"
                                    href="<?= e(url('unit-usaha.php')) ?>">
                                    <span>Jelajahi Unit Usaha</span>
                                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                </a>
                                <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-surface-container-lowest hover:bg-surface-container-low text-on-surface font-label-md text-label-md transition-all border border-outline-variant/30"
                                    href="<?= e(url('kontak.php')) ?>">
                                    <span>Hubungi Kami</span>
                                    <span class="material-symbols-outlined text-[18px]">support_agent</span>
                                </a>
                            </div>
                        </div>
                        <div class="lg:col-span-5 relative">
                            <div class="relative rounded-2xl overflow-hidden shadow-xl bg-surface-container">
                                <?php if (!empty($company['foto'])): ?>
                                    <img class="w-full h-56 sm:h-72 lg:h-[350px] object-cover"
                                        src="<?= e(upload_url($company['foto'])) ?>" alt="Profil <?= e($companyName) ?>"
                                        loading="lazy" />
                                <?php else: ?>
                                    <div
                                        class="w-full h-56 sm:h-72 lg:h-[350px] flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/30 to-surface-container">
                                        <span
                                            class="font-title-lg text-title-lg font-bold text-primary"><?= e($companyName) ?></span>
                                        <small class="font-body-sm text-body-sm text-on-surface-variant">Profil
                                            Perusahaan</small>
                                    </div>
                                <?php endif; ?>
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-primary/80 via-transparent to-transparent opacity-60">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. SECTION SEJARAH PERUSAHAAN -->
            <?php if (!empty($company['sejarah'])): ?>
                <section class="w-full bg-surface-container-lowest py-12 lg:py-20 relative scroll-mt-20" id="sejarah">
                    <div class="max-w-7xl mx-auto px-6 lg:px-12">
                        <div class="text-center max-w-2xl mx-auto mb-8 lg:mb-12">
                            <div class="inline-flex items-center gap-2 mb-3">
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                                <span
                                    class="font-label-md text-label-md text-primary font-bold uppercase tracking-widest">Perjalanan
                                    Perusahaan</span>
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                            </div>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold">
                                Sejarah <?= e($companyName) ?>
                            </h2>
                        </div>
                        <div class="max-w-3xl mx-auto mb-10 lg:mb-16">
                            <div class="prose font-body-md text-body-md text-on-surface-variant leading-relaxed">
                                <?= text_to_html($company['sejarah']) ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- 4. SECTION VISI PERUSAHAAN -->
            <?php if (!empty($company['visi'])): ?>
                <section
                    class="w-full bg-gradient-to-br from-primary via-secondary to-[#63060c] text-on-primary py-12 lg:py-20 relative overflow-hidden"
                    id="visi-misi">
                    <div class="absolute inset-0 opacity-10 pointer-events-none">
                        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <pattern height="60" id="grid-pattern" patternUnits="userSpaceOnUse" width="60">
                                    <path d="M 60 0 L 0 0 0 60" fill="none" stroke="currentColor" stroke-width="1.5"></path>
                                </pattern>
                            </defs>
                            <rect fill="url(#grid-pattern)" height="100%" width="100%"></rect>
                        </svg>
                    </div>
                    <div class="max-w-5xl mx-auto px-6 lg:px-12 relative z-10 text-center flex flex-col items-center">
                        <div
                            class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white mb-6 shadow-inner">
                            <span class="material-symbols-outlined text-[40px]">visibility</span>
                        </div>
                        <span
                            class="font-label-md text-label-md text-on-primary-container uppercase font-bold tracking-widest mb-3">Visi
                            Perusahaan</span>
                        <h2 class="font-headline-md text-headline-md-mobile lg:text-headline-md text-white font-bold mb-8">
                            Visi Kami</h2>
                        <div class="relative px-6 sm:px-12 py-8 rounded-3xl bg-white/5 backdrop-blur-sm">
                            <span
                                class="material-symbols-outlined text-[48px] text-white/20 absolute -top-4 left-6">format_quote</span>
                            <div
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-white font-medium leading-relaxed tracking-tight max-w-4xl prose">
                                <?= text_to_html($company['visi']) ?>
                            </div>
                            <span
                                class="material-symbols-outlined text-[48px] text-white/20 absolute -bottom-4 right-6 rotate-180">format_quote</span>
                        </div>
                        <div class="w-24 h-1 bg-white/30 rounded-full mt-10"></div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- 5. SECTION MISI PERUSAHAAN -->
            <?php if ($missions !== []): ?>
                <section class="w-full bg-surface-container-low py-12 lg:py-20 relative">
                    <div class="max-w-7xl mx-auto px-6 lg:px-12">
                        <div class="text-center max-w-3xl mx-auto mb-10 lg:mb-16">
                            <div class="inline-flex items-center gap-2 mb-3">
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                                <span
                                    class="font-label-md text-label-md text-primary font-bold uppercase tracking-widest">Misi
                                    Perusahaan</span>
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                            </div>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold">
                                Misi Kami dalam Mewujudkan Visi
                            </h2>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-3">
                                Langkah nyata dan komitmen operasional <?= e($companyName) ?> dalam memberikan dampak
                                terukur.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <?php foreach ($missions as $index => $mission): ?>
                                <?php $missionIcon = $missionIcons[$index % count($missionIcons)]; ?>
                                <div
                                    class="bg-surface-container-lowest p-8 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                                    <div>
                                        <div class="flex items-center justify-between mb-6">
                                            <span
                                                class="font-display-hero text-headline-lg font-black text-primary/20 group-hover:text-primary transition-colors"><?= e(sprintf('%02d', $index + 1)) ?></span>
                                            <div
                                                class="w-12 h-12 rounded-xl bg-primary-fixed/60 text-primary flex items-center justify-center">
                                                <span
                                                    class="material-symbols-outlined text-[26px]"><?= e($missionIcon) ?></span>
                                            </div>
                                        </div>
                                        <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                                            <?= e($mission) ?>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-6 pt-4 border-t-2 border-surface-container-low flex items-center text-primary font-label-sm text-label-sm font-semibold">
                                        <span>Misi Perusahaan</span>
                                        <span class="material-symbols-outlined text-[16px] ml-1">check_circle</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- 6. SECTION BRAND STATEMENT -->
            <section class="w-full bg-surface-container-lowest py-20 lg:py-24 relative overflow-hidden">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div
                        class="p-8 sm:p-14 rounded-3xl bg-surface-container-low relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                        <div class="flex items-start gap-6">
                            <div class="w-2.5 self-stretch bg-primary rounded-full shrink-0"></div>
                            <div class="flex flex-col gap-3">
                                <span
                                    class="font-label-md text-label-md text-primary font-bold uppercase tracking-wider">Komitmen
                                    Korporasi</span>
                                <h3
                                    class="font-headline-lg text-headline-lg-mobile lg:text-headline-lg text-on-surface font-black tracking-tight leading-tight">
                                    Melangkah Bersama, <br />
                                    <span class="text-primary">Bertumbuh Lebih Kuat.</span>
                                </h3>
                                <p
                                    class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mt-1 leading-relaxed">
                                    Keberhasilan sejati bukanlah sekadar capaian neraca keuangan semata, melainkan
                                    resonansi nilai nyata yang kami bagikan kepada mitra, keluarga karyawan, serta
                                    masyarakat.
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row lg:flex-col gap-4 shrink-0">
                            <a class="inline-flex items-center gap-3 px-6 py-4 rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all"
                                href="<?= e(url('kemitraan.php')) ?>">
                                <span class="material-symbols-outlined text-primary text-[28px]">handshake</span>
                                <div>
                                    <p class="font-title-lg text-title-lg font-bold text-on-surface">Kemitraan</p>
                                    <p class="font-caption text-caption text-on-surface-variant">Peluang Kerja Sama</p>
                                </div>
                            </a>
                            <a class="inline-flex items-center gap-3 px-6 py-4 rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all"
                                href="<?= e(url('kontak.php')) ?>">
                                <span class="material-symbols-outlined text-primary text-[28px]">support_agent</span>
                                <div>
                                    <p class="font-title-lg text-title-lg font-bold text-on-surface">Hubungi Kami</p>
                                    <p class="font-caption text-caption text-on-surface-variant">Informasi Kontak</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 7. SECTION CTA -->
            <section class="w-full bg-surface-container-lowest pb-20 lg:pb-28">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div
                        class="w-full rounded-3xl bg-gradient-to-r from-primary via-primary-container to-secondary p-10 sm:p-16 text-on-primary shadow-2xl relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-10">
                        <div
                            class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-white/10 blur-2xl pointer-events-none">
                        </div>
                        <div class="max-w-2xl relative z-10">
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white font-label-sm text-label-sm mb-4 backdrop-blur-sm">
                                <span class="material-symbols-outlined text-[16px]">support_agent</span>
                                <span>Kolaborasi Strategis</span>
                            </div>
                            <h2
                                class="font-headline-lg text-headline-lg-mobile lg:text-headline-lg text-white font-black tracking-tight mb-4 leading-tight">
                                Terhubung dengan <?= e($companyName) ?>
                            </h2>
                            <p class="font-body-lg text-body-lg text-white/90 leading-relaxed">
                                Hubungi <?= e($companyName) ?> untuk informasi layanan dan peluang kerja sama.
                            </p>
                        </div>
                        <div
                            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 relative z-10 shrink-0">
                            <?php if ($contactWhatsapp !== null): ?>
                                <a class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-white text-primary hover:bg-surface-container-lowest font-label-md text-label-md font-bold shadow-lg hover:shadow-xl transition-all duration-200"
                                    href="<?= e($contactWhatsapp) ?>" target="_blank" rel="noopener noreferrer">
                                    <span class="material-symbols-outlined text-[20px]">chat</span>
                                    <span>Hubungi via WhatsApp</span>
                                </a>
                            <?php else: ?>
                                <a class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-white text-primary hover:bg-surface-container-lowest font-label-md text-label-md font-bold shadow-lg hover:shadow-xl transition-all duration-200"
                                    href="<?= e(url('kontak.php')) ?>">
                                    <span class="material-symbols-outlined text-[20px]">mail</span>
                                    <span>Hubungi Kami</span>
                                </a>
                            <?php endif; ?>
                            <a class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-label-md text-label-md font-bold backdrop-blur-sm transition-all duration-200"
                                href="<?= e(url('unit-usaha.php')) ?>">
                                <span class="material-symbols-outlined text-[20px]">domain</span>
                                <span>Jelajahi Unit Usaha</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- Floating WhatsApp Badge -->
    <?php if ($contactWhatsapp !== null): ?>
        <div class="fixed bottom-6 right-6 z-50">
            <a aria-label="Hubungi melalui WhatsApp"
                class="flex items-center gap-2.5 px-4 py-3 rounded-full bg-[#25D366] text-white shadow-[0_8px_24px_rgba(37,211,102,0.35)] hover:shadow-[0_12px_28px_rgba(37,211,102,0.45)] hover:scale-105 transition-all"
                href="<?= e($contactWhatsapp) ?>" target="_blank" rel="noopener noreferrer">
                <span class="material-symbols-outlined text-[24px]">chat</span>
                <span class="font-label-md text-label-md font-semibold tracking-wide pr-1">WhatsApp CS</span>
            </a>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>