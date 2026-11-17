<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$activePage = 'berita';
$pageTitle = 'Berita dan Kegiatan | Moza Group';
$pageDescription = 'Berita terbaru dan dokumentasi kegiatan Moza Group.';
$canonicalUrl = url('berita.php');

$search = input_string($_GET, 'q');

if (text_length($search) > 200) {
    $search = '';
}

$now = date('Y-m-d H:i:s');

$where = ' WHERE status = ? AND published_at <= ?';
$params = ['terbit', $now];

if ($search !== '') {
    $where .= ' AND LOCATE(?, judul) > 0';
    $params[] = $search;
}

$statement = $pdo->prepare(
    'SELECT COUNT(*) FROM berita' . $where
);

$statement->execute($params);

$pagination = pagination(
    (int) $statement->fetchColumn(),
    9
);

$statement = $pdo->prepare(
    'SELECT judul, slug, ringkasan, foto, published_at
     FROM berita'
    . $where
    . ' ORDER BY published_at DESC, id DESC
        LIMIT ? OFFSET ?'
);

$position = 1;

foreach ($params as $value) {
    $statement->bindValue(
        $position++,
        $value,
        PDO::PARAM_STR
    );
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

$news = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<!-- Tailwind CDN + config + fonts (dipasang di body agar kelas Code 2 aktif) -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}::-webkit-scrollbar{display:none;}</style>
<script src="https://cdn.tailwindcss.com"></script>
<script id="tailwind-config">tailwind.config={"darkMode":"class","theme":{"extend":{"colors":{"on-primary-fixed":"#410002","outline":"#926f6b","tertiary":"#ab091b","primary-fixed-dim":"#ffb4ab","error-container":"#ffdad6","surface-dim":"#dbd9dd","secondary":"#b51b1e","tertiary-fixed-dim":"#ffb3ad","surface-container-high":"#e9e7eb","on-secondary":"#ffffff","background":"#faf9fd","on-secondary-fixed":"#410003","primary-container":"#d71920","surface-bright":"#faf9fd","surface":"#faf9fd","surface-container-lowest":"#ffffff","on-secondary-fixed-variant":"#93000e","on-secondary-container":"#fffbff","secondary-fixed":"#ffdad6","on-primary-fixed-variant":"#93000d","on-primary":"#ffffff","error":"#ba1a1a","primary":"#ae0011","on-tertiary-container":"#ffebe9","on-error-container":"#93000a","secondary-fixed-dim":"#ffb4ac","outline-variant":"#e6bdb8","inverse-on-surface":"#f1f0f4","on-tertiary-fixed":"#410004","surface-container-low":"#f4f3f7","on-surface-variant":"#5d3f3c","primary-fixed":"#ffdad6","tertiary-fixed":"#ffdad7","surface-tint":"#c00014","on-tertiary-fixed-variant":"#930013","surface-container":"#efedf1","on-surface":"#1a1b1e","inverse-surface":"#2f3033","secondary-container":"#d83734","inverse-primary":"#ffb4ab","on-tertiary":"#ffffff","on-error":"#ffffff","surface-variant":"#e3e2e6","on-primary-container":"#ffece9","surface-container-highest":"#e3e2e6","tertiary-container":"#cf2b30","on-background":"#1a1b1e"},"borderRadius":{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},"spacing":{"margin-tablet":"2.5rem","gutter-mobile":"1rem","space-lg":"1.5rem","margin":"1.5rem","space-xl":"2.5rem","gutter-desktop":"2rem","space-md":"1rem","gutter":"1.5rem","space-xs":"0.25rem","space-sm":"0.5rem","margin-mobile":"1.25rem","space-3xl":"6rem","space-2xl":"4rem","margin-desktop":"5rem"},"fontFamily":{"headline-lg-mobile":["Plus Jakarta Sans"],"headline-sm":["Plus Jakarta Sans"],"headline-md-mobile":["Plus Jakarta Sans"],"headline-md":["Plus Jakarta Sans"],"display-hero-mobile":["Plus Jakarta Sans"],"display-hero":["Plus Jakarta Sans"],"body-sm":["Inter"],"label-md":["Inter"],"caption":["Inter"],"label-sm":["Inter"],"body-lg":["Inter"],"headline-lg":["Plus Jakarta Sans"],"title-lg":["Plus Jakarta Sans"],"body-md":["Inter"]},"fontSize":{"headline-lg-mobile":["28px",{"lineHeight":"36px","letterSpacing":"-0.01em","fontWeight":"700"}],"headline-sm":["22px",{"lineHeight":"30px","letterSpacing":"0","fontWeight":"600"}],"headline-md-mobile":["24px",{"lineHeight":"32px","letterSpacing":"0","fontWeight":"600"}],"headline-md":["32px",{"lineHeight":"40px","letterSpacing":"-0.01em","fontWeight":"600"}],"display-hero-mobile":["36px",{"lineHeight":"44px","letterSpacing":"-0.015em","fontWeight":"700"}],"display-hero":["56px",{"lineHeight":"64px","letterSpacing":"-0.02em","fontWeight":"700"}],"body-sm":["14px",{"lineHeight":"20px","fontWeight":"400"}],"label-md":["14px",{"lineHeight":"20px","letterSpacing":"0.01em","fontWeight":"500"}],"caption":["12px",{"lineHeight":"16px","fontWeight":"400"}],"label-sm":["12px",{"lineHeight":"16px","letterSpacing":"0.04em","fontWeight":"600"}],"body-lg":["18px",{"lineHeight":"28px","fontWeight":"400"}],"headline-lg":["40px",{"lineHeight":"48px","letterSpacing":"-0.02em","fontWeight":"700"}],"title-lg":["18px",{"lineHeight":"26px","letterSpacing":"0","fontWeight":"600"}],"body-md":["16px",{"lineHeight":"24px","fontWeight":"400"}]}}}};</script>

