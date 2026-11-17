<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$slug = input_string($_GET, 'slug');

if ($slug === '' || strlen($slug) > 191) {
    require __DIR__ . '/404.php';
    exit;
}

$statement = $pdo->prepare(
    'SELECT
        p.*,
        u.nama AS nama_unit,
        u.slug AS slug_unit,
        u.whatsapp AS whatsapp_unit
     FROM produk p
     INNER JOIN unit_usaha u ON u.id = p.unit_usaha_id
     WHERE p.slug = ?
       AND p.aktif = 1
       AND u.aktif = 1
     LIMIT 1'
);

$statement->execute([$slug]);

$product = $statement->fetch();

if (!$product) {
    require __DIR__ . '/404.php';
    exit;
}

$activePage = 'produk';
$pageTitle = $product['nama'] . ' | Moza Group';

$pageDescription = !empty($product['ringkasan'])
    ? $product['ringkasan']
    : 'Informasi produk ' . $product['nama']
        . ' dari ' . $product['nama_unit'] . '.';

$canonicalUrl = url(
    'detail-produk.php?slug=' . rawurlencode($product['slug'])
);

require __DIR__ . '/includes/header.php';

$message = 'Halo ' . $product['nama_unit']
    . ', saya tertarik dengan produk "' . $product['nama']
    . '". Mohon informasi dan cara pemesanannya.'
    . "\n\n" . $canonicalUrl;

// Gunakan nomor unit, lalu nomor perusahaan jika belum tersedia.
$orderWhatsapp = whatsapp_url(
    $product['whatsapp_unit'],
    $message
) ?? whatsapp_url(
    $settings['whatsapp'] ?? null,
    $message
);
?>

<section class="page-banner">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= e(url()) ?>">Beranda</a>
            <span aria-hidden="true">/</span>

            <a href="<?= e(url('produk.php')) ?>">Produk</a>
            <span aria-hidden="true">/</span>

            <span aria-current="page">
                <?= e($product['nama']) ?>
            </span>
        </nav>

        <span class="eyebrow"><?= e($product['nama_unit']) ?></span>

        <h1><?= e($product['nama']) ?></h1>

        <?php if (!empty($product['ringkasan'])): ?>
            <p><?= e($product['ringkasan']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container detail-grid">
        <div>
            <?php if (!empty($product['foto'])): ?>
                <img
                    class="detail-cover product-detail-image"
                    src="<?= e(upload_url($product['foto'])) ?>"
                    alt="<?= e($product['nama']) ?>"
                >
            <?php else: ?>
                <div class="detail-cover image-placeholder product-placeholder">
                    <?= e($product['nama']) ?>
                </div>
            <?php endif; ?>

            <h2 class="section-title">Detail Produk</h2>

            <div class="prose">
                <?php if (!empty($product['deskripsi'])): ?>
                    <?= text_to_html($product['deskripsi']) ?>
                <?php elseif (!empty($product['ringkasan'])): ?>
                    <?= text_to_html($product['ringkasan']) ?>
                <?php else: ?>
                    <p>
                        Hubungi kami untuk informasi lebih lanjut
                        mengenai produk ini.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <aside class="info-card">
            <span class="eyebrow">Informasi Produk</span>

            <h2><?= e($product['nama']) ?></h2>

            <div class="info-item">
                <h3>Harga</h3>

                <div class="product-price">
                    <?= e(rupiah($product['harga'])) ?>
                </div>

                <?php if (!empty($product['keterangan_harga'])): ?>
                    <small class="price-note">
                        <?= e($product['keterangan_harga']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="info-item">
                <h3>Unit Usaha</h3>

                <a
                    class="text-link"
                    href="<?= e(url(
                        'detail-unit.php?slug='
                        . rawurlencode($product['slug_unit'])
                    )) ?>"
                >
                    <?= e($product['nama_unit']) ?>
                </a>
            </div>

            <div class="info-actions">
                <?php if ($orderWhatsapp !== null): ?>
                    <a
                        class="button"
                        href="<?= e($orderWhatsapp) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Tanyakan via WhatsApp
                    </a>
                <?php else: ?>
                    <a
                        class="button"
                        href="<?= e(url('kontak.php')) ?>"
                    >
                        Hubungi Kami
                    </a>
                <?php endif; ?>

                <a
                    class="button button-outline"
                    href="<?= e(url(
                        'produk.php?unit_usaha_id='
                        . $product['unit_usaha_id']
                    )) ?>"
                >
                    Produk Lain dari Unit Ini
                </a>
            </div>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>