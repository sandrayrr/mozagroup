<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$activePage = 'kontak';
$pageTitle = 'Kontak | Moza Group';
$pageDescription = 'Hubungi Moza Group untuk informasi layanan, produk, dan kemitraan.';
$canonicalUrl = url('kontak.php');

require __DIR__ . '/includes/header.php';

$email = (string) ($settings['email'] ?? '');

$hasEmail = $email !== ''
    && validate_email($email) === null;

$mapsUrl = (string) ($settings['maps_url'] ?? '');

$mapsScheme = strtolower(
    (string) parse_url($mapsUrl, PHP_URL_SCHEME)
);

$hasMaps = $mapsUrl !== ''
    && filter_var($mapsUrl, FILTER_VALIDATE_URL) !== false
    && in_array($mapsScheme, ['http', 'https'], true);

$socialLabels = [
    'instagram' => 'Instagram',
    'facebook' => 'Facebook',
    'tiktok' => 'TikTok',
    'youtube' => 'YouTube',
];

$availableSocials = [];

foreach ($socialLabels as $field => $label) {
    $link = (string) ($settings[$field] ?? '');

    if ($link !== '' && validate_url($link) === null) {
        $availableSocials[] = [
            'label' => $label,
            'url' => $link,
        ];
    }
}

// Ikon dan deskripsi untuk tiap platform media sosial.
$socialIcons = [
    'Instagram' => 'photo_camera',
    'Facebook' => 'public',
    'TikTok' => 'music_note',
    'YouTube' => 'play_circle',
];

$socialDescriptions = [
    'Instagram' => 'Dokumentasi visual proyek, keterlibatan komunitas, dan budaya kerja.',
    'Facebook' => 'Kanal warta publik, inisiatif CSR, dan siaran pers.',
    'TikTok' => 'Konten singkat kegiatan korporasi dan dokumentasi.',
    'YouTube' => 'Video profil korporat, dokumenter operasional, dan liputan.',
];
?>

<!-- Tailwind CDN + config + fonts (dipasang di body agar kelas Code 2 aktif) -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@600;700;800&amp;display=swap"
    rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
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

        main> :first-child {
            margin-top: 0 !important;
        }

        main> :last-child {
            margin-bottom: 0 !important;
        }
    }

    ::-webkit-scrollbar {
        display: none;
    }