<div class="bg-surface font-body-md text-on-surface antialiased selection:bg-primary selection:text-on-primary">

<main class="w-full bg-surface pt-20">
<div class="flex flex-col w-full">

<div class="relative w-full overflow-hidden">
<div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[840px] h-[340px] bg-primary/5 blur-[120px] rounded-full pointer-events-none"></div>
<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop pt-space-md pb-space-xl relative z-10">

<!-- Breadcrumb -->


<!-- Hero Section -->
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-gutter pb-space-lg">
<div class="flex flex-col max-w-3xl">
<div class="flex flex-wrap items-center gap-3 mb-space-sm">
<span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm uppercase tracking-wider">
<span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
NEWSROOM <?= e(strtoupper($companyName)) ?>
</span>
<div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-high text-on-surface-variant font-caption text-caption">
<span class="material-symbols-outlined text-[16px] text-primary">database</span>
<span>Portal Berita Resmi Terkoneksi Database</span>
</div>
</div>
<h1 class="font-headline-lg text-headline-lg-mobile lg:text-headline-lg text-on-surface tracking-tight leading-[1.15]">
Kabar dan <span class="text-primary-container">Cerita Terbaru</span> dari <?= e($companyName) ?>
</h1>
<p class="mt-space-sm font-body-md text-body-md text-on-surface-variant max-w-2xl leading-relaxed">
Ikuti berbagai informasi, perkembangan, aktivitas, dan perjalanan <?= e($companyName) ?> melalui publikasi berita terbaru kami.
</p>
</div>
<div class="shrink-0 bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center gap-4">
<div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
<span class="material-symbols-outlined text-[26px]">newspaper</span>
</div>
<div>
<span class="font-caption text-caption text-on-surface-variant uppercase tracking-wider block">Status Publikasi</span>
<span class="font-title-lg text-title-lg text-on-surface font-bold">Arsip Terverifikasi</span>
</div>
</div>
</div>

