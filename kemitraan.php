<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$partnership = $pdo->query(
    'SELECT * FROM kemitraan WHERE id = 1'
)->fetch() ?: [];

$partners = $pdo->query(
    'SELECT nama, logo, website
     FROM mitra
     WHERE aktif = 1
     ORDER BY urutan ASC, id DESC'
)->fetchAll();

// Mengubah teks per baris menjadi daftar poin.
$toList = static function (?string $text): array {
    $lines = preg_split('/\R/u', $text ?? '') ?: [];

    return array_values(array_filter(
        array_map('trim', $lines),
        static fn(string $line): bool => $line !== ''
    ));
};

$types = $toList($partnership['jenis_kerja_sama'] ?? null);
$benefits = $toList($partnership['manfaat'] ?? null);
$requirements = $toList($partnership['persyaratan'] ?? null);
$steps = $toList($partnership['alur_pengajuan'] ?? null);

$activePage = 'kemitraan';
$pageTitle = ($partnership['judul'] ?? 'Kemitraan') . ' | Moza Group';
$pageDescription = 'Informasi peluang kerja sama, persyaratan, '
    . 'dan pengajuan kemitraan Moza Group.';
$canonicalUrl = url('kemitraan.php');

require __DIR__ . '/includes/header.php';

$message = "Halo Moza Group, saya ingin mengetahui informasi kemitraan."
    . "\n\nNama: "
    . "\nPerusahaan/Usaha: "
    . "\nJenis kerja sama: "
    . "\nLokasi: ";

$partnershipWhatsapp = whatsapp_url(
    $partnership['whatsapp'] ?? null,
    $message
) ?? whatsapp_url(
    $settings['whatsapp'] ?? null,
    $message
);

