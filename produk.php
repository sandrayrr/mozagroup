<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$activePage = 'produk';
$pageTitle = 'Produk dan Layanan | Moza Group';
$pageDescription = 'Temukan produk dan paket layanan dari unit usaha Moza Group.';
$canonicalUrl = url('produk.php');

$search = input_string($_GET, 'q');
$unitId = positive_id($_GET['unit_usaha_id'] ?? null);

if (text_length($search) > 150) {
    $search = '';
}

$units = $pdo->query(
    'SELECT id, nama
     FROM unit_usaha
     WHERE aktif = 1
     ORDER BY urutan ASC, nama ASC'
)->fetchAll();

$validUnitIds = array_map(
    static fn (array $unit): int => (int) $unit['id'],
    $units
);

// Filter unit yang tidak tersedia menghasilkan daftar kosong.
$invalidUnit = $unitId !== null
    && !in_array($unitId, $validUnitIds, true);

$conditions = [
    'p.aktif = 1',
    'u.aktif = 1',
];

$params = [];

if ($invalidUnit) {
    $conditions[] = '1 = 0';
} elseif ($unitId !== null) {
    $conditions[] = 'p.unit_usaha_id = ?';
    $params[] = $unitId;
}

if ($search !== '') {
    $conditions[] = 'LOCATE(?, p.nama) > 0';
    $params[] = $search;
}

$where = ' WHERE ' . implode(' AND ', $conditions);

$statement = $pdo->prepare(
    'SELECT COUNT(*)
     FROM produk p
     INNER JOIN unit_usaha u ON u.id = p.unit_usaha_id'
    . $where
);

$statement->execute($params);

$pagination = pagination(
    (int) $statement->fetchColumn(),
    9
);

$statement = $pdo->prepare(
    'SELECT
        p.nama,
        p.slug,
        p.ringkasan,
        p.foto,
        p.harga,
        p.keterangan_harga,
        u.nama AS nama_unit
     FROM produk p
     INNER JOIN unit_usaha u ON u.id = p.unit_usaha_id'
    . $where
    . ' ORDER BY p.urutan ASC, p.id DESC
        LIMIT ? OFFSET ?'
);

$position = 1;