<!-- Search & Filter Bar -->
<section class="w-full bg-surface-container-lowest rounded-2xl p-space-md md:p-space-lg shadow-sm mb-space-xl">
<div class="flex flex-col md:flex-row md:items-center justify-between gap-gutter pb-space-sm">
<div class="flex items-center gap-2.5">
<div class="w-7 h-7 rounded-md bg-primary-container text-on-primary flex items-center justify-center">
<span class="material-symbols-outlined text-[18px]">search</span>
</div>
<h2 class="font-title-lg text-title-lg text-on-surface font-bold tracking-tight">Temukan Informasi</h2>
</div>
<div class="inline-flex items-center gap-2 text-body-sm font-body-sm text-on-surface-variant bg-surface-container-low px-3 py-1 rounded-full">
<span class="w-2 h-2 rounded-full bg-emerald-500"></span>
<span class="font-semibold text-on-surface">Ditemukan <?= e($pagination['total']) ?> berita</span>
</div>
</div>
<form method="get" action="<?= e(url('berita.php')) ?>" class="grid grid-cols-1 md:grid-cols-12 gap-3 pt-space-xs">
<div class="md:col-span-8 lg:col-span-9 relative">
<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-on-surface-variant">
<span class="material-symbols-outlined text-[20px]">manage_search</span>
</div>
<input type="search" name="q" value="<?= e($search) ?>" maxlength="200" placeholder="Cari judul berita..." class="w-full pl-10 pr-4 py-3 rounded-xl bg-surface-container-low text-on-surface font-body-md text-body-md placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest transition-all"/>
</div>
<div class="md:col-span-4 lg:col-span-3 flex items-center gap-2">
<button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 font-label-md text-label-md text-on-primary bg-primary-container hover:bg-primary py-3 px-space-md rounded-xl shadow-sm hover:shadow-md transition-all">
<span class="material-symbols-outlined text-[18px]">search</span>
<span>Cari</span>
</button>
<a class="inline-flex items-center justify-center gap-1.5 font-label-md text-label-md text-on-surface hover:text-primary bg-surface-container-low hover:bg-surface-container-high py-3 px-space-md rounded-xl transition-all" href="<?= e(url('berita.php')) ?>">
<span class="material-symbols-outlined text-[18px]">refresh</span>
<span>Reset</span>
</a>
</div>
</form>
</section>

<?php if ($news === []): ?>
<!-- Empty State -->
<div class="w-full bg-surface-container-lowest rounded-2xl p-space-2xl text-center shadow-sm flex flex-col items-center justify-center mb-space-xl">
<div class="w-16 h-16 rounded-2xl bg-surface-container-high text-outline flex items-center justify-center mb-space-md">
<span class="material-symbols-outlined text-[32px]">newspaper</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight">Belum ada berita yang sesuai.</h3>
<p class="font-body-md text-body-md text-on-surface-variant max-w-md mt-2">
Coba ubah kata kunci pencarian atau reset filter untuk melihat semua berita.
</p>
<a class="inline-flex items-center gap-2 mt-space-lg px-space-lg py-3 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-semibold transition-all shadow-md" href="<?= e(url('berita.php')) ?>">
<span class="material-symbols-outlined text-[18px]">restart_alt</span>
<span>Reset Pencarian</span>
</a>
</div>
<?php else: ?>

<?php
$featured = $news[0];
$featuredUrl = url('detail-berita.php?slug=' . rawurlencode($featured['slug']));
$featuredDate = new DateTimeImmutable($featured['published_at'], new DateTimeZone('Asia/Jakarta'));
?>