// Ikon yang dipakai berputar untuk kartu jenis kerja sama.
$typeIcons = ['handshake', 'hub', 'apartment', 'local_shipping', 'diversity_3', 'public'];
$stepIcons = ['flag', 'trending_up', 'hub', 'bolt', 'route', 'task_alt'];
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

            <!-- HERO SECTION: KEMITRAAN -->
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
                                <span class="font-label-sm text-label-sm font-semibold tracking-wider uppercase">Peluang
                                    Kerja Sama</span>
                            </div>
                            <h1
                                class="font-headline-lg text-headline-lg-mobile lg:text-headline-lg text-on-surface tracking-tight">
                                <?= e($partnership['judul'] ?? 'Kemitraan ' . $companyName) ?>
                            </h1>
                            <?php if (!empty($partnership['pengantar'])): ?>
                                <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed prose">
                                    <?= text_to_html($partnership['pengantar']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($partnershipWhatsapp !== null): ?>
                                <div class="pt-2 flex flex-wrap items-center gap-4">
                                    <a class="inline-flex items-center gap-3 px-8 py-3.5 rounded-xl bg-primary hover:bg-secondary text-on-primary font-label-md text-label-md shadow-md hover:shadow-lg transition-all duration-200"
                                        href="<?= e($partnershipWhatsapp) ?>" target="_blank" rel="noopener noreferrer">
                                        <span>Ajukan Kemitraan</span>
                                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                                    </a>
                                    <a class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md transition-all"
                                        href="<?= e(url('kontak.php')) ?>">
                                        <span class="material-symbols-outlined text-[18px]">support_agent</span>
                                        <span>Hubungi Kami</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="lg:col-span-5 relative">
                            <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-surface-container">
                                <?php if (!empty($settings['hero_foto'])): ?>
                                    <img class="w-full h-64 sm:h-80 lg:h-[400px] object-cover"
                                        src="<?= e(upload_url($settings['hero_foto'])) ?>"
                                        alt="Kemitraan <?= e($companyName) ?>" loading="lazy" />
                                <?php else: ?>
                                    <div
                                        class="w-full h-64 sm:h-80 lg:h-[400px] flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/30 to-surface-container">
                                        <span
                                            class="font-title-lg text-title-lg font-bold text-primary"><?= e($companyName) ?></span>
                                        <small class="font-body-sm text-body-sm text-on-surface-variant">Peluang
                                            Kemitraan</small>
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
                                            <span class="material-symbols-outlined text-[20px]">handshake</span>
                                        </div>
                                        <div>
                                            <p class="font-label-md text-label-md font-bold text-on-surface">
                                                <?= e($companyName) ?></p>
                                            <p class="font-caption text-caption text-on-surface-variant">Program
                                                Kemitraan Resmi</p>
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm">Resmi</span>
                                </div>
                            </div>
                            <div
                                class="absolute -top-4 -left-4 px-4 py-2.5 rounded-xl bg-primary text-on-primary shadow-xl hidden sm:flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">stars</span>
                                <span class="font-label-sm text-label-sm font-semibold tracking-wide">Sinergi
                                    Strategis</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- JENIS KEMITRAAN -->
            <?php if ($types !== []): ?>
                <section class="w-full bg-surface-container-low py-10 lg:py-16 relative">
                    <div class="max-w-7xl mx-auto px-6 lg:px-12">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-1 bg-primary rounded-full"></div>
                            <span
                                class="font-label-md text-label-md text-primary uppercase font-bold tracking-widest">Peluang
                                Kerja Sama</span>
                        </div>
                        <h2
                            class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface mb-10 max-w-2xl font-bold">
                            Jenis Kemitraan
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($types as $index => $type): ?>
                                <?php $typeIcon = $typeIcons[$index % count($typeIcons)]; ?>
                                <div
                                    class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                                    <div>
                                        <div class="flex items-center justify-between mb-4">
                                            <span
                                                class="font-headline-lg text-headline-sm font-black text-primary/20 group-hover:text-primary transition-colors"><?= e(sprintf('%02d', $index + 1)) ?></span>
                                            <div
                                                class="w-12 h-12 rounded-xl bg-primary-fixed/60 text-primary flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[26px]"><?= e($typeIcon) ?></span>
                                            </div>
                                        </div>
                                        <h3 class="font-title-lg text-title-lg font-bold text-on-surface"><?= e($type) ?></h3>
                                    </div>
                                    <div
                                        class="mt-4 pt-4 border-t-2 border-surface-container-low flex items-center text-primary font-label-sm text-label-sm font-semibold">
                                        <span>Jenis Kerja Sama</span>
                                        <span class="material-symbols-outlined text-[16px] ml-1">check_circle</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- MANFAAT & PERSYARATAN -->
            <?php if ($benefits !== [] || $requirements !== []): ?>
                <section class="w-full bg-surface-container-lowest py-10 lg:py-16 relative">
                    <div class="max-w-7xl mx-auto px-6 lg:px-12">
                        <div class="text-center max-w-2xl mx-auto mb-10">
                            <div class="inline-flex items-center gap-2 mb-3">
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                                <span
                                    class="font-label-md text-label-md text-primary font-bold uppercase tracking-widest">Informasi
                                    Kerja Sama</span>
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                            </div>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold">
                                Manfaat &amp; Persyaratan Kemitraan
                            </h2>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <?php if ($benefits !== []): ?>
                                <div class="p-6 lg:p-8 rounded-2xl bg-surface-container-low border border-outline-variant/30">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-primary text-on-primary flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[22px]">card_membership</span>
                                        </div>
                                        <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Manfaat Kemitraan</h3>
                                    </div>
                                    <ul class="space-y-3 font-body-sm text-body-sm text-on-surface-variant">
                                        <?php foreach ($benefits as $benefit): ?>
                                            <li class="flex items-start gap-2"><span
                                                    class="text-primary font-bold">✓</span><span><?= e($benefit) ?></span></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($requirements !== []): ?>
                                <div class="p-6 lg:p-8 rounded-2xl bg-surface-container-low border border-outline-variant/30">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-primary text-on-primary flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[22px]">assignment_turned_in</span>
                                        </div>
                                        <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Persyaratan</h3>
                                    </div>
                                    <ul class="space-y-3 font-body-sm text-body-sm text-on-surface-variant">
                                        <?php foreach ($requirements as $requirement): ?>
                                            <li class="flex items-start gap-2"><span
                                                    class="text-primary font-bold">✓</span><span><?= e($requirement) ?></span></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- ALUR PENGAJUAN -->
            <?php if ($steps !== []): ?>
                <section class="w-full bg-surface-container-low py-10 lg:py-16 relative">
                    <div class="max-w-7xl mx-auto px-6 lg:px-12">
                        <div class="text-center max-w-2xl mx-auto mb-10">
                            <div class="inline-flex items-center gap-2 mb-3">
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                                <span
                                    class="font-label-md text-label-md text-primary font-bold uppercase tracking-widest">Cara
                                    Bergabung</span>
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                            </div>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold">
                                Alur Pengajuan Kemitraan
                            </h2>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-3">
                                Ikuti langkah-langkah berikut untuk mengajukan kemitraan dengan <?= e($companyName) ?>.
                            </p>
                        </div>
                        <div class="relative">
                            <div
                                class="hidden lg:block absolute left-1/2 top-8 bottom-8 w-1 bg-primary/20 -translate-x-1/2 rounded-full">
                            </div>
                            <div class="flex flex-col gap-8">
                                <?php foreach ($steps as $index => $step): ?>
                                    <?php
                                    $stepIcon = $stepIcons[$index % count($stepIcons)];
                                    $isLeft = $index % 2 === 0;
                                    ?>
                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-8 items-center relative">
                                        <?php if ($isLeft): ?>
                                            <div class="lg:col-span-5 lg:text-right">
                                                <div
                                                    class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm hover:shadow-md transition-all">
                                                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                                                        <?= e($step) ?></p>
                                                </div>
                                            </div>
                                            <div class="hidden lg:flex lg:col-span-2 justify-center items-center">
                                                <div
                                                    class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center ring-8 ring-primary-fixed/40 shadow-lg">
                                                    <span class="material-symbols-outlined text-[20px]"><?= e($stepIcon) ?></span>
                                                </div>
                                            </div>
                                            <div class="lg:col-span-5">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm font-bold">
                                                    <span class="material-symbols-outlined text-[16px]">tag</span>
                                                    Langkah <?= e($index + 1) ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <div class="lg:col-span-5 order-3 lg:order-1">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm font-bold">
                                                    <span class="material-symbols-outlined text-[16px]">tag</span>
                                                    Langkah <?= e($index + 1) ?>
                                                </span>
                                            </div>
                                            <div class="hidden lg:flex lg:col-span-2 order-2 justify-center items-center">
                                                <div
                                                    class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center ring-8 ring-primary-fixed/40 shadow-lg">
                                                    <span class="material-symbols-outlined text-[20px]"><?= e($stepIcon) ?></span>
                                                </div>
                                            </div>
                                            <div class="lg:col-span-5 order-1 lg:order-3">
                                                <div
                                                    class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm hover:shadow-md transition-all">
                                                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                                                        <?= e($step) ?></p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- FORM PENGAJUAN KEMITRAAN -->
            <?php if ($partnershipWhatsapp !== null): ?>
                <section class="w-full bg-surface-container-lowest py-10 lg:py-16 relative" id="form-kemitraan">
                    <div class="max-w-3xl mx-auto px-6 lg:px-12">
                        <div class="text-center max-w-2xl mx-auto mb-8">
                            <div class="inline-flex items-center gap-2 mb-3">
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                                <span
                                    class="font-label-md text-label-md text-primary font-bold uppercase tracking-widest">Ajukan
                                    Kemitraan</span>
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                            </div>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold">
                                Form Pengajuan Kemitraan
                            </h2>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-3">
                                Isi formulir di bawah ini, lalu klik kirim. Anda akan diarahkan ke WhatsApp dengan pesan
                                yang sudah terisi otomatis.
                            </p>
                        </div>
                        <form id="partnership-form"
                            class="bg-surface-container-low rounded-2xl p-6 lg:p-8 shadow-sm flex flex-col gap-5"
                            onsubmit="submitPartnershipForm(event)">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-2">
                                    <label for="form-nama"
                                        class="font-label-sm text-label-sm font-semibold text-on-surface">Nama Lengkap <span
                                            class="text-primary">*</span></label>
                                    <input type="text" id="form-nama" name="nama" required
                                        placeholder="Masukkan nama lengkap"
                                        class="px-4 py-3 rounded-xl bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container transition-all border border-outline-variant/30" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="form-perusahaan"
                                        class="font-label-sm text-label-sm font-semibold text-on-surface">Perusahaan / Usaha
                                        <span class="text-primary">*</span></label>
                                    <input type="text" id="form-perusahaan" name="perusahaan" required
                                        placeholder="Nama perusahaan atau usaha"
                                        class="px-4 py-3 rounded-xl bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container transition-all border border-outline-variant/30" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-2">
                                    <label for="form-jenis"
                                        class="font-label-sm text-label-sm font-semibold text-on-surface">Jenis Kerja
                                        Sama</label>
                                    <?php if ($types !== []): ?>
                                        <select id="form-jenis" name="jenis"
                                            class="px-4 py-3 rounded-xl bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container transition-all border border-outline-variant/30 cursor-pointer">
                                            <option value="">Pilih jenis kerja sama</option>
                                            <?php foreach ($types as $type): ?>
                                                <option value="<?= e($type) ?>"><?= e($type) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" id="form-jenis" name="jenis"
                                            placeholder="Jenis kerja sama yang diminati"
                                            class="px-4 py-3 rounded-xl bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container transition-all border border-outline-variant/30" />
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="form-lokasi"
                                        class="font-label-sm text-label-sm font-semibold text-on-surface">Lokasi</label>
                                    <input type="text" id="form-lokasi" name="lokasi"
                                        placeholder="Kota / provinsi lokasi usaha"
                                        class="px-4 py-3 rounded-xl bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container transition-all border border-outline-variant/30" />
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="form-pesan"
                                    class="font-label-sm text-label-sm font-semibold text-on-surface">Pesan /
                                    Catatan</label>
                                <textarea id="form-pesan" name="pesan" rows="4"
                                    placeholder="Tuliskan pertanyaan atau informasi tambahan"
                                    class="px-4 py-3 rounded-xl bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container transition-all border border-outline-variant/30 resize-none"></textarea>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                                <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-primary hover:bg-secondary text-on-primary font-label-md text-label-md font-semibold shadow-md hover:shadow-lg transition-all">
                                    <span class="material-symbols-outlined text-[20px]">send</span>
                                    <span>Kirim via WhatsApp</span>
                                </button>
                                <a class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-semibold transition-all"
                                    href="<?= e(url('kontak.php')) ?>">
                                    <span class="material-symbols-outlined text-[18px]">support_agent</span>
                                    <span>Hubungi via Kontak</span>
                                </a>
                            </div>
                        </form>
                    </div>
                </section>

                <script>
                    function submitPartnershipForm(event) {
                        event.preventDefault();

                        var nama = document.getElementById('form-nama').value.trim();
                        var perusahaan = document.getElementById('form-perusahaan').value.trim();
                        var jenis = document.getElementById('form-jenis').value.trim();
                        var lokasi = document.getElementById('form-lokasi').value.trim();
                        var pesan = document.getElementById('form-pesan').value.trim();

                        var message = "Halo <?= e($companyName) ?>, saya ingin mengetahui informasi kemitraan.";
                        message += "\n\nNama: " + nama;
                        message += "\nPerusahaan/Usaha: " + perusahaan;
                        if (jenis) message += "\nJenis kerja sama: " + jenis;
                        if (lokasi) message += "\nLokasi: " + lokasi;
                        if (pesan) message += "\n\nPesan: " + pesan;

                        var baseUrl = <?= json_encode($partnershipWhatsapp) ?>;
                        var phoneMatch = baseUrl.match(/wa\.me\/(\d+)/);
                        if (phoneMatch) {
                            var waUrl = "https://wa.me/" + phoneMatch[1] + "?text=" + encodeURIComponent(message);
                            window.open(waUrl, "_blank");
                        }
                    }
                </script>
            <?php endif; ?>

            <!-- MITRA PERUSAHAAN -->
            <?php if ($partners !== []): ?>
                <section class="w-full bg-surface-container-lowest py-10 lg:py-16 relative overflow-hidden">
                    <div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10">
                        <div class="text-center max-w-2xl mx-auto mb-10">
                            <div class="inline-flex items-center gap-2 mb-3">
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                                <span
                                    class="font-label-md text-label-md text-primary font-bold uppercase tracking-widest">Mitra
                                    Kami</span>
                                <div class="w-3 h-3 rounded-full bg-primary"></div>
                            </div>
                            <h2
                                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold">
                                Mitra Perusahaan
                            </h2>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-3">
                                Tumbuh dan bergerak bersama jaringan institusi terkemuka.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-center">
                            <?php foreach ($partners as $partner): ?>
                                <?php
                                $website = (string) ($partner['website'] ?? '');
                                $hasWebsite = $website !== '' && validate_url($website) === null;
                                ?>
                                <div
                                    class="h-24 bg-surface-container-low rounded-2xl flex items-center justify-center p-4 shadow-sm hover:shadow-md transition-shadow border border-outline-variant/30">
                                    <?php if ($hasWebsite): ?>
                                        <a href="<?= e($website) ?>" target="_blank" rel="noopener noreferrer"
                                            class="flex flex-col items-center gap-2">
                                            <?php if (!empty($partner['logo'])): ?>
                                                <img src="<?= e(upload_url($partner['logo'])) ?>" alt="<?= e($partner['nama']) ?>"
                                                    loading="lazy" class="h-8 w-auto object-contain" />
                                            <?php endif; ?>
                                            <span
                                                class="font-label-sm text-label-sm font-bold text-on-surface text-center tracking-tight"><?= e($partner['nama']) ?></span>
                                        </a>
                                    <?php else: ?>
                                        <div class="flex flex-col items-center gap-2">
                                            <?php if (!empty($partner['logo'])): ?>
                                                <img src="<?= e(upload_url($partner['logo'])) ?>" alt="<?= e($partner['nama']) ?>"
                                                    loading="lazy" class="h-8 w-auto object-contain" />
                                            <?php endif; ?>
                                            <span
                                                class="font-label-sm text-label-sm font-bold text-on-surface text-center tracking-tight"><?= e($partner['nama']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- BRAND STATEMENT -->
            <section class="w-full bg-surface-container-low py-10 lg:py-14 relative overflow-hidden">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div
                        class="p-6 sm:p-10 rounded-3xl bg-surface-container-lowest relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="flex items-start gap-6">
                            <div class="w-2.5 self-stretch bg-primary rounded-full shrink-0"></div>
                            <div class="flex flex-col gap-3">
                                <span
                                    class="font-label-md text-label-md text-primary font-bold uppercase tracking-wider">Komitmen
                                    Kemitraan</span>
                                <h3
                                    class="font-headline-lg text-headline-lg-mobile lg:text-headline-lg text-on-surface font-black tracking-tight leading-tight">
                                    Melangkah Bersama, <br />
                                    <span class="text-primary">Bertumbuh Lebih Kuat.</span>
                                </h3>
                                <p
                                    class="font-body-md text-body-md text-on-surface-variant max-w-2xl mt-1 leading-relaxed">
                                    Keberhasilan sejati bukanlah sekadar capaian neraca keuangan semata, melainkan
                                    resonansi nilai nyata yang kami bagikan kepada mitra dan masyarakat.
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row lg:flex-col gap-4 shrink-0">
                            <?php if ($partnershipWhatsapp !== null): ?>
                                <a class="inline-flex items-center gap-3 px-6 py-4 rounded-2xl bg-surface-container-lowest shadow-sm hover:shadow-md transition-all"
                                    href="<?= e($partnershipWhatsapp) ?>" target="_blank" rel="noopener noreferrer">
                                    <span class="material-symbols-outlined text-primary text-[28px]">chat</span>
                                    <div>
                                        <p class="font-title-lg text-title-lg font-bold text-on-surface">Tanyakan Kemitraan
                                        </p>
                                        <p class="font-caption text-caption text-on-surface-variant">Via WhatsApp</p>
                                    </div>
                                </a>
                            <?php endif; ?>
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

            <!-- CTA -->
            <section class="w-full bg-surface-container-lowest pb-10 lg:pb-16">
                <div class="max-w-7xl mx-auto px-6 lg:px-12">
                    <div
                        class="w-full rounded-3xl bg-gradient-to-r from-primary via-primary-container to-secondary p-8 sm:p-12 text-on-primary shadow-2xl relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                        <div
                            class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-white/10 blur-2xl pointer-events-none">
                        </div>
                        <div class="max-w-2xl relative z-10">
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white font-label-sm text-label-sm mb-4 backdrop-blur-sm">
                                <span class="material-symbols-outlined text-[16px]">handshake</span>
                                <span>Kolaborasi Strategis</span>
                            </div>
                            <h2
                                class="font-headline-lg text-headline-lg-mobile lg:text-headline-lg text-white font-black tracking-tight mb-4 leading-tight">
                                <?= e($partnership['cta_judul'] ?? 'Tertarik Menjadi Mitra?') ?>
                            </h2>
                            <?php if (!empty($partnership['cta_deskripsi'])): ?>
                                <div class="font-body-md text-body-md text-white/90 leading-relaxed prose">
                                    <?= text_to_html($partnership['cta_deskripsi']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div
                            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 relative z-10 shrink-0">
                            <?php if ($partnershipWhatsapp !== null): ?>
                                <a class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-white text-primary hover:bg-surface-container-lowest font-label-md text-label-md font-bold shadow-lg hover:shadow-xl transition-all duration-200"
                                    href="<?= e($partnershipWhatsapp) ?>" target="_blank" rel="noopener noreferrer">
                                    <span class="material-symbols-outlined text-[20px]">chat</span>
                                    <span>Ajukan via WhatsApp</span>
                                </a>
                            <?php else: ?>
                                <a class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-white text-primary hover:bg-surface-container-lowest font-label-md text-label-md font-bold shadow-lg hover:shadow-xl transition-all duration-200"
                                    href="<?= e(url('kontak.php')) ?>">
                                    <span class="material-symbols-outlined text-[20px]">mail</span>
                                    <span>Lihat Kontak</span>
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
    <?php if ($partnershipWhatsapp !== null): ?>
        <div class="fixed bottom-6 right-6 z-50">
            <a aria-label="Hubungi melalui WhatsApp"
                class="flex items-center gap-2.5 px-4 py-3 rounded-full bg-[#25D366] text-white shadow-[0_8px_24px_rgba(37,211,102,0.35)] hover:shadow-[0_12px_28px_rgba(37,211,102,0.45)] hover:scale-105 transition-all"
                href="<?= e($partnershipWhatsapp) ?>" target="_blank" rel="noopener noreferrer">
                <span class="material-symbols-outlined text-[24px]">chat</span>
                <span class="font-label-md text-label-md font-semibold tracking-wide pr-1">WhatsApp Kemitraan</span>
            </a>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>