<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$activePage = 'beranda';

// Mengubah teks per baris menjadi daftar.
$toList = static function (?string $text): array {
  $lines = preg_split('/\R/u', $text ?? '') ?: [];

  return array_values(array_filter(
    array_map('trim', $lines),
    static fn(string $line): bool => $line !== ''
  ));
};

// Membuat ringkasan tanpa merusak karakter UTF-8.
$homeExcerpt = static function (?string $text, int $limit = 450): string {
  $text = trim($text ?? '');

  if ($text === '') {
    return '';
  }

  if (
    function_exists('mb_strlen')
    && function_exists('mb_substr')
  ) {
    return mb_strlen($text, 'UTF-8') > $limit
      ? mb_substr($text, 0, $limit, 'UTF-8') . '…'
      : $text;
  }

  $characters = preg_split(
    '//u',
    $text,
    -1,
    PREG_SPLIT_NO_EMPTY
  );

  if ($characters === false) {
    return '';
  }

  return count($characters) > $limit
    ? implode('', array_slice($characters, 0, $limit)) . '…'
    : $text;
};

// Unit usaha aktif.
$units = $pdo->query(
  'SELECT
        id, nama, slug, ringkasan, foto, logo,
        layanan, alamat, jam_operasional
     FROM unit_usaha
     WHERE aktif = 1
     ORDER BY urutan ASC, id DESC
     LIMIT 6'
)->fetchAll();

// Produk unggulan diprioritaskan, disusul produk aktif lainnya.
$products = $pdo->query(
  'SELECT
        p.nama,
        p.slug,
        p.ringkasan,
        p.foto,
        p.harga,
        p.keterangan_harga,
        p.unggulan,
        u.nama AS nama_unit
     FROM produk p
     INNER JOIN unit_usaha u ON u.id = p.unit_usaha_id
     WHERE p.aktif = 1 AND u.aktif = 1
     ORDER BY p.unggulan DESC, p.urutan ASC, p.id DESC
     LIMIT 6'
)->fetchAll();

// Berita yang sudah waktunya dipublikasikan.
$statement = $pdo->prepare(
  'SELECT judul, slug, ringkasan, foto, published_at
     FROM berita
     WHERE status = ? AND published_at <= ?
     ORDER BY published_at DESC, id DESC
     LIMIT 3'
);

$statement->execute([
  'terbit',
  date('Y-m-d H:i:s'),
]);

$news = $statement->fetchAll();

// Mitra aktif.
$partners = $pdo->query(
  'SELECT nama, logo, website
     FROM mitra
     WHERE aktif = 1
     ORDER BY urutan ASC, id DESC
     LIMIT 12'
)->fetchAll();

// Foto umum dan dokumentasi unit usaha aktif.
$photos = $pdo->query(
  'SELECT g.judul, g.foto, u.nama AS nama_unit
     FROM galeri g
     LEFT JOIN unit_usaha u ON u.id = g.unit_usaha_id
     WHERE g.unit_usaha_id IS NULL OR u.aktif = 1
     ORDER BY g.urutan ASC, g.id DESC
     LIMIT 4'
)->fetchAll();

$partnership = $pdo->query(
  'SELECT * FROM kemitraan WHERE id = 1'
)->fetch() ?: [];

// Statistik berdasarkan data asli.
$unitCount = (int) $pdo->query(
  'SELECT COUNT(*) FROM unit_usaha WHERE aktif = 1'
)->fetchColumn();

$productCount = (int) $pdo->query(
  'SELECT COUNT(*)
     FROM produk p
     INNER JOIN unit_usaha u ON u.id = p.unit_usaha_id
     WHERE p.aktif = 1 AND u.aktif = 1'
)->fetchColumn();

$partnerCount = (int) $pdo->query(
  'SELECT COUNT(*) FROM mitra WHERE aktif = 1'
)->fetchColumn();

$partnershipTypes = array_slice(
  $toList($partnership['jenis_kerja_sama'] ?? null),
  0,
  4
);

$partnershipBenefits = array_slice(
  $toList($partnership['manfaat'] ?? null),
  0,
  4
);

$partnershipRequirements = array_slice(
  $toList($partnership['persyaratan'] ?? null),
  0,
  4
);

$partnershipSteps = array_slice(
  $toList($partnership['alur_pengajuan'] ?? null),
  0,
  4
);

// Memuat pengaturan, profil perusahaan, dan navbar.
require __DIR__ . '/includes/header.php';

$companyMissions = array_slice(
  $toList($company['misi'] ?? null),
  0,
  5
);

$advantages = [];

for ($i = 1; $i <= 3; $i++) {
  $title = trim(
    (string) ($settings["keunggulan_{$i}_judul"] ?? '')
  );

  if ($title !== '') {
    $advantages[] = [
      'judul' => $title,
      'deskripsi' =>
        $settings["keunggulan_{$i}_deskripsi"] ?? '',
    ];
  }
}

$allowedPaths = [
  'tentang.php',
  'unit-usaha.php',
  'produk.php',
  'kemitraan.php',
  'berita.php',
  'galeri.php',
  'kontak.php',
];

$primaryPath = $settings['hero_tombol_utama_url']
  ?? 'unit-usaha.php';

$secondaryPath = $settings['hero_tombol_kedua_url']
  ?? 'kontak.php';

if (!in_array($primaryPath, $allowedPaths, true)) {
  $primaryPath = 'unit-usaha.php';
}

if (!in_array($secondaryPath, $allowedPaths, true)) {
  $secondaryPath = 'kontak.php';
}

$partnershipMessage = 'Halo Moza Group, saya ingin mengetahui '
  . 'informasi kemitraan.'
  . "\n\nNama: "
  . "\nPerusahaan/Usaha: "
  . "\nJenis kerja sama: "
  . "\nLokasi: ";

$partnershipWhatsapp = whatsapp_url(
  $partnership['whatsapp'] ?? null,
  $partnershipMessage
) ?? whatsapp_url(
  $settings['whatsapp'] ?? null,
  $partnershipMessage
);
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