foreach ($params as $value) {
    $statement->bindValue(
        $position++,
        $value,
        is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
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

$products = $statement->fetchAll();

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

<main class="w-full pt-20 bg-surface">
<div class="flex flex-col w-full">

<!-- BREADCRUMB & HERO SECTION -->
<section class="w-full bg-surface py-space-lg lg:py-space-xl">
<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
<nav aria-label="Breadcrumb" class="flex items-center gap-2 mb-space-lg text-body-sm font-body-sm text-on-surface-variant">

</nav>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-center">
<div class="lg:col-span-7 flex flex-col gap-space-md">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 w-fit">
<span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
<span class="font-label-sm text-label-sm font-semibold tracking-wider text-primary uppercase">PRODUK &amp; LAYANAN</span>
</div>
<h1 class="font-headline-lg text-headline-lg-mobile lg:text-headline-lg font-bold text-on-surface tracking-tight leading-tight">
Temukan Produk dan Layanan dari <span class="text-primary-container"><?= e($companyName) ?></span>
</h1>
<p class="font-body-md text-body-md text-on-surface-variant max-w-xl leading-relaxed">
Jelajahi berbagai produk dan paket layanan dari unit usaha <?= e($companyName) ?>. Kami menghadirkan pilihan yang dirancang untuk memenuhi beragam kebutuhan pelanggan.
</p>
<div class="flex flex-wrap items-center gap-3 pt-space-xs">
<div class="flex items-center gap-2 px-space-md py-2.5 rounded-xl bg-surface-container-lowest shadow-sm">
<span class="material-symbols-outlined text-primary-container text-[20px]">verified</span>
<span class="font-label-sm text-label-sm text-on-surface font-semibold">Katalog Terverifikasi</span>
</div>
<div class="flex items-center gap-2 px-space-md py-2.5 rounded-xl bg-surface-container-lowest shadow-sm">
<span class="material-symbols-outlined text-primary-container text-[20px]">payments</span>
<span class="font-label-sm text-label-sm text-on-surface font-semibold">Transparansi Harga</span>
</div>
<div class="flex items-center gap-2 px-space-md py-2.5 rounded-xl bg-surface-container-lowest shadow-sm">
<span class="material-symbols-outlined text-primary-container text-[20px]">hub</span>
<span class="font-label-sm text-label-sm text-on-surface font-semibold">Koneksi Langsung Unit</span>
</div>
</div>
</div>
<div class="lg:col-span-5 relative">
<div class="relative rounded-2xl overflow-hidden bg-surface-container-lowest shadow-xl group">
<div class="aspect-[4/3] w-full overflow-hidden bg-surface-container relative">
<?php if (!empty($settings['hero_foto'])): ?>
<img alt="Showcase Portofolio Produk <?= e($companyName) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" src="<?= e(upload_url($settings['hero_foto'])) ?>"/>
<?php else: ?>
<div class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/30 to-surface-container">
<span class="font-title-lg text-title-lg font-bold text-primary"><?= e($companyName) ?></span>
<small class="font-body-sm text-body-sm text-on-surface-variant">Katalog Produk &amp; Layanan</small>
</div>
<?php endif; ?>
<div class="absolute inset-0 bg-gradient-to-t from-on-surface/60 via-transparent to-transparent"></div>
<div class="absolute bottom-3 left-4 right-4 flex items-center justify-between text-on-primary">
<span class="font-caption text-caption tracking-wider uppercase font-semibold bg-primary-container/90 px-2.5 py-1 rounded-md backdrop-blur-sm">Official Release</span>
<span class="font-caption text-caption text-surface-container-lowest/90 font-medium">Katalog <?= e(date('Y')) ?></span>
</div>
</div>
<div class="p-space-md bg-surface-container-lowest flex items-center justify-between">
<div class="flex flex-col">
<span class="font-title-lg text-title-lg font-bold text-on-surface">Showcase Portofolio Strategis</span>
<span class="font-caption text-caption text-on-surface-variant"><?= e($companyName) ?> Integrated Enterprise Framework</span>
</div>
<span class="material-symbols-outlined text-primary-container text-[24px]">verified_user</span>
</div>
</div>
<div class="absolute -bottom-4 -right-4 w-32 h-32 bg-primary/10 rounded-full blur-2xl -z-10 pointer-events-none"></div>
</div>
</div>
</div>
</section>

<!-- SEARCH & FILTER SECTION -->
<section class="w-full bg-surface-container-low py-space-lg">
<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
<div class="bg-surface-container-lowest rounded-2xl p-space-lg shadow-sm">
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-lg mb-space-md">
<div>
<h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Temukan Produk</h2>
<p class="font-body-sm text-body-sm text-on-surface-variant">Saring katalog berdasarkan kata kunci atau unit bisnis holding.</p>
</div>
<div class="flex items-center gap-3 self-start lg:self-center">
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm">
<span class="w-2 h-2 rounded-full bg-primary-container"></span>
Ditemukan <?= e($pagination['total']) ?> produk
</span>
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm font-semibold">
<span class="material-symbols-outlined text-[14px]">sync</span>
Sinkronisasi Database Terkini
</span>
</div>
</div>
<form method="get" action="<?= e(url('produk.php')) ?>" class="flex flex-col md:flex-row items-stretch md:items-center gap-3 mb-space-md">
<div class="relative flex-1">
<span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
<input type="search" name="q" value="<?= e($search) ?>" maxlength="150" placeholder="Cari nama produk..." class="w-full pl-11 pr-4 py-3 rounded-xl bg-surface-container-low text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container transition-all"/>
</div>
<select name="unit_usaha_id" class="px-4 py-3 rounded-xl bg-surface-container-low text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary-container transition-all border-0 cursor-pointer">
<option value="">Semua Unit Usaha</option>
<?php foreach ($units as $unit): ?>
<option value="<?= e($unit['id']) ?>" <?= $unitId === (int) $unit['id'] ? 'selected' : '' ?>>
<?= e($unit['nama']) ?>
</option>
<?php endforeach; ?>
</select>
<button type="submit" class="inline-flex items-center justify-center gap-2 px-space-xl py-3 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-semibold transition-all duration-200 shadow-sm">
<span class="material-symbols-outlined text-[18px]">search</span>
<span>Cari</span>
</button>
<a class="inline-flex items-center justify-center gap-2 px-space-md py-3 rounded-xl bg-surface-container-high hover:bg-surface-variant text-on-surface font-label-md text-label-md font-semibold transition-all duration-200" href="<?= e(url('produk.php')) ?>">
<span class="material-symbols-outlined text-[18px]">restart_alt</span>
<span>Reset</span>
</a>
</form>
</div>
</div>
</section>

<!-- KATALOG PRODUK SECTION -->
<section class="w-full bg-surface py-space-xl">
<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-space-sm mb-space-lg">
<div>
<span class="font-label-sm text-label-sm text-primary font-bold uppercase tracking-wider">Katalog Terdaftar</span>
<h2 class="font-headline-md text-headline-md-mobile lg:text-headline-md font-bold text-on-surface mt-1">Jelajahi Produk Kami</h2>
<p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mt-1">
Temukan berbagai pilihan produk dan layanan dari unit usaha <?= e($companyName) ?>.
</p>
</div>
<div class="hidden sm:flex items-center gap-2 text-caption font-caption text-on-surface-variant">
<span class="material-symbols-outlined text-[16px] text-primary">info</span>
Data terintegrasi secara terpusat
</div>
</div>

<?php if ($products === []): ?>
<!-- Empty State -->
<div class="w-full bg-surface-container-lowest rounded-2xl p-space-2xl text-center shadow-sm flex flex-col items-center justify-center">
<div class="w-16 h-16 rounded-2xl bg-surface-container-high text-outline flex items-center justify-center mb-space-md">
<span class="material-symbols-outlined text-[32px]">inventory_2</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-bold tracking-tight">Tidak ada produk yang sesuai.</h3>
<p class="font-body-md text-body-md text-on-surface-variant max-w-md mt-2">
Coba ubah pencarian atau filter unit usaha.
</p>
<a class="inline-flex items-center gap-2 mt-space-lg px-space-lg py-3 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-semibold transition-all shadow-md" href="<?= e(url('produk.php')) ?>">
<span class="material-symbols-outlined text-[18px]">restart_alt</span>
<span>Reset Filter</span>
</a>
</div>
<?php else: ?>
<!-- 3-Column Responsive Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter-desktop">
<?php foreach ($products as $index => $product): ?>
<?php
$detailUrl = url('detail-produk.php?slug=' . rawurlencode($product['slug']));
?>
<div class="flex flex-col bg-surface-container-lowest rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 group">
<div class="h-1.5 w-full bg-primary-container"></div>
<div class="relative aspect-[4/3] w-full overflow-hidden bg-surface-container">
<?php if (!empty($product['foto'])): ?>
<img alt="<?= e($product['nama']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" src="<?= e(upload_url($product['foto'])) ?>" loading="lazy"/>
<?php else: ?>
<div class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/30 to-surface-container">
<span class="font-title-lg text-title-lg font-bold text-primary/70"><?= e($product['nama']) ?></span>
</div>
<?php endif; ?>
<div class="absolute top-3 left-3 flex flex-wrap gap-2">
<span class="px-2.5 py-1 rounded-full bg-primary-container/95 text-on-primary font-label-sm text-label-sm font-semibold tracking-wide backdrop-blur-sm shadow-sm">Produk Aktif</span>
<span class="px-2.5 py-1 rounded-full bg-surface-container-lowest/90 text-on-surface font-label-sm text-label-sm font-semibold backdrop-blur-sm shadow-sm">Urutan #<?= e(sprintf('%02d', $index + 1)) ?></span>
</div>
<div class="absolute bottom-3 left-3 right-3 flex justify-between items-center">
<span class="px-3 py-1 rounded-md bg-on-surface/80 text-on-primary font-caption text-caption backdrop-blur-md flex items-center gap-1.5">
<span class="material-symbols-outlined text-[14px]">corporate_fare</span>
Unit: <?= e($product['nama_unit']) ?>
</span>
</div>
</div>
<div class="flex flex-col flex-1 p-space-lg justify-between gap-space-md">
<div>
<div class="flex items-center justify-between text-on-surface-variant font-caption text-caption mb-1">
<span>Unit: <strong class="text-on-surface font-semibold"><?= e($product['nama_unit']) ?></strong></span>
<span class="text-primary font-medium">Verified SKU</span>
</div>
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface tracking-tight group-hover:text-primary transition-colors">
<a href="<?= e($detailUrl) ?>"><?= e($product['nama']) ?></a>
</h3>
<div class="mt-space-sm mb-space-sm p-space-sm rounded-xl bg-surface-container-low flex flex-col">
<span class="font-caption text-caption text-on-surface-variant font-medium">Harga Resmi:</span>
<div class="flex items-baseline gap-1.5 mt-0.5">
<span class="font-headline-md text-headline-sm font-bold text-primary-container"><?= e(rupiah($product['harga'])) ?></span>
<?php if (!empty($product['keterangan_harga'])): ?>
<span class="font-caption text-caption text-on-surface-variant">/ <?= e($product['keterangan_harga']) ?></span>
<?php endif; ?>
</div>
</div>
<?php if (!empty($product['ringkasan'])): ?>
<p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-2 leading-relaxed">
<?= e($product['ringkasan']) ?>
</p>
<?php endif; ?>
</div>
<div class="flex flex-col gap-2 pt-space-xs">
<a class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-semibold transition-all shadow-[0_2px_8px_rgba(215,25,32,0.25)] hover:shadow-[0_4px_16px_rgba(215,25,32,0.35)]" href="<?= e($detailUrl) ?>">
<span class="material-symbols-outlined text-[18px]">north_east</span>
<span>Detail Produk</span>
</a>
<a class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-medium transition-colors" href="<?= e(url('kontak.php')) ?>">
<span>Hubungi Kami</span>
<span class="material-symbols-outlined text-[16px] text-primary">arrow_forward</span>
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
    'produk.php',
    [
        'q' => $search,
        'unit_usaha_id' => $unitId ?? '',
    ]
);
?>
<?php endif; ?>
</div>
</section>

<!-- SPESIFIKASI & KONSULTASI SECTION -->
<section class="w-full bg-surface-container-low py-space-lg">
<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
<div class="bg-surface-container-lowest rounded-2xl p-space-lg shadow-sm">
<div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-space-lg mb-space-lg">
<div class="max-w-xl">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm font-semibold mb-2">
<span class="material-symbols-outlined text-[14px]">checklist</span>
PROTOKOL TRANSAKSI RESMI
</div>
<h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Spesifikasi &amp; Konsultasi Langsung</h2>
<p class="font-body-md text-body-md text-on-surface-variant mt-1">
Alur pemesanan dan verifikasi produk melalui komunikasi resmi unit usaha tanpa perantara untuk kepastian harga dan ketersediaan.
</p>
</div>
<div class="flex items-center gap-3">
<div class="px-4 py-2 rounded-xl bg-surface-container text-on-surface font-label-sm text-label-sm flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-[18px]">security</span>
Enkripsi Transaksi Langsung
</div>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter-desktop">
<div class="p-space-lg rounded-xl bg-surface-container-low flex flex-col gap-3 relative">
<span class="font-headline-lg text-headline-lg font-bold text-surface-variant select-none">01</span>
<h4 class="font-title-lg text-title-lg font-bold text-on-surface -mt-4">Pilih Produk &amp; SKU</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant">
Tentukan produk dari etalase terverifikasi lalu klik tombol Detail Produk untuk informasi lengkap.
</p>
</div>
<div class="p-space-lg rounded-xl bg-surface-container-low flex flex-col gap-3 relative">
<span class="font-headline-lg text-headline-lg font-bold text-surface-variant select-none">02</span>
<h4 class="font-title-lg text-title-lg font-bold text-on-surface -mt-4">Verifikasi Unit Usaha</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant">
Pelajari detail produk dan hubungi tim unit usaha terkait untuk konfirmasi ketersediaan dan dokumen resmi.
</p>
</div>
<div class="p-space-lg rounded-xl bg-surface-container-low flex flex-col gap-3 relative">
<span class="font-headline-lg text-headline-lg font-bold text-surface-variant select-none">03</span>
<h4 class="font-title-lg text-title-lg font-bold text-on-surface -mt-4">Penerbitan Invoice</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant">
Unit usaha menerbitkan bukti pemesanan formal dan penagihan berizin di bawah pengawasan <?= e($companyName) ?>.
</p>
</div>
</div>
</div>
</div>
</section>

<!-- BRAND STATEMENT SECTION -->
<section class="w-full bg-tertiary text-on-tertiary py-space-xl relative overflow-hidden">
<div class="absolute inset-0 pointer-events-none opacity-10 flex items-center justify-end">
<svg class="w-full h-full max-w-2xl" fill="none" height="600" viewBox="0 0 600 600" width="600">
<circle cx="300" cy="300" r="250" stroke="currentColor" stroke-width="2"></circle>
<circle cx="300" cy="300" r="180" stroke="currentColor" stroke-dasharray="8 8" stroke-width="1.5"></circle>
<line stroke="currentColor" stroke-width="1.5" x1="50" x2="550" y1="300" y2="300"></line>
<line stroke="currentColor" stroke-width="1.5" x1="300" x2="300" y1="50" y2="550"></line>
</svg>
</div>
<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop relative z-10">
<div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-space-xl">
<div class="max-w-2xl">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-on-tertiary/15 text-on-tertiary font-label-sm text-label-sm uppercase tracking-wider font-semibold mb-4">
Komitmen Kualitas &amp; Standarisasi
</div>
<h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg font-bold text-on-tertiary tracking-tight leading-tight">
Beragam Produk, Beragam Solusi
</h2>
<p class="font-body-md text-body-md text-tertiary-fixed mt-4 leading-relaxed">
<?= e($companyName) ?> menghadirkan berbagai pilihan produk dan layanan melalui unit usaha yang terus berkembang demi memberikan nilai tambah maksimal bagi para mitra dan klien nasional.
</p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-4 w-full lg:w-80 shrink-0">
<div class="p-4 rounded-xl bg-on-tertiary/10 backdrop-blur-sm flex items-center gap-3.5">
<div class="w-10 h-10 rounded-lg bg-on-tertiary/20 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-on-tertiary text-[22px]">verified</span>
</div>
<div>
<span class="font-title-lg text-title-lg font-bold text-on-tertiary block leading-snug">Kualitas Terstandarisasi</span>
<span class="font-caption text-caption text-tertiary-fixed">Pengujian mutu berkala</span>
</div>
</div>
<div class="p-4 rounded-xl bg-on-tertiary/10 backdrop-blur-sm flex items-center gap-3.5">
<div class="w-10 h-10 rounded-lg bg-on-tertiary/20 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-on-tertiary text-[22px]">support_agent</span>
</div>
<div>
<span class="font-title-lg text-title-lg font-bold text-on-tertiary block leading-snug">Dukungan Langsung Unit</span>
<span class="font-caption text-caption text-tertiary-fixed">Respons operasional sigap</span>
</div>
</div>
<div class="p-4 rounded-xl bg-on-tertiary/10 backdrop-blur-sm flex items-center gap-3.5">
<div class="w-10 h-10 rounded-lg bg-on-tertiary/20 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-on-tertiary text-[22px]">gavel</span>
</div>
<div>
<span class="font-title-lg text-title-lg font-bold text-on-tertiary block leading-snug">Tata Kelola Transparan</span>
<span class="font-caption text-caption text-tertiary-fixed">Kepatuhan regulasi komersial</span>
</div>
</div>
</div>
</div>
</div>
</section>

<!-- CTA SECTION -->
<section class="w-full bg-surface py-space-xl">
<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-tablet lg:px-margin-desktop">
<div class="bg-surface-container-lowest rounded-2xl p-space-lg lg:p-space-xl shadow-xl flex flex-col lg:flex-row items-center justify-between gap-space-xl">
<div class="max-w-2xl text-left">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm font-semibold mb-3">
<span class="material-symbols-outlined text-[14px]">contact_support</span>
BANTUAN &amp; KONSULTASI PENGADAAN
</div>
<h2 class="font-headline-md text-headline-md-mobile lg:text-headline-md font-bold text-on-surface">
Butuh Informasi Lebih Lanjut?
</h2>
<p class="font-body-md text-body-md text-on-surface-variant mt-2 leading-relaxed">
Tim <?= e($companyName) ?> siap membantu Anda menemukan informasi lengkap mengenai produk, spesifikasi paket, dan opsi kemitraan pengadaan yang tersedia.
</p>
</div>
<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
<a class="inline-flex items-center justify-center gap-2 px-space-xl py-3.5 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md font-semibold transition-all shadow-[0_2px_12px_rgba(215,25,32,0.3)] hover:shadow-[0_4px_20px_rgba(215,25,32,0.4)]" href="<?= e(url('kontak.php')) ?>">
<span class="material-symbols-outlined text-[20px]">call</span>
<span>Hubungi Kami</span>
</a>
<a class="inline-flex items-center justify-center gap-2 px-space-lg py-3.5 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-semibold transition-colors" href="<?= e(url('unit-usaha.php')) ?>">
<span class="material-symbols-outlined text-[20px]">arrow_back</span>
<span>Kembali ke Unit Usaha</span>
</a>
</div>
</div>
</div>
</section>

</div>
</main>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