</style>
<script src="https://cdn.tailwindcss.com"></script>
<script
    id="tailwind-config">tailwind.config = { darkMode: "class", theme: { extend: { "colors": { "outline-variant": "#e6bdb8", "surface": "#faf9fd", "on-primary": "#ffffff", "surface-dim": "#dbd9dd", "on-surface": "#1a1b1e", "primary-fixed": "#ffdad6", "primary": "#ae0011", "on-secondary-container": "#fffbff", "on-tertiary-fixed": "#410004", "surface-container-highest": "#e3e2e6", "on-primary-container": "#ffece9", "tertiary-container": "#cf2b30", "tertiary-fixed-dim": "#ffb3ad", "on-tertiary": "#ffffff", "secondary-fixed-dim": "#ffb4ac", "on-secondary-fixed-variant": "#93000e", "on-secondary-fixed": "#410003", "on-background": "#1a1b1e", "on-primary-fixed": "#410002", "secondary-container": "#d83734", "surface-container": "#efedf1", "inverse-on-surface": "#f1f0f4", "surface-container-high": "#e9e7eb", "surface-container-low": "#f4f3f7", "on-tertiary-container": "#ffebe9", "error": "#ba1a1a", "tertiary-fixed": "#ffdad7", "error-container": "#ffdad6", "on-primary-fixed-variant": "#93000d", "outline": "#926f6b", "primary-fixed-dim": "#ffb4ab", "on-surface-variant": "#5d3f3c", "secondary-fixed": "#ffdad6", "primary-container": "#d71920", "surface-container-lowest": "#ffffff", "inverse-surface": "#2f3033", "on-secondary": "#ffffff", "on-tertiary-fixed-variant": "#930013", "surface-bright": "#faf9fd", "tertiary": "#ab091b", "surface-tint": "#c00014", "on-error-container": "#93000a", "inverse-primary": "#ffb4ab", "on-error": "#ffffff", "secondary": "#b51b1e", "background": "#faf9fd", "surface-variant": "#e3e2e6" }, "borderRadius": { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" }, "spacing": { "gutter": "1.5rem", "space-2xl": "4rem", "margin": "1.5rem", "space-lg": "1.5rem", "gutter-desktop": "2rem", "space-xs": "0.25rem", "space-3xl": "6rem", "space-sm": "0.5rem", "margin-desktop": "5rem", "space-xl": "2.5rem", "margin-mobile": "1.25rem", "gutter-mobile": "1rem", "space-md": "1rem", "margin-tablet": "2.5rem" }, "fontFamily": { "headline-md-mobile": ["Plus Jakarta Sans"], "headline-lg-mobile": ["Plus Jakarta Sans"], "headline-sm": ["Plus Jakarta Sans"], "label-md": ["Inter"], "headline-lg": ["Plus Jakarta Sans"], "display-hero-mobile": ["Plus Jakarta Sans"], "body-md": ["Inter"], "title-lg": ["Plus Jakarta Sans"], "body-sm": ["Inter"], "label-sm": ["Inter"], "display-hero": ["Plus Jakarta Sans"], "headline-md": ["Plus Jakarta Sans"], "body-lg": ["Inter"], "caption": ["Inter"] }, "fontSize": { "headline-md-mobile": ["24px", { "lineHeight": "32px", "letterSpacing": "0", "fontWeight": "600" }], "headline-lg-mobile": ["28px", { "lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700" }], "headline-sm": ["22px", { "lineHeight": "30px", "letterSpacing": "0", "fontWeight": "600" }], "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500" }], "headline-lg": ["40px", { "lineHeight": "48px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "display-hero-mobile": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.015em", "fontWeight": "700" }], "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }], "title-lg": ["18px", { "lineHeight": "26px", "letterSpacing": "0", "fontWeight": "600" }], "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }], "label-sm": ["12px", { "lineHeight": "16px", "letterSpacing": "0.04em", "fontWeight": "600" }], "display-hero": ["56px", { "lineHeight": "64px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "headline-md": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600" }], "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }], "caption": ["12px", { "lineHeight": "16px", "fontWeight": "400" }] } } } }</script>

