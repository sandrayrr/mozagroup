<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$slug = input_string($_GET, 'slug');

if ($slug === '' || strlen($slug) > 191) {
    require __DIR__ . '/404.php';
    exit;
}

$statement = $pdo->prepare(
    'SELECT *
     FROM unit_usaha
     WHERE slug = ? AND aktif = 1
     LIMIT 1'
);

$statement->execute([$slug]);

$unit = $statement->fetch();

if (!$unit) {
    require __DIR__ . '/404.php';
    exit;
}

// Produk aktif dari unit ini.
$statement = $pdo->prepare(
    'SELECT nama, slug, ringkasan, foto, harga, keterangan_harga
     FROM produk
     WHERE unit_usaha_id = ? AND aktif = 1
     ORDER BY urutan ASC, id DESC
     LIMIT 6'
);

$statement->execute([$unit['id']]);

$products = $statement->fetchAll();

// Dokumentasi unit ini.
$statement = $pdo->prepare(
    'SELECT judul, foto
     FROM galeri
     WHERE unit_usaha_id = ?
     ORDER BY urutan ASC, id DESC
     LIMIT 6'
);

$statement->execute([$unit['id']]);

$photos = $statement->fetchAll();

$serviceLines = preg_split(
    '/\R/u',
    (string) ($unit['layanan'] ?? '')
) ?: [];

$services = array_values(array_filter(
    array_map('trim', $serviceLines),
    static fn (string $line): bool => $line !== ''
));

$activePage = 'unit-usaha';
$pageTitle = $unit['nama'] . ' | Moza Group';

$pageDescription = !empty($unit['ringkasan'])
    ? $unit['ringkasan']
    : 'Informasi layanan ' . $unit['nama'] . ' dari Moza Group.';

$canonicalUrl = url(
    'detail-unit.php?slug=' . rawurlencode($unit['slug'])
);

require __DIR__ . '/includes/header.php';

$unitWhatsapp = whatsapp_url(
    $unit['whatsapp'],
    'Halo ' . $unit['nama'] . ', saya ingin mengetahui informasi layanan.'
);

// Gunakan kontak perusahaan jika nomor unit belum tersedia.
$unitWhatsapp = $unitWhatsapp ?? $contactWhatsapp;

$website = (string) ($unit['website'] ?? '');

$hasWebsite = $website !== ''
    && validate_url($website) === null;
?>

<section class="page-banner">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= e(url()) ?>">Beranda</a>
            <span aria-hidden="true">/</span>

            <a href="<?= e(url('unit-usaha.php')) ?>">Unit Usaha</a>
            <span aria-hidden="true">/</span>

            <span aria-current="page"><?= e($unit['nama']) ?></span>
        </nav>

        <span class="eyebrow">Unit Usaha</span>
        <h1><?= e($unit['nama']) ?></h1>

        <?php if (!empty($unit['ringkasan'])): ?>
            <p><?= e($unit['ringkasan']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container detail-grid">
        <div>
            <?php if (!empty($unit['foto'])): ?>
                <img
                    class="detail-cover"
                    src="<?= e(upload_url($unit['foto'])) ?>"
                    alt="<?= e($unit['nama']) ?>"
                    width="960"
                    height="600"
                >
            <?php endif; ?>

            <h2 class="section-title">Tentang <?= e($unit['nama']) ?></h2>

            <div class="prose">
                <?= text_to_html(
                    $unit['deskripsi'] ?: $unit['ringkasan']
                ) ?>
            </div>

            <?php if ($services !== []): ?>
                <div class="detail-section">
                    <h2 class="section-title">Layanan Kami</h2>

                    <ul class="service-list">
                        <?php foreach ($services as $service): ?>
                            <li><?= e($service) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <aside class="info-card">
            <?php if (!empty($unit['logo'])): ?>
                <img
                    class="unit-logo"
                    src="<?= e(upload_url($unit['logo'])) ?>"
                    alt="Logo <?= e($unit['nama']) ?>"
                >
            <?php endif; ?>

            <h2>Informasi Unit Usaha</h2>

            <?php if (!empty($unit['alamat'])): ?>
                <div class="info-item">
                    <h3>Alamat</h3>
                    <div><?= text_to_html($unit['alamat']) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($unit['jam_operasional'])): ?>
                <div class="info-item">
                    <h3>Jam Operasional</h3>
                    <p><?= e($unit['jam_operasional']) ?></p>
                </div>
            <?php endif; ?>

            <div class="info-actions">
                <?php if ($unitWhatsapp !== null): ?>
                    <a
                        class="button"
                        href="<?= e($unitWhatsapp) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Hubungi via WhatsApp
                    </a>
                <?php endif; ?>

                <?php if ($hasWebsite): ?>
                    <a
                        class="button button-outline"
                        href="<?= e($website) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Kunjungi Website
                    </a>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</section>

<?php if ($products !== []): ?>
    <section class="section section-soft">
        <div class="container">
            <div class="section-header">
                <div class="section-heading">
                    <span class="eyebrow">Produk dan Paket</span>
                    <h2 class="section-title">Pilihan Layanan</h2>
                </div>

                <a
                    class="text-link"
                    href="<?= e(url(
                        'produk.php?unit_usaha_id=' . $unit['id']
                    )) ?>"
                >
                    Semua Produk Unit Ini →
                </a>
            </div>

            <div class="card-grid">
                <?php foreach ($products as $product): ?>
                    <?php
                    $productUrl = url(
                        'detail-produk.php?slug='
                        . rawurlencode($product['slug'])
                    );
                    ?>

                    <article class="card">
                        <?php if (!empty($product['foto'])): ?>
                            <img
                                class="card-image"
                                src="<?= e(upload_url($product['foto'])) ?>"
                                alt="<?= e($product['nama']) ?>"
                                loading="lazy"
                                width="640"
                                height="400"
                            >
                        <?php endif; ?>

                        <div class="card-body">
                            <h3>
                                <a
                                    class="card-title-link"
                                    href="<?= e($productUrl) ?>"
                                >
                                    <?= e($product['nama']) ?>
                                </a>
                            </h3>

                            <p><?= e($product['ringkasan']) ?></p>

                            <div class="product-price">
                                <?= e(rupiah($product['harga'])) ?>
                            </div>

                            <?php if (!empty($product['keterangan_harga'])): ?>
                                <small class="price-note">
                                    <?= e($product['keterangan_harga']) ?>
                                </small>
                            <?php endif; ?>

                            <a class="text-link" href="<?= e($productUrl) ?>">
                                Detail Produk →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($photos !== []): ?>
    <section class="section">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Dokumentasi</span>
                <h2 class="section-title">Galeri <?= e($unit['nama']) ?></h2>
            </div>

            <div class="card-grid">
                <?php foreach ($photos as $photo): ?>
                    <figure class="card gallery-item">
                        <a
                            href="<?= e(upload_url($photo['foto'])) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <img
                                class="card-image"
                                src="<?= e(upload_url($photo['foto'])) ?>"
                                alt="<?= e($photo['judul']) ?>"
                                loading="lazy"
                                width="640"
                                height="400"
                            >
                        </a>

                        <figcaption class="card-body">
                            <?= e($photo['judul']) ?>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>