<div class="bg-surface font-body-md text-on-surface antialiased min-h-screen flex flex-col">

  <main class="w-full pt-20 bg-surface flex-1">
    <div class="flex flex-col w-full">

      <!-- 2. HERO SECTION -->
      <section class="relative w-full bg-surface-container-lowest overflow-hidden py-10 lg:py-16">
        <div
          class="absolute -top-32 right-0 w-[600px] h-[600px] bg-gradient-to-bl from-primary-fixed/30 via-surface-container-low/40 to-transparent rounded-full blur-3xl pointer-events-none">
        </div>
        <div
          class="absolute -bottom-24 left-1/4 w-[450px] h-[450px] bg-primary-fixed/20 rounded-full blur-3xl pointer-events-none">
        </div>
        <div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-6 items-center">
            <div class="lg:col-span-7 flex flex-col items-start gap-6">
              <div
                class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-primary-fixed/60 text-primary font-label-md text-label-md">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                <span class="tracking-wide uppercase font-semibold"><?= e($companyName) ?></span>
              </div>
              <h1
                class="font-display-hero text-headline-lg-mobile lg:text-headline-lg text-on-surface tracking-tight leading-tight">
                <?= e($settings['hero_judul'] ?? 'Selamat Datang di ' . $companyName) ?>
              </h1>
              <?php if (!empty($settings['hero_deskripsi'])): ?>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl leading-relaxed">
                  <?= e($settings['hero_deskripsi']) ?>
                </p>
              <?php endif; ?>
              <div class="flex flex-wrap items-center gap-4 pt-2">
                <a class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-xl bg-primary-container hover:bg-tertiary-container text-on-primary font-label-md text-label-md shadow-lg shadow-primary-container/25 transition-all duration-300 transform hover:-translate-y-0.5"
                  href="<?= e(url($primaryPath)) ?>">
                  <span><?= e($settings['hero_tombol_utama_teks'] ?? 'Jelajahi Unit Usaha') ?></span>
                  <span class="material-symbols-outlined text-[18px]">arrow_downward</span>
                </a>
                <a class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl bg-surface-container-lowest hover:bg-surface-container-low text-primary-container font-label-md text-label-md shadow-sm transition-all duration-300 border border-outline-variant/40"
                  href="<?= e(url($secondaryPath)) ?>">
                  <span class="material-symbols-outlined text-[18px]">support_agent</span>
                  <span><?= e($settings['hero_tombol_kedua_teks'] ?? 'Hubungi Kami') ?></span>
                </a>
              </div>
            </div>
            <div class="lg:col-span-5 relative">
              <div
                class="relative w-full aspect-[4/5] max-w-md mx-auto rounded-3xl overflow-hidden shadow-2xl bg-surface-container">
                <?php if (!empty($settings['hero_foto'])): ?>
                  <img class="w-full h-full object-cover" src="<?= e(upload_url($settings['hero_foto'])) ?>"
                    alt="Kegiatan <?= e($companyName) ?>" fetchpriority="high" />
                <?php else: ?>
                  <div
                    class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/40 to-surface-container">
                    <span class="font-title-lg text-title-lg font-bold text-primary"><?= e($companyName) ?></span>
                    <small class="font-body-sm text-body-sm text-on-surface-variant">Kenali bisnis dan layanan
                      kami</small>
                  </div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-on-surface/80 via-transparent to-transparent"></div>
                <div
                  class="absolute bottom-6 left-6 right-6 p-4 rounded-2xl bg-surface-container-lowest/95 backdrop-blur-md shadow-xl flex items-center gap-3">
                  <div
                    class="w-12 h-12 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-[26px]">verified_user</span>
                  </div>
                  <div class="flex flex-col min-w-0">
                    <span class="font-label-md text-label-md font-bold text-on-surface"><?= e($companyName) ?></span>
                    <span class="font-caption text-caption text-on-surface-variant truncate">Profil perusahaan dan unit
                      usaha</span>
                  </div>
                </div>
                <div
                  class="absolute top-5 right-5 px-3.5 py-1.5 rounded-full bg-primary-container text-on-primary font-label-sm text-label-sm shadow-md">
                  Holding Korporasi
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 3. STATISTIK / OVERVIEW -->
      <section class="w-full bg-surface-container py-8 lg:py-12 border-y border-outline-variant/30">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
          <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
              <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Sekilas
                Perusahaan</span>
              <h2
                class="font-headline-md text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight mt-1">
                Kenali Ruang Lingkup Kami
              </h2>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-md">
              Jejak pertumbuhan terukur yang mencerminkan kapabilitas operasional dan kepercayaan institusi.
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
              class="flex items-center gap-5 p-6 rounded-2xl bg-surface-container-lowest shadow-sm border border-outline-variant/20 hover:border-primary/40 transition-colors">
              <div
                class="w-16 h-16 rounded-2xl bg-primary-fixed/70 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[32px]">domain</span>
              </div>
              <div>
                <span
                  class="font-headline-lg text-headline-md font-extrabold text-primary leading-none block"><?= e($unitCount) ?>+</span>
                <h3 class="font-title-lg text-title-lg font-bold text-on-surface mt-1">Unit Usaha Aktif</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Lini usaha terintegrasi lintas sektor
                  strategis.</p>
              </div>
            </div>
            <div
              class="flex items-center gap-5 p-6 rounded-2xl bg-surface-container-lowest shadow-sm border border-outline-variant/20 hover:border-primary/40 transition-colors">
              <div
                class="w-16 h-16 rounded-2xl bg-primary-fixed/70 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[32px]">inventory_2</span>
              </div>
              <div>
                <span
                  class="font-headline-lg text-headline-md font-extrabold text-primary leading-none block"><?= e($productCount) ?>+</span>
                <h3 class="font-title-lg text-title-lg font-bold text-on-surface mt-1">Produk &amp; Paket Layanan</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Katalog solusi teruji untuk kebutuhan
                  industri.</p>
              </div>
            </div>
            <div
              class="flex items-center gap-5 p-6 rounded-2xl bg-surface-container-lowest shadow-sm border border-outline-variant/20 hover:border-primary/40 transition-colors">
              <div
                class="w-16 h-16 rounded-2xl bg-primary-fixed/70 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[32px]">handshake</span>
              </div>
              <div>
                <span
                  class="font-headline-lg text-headline-md font-extrabold text-primary leading-none block"><?= e($partnerCount) ?>+</span>
                <h3 class="font-title-lg text-title-lg font-bold text-on-surface mt-1">Mitra Perusahaan</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Kolaborasi erat bersama institusi utama.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 4. PROFIL SINGKAT DAN VISI -->
      <section class="w-full bg-surface-container-low py-10 lg:py-16" id="tentang-kami">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="lg:col-span-6 relative">
              <div class="relative rounded-3xl overflow-hidden shadow-xl bg-surface-container aspect-[4/3]">
                <?php if (!empty($company['foto'])): ?>
                  <img class="w-full h-full object-cover" src="<?= e(upload_url($company['foto'])) ?>"
                    alt="Profil <?= e($companyName) ?>" loading="lazy" />
                <?php else: ?>
                  <div
                    class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-primary-fixed/30 to-surface-container">
                    <span class="font-title-lg text-title-lg font-bold text-primary"><?= e($companyName) ?></span>
                    <small class="font-body-sm text-body-sm text-on-surface-variant">Profil Perusahaan</small>
                  </div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-tr from-on-surface/50 via-transparent to-transparent"></div>
              </div>
              <div
                class="absolute -bottom-6 -right-2 sm:right-6 bg-surface-container-lowest p-5 rounded-2xl shadow-xl flex items-center gap-4 max-w-xs border border-outline-variant/30">
                <div
                  class="w-12 h-12 rounded-full bg-primary-container text-on-primary flex items-center justify-center font-bold font-title-lg text-title-lg shrink-0">
                  <span class="material-symbols-outlined">flag</span>
                </div>
                <div>
                  <p class="font-caption text-caption text-outline uppercase font-semibold">Tentang Perusahaan</p>
                  <p class="font-title-lg text-title-lg font-bold text-on-surface"><?= e($companyName) ?></p>
                </div>
              </div>
            </div>
            <div class="lg:col-span-6 flex flex-col items-start gap-4">
              <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Tentang
                Kami</span>
              <h2
                class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface tracking-tight font-bold">
                Mengenal <?= e($companyName) ?>
              </h2>
              <?php if (!empty($company['ringkasan'])): ?>
                <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                  <?= text_to_html($company['ringkasan']) ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($company['visi'])): ?>
                <div class="w-full p-5 rounded-2xl bg-surface-container-lowest border-l-4 border-primary shadow-sm my-1">
                  <span
                    class="font-label-sm text-label-sm uppercase font-bold text-primary tracking-wider block mb-1">Visi
                    Perusahaan</span>
                  <p class="font-body-md text-body-md text-on-surface italic font-medium leading-relaxed">
                    <?= text_to_html($homeExcerpt($company['visi'], 450)) ?>
                  </p>
                </div>
              <?php endif; ?>
              <div class="flex flex-wrap gap-2.5 pt-2">
                <span
                  class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface-container-lowest text-on-surface font-label-sm text-label-sm shadow-sm border border-outline-variant/30">
                  <span class="material-symbols-outlined text-[16px] text-primary">verified</span>
                  Integritas Tinggi
                </span>
                <span
                  class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface-container-lowest text-on-surface font-label-sm text-label-sm shadow-sm border border-outline-variant/30">
                  <span class="material-symbols-outlined text-[16px] text-primary">lightbulb</span>
                  Inovasi Berkelanjutan
                </span>
                <span
                  class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface-container-lowest text-on-surface font-label-sm text-label-sm shadow-sm border border-outline-variant/30">
                  <span class="material-symbols-outlined text-[16px] text-primary">workspace_premium</span>
                  Standar Global
                </span>
              </div>
              <a class="inline-flex items-center gap-2 mt-4 px-6 py-3 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-bold hover:bg-secondary group transition-all shadow-md shadow-primary/20"
                href="<?= e(url('tentang.php')) ?>">
                <span>Profil Selengkapnya</span>
                <span
                  class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </a>
            </div>
          </div>
        </div>
      </section>

      <!-- 5. DESKRIPSI, SEJARAH, DAN MISI -->
      <?php if (!empty($company['deskripsi']) || !empty($company['sejarah']) || $companyMissions !== []): ?>
        <section class="w-full bg-surface-container-lowest py-10 lg:py-16" id="informasi-perusahaan">
          <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="max-w-2xl mx-auto text-center flex flex-col items-center gap-3 mb-10">
              <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Informasi
                Perusahaan</span>
              <h2
                class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
                Fondasi, Perjalanan, dan Tujuan Kami
              </h2>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Menelusuri pilar fundamental yang membentuk dedikasi <?= e($companyName) ?>.
              </p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <?php if (!empty($company['deskripsi'])): ?>
                <div
                  class="p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
                  <div>
                    <div
                      class="w-14 h-14 rounded-2xl bg-primary text-on-primary flex items-center justify-center mb-6 shadow-md shadow-primary/30">
                      <span class="material-symbols-outlined text-[28px]">corporate_fare</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface mb-3">Tentang Perusahaan</h3>
                    <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                      <?= text_to_html($homeExcerpt($company['deskripsi'], 600)) ?>
                    </div>
                  </div>
                  <div class="pt-6 mt-6 border-t border-outline-variant/30">
                    <a class="inline-flex items-center gap-2 text-primary font-label-sm text-label-sm font-semibold"
                      href="<?= e(url('tentang.php')) ?>">
                      <span class="material-symbols-outlined text-[18px]">verified</span>
                      <span>Profil Lengkap →</span>
                    </a>
                  </div>
                </div>
              <?php endif; ?>
              <?php if (!empty($company['sejarah'])): ?>
                <div
                  class="p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
                  <div>
                    <div
                      class="w-14 h-14 rounded-2xl bg-primary-container text-on-primary flex items-center justify-center mb-6 shadow-md shadow-primary-container/30">
                      <span class="material-symbols-outlined text-[28px]">history_edu</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface mb-3">Sejarah Perusahaan</h3>
                    <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                      <?= text_to_html($homeExcerpt($company['sejarah'], 500)) ?>
                    </div>
                  </div>
                  <div class="pt-6 mt-6 border-t border-outline-variant/30">
                    <a class="inline-flex items-center gap-2 text-primary font-label-sm text-label-sm font-semibold"
                      href="<?= e(url('tentang.php')) ?>">
                      <span class="material-symbols-outlined text-[18px]">schedule</span>
                      <span>Baca Selengkapnya →</span>
                    </a>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($companyMissions !== []): ?>
                <div
                  class="p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
                  <div>
                    <div
                      class="w-14 h-14 rounded-2xl bg-tertiary-container text-on-primary flex items-center justify-center mb-6 shadow-md shadow-tertiary-container/30">
                      <span class="material-symbols-outlined text-[28px]">task_alt</span>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface mb-3">Misi Perusahaan</h3>
                    <ul class="font-body-md text-body-md text-on-surface-variant space-y-2.5 leading-relaxed">
                      <?php foreach ($companyMissions as $mission): ?>
                        <li class="flex items-start gap-2">
                          <span class="text-primary font-bold">•</span>
                          <span><?= e($mission) ?></span>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                  <div class="pt-6 mt-6 border-t border-outline-variant/30">
                    <a class="inline-flex items-center gap-2 text-primary font-label-sm text-label-sm font-semibold"
                      href="<?= e(url('tentang.php')) ?>">
                      <span class="material-symbols-outlined text-[18px]">track_changes</span>
                      <span>Visi dan Misi →</span>
                    </a>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <!-- 6. UNIT USAHA -->
      <?php if ($units !== []): ?>
        <section class="w-full bg-surface-container-low py-10 lg:py-16" id="unit-usaha">
          <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
              <div class="max-w-xl">
                <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Ekosistem
                  Bisnis</span>
                <h2
                  class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight mt-1">
                  Unit Usaha Kami
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                  Kenali layanan, lokasi, dan jam operasional unit usaha kami.
                </p>
              </div>
              <a class="inline-flex items-center gap-2 font-label-md text-label-md font-bold text-primary hover:text-secondary group shrink-0"
                href="<?= e(url('unit-usaha.php')) ?>">
                <span>Semua Unit Usaha</span>
                <span
                  class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </a>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <?php foreach ($units as $index => $unit): ?>
                <?php
                $detailUrl = url('detail-unit.php?slug=' . rawurlencode($unit['slug']));
                $unitServices = array_slice($toList($unit['layanan'] ?? null), 0, 3);
                ?>
                <div
                  class="group rounded-3xl bg-surface-container-lowest overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col border border-outline-variant/30">
                  <div class="relative h-60 overflow-hidden bg-surface-container">
                    <?php if (!empty($unit['foto'])): ?>
                      <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        src="<?= e(upload_url($unit['foto'])) ?>" alt="<?= e($unit['nama']) ?>" loading="lazy" />
                    <?php else: ?>
                      <div
                        class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-fixed/30 to-surface-container">
                        <span class="font-title-lg text-title-lg font-bold text-primary/70"><?= e($unit['nama']) ?></span>
                      </div>
                    <?php endif; ?>
                    <div class="absolute top-4 left-4">
                      <span
                        class="px-3.5 py-1 rounded-full bg-primary-container text-on-primary font-label-sm text-label-sm font-bold shadow-md">
                        <?= e(sprintf('%02d', $index + 1)) ?>
                      </span>
                    </div>
                    <?php if (!empty($unit['logo'])): ?>
                      <div class="absolute top-4 right-4">
                        <img class="w-10 h-10 rounded-lg bg-surface-container-lowest/90 backdrop-blur-md p-1.5 object-contain"
                          src="<?= e(upload_url($unit['logo'])) ?>" alt="Logo <?= e($unit['nama']) ?>" loading="lazy" />
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="p-8 flex-1 flex flex-col justify-between gap-6">
                    <div>
                      <h3
                        class="font-headline-sm text-headline-sm font-bold text-on-surface mb-3 group-hover:text-primary transition-colors">
                        <a href="<?= e($detailUrl) ?>"><?= e($unit['nama']) ?></a>
                      </h3>
                      <?php if (!empty($unit['ringkasan'])): ?>
                        <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-4">
                          <?= e($homeExcerpt($unit['ringkasan'], 220)) ?>
                        </p>
                      <?php endif; ?>
                      <div
                        class="space-y-2 py-3 border-y border-outline-variant/30 font-body-sm text-body-sm text-on-surface-variant">
                        <?php if ($unitServices !== []): ?>
                          <div class="flex items-center gap-2"><span
                              class="material-symbols-outlined text-[18px] text-primary">engineering</span><span><strong>Layanan:</strong>
                              <?= e(implode(', ', $unitServices)) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($unit['alamat'])): ?>
                          <div class="flex items-center gap-2"><span
                              class="material-symbols-outlined text-[18px] text-primary">location_on</span><span><strong>Lokasi:</strong>
                              <?= e($homeExcerpt($unit['alamat'], 140)) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($unit['jam_operasional'])): ?>
                          <div class="flex items-center gap-2"><span
                              class="material-symbols-outlined text-[18px] text-primary">schedule</span><span><strong>Operasional:</strong>
                              <?= e($unit['jam_operasional']) ?></span></div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <a class="inline-flex items-center gap-2 text-primary font-label-md text-label-md font-bold group-hover:translate-x-1 transition-all"
                      href="<?= e($detailUrl) ?>">
                      <span>Kenali Unit Usaha</span>
                      <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <!-- 7. KEUNGGULAN -->
      <?php if ($advantages !== []): ?>
        <section class="w-full bg-surface-container-lowest py-10 lg:py-16">
          <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="max-w-2xl mx-auto text-center flex flex-col items-center gap-3">
              <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Keunggulan
                Kami</span>
              <h2
                class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
                <?= e($settings['keunggulan_judul'] ?? 'Mengapa Memilih ' . $companyName . '?') ?>
              </h2>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Kami mengutamakan kualitas, pelayanan, dan hubungan kerja sama yang berkelanjutan.
              </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
              <?php foreach ($advantages as $index => $advantage): ?>
                <?php
                $advantageIcons = ['verified', 'groups', 'handshake'];
                $icon = $advantageIcons[$index] ?? 'verified';
                ?>
                <div
                  class="relative flex flex-col p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                  <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-primary-fixed/80 text-primary flex items-center justify-center">
                      <span class="material-symbols-outlined text-[28px]"
                        style="font-variation-settings: 'FILL' 1;"><?= e($icon) ?></span>
                    </div>
                    <span
                      class="font-display-hero text-2xl font-bold text-outline/30"><?= e(sprintf('%02d', $index + 1)) ?></span>
                  </div>
                  <h3 class="font-title-lg text-title-lg font-bold text-on-surface mb-3"><?= e($advantage['judul']) ?></h3>
                  <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    <?= text_to_html($advantage['deskripsi']) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <!-- 8. PRODUK -->
      <?php if ($products !== []): ?>
        <section class="w-full bg-surface-container-low py-10 lg:py-16" id="produk">
          <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="max-w-2xl mx-auto text-center flex flex-col items-center gap-3 mb-10">
              <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Pilihan untuk
                Anda</span>
              <h2
                class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
                Produk &amp; Paket Layanan
              </h2>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Jelajahi penawaran dari unit usaha kami.
              </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <?php foreach ($products as $product): ?>
                <?php
                $detailUrl = url('detail-produk.php?slug=' . rawurlencode($product['slug']));
                ?>
                <div
                  class="rounded-2xl bg-surface-container-lowest border border-outline-variant/30 shadow-sm hover:shadow-lg transition-all duration-300 p-5 flex flex-col justify-between">
                  <div>
                    <div class="h-44 w-full rounded-xl overflow-hidden mb-4 bg-surface-container">
                      <?php if (!empty($product['foto'])): ?>
                        <img class="w-full h-full object-cover" src="<?= e(upload_url($product['foto'])) ?>"
                          alt="<?= e($product['nama']) ?>" loading="lazy" />
                      <?php else: ?>
                        <div
                          class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-fixed/30 to-surface-container">
                          <span class="font-body-sm text-body-sm text-primary/70"><?= e($product['nama']) ?></span>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                      <span
                        class="px-2.5 py-0.5 rounded-md bg-surface-container text-primary font-label-sm text-label-sm font-semibold"><?= e($product['nama_unit']) ?></span>
                      <span class="font-title-lg text-base font-bold text-primary">
                        <?= e(rupiah($product['harga'])) ?>
                        <?php if (!empty($product['keterangan_harga'])): ?>
                          <span class="text-xs font-normal text-outline"><?= e($product['keterangan_harga']) ?></span>
                        <?php endif; ?>
                      </span>
                    </div>
                    <h4 class="font-title-lg text-title-lg font-bold text-on-surface mb-2"><a
                        href="<?= e($detailUrl) ?>"><?= e($product['nama']) ?></a></h4>
                    <?php if (!empty($product['ringkasan'])): ?>
                      <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                        <?= e($homeExcerpt($product['ringkasan'], 200)) ?>
                      </p>
                    <?php endif; ?>
                  </div>
                  <div class="mt-6 pt-4 border-t border-outline-variant/20">
                    <a class="w-full py-2.5 rounded-xl bg-primary hover:bg-secondary text-on-primary font-label-md text-label-md font-semibold text-center block shadow-sm transition-colors"
                      href="<?= e($detailUrl) ?>">
                      Lihat Detail
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <!-- 9. PANDUAN NAVIGASI / PROSES -->
      <section class="w-full bg-surface-container-lowest py-10 lg:py-16">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
          <div class="max-w-2xl mx-auto text-center flex flex-col items-center gap-3 mb-10">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Mulai dari
              Sini</span>
            <h2
              class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
              Temukan Layanan dengan Mudah
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
              Tiga langkah praktis untuk memulai kolaborasi dan menemukan solusi bisnis yang tepat.
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <div
              class="relative p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30 flex flex-col">
              <div
                class="w-12 h-12 rounded-2xl bg-primary text-on-primary font-title-lg text-title-lg font-bold flex items-center justify-center mb-6 shadow-md shadow-primary/25">
                01
              </div>
              <h3 class="font-title-lg text-title-lg font-bold text-on-surface mb-3">Jelajahi Unit Usaha</h3>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Kenali bisnis dan layanan yang tersedia.
              </p>
              <a class="inline-flex items-center gap-2 text-primary font-label-sm text-label-sm font-semibold mt-4"
                href="<?= e(url('unit-usaha.php')) ?>">
                <span>Lihat Unit Usaha →</span>
              </a>
            </div>
            <div
              class="relative p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30 flex flex-col">
              <div
                class="w-12 h-12 rounded-2xl bg-primary text-on-primary font-title-lg text-title-lg font-bold flex items-center justify-center mb-6 shadow-md shadow-primary/25">
                02
              </div>
              <h3 class="font-title-lg text-title-lg font-bold text-on-surface mb-3">Pilih Produk atau Paket</h3>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Baca detail, harga, dan ketentuan layanan.
              </p>
              <a class="inline-flex items-center gap-2 text-primary font-label-sm text-label-sm font-semibold mt-4"
                href="<?= e(url('produk.php')) ?>">
                <span>Jelajahi Produk →</span>
              </a>
            </div>
            <div
              class="relative p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30 flex flex-col">
              <div
                class="w-12 h-12 rounded-2xl bg-primary text-on-primary font-title-lg text-title-lg font-bold flex items-center justify-center mb-6 shadow-md shadow-primary/25">
                03
              </div>
              <h3 class="font-title-lg text-title-lg font-bold text-on-surface mb-3">Hubungi Tim Terkait</h3>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Tanyakan ketersediaan melalui kontak resmi.
              </p>
              <a class="inline-flex items-center gap-2 text-primary font-label-sm text-label-sm font-semibold mt-4"
                href="<?= e(url('kontak.php')) ?>">
                <span>Informasi Kontak →</span>
              </a>
            </div>
          </div>
        </div>
      </section>

      <!-- 10. KEMITRAAN -->
      <?php if ($partnership !== []): ?>
        <section class="w-full bg-surface-container-low py-10 lg:py-16" id="kemitraan">
          <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-10 mb-10">
              <div class="max-w-2xl">
                <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Peluang Kerja
                  Sama</span>
                <h2
                  class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight mt-1">
                  <?= e($partnership['judul'] ?? 'Kemitraan ' . $companyName) ?>
                </h2>
                <?php if (!empty($partnership['pengantar'])): ?>
                  <div class="font-body-md text-body-md text-on-surface-variant mt-3 leading-relaxed">
                    <?= text_to_html($homeExcerpt($partnership['pengantar'], 600)) ?>
                  </div>
                <?php endif; ?>
              </div>
              <a class="px-8 py-3.5 rounded-full bg-primary-container hover:bg-tertiary-container text-on-primary font-label-md text-label-md font-semibold shadow-lg shadow-primary-container/20 transition-all shrink-0"
                href="<?= e(url('kemitraan.php')) ?>">
                Pelajari Kemitraan
              </a>
            </div>
            <?php if ($partnershipTypes !== []): ?>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($partnershipTypes as $type): ?>
                  <div
                    class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/30 flex flex-col justify-between">
                    <div>
                      <div class="w-12 h-12 rounded-xl bg-primary-fixed text-primary flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-[24px]">hub</span>
                      </div>
                      <h3 class="font-title-lg text-title-lg font-bold text-on-surface mb-2"><?= e($type) ?></h3>
                    </div>
                    <span class="text-primary font-label-sm text-label-sm font-semibold mt-4">Jenis Kerja Sama</span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <?php if (!empty($partnership['cta_judul']) || !empty($partnership['cta_deskripsi'])): ?>
                <div
                  class="p-8 rounded-3xl bg-surface-container-lowest border border-outline-variant/30 flex flex-col items-center text-center gap-3">
                  <div class="w-14 h-14 rounded-2xl bg-primary-fixed text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[28px]">handshake</span>
                  </div>
                  <?php if (!empty($partnership['cta_judul'])): ?>
                    <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface"><?= e($partnership['cta_judul']) ?>
                    </h3>
                  <?php endif; ?>
                  <?php if (!empty($partnership['cta_deskripsi'])): ?>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed max-w-xl">
                      <?= text_to_html($partnership['cta_deskripsi']) ?></p>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </section>
      <?php endif; ?>

      <!-- 11. INFORMASI KERJA SAMA / DETAIL KEMITRAAN -->
      <?php if ($partnershipBenefits !== [] || $partnershipRequirements !== [] || $partnershipSteps !== []): ?>
        <section class="w-full bg-surface-container-lowest py-10 lg:py-16" id="detail-kemitraan">
          <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="max-w-2xl mx-auto text-center flex flex-col items-center gap-3 mb-10">
              <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Informasi Kerja
                Sama</span>
              <h2
                class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
                Persiapkan Langkah Kemitraan Anda
              </h2>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Ketahui panduan komprehensif untuk memulai kemitraan resmi.
              </p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
              <?php if ($partnershipBenefits !== []): ?>
                <div class="p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30">
                  <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-primary text-on-primary flex items-center justify-center">
                      <span class="material-symbols-outlined text-[22px]">card_membership</span>
                    </div>
                    <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Manfaat Kemitraan</h3>
                  </div>
                  <ul class="space-y-3 font-body-sm text-body-sm text-on-surface-variant">
                    <?php foreach ($partnershipBenefits as $benefit): ?>
                      <li class="flex items-start gap-2"><span
                          class="text-primary font-bold">✓</span><span><?= e($benefit) ?></span></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
              <?php if ($partnershipRequirements !== []): ?>
                <div class="p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30">
                  <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-primary text-on-primary flex items-center justify-center">
                      <span class="material-symbols-outlined text-[22px]">assignment_turned_in</span>
                    </div>
                    <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Persyaratan</h3>
                  </div>
                  <ul class="space-y-3 font-body-sm text-body-sm text-on-surface-variant">
                    <?php foreach ($partnershipRequirements as $requirement): ?>
                      <li class="flex items-start gap-2"><span
                          class="text-primary font-bold">✓</span><span><?= e($requirement) ?></span></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
              <?php if ($partnershipSteps !== []): ?>
                <div class="p-8 rounded-3xl bg-surface-container-low border border-outline-variant/30">
                  <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-primary text-on-primary flex items-center justify-center">
                      <span class="material-symbols-outlined text-[22px]">route</span>
                    </div>
                    <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Alur Pengajuan</h3>
                  </div>
                  <ol class="space-y-3 font-body-sm text-body-sm text-on-surface-variant list-none">
                    <?php foreach ($partnershipSteps as $stepIndex => $step): ?>
                      <li class="flex items-start gap-2"><span
                          class="text-primary font-bold"><?= e($stepIndex + 1) ?>.</span><span><?= e($step) ?></span></li>
                    <?php endforeach; ?>
                  </ol>
                </div>
              <?php endif; ?>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-4">
              <?php if ($partnershipWhatsapp !== null): ?>
                <a class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-full bg-primary hover:bg-secondary text-on-primary font-label-md text-label-md font-semibold shadow-lg shadow-primary/20 transition-all"
                  href="<?= e($partnershipWhatsapp) ?>" target="_blank" rel="noopener noreferrer">
                  <span class="material-symbols-outlined text-[20px]">chat</span>
                  <span>Tanyakan Kemitraan</span>
                </a>
              <?php endif; ?>
              <a class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md font-semibold transition-all border border-outline-variant/40"
                href="<?= e(url('kemitraan.php')) ?>">
                <span class="material-symbols-outlined text-[20px]">description</span>
                <span>Informasi Lengkap</span>
              </a>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <!-- 12. MITRA -->
      <?php if ($partners !== []): ?>
        <section class="w-full bg-surface-container-low py-10 lg:py-16 relative overflow-hidden">
          <div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10">
            <div class="max-w-2xl mx-auto text-center flex flex-col items-center gap-3 mb-10">
              <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Relasi
                Perusahaan</span>
              <h2
                class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight">
                Mitra Kami
              </h2>
              <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Tumbuh dan bergerak bersama jaringan institusi terkemuka.
              </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 items-center">
              <?php foreach ($partners as $partner): ?>
                <?php
                $website = (string) ($partner['website'] ?? '');
                $hasWebsite = $website !== '' && validate_url($website) === null;
                ?>
                <div
                  class="h-20 bg-surface-container-lowest rounded-2xl flex items-center justify-center p-4 shadow-sm hover:shadow-md transition-shadow border border-outline-variant/30">
                  <?php if ($hasWebsite): ?>
                    <a href="<?= e($website) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2">
                      <?php if (!empty($partner['logo'])): ?>
                        <img src="<?= e(upload_url($partner['logo'])) ?>" alt="<?= e($partner['nama']) ?>" loading="lazy"
                          class="h-8 w-auto object-contain" />
                      <?php endif; ?>
                      <span
                        class="font-title-lg text-[13px] font-bold text-on-surface tracking-tight leading-tight text-center"><?= e($partner['nama']) ?></span>
                    </a>
                  <?php else: ?>
                    <div class="flex items-center gap-2">
                      <?php if (!empty($partner['logo'])): ?>
                        <img src="<?= e(upload_url($partner['logo'])) ?>" alt="<?= e($partner['nama']) ?>" loading="lazy"
                          class="h-8 w-auto object-contain" />
                      <?php endif; ?>
                      <span
                        class="font-title-lg text-[13px] font-bold text-on-surface tracking-tight leading-tight text-center"><?= e($partner['nama']) ?></span>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <!-- 13. GALERI / DOKUMENTASI -->
      <?php if ($photos !== []): ?>
        <section class="w-full bg-surface-container-lowest py-10 lg:py-16" id="galeri">
          <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
              <div>
                <span
                  class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Dokumentasi</span>
                <h2
                  class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight mt-1">
                  Cerita dalam Gambar
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                  Dokumentasi kegiatan operasional dan kontribusi nyata kami.
                </p>
              </div>
              <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-surface-container-low hover:bg-surface-container text-primary font-label-md text-label-md font-semibold transition-all shrink-0 border border-outline-variant/30"
                href="<?= e(url('galeri.php')) ?>">
                <span>Semua Dokumentasi</span>
                <span class="material-symbols-outlined text-[18px]">photo_library</span>
              </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
              <?php foreach ($photos as $photoIndex => $photo): ?>
                <?php
                $photoSpans = ['md:col-span-8', 'md:col-span-4', 'md:col-span-6', 'md:col-span-6'];
                $photoSpan = $photoSpans[$photoIndex] ?? 'md:col-span-6';
                $photoHeights = ['h-80 lg:h-96', 'h-80 lg:h-96', 'h-72', 'h-72'];
                $photoHeight = $photoHeights[$photoIndex] ?? 'h-72';
                ?>
                <div
                  class="<?= e($photoSpan) ?> group relative rounded-3xl overflow-hidden shadow-md <?= e($photoHeight) ?> bg-surface-container">
                  <a href="<?= e(url('galeri.php')) ?>">
                    <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                      src="<?= e(upload_url($photo['foto'])) ?>" alt="<?= e($photo['judul']) ?>" loading="lazy" />
                    <div
                      class="absolute inset-0 bg-gradient-to-t from-on-surface/90 via-on-surface/30 to-transparent flex flex-col justify-end p-6">
                      <span
                        class="text-primary-fixed font-label-sm text-label-sm uppercase font-semibold"><?= e($photo['nama_unit'] ?? 'Kegiatan Perusahaan') ?></span>
                      <strong
                        class="font-title-lg text-title-lg font-bold text-on-primary mt-1"><?= e($photo['judul']) ?></strong>
                    </div>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <!-- 14. BERITA -->
      <?php if ($news !== []): ?>
        <section class="w-full bg-surface-container-low py-10 lg:py-16" id="berita">
          <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
              <div>
                <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">Kabar
                  Perusahaan</span>
                <h2
                  class="font-headline-lg text-headline-md-mobile lg:text-headline-md text-on-surface font-bold tracking-tight mt-1">
                  Informasi Terbaru
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                  Ikuti perkembangan dan rilis resmi dari <?= e($companyName) ?>.
                </p>
              </div>
              <a class="inline-flex items-center gap-2 font-label-md text-label-md font-bold text-primary hover:text-secondary group shrink-0"
                href="<?= e(url('berita.php')) ?>">
                <span>Semua Berita</span>
                <span
                  class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
              <?php foreach ($news as $item): ?>
                <?php
                $detailUrl = url('detail-berita.php?slug=' . rawurlencode($item['slug']));
                $published = new DateTimeImmutable($item['published_at'], new DateTimeZone('Asia/Jakarta'));
                ?>
                <article
                  class="flex flex-col rounded-3xl overflow-hidden bg-surface-container-lowest border border-outline-variant/30 shadow-sm hover:shadow-xl transition-all duration-300">
                  <div class="relative h-52 bg-surface-container overflow-hidden">
                    <?php if (!empty($item['foto'])): ?>
                      <img class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                        src="<?= e(upload_url($item['foto'])) ?>" alt="<?= e($item['judul']) ?>" loading="lazy" />
                    <?php else: ?>
                      <div
                        class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-fixed/30 to-surface-container">
                        <span class="font-body-sm text-body-sm text-primary/70"><?= e($companyName) ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                      <time class="font-caption text-caption text-outline block"
                        datetime="<?= e($published->format(DATE_ATOM)) ?>">
                        <?= e($published->format('d/m/Y')) ?>
                      </time>
                      <h3
                        class="font-title-lg text-title-lg font-bold text-on-surface mt-2 mb-3 leading-snug hover:text-primary transition-colors">
                        <a href="<?= e($detailUrl) ?>"><?= e($item['judul']) ?></a>
                      </h3>
                      <?php if (!empty($item['ringkasan'])): ?>
                        <p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-3 leading-relaxed">
                          <?= e($homeExcerpt($item['ringkasan'], 200)) ?>
                        </p>
                      <?php endif; ?>
                    </div>
                    <a class="inline-flex items-center gap-2 text-primary font-label-md text-label-md font-bold mt-6 group"
                      href="<?= e($detailUrl) ?>">
                      <span>Baca Selengkapnya</span>
                      <span
                        class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <!-- 15. CTA AKHIR -->
      <section class="w-full py-10 lg:py-16 bg-surface-container-lowest" id="kontak">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
          <div
            class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-tertiary via-primary to-primary-container p-10 md:p-16 lg:p-20 text-center shadow-2xl">
            <svg class="absolute inset-0 w-full h-full opacity-10 pointer-events-none" height="100%" width="100%"
              xmlns="http://www.w3.org/2000/svg">
              <defs>
                <pattern height="30" id="cta-dots" patternUnits="userSpaceOnUse" width="30">
                  <circle cx="2" cy="2" fill="#ffffff" r="2"></circle>
                </pattern>
              </defs>
              <rect fill="url(#cta-dots)" height="100%" width="100%"></rect>
            </svg>
            <div class="relative z-10 max-w-3xl mx-auto flex flex-col items-center gap-6">
              <div
                class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/15 backdrop-blur-sm text-on-primary font-label-sm text-label-sm uppercase tracking-wider font-semibold">
                Hubungi Kami
              </div>
              <h2
                class="font-headline-lg text-headline-lg-mobile md:text-headline-lg font-bold text-on-primary tracking-tight">
                <?= e($settings['cta_judul'] ?? 'Mari Terhubung dengan ' . $companyName) ?>
              </h2>
              <?php if (!empty($settings['cta_deskripsi'])): ?>
                <p class="font-body-lg text-body-lg text-on-primary-container max-w-2xl leading-relaxed">
                  <?= text_to_html($settings['cta_deskripsi']) ?>
                </p>
              <?php endif; ?>
              <div class="pt-4 flex flex-col items-center gap-4">
                <?php if ($contactWhatsapp !== null): ?>
                  <a class="inline-flex items-center gap-3 px-8 py-4 rounded-full bg-white text-primary hover:bg-surface hover:scale-105 font-title-lg text-title-lg font-bold shadow-2xl transition-all duration-300"
                    href="<?= e($contactWhatsapp) ?>" target="_blank" rel="noopener noreferrer">
                    <svg class="w-6 h-6 fill-current text-primary" viewBox="0 0 24 24">
                      <path
                        d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.764.792 2.791.792h.001c3.18 0 5.768-2.586 5.768-5.766 0-3.18-2.587-5.778-5.769-5.778zm7.392 5.767c0 4.079-3.313 7.393-7.393 7.393-.996 0-2.18-.328-3.219-.785l-4.811 1.258 1.284-4.693c-.563-1.096-.948-2.09-.948-3.173 0-4.08 3.314-7.394 7.394-7.394 4.079 0 7.293 3.314 7.293 7.394zm-3.539 2.062c-.172-.086-.998-.492-1.152-.549-.155-.057-.268-.086-.381.086-.113.172-.437.549-.537.663-.1.115-.199.129-.371.043-.172-.086-.725-.267-1.381-.852-.511-.456-.856-1.02-.956-1.192-.1-.172-.011-.265.076-.351.077-.077.172-.2.258-.3.086-.1.115-.172.172-.286.057-.114.029-.215-.014-.301-.043-.086-.381-.918-.523-1.259-.138-.332-.278-.287-.381-.292h-.326c-.114 0-.301.043-.459.215-.157.172-.601.587-.601 1.432 0 .845.615 1.662.702 1.777.086.115 1.21 1.847 2.932 2.59 1.722.744 1.722.496 2.032.467.31-.029 1.002-.409 1.144-.805.143-.396.143-.736.1-.808-.043-.072-.158-.115-.33-.201z">
                      </path>
                    </svg>
                    <span><?= e($settings['cta_tombol_teks'] ?? 'Hubungi via WhatsApp') ?></span>
                  </a>
                <?php else: ?>
                  <a class="inline-flex items-center gap-3 px-8 py-4 rounded-full bg-white text-primary hover:bg-surface hover:scale-105 font-title-lg text-title-lg font-bold shadow-2xl transition-all duration-300"
                    href="<?= e(url('kontak.php')) ?>">
                    <span class="material-symbols-outlined text-[24px]">arrow_forward</span>
                    <span>Lihat Kontak</span>
                  </a>
                <?php endif; ?>
                <p class="font-caption text-caption text-on-primary-container/80 flex items-center gap-1.5 pt-1">
                  <span class="material-symbols-outlined text-[16px]">schedule</span>
                  <span>Respon cepat dalam jam kerja operasional</span>
                </p>
              </div>
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