<!-- Featured News (Berita Pilihan) -->
<section class="w-full mb-space-xl">
<div class="flex items-center justify-between mb-space-md">
<div class="flex items-center gap-2">
<span class="w-1.5 h-5 bg-primary-container rounded-full"></span>
<span class="font-label-sm text-label-sm font-bold text-primary-container uppercase tracking-wider">BERITA PILIHAN</span>
</div>
<span class="font-caption text-caption text-on-surface-variant hidden sm:inline">Sorotan Redaksi Korporat</span>
</div>
<div class="bg-surface-container-lowest rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group">
<div class="grid grid-cols-1 lg:grid-cols-12">
<div class="lg:col-span-7 relative min-h-[280px] sm:min-h-[340px] lg:min-h-[400px] overflow-hidden bg-surface-dim">
<?php if (!empty($featured['foto'])): ?>
<img alt="<?= e($featured['judul']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" src="<?= e(upload_url($featured['foto'])) ?>" loading="lazy"/>
<?php else: ?>
<div class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/30 to-surface-container">
<span class="font-title-lg text-title-lg font-bold text-primary/70"><?= e($companyName) ?></span>
<small class="font-body-sm text-body-sm text-on-surface-variant">Berita</small>
</div>
<?php endif; ?>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent lg:hidden"></div>
<div class="absolute top-4 left-4 z-10">
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-lowest/90 backdrop-blur-md text-on-surface font-label-sm text-label-sm shadow-sm font-semibold">
<span class="w-2 h-2 rounded-full bg-emerald-500"></span>
Terbit
</span>
</div>
</div>
<div class="lg:col-span-5 p-space-lg lg:p-space-xl flex flex-col justify-between bg-surface-container-lowest">
<div>
<div class="flex items-center gap-3 text-caption font-caption text-on-surface-variant mb-space-sm">
<span class="inline-flex items-center gap-1">
<span class="material-symbols-outlined text-[16px] text-primary">calendar_today</span>
<time datetime="<?= e($featuredDate->format(DATE_ATOM)) ?>"><?= e($featuredDate->format('d/m/Y')) ?></time>
</span>
<span>•</span>
<span class="inline-flex items-center gap-1">
<span class="material-symbols-outlined text-[16px] text-primary">schedule</span>
<?= e($featuredDate->format('H:i')) ?> WIB
</span>
</div>
<h3 class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold leading-tight mb-space-sm group-hover:text-primary-container transition-colors">
<a href="<?= e($featuredUrl) ?>"><?= e($featured['judul']) ?></a>
</h3>
<?php if (!empty($featured['ringkasan'])): ?>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-space-md">
<?= e($featured['ringkasan']) ?>
</p>
<?php endif; ?>
</div>
<div class="pt-space-md flex flex-wrap items-center gap-3">
<a class="inline-flex items-center gap-2 font-label-md text-label-md text-on-primary bg-primary-container hover:bg-primary px-space-md py-2.5 rounded-xl shadow-sm hover:shadow-md transition-all" href="<?= e($featuredUrl) ?>">
<span>Baca Selengkapnya</span>
<span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</a>
</div>
</div>
</div>
</div>
</section>

<!-- News Grid -->
<section class="w-full mb-space-xl">
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-space-lg">
<div>
<h2 class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">Berita dan Kegiatan Terbaru</h2>
<p class="font-body-md text-body-md text-on-surface-variant mt-1">Temukan berbagai informasi dan dokumentasi aktivitas <?= e($companyName) ?>.</p>
</div>
<div class="flex items-center gap-2">
<span class="font-caption text-caption text-on-surface-variant">Urutan: Terkini</span>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
<?php foreach ($news as $index => $item): ?>
<?php if ($index === 0) continue; ?>
<?php
$detailUrl = url('detail-berita.php?slug=' . rawurlencode($item['slug']));
$published = new DateTimeImmutable($item['published_at'], new DateTimeZone('Asia/Jakarta'));
?>
<article class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group">
<div class="relative aspect-[16/10] overflow-hidden bg-surface-dim">
<?php if (!empty($item['foto'])): ?>
<img alt="<?= e($item['judul']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="<?= e(upload_url($item['foto'])) ?>" loading="lazy"/>
<?php else: ?>
<div class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/30 to-surface-container">
<span class="font-body-sm text-body-sm text-primary/70">Berita <?= e($companyName) ?></span>
</div>
<?php endif; ?>
<div class="absolute top-3 left-3 flex gap-2">
<span class="px-2.5 py-0.5 rounded-full bg-surface-container-lowest/90 backdrop-blur-md text-on-surface font-caption text-caption font-semibold flex items-center gap-1">
<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
Terbit
</span>
</div>
</div>
<div class="p-space-lg flex flex-col flex-1 justify-between">
<div>
<div class="flex items-center gap-2 text-caption font-caption text-on-surface-variant mb-2">
<span class="material-symbols-outlined text-[15px] text-primary">event</span>
<time datetime="<?= e($published->format(DATE_ATOM)) ?>"><?= e($published->format('d/m/Y')) ?></time>
<span>•</span>
<span><?= e($published->format('H:i')) ?> WIB</span>
</div>
<h3 class="font-title-lg text-title-lg text-on-surface font-bold leading-snug group-hover:text-primary-container transition-colors mb-2">
<a href="<?= e($detailUrl) ?>"><?= e($item['judul']) ?></a>
</h3>
<?php if (!empty($item['ringkasan'])): ?>
<p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-3 leading-relaxed">
<?= e($item['ringkasan']) ?>
</p>
<?php endif; ?>
</div>
<div class="mt-space-md pt-space-sm flex items-center justify-between text-primary font-label-sm text-label-sm font-semibold">
<a class="inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform" href="<?= e($detailUrl) ?>">
Baca Artikel
<span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</a>
</div>
</div>
</article>
<?php endforeach; ?>
</div>
</section>

