<?php

declare(strict_types=1);

// Halaman publik harus memuat bootstrap.php sebelum file ini.
$settings = $pdo->query(
    'SELECT * FROM pengaturan WHERE id = 1'
)->fetch() ?: [];

$company = $pdo->query(
    'SELECT * FROM profil_perusahaan WHERE id = 1'
)->fetch() ?: [];

$companyName = $company['nama_perusahaan']
    ?? config('nama_aplikasi');

$pageTitle = $pageTitle
    ?? ($settings['seo_judul'] ?? $companyName);

$pageDescription = $pageDescription
    ?? ($settings['seo_deskripsi'] ?? 'Informasi resmi Moza Group.');

$activePage = $activePage ?? '';

$canonicalUrl = $canonicalUrl ?? url(
    basename($_SERVER['SCRIPT_NAME'] ?? 'index.php') === 'index.php'
        ? ''
        : basename($_SERVER['SCRIPT_NAME'] ?? '')
);

$contactWhatsapp = whatsapp_url(
    $settings['whatsapp'] ?? null,
    'Halo Moza Group, saya ingin mendapatkan informasi lebih lanjut.'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?></title>

    <meta name="description" content="<?= e($pageDescription) ?>">

    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="<?= e($companyName) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">

    <?php if (!empty($settings['hero_foto'])): ?>
        <meta
            property="og:image"
            content="<?= e(upload_url($settings['hero_foto'])) ?>"
        >
    <?php endif; ?>

    <?php if (!empty($settings['favicon'])): ?>
        <link
            rel="icon"
            href="<?= e(upload_url($settings['favicon'])) ?>"
        >
    <?php endif; ?>

    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
    <a class="skip-link" href="#konten">Lewati ke konten</a>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main id="konten">