<div class="bg-surface font-body-md text-on-surface antialiased">

    <main class="w-full pt-20 bg-surface min-h-[calc(100vh-20rem)]">
        <div class="flex flex-col w-full">

            <!-- BREADCRUMB & HERO SECTION -->
            <section class="relative w-full bg-surface-container-lowest overflow-hidden pb-space-2xl md:pb-space-3xl">
                <div class="absolute inset-0 pointer-events-none opacity-40">
                    <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-primary/5 blur-3xl"></div>
                    <div class="absolute top-1/2 -left-20 w-80 h-80 rounded-full bg-primary-container/5 blur-3xl"></div>
                    <svg class="w-full h-full text-surface-container-high" fill="none">
                        <defs>
                            <pattern height="48" id="corporate-grid" patternUnits="userSpaceOnUse" width="48">
                                <path d="M 48 0 L 0 0 0 48" fill="none" stroke="currentColor" stroke-dasharray="2 6"
                                    stroke-width="0.75"></path>
                            </pattern>
                        </defs>
                        <rect fill="url(#corporate-grid)" height="100%" width="100%"></rect>
                    </svg>
                </div>
                <div
                    class="relative max-w-7xl mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop pt-space-lg md:pt-space-xl">
                   
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-end">
                        <div class="lg:col-span-8 flex flex-col gap-space-sm">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 w-fit">
                                <span class="w-2 h-2 rounded-full bg-primary-container animate-ping"></span>
                                <span class="w-2 h-2 rounded-full bg-primary-container -ml-3"></span>
                                <span
                                    class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-semibold">Hubungi
                                    Kami</span>
                            </div>
                            <h1
                                class="font-display-hero text-headline-lg-mobile md:text-headline-lg text-on-surface tracking-tight font-bold">
                                Mari <span class="text-primary-container">Terhubung</span>
                            </h1>
                            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl leading-relaxed">
                                Hubungi <?= e($companyName) ?> untuk informasi produk, layanan, dan peluang kerja sama
                                korporasi. Kantor perwakilan kami siap melayani komunikasi institusional Anda secara
                                terstruktur dan transparan.
                            </p>
                        </div>
                        <div class="lg:col-span-4 flex flex-col gap-space-xs pt-space-md lg:pt-0">
                            <div
                                class="flex items-center gap-space-sm p-space-sm rounded-lg bg-surface-container-low shadow-sm">
                                <span class="material-symbols-outlined text-primary text-[22px]">schedule</span>
                                <div class="flex flex-col">
                                    <span class="font-label-md text-label-md font-semibold text-on-surface">Respon
                                        Cepat</span>
                                    <span class="font-caption text-caption text-on-surface-variant">Layanan resmi hari
                                        kerja <?= e($companyName) ?></span>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-space-sm p-space-sm rounded-lg bg-surface-container-low shadow-sm">
                                <span class="material-symbols-outlined text-primary text-[22px]">hub</span>
                                <div class="flex flex-col">
                                    <span class="font-label-md text-label-md font-semibold text-on-surface">Terkoneksi
                                        Database Holding</span>
                                    <span class="font-caption text-caption text-on-surface-variant">Terhubung langsung
                                        ke unit operasional</span>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-space-sm p-space-sm rounded-lg bg-surface-container-low shadow-sm">
                                <span class="material-symbols-outlined text-primary text-[22px]">verified_user</span>
                                <div class="flex flex-col">
                                    <span class="font-label-md text-label-md font-semibold text-on-surface">Jalur Resmi
                                        Terverifikasi</span>
                                    <span class="font-caption text-caption text-on-surface-variant">Keamanan enkripsi
                                        &amp; kepatuhan data GCG</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- INFORMASI KONTAK & LOKASI -->
            <section class="w-full bg-surface py-space-xl md:py-space-2xl">
                <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop">

                        <!-- LEFT: Informasi Kontak -->
                        <div class="lg:col-span-7 flex flex-col gap-space-lg">
                            <div>
                                <span
                                    class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Informasi
                                    Kontak</span>
                                <h2
                                    class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold mt-1">
                                    <?= e($companyName) ?></h2>
                                <p class="font-body-md text-body-md text-on-surface-variant mt-2 max-w-xl">
                                    Pilih saluran komunikasi yang tersedia untuk menyampaikan pertanyaan, konsultasi
                                    kemitraan, atau kebutuhan resmi perusahaan Anda.
                                </p>
                            </div>
                            <div class="flex flex-col gap-space-md">

                                <?php if (
                                    empty($settings['alamat'])
                                    && $contactWhatsapp === null
                                    && !$hasEmail
                                ): ?>
                                    <div
                                        class="rounded-xl bg-surface-container-lowest p-space-xl shadow-sm text-center flex flex-col items-center justify-center">
                                        <div
                                            class="w-14 h-14 rounded-full bg-surface-container text-on-surface-variant flex items-center justify-center mb-3">
                                            <span class="material-symbols-outlined text-[28px]">contact_support</span>
                                        </div>
                                        <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold">
                                            Informasi kontak belum tersedia.</h3>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                                            Silakan hubungi kami melalui halaman kemitraan.
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($settings['alamat'])): ?>
                                    <div
                                        class="group relative rounded-xl bg-surface-container-lowest p-space-lg shadow-sm hover:shadow-md transition-all">
                                        <div class="flex flex-col sm:flex-row items-start gap-space-md">
                                            <div
                                                class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center shrink-0 text-primary">
                                                <span class="material-symbols-outlined text-[28px]"
                                                    style="font-variation-settings: 'FILL' 1;">location_on</span>
                                            </div>
                                            <div class="flex-1 flex flex-col">
                                                <div class="flex flex-wrap items-center justify-between gap-space-xs">
                                                    <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Alamat
                                                        Kantor</h3>
                                                    <span
                                                        class="px-2.5 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm font-semibold">Kantor
                                                        Pusat</span>
                                                </div>
                                                <div
                                                    class="font-body-md text-body-md text-on-surface font-medium mt-2 leading-relaxed prose">
                                                    <?= text_to_html($settings['alamat']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($contactWhatsapp !== null): ?>
                                    <div
                                        class="group relative rounded-xl bg-surface-container-lowest p-space-lg shadow-sm hover:shadow-md transition-all">
                                        <div class="flex flex-col sm:flex-row items-start gap-space-md">
                                            <div
                                                class="w-14 h-14 rounded-full bg-primary-container/10 flex items-center justify-center shrink-0 text-primary-container">
                                                <span class="material-symbols-outlined text-[28px]">chat</span>
                                            </div>
                                            <div class="flex-1 flex flex-col">
                                                <div class="flex flex-wrap items-center justify-between gap-space-xs">
                                                    <h3 class="font-title-lg text-title-lg text-on-surface font-bold">
                                                        WhatsApp</h3>
                                                    <span
                                                        class="px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm">Layanan
                                                        Terverifikasi</span>
                                                </div>
                                                <span
                                                    class="font-headline-sm text-headline-sm font-bold text-on-surface tracking-wide mt-2">
                                                    <?= e(normalize_whatsapp($settings['whatsapp'] ?? '')) ?>
                                                </span>
                                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                                                    Layanan respon cepat hari kerja. Pesan di luar jam kerja akan diproses
                                                    pada hari kerja berikutnya.
                                                </p>
                                                <div class="mt-space-md">
                                                    <a class="inline-flex items-center gap-space-xs px-space-md py-space-sm bg-primary-container text-on-primary font-label-md text-label-md font-semibold rounded-lg shadow-sm hover:bg-tertiary-container transition-all"
                                                        href="<?= e($contactWhatsapp) ?>" target="_blank"
                                                        rel="noopener noreferrer">
                                                        <span>Chat via WhatsApp</span>
                                                        <span
                                                            class="material-symbols-outlined text-[18px]">open_in_new</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($hasEmail): ?>
                                    <div
                                        class="group relative rounded-xl bg-surface-container-lowest p-space-lg shadow-sm hover:shadow-md transition-all">
                                        <div class="flex flex-col sm:flex-row items-start gap-space-md">
                                            <div
                                                class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center shrink-0 text-primary">
                                                <span class="material-symbols-outlined text-[28px]">mark_email_read</span>
                                            </div>
                                            <div class="flex-1 flex flex-col">
                                                <div class="flex flex-wrap items-center justify-between gap-space-xs">
                                                    <h3 class="font-title-lg text-title-lg text-on-surface font-bold">Email
                                                    </h3>
                                                    <span
                                                        class="px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm">Korespondensi
                                                        Resmi</span>
                                                </div>
                                                <a class="font-headline-sm text-headline-sm font-bold text-primary hover:text-tertiary transition-colors mt-2 break-all"
                                                    href="<?= e('mailto:' . $email) ?>">
                                                    <?= e($email) ?>
                                                </a>
                                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                                                    Untuk korespondensi formal, legalitas korporat, dan permohonan kemitraan
                                                    strategis.
                                                </p>
                                                <div class="mt-space-md">
                                                    <a class="inline-flex items-center gap-space-xs px-space-md py-space-sm bg-surface-container text-on-surface font-label-md text-label-md font-semibold rounded-lg hover:bg-surface-container-high transition-colors"
                                                        href="<?= e('mailto:' . $email) ?>">
                                                        <span
                                                            class="material-symbols-outlined text-[18px] text-primary">send</span>
                                                        <span>Kirim Email</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>

                        <!-- RIGHT: Lokasi Perusahaan -->
                        <div class="lg:col-span-5 flex flex-col gap-space-lg">
                            <div>
                                <span
                                    class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Lokasi
                                    Perusahaan</span>
                                <h2
                                    class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold mt-1">
                                    Temukan Kami</h2>
                                <?php if (!empty($settings['alamat'])): ?>
                                    <div
                                        class="prose font-body-md text-body-md text-on-surface-variant mt-2 leading-relaxed">
                                        <?= text_to_html($settings['alamat']) ?>
                                    </div>
                                <?php else: ?>
                                    <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                                        Alamat perusahaan belum tersedia.
                                    </p>
                                <?php endif; ?>
                            </div>

                            <?php if ($hasMaps): ?>
                                <div
                                    class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm overflow-hidden flex flex-col">
                                    <div
                                        class="relative w-full h-64 sm:h-80 rounded-lg overflow-hidden bg-surface-container-high flex flex-col items-center justify-center p-space-md">
                                        <div
                                            class="absolute inset-0 bg-gradient-to-br from-primary/5 via-surface-container to-surface-container-high">
                                        </div>
                                        <div class="relative z-10 flex flex-col items-center text-center">
                                            <div class="relative flex items-center justify-center">
                                                <div
                                                    class="absolute w-12 h-12 bg-primary rounded-full animate-ping opacity-30">
                                                </div>
                                                <div
                                                    class="w-10 h-10 bg-primary-container rounded-full flex items-center justify-center text-on-primary shadow-lg ring-4 ring-surface-container-lowest">
                                                    <span
                                                        class="material-symbols-outlined text-[22px]">corporate_fare</span>
                                                </div>
                                            </div>
                                            <div
                                                class="mt-space-sm bg-surface-container-lowest/95 backdrop-blur-md px-space-md py-1.5 rounded-full shadow-md">
                                                <span
                                                    class="font-label-md text-label-md font-bold text-on-surface"><?= e($companyName) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-space-md">
                                        <a class="w-full inline-flex items-center justify-center gap-space-xs px-space-md py-3.5 bg-primary-container text-on-primary font-label-md text-label-md font-semibold rounded-lg shadow-sm hover:bg-tertiary-container transition-all"
                                            href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener noreferrer">
                                            <span class="material-symbols-outlined text-[20px]">directions</span>
                                            <span>Buka Google Maps</span>
                                            <span class="material-symbols-outlined text-[16px]">north_east</span>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="w-full h-px bg-surface-container-high my-space-md"></div>

                            <div class="p-space-sm rounded-lg bg-surface-container-lowest flex flex-col gap-space-xs">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="material-symbols-outlined text-primary text-[20px]">business_center</span>
                                    <h4 class="font-title-lg text-title-lg font-bold text-on-surface">Pertanyaan tentang
                                        Layanan?</h4>
                                </div>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 leading-relaxed">
                                    Kunjungi halaman unit usaha untuk melihat profil layanan, kapabilitas teknis, dan
                                    kontak masing-masing divisi bisnis.
                                </p>
                                <div class="mt-space-sm">
                                    <a class="inline-flex items-center gap-1.5 font-label-md text-label-md font-bold text-primary hover:text-tertiary group transition-colors"
                                        href="<?= e(url('unit-usaha.php')) ?>">
                                        <span>Lihat Unit Usaha</span>
                                        <span
                                            class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- MEDIA SOSIAL -->
            <?php if ($availableSocials !== []): ?>
                <section class="w-full bg-surface-container-low py-space-xl md:py-space-2xl">
                    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
                        <div class="flex flex-col items-center text-center max-w-2xl mx-auto mb-space-xl">
                            <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Media
                                Sosial</span>
                            <h2
                                class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface font-bold mt-1">
                                Ikuti Informasi Kami
                            </h2>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-2 leading-relaxed">
                                Temukan informasi perusahaan, pembaharuan proyek strategis, dan kegiatan korporasi terbaru
                                melalui kanal media sosial resmi <?= e($companyName) ?>.
                            </p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter-desktop">
                            <?php foreach ($availableSocials as $social): ?>
                                <?php
                                $icon = $socialIcons[$social['label']] ?? 'share';
                                $desc = $socialDescriptions[$social['label']] ?? 'Ikuti informasi terbaru dari ' . $companyName;
                                ?>
                                <a class="group rounded-xl bg-surface-container-lowest p-space-lg shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                                    href="<?= e($social['url']) ?>" target="_blank" rel="noopener noreferrer">
                                    <div>
                                        <div class="flex items-center justify-between mb-space-md">
                                            <div
                                                class="w-12 h-12 rounded-lg bg-surface-container-high group-hover:bg-primary/10 flex items-center justify-center transition-colors text-primary">
                                                <span class="material-symbols-outlined text-[28px]"><?= e($icon) ?></span>
                                            </div>
                                            <span
                                                class="material-symbols-outlined text-outline text-[20px] group-hover:text-primary group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all">north_east</span>
                                        </div>
                                        <h3
                                            class="font-title-lg text-title-lg text-on-surface font-bold mt-1 group-hover:text-primary transition-colors">
                                            <?= e($social['label']) ?></h3>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-2">
                                            <?= e($desc) ?>
                                        </p>
                                    </div>
                                    <div
                                        class="mt-space-lg pt-space-sm border-t border-surface-container-high flex items-center justify-between">
                                        <span
                                            class="font-label-sm text-label-sm font-semibold text-primary"><?= e($companyName) ?></span>
                                        <span class="font-caption text-caption text-on-surface-variant">Akun Resmi</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- CTA SECTION -->
            <section class="relative w-full bg-primary text-on-primary py-space-xl md:py-space-2xl overflow-hidden">
                <div class="absolute inset-0 pointer-events-none opacity-10">
                    <svg class="w-full h-full" fill="none" preserveAspectRatio="none" viewBox="0 0 1200 400">
                        <path d="M0,0 L600,400 L1200,0 Z" fill="currentColor"></path>
                        <circle cx="950" cy="200" r="180" stroke="currentColor" stroke-width="2"></circle>
                        <circle cx="150" cy="100" r="120" stroke="currentColor" stroke-dasharray="8 8" stroke-width="2">
                        </circle>
                    </svg>
                </div>
                <div class="relative max-w-7xl mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-space-xl">
                        <div class="flex flex-col gap-space-xs text-center lg:text-left max-w-2xl">
                            <span
                                class="font-label-sm text-label-sm uppercase tracking-widest text-on-primary-container font-semibold">
                                Kolaborasi &amp; Investasi Strategis
                            </span>
                            <h2
                                class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-primary font-bold">
                                Ingin Menjalin Kerja Sama?
                            </h2>
                            <p class="font-body-md text-body-md text-on-primary-container leading-relaxed mt-1">
                                Pelajari informasi, kriteria eligibilitas, dan alur pengajuan proposal kemitraan bersama
                                <?= e($companyName) ?> untuk membangun sinergi industri yang berdampak nyata.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center gap-space-md shrink-0">
                            <a class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-space-xl py-3.5 bg-surface-container-lowest text-primary font-label-md text-label-md font-bold rounded-lg shadow-md hover:bg-surface-container-low transition-all"
                                href="<?= e(url('kemitraan.php')) ?>">
                                <span>Informasi Kemitraan</span>
                                <span class="material-symbols-outlined text-[18px]">handshake</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>