<!-- Pagination -->
<?php
render_pagination(
    $pagination,
    'berita.php',
    ['q' => $search]
);
?>

<?php endif; ?>

<!-- Brand Statement -->
<section class="w-full rounded-3xl bg-[#A30F18] text-white p-space-lg md:p-space-xl relative overflow-hidden mb-space-xl shadow-xl">
<div class="absolute -right-20 -bottom-20 w-96 h-96 opacity-10 pointer-events-none">
<svg fill="currentColor" viewBox="0 0 200 200">
<polygon points="100,10 190,90 150,190 50,190 10,90"></polygon>
<circle cx="100" cy="100" fill="none" r="70" stroke="white" stroke-width="6"></circle>
</svg>
</div>
<div class="absolute top-0 left-1/3 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
<div class="relative z-10 max-w-3xl flex flex-col">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-white font-label-sm text-label-sm w-fit mb-space-md">
<span class="material-symbols-outlined text-[16px]">campaign</span>
<span>Komitmen Transparansi Korporasi</span>
</div>
<h2 class="font-headline-lg text-headline-lg-mobile lg:text-headline-lg text-white font-bold tracking-tight leading-tight mb-space-sm">
Setiap Perjalanan Memiliki Cerita
</h2>
<p class="font-body-md text-body-md text-white/90 leading-relaxed max-w-2xl mb-space-lg">
Melalui berbagai kegiatan dan publikasi, <?= e($companyName) ?> membagikan perkembangan serta perjalanan perusahaan kepada masyarakat.
</p>
<div class="grid grid-cols-2 sm:grid-cols-3 gap-6 pt-space-xs">
<div>
<span class="font-headline-md text-headline-md font-bold text-white block">100%</span>
<span class="font-caption text-caption text-white/80">Keterbukaan Berita</span>
</div>
<div>
<span class="font-headline-md text-headline-md font-bold text-white block">Multi</span>
<span class="font-caption text-caption text-white/80">Sektor Nasional</span>
</div>
<div>
<span class="font-headline-md text-headline-md font-bold text-white block">24/7</span>
<span class="font-caption text-caption text-white/80">Akses Informasi</span>
</div>
</div>
</div>
</section>

<!-- CTA -->
<section class="w-full bg-surface-container-lowest rounded-3xl p-space-lg md:p-space-xl shadow-sm mb-space-xl">
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-gutter">
<div class="max-w-2xl">
<div class="w-12 h-12 rounded-xl bg-primary/10 text-primary-container flex items-center justify-center mb-space-md">
<span class="material-symbols-outlined text-[28px]">handshake</span>
</div>
<h2 class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
Ingin Mengenal Lebih Dekat <?= e($companyName) ?>?
</h2>
<p class="mt-space-xs font-body-md text-body-md text-on-surface-variant leading-relaxed">
Jelajahi berbagai aktivitas dan informasi perusahaan atau hubungi tim kami untuk mendapatkan informasi lebih lanjut.
</p>
</div>
<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
<a class="inline-flex items-center justify-center gap-2 font-label-md text-label-md text-on-primary bg-primary-container hover:bg-primary px-space-xl py-3.5 rounded-xl shadow-md hover:shadow-lg transition-all text-center" href="<?= e(url('kontak.php')) ?>">
<span class="material-symbols-outlined text-[18px]">mail</span>
<span>Hubungi Kami</span>
</a>
<a class="inline-flex items-center justify-center gap-2 font-label-md text-label-md text-primary hover:bg-primary/5 px-space-xl py-3.5 rounded-xl transition-all text-center bg-surface-container-low" href="<?= e(url('unit-usaha.php')) ?>">
<span class="material-symbols-outlined text-[18px]">business</span>
<span>Jelajahi Unit Usaha</span>
</a>
</div>
</div>
</section>

</div>
</div>

</div>
</main>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
