<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$slug = input_string($_GET, 'slug');

if ($slug === '' || strlen($slug) > 191) {
    require __DIR__ . '/404.php';
    exit;
}

$now = date('Y-m-d H:i:s');

$statement = $pdo->prepare(
    'SELECT id, judul, slug, ringkasan, isi, foto, published_at
     FROM berita
     WHERE slug = ?
       AND status = ?
       AND published_at <= ?
     LIMIT 1'
);

$statement->execute([$slug, 'terbit', $now]);

$news = $statement->fetch();

if (!$news) {
    require __DIR__ . '/404.php';
    exit;
}

// Tiga berita terbaru selain berita yang sedang dibaca.
$statement = $pdo->prepare(
    'SELECT judul, slug, published_at
     FROM berita
     WHERE id <> ?
       AND status = ?
       AND published_at <= ?
     ORDER BY published_at DESC, id DESC
     LIMIT 3'
);

$statement->execute([$news['id'], 'terbit', $now]);

$otherNews = $statement->fetchAll();

$published = new DateTimeImmutable(
    $news['published_at'],
    new DateTimeZone('Asia/Jakarta')
);

// Setiap paragraf dipisahkan dengan baris kosong.
$paragraphs = preg_split(
    '/\R\s*\R/u',
    trim((string) $news['isi'])
) ?: [];

$activePage = 'berita';
$pageTitle = $news['judul'] . ' | Moza Group';

$pageDescription = !empty($news['ringkasan'])
    ? $news['ringkasan']
    : 'Baca berita ' . $news['judul'] . ' dari Moza Group.';

$canonicalUrl = url(
    'detail-berita.php?slug=' . rawurlencode($news['slug'])
);

$shareWhatsapp = 'https://wa.me/?text=' . rawurlencode(
    $news['judul'] . "\n" . $canonicalUrl
);

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= e(url()) ?>">Beranda</a>
            <span aria-hidden="true">/</span>

            <a href="<?= e(url('berita.php')) ?>">Berita</a>
            <span aria-hidden="true">/</span>

            <span aria-current="page">Detail Berita</span>
        </nav>

        <span class="eyebrow">Berita Perusahaan</span>
        <h1><?= e($news['judul']) ?></h1>

        <time datetime="<?= e($published->format(DATE_ATOM)) ?>">
            <?= e($published->format('d/m/Y H:i')) ?> WIB
        </time>
    </div>
</section>

<section class="section">
    <div class="container detail-grid">
        <article>
            <?php if (!empty($news['foto'])): ?>
                <img
                    class="detail-cover"
                    src="<?= e(upload_url($news['foto'])) ?>"
                    alt="<?= e($news['judul']) ?>"
                >
            <?php endif; ?>

            <?php if (!empty($news['ringkasan'])): ?>
                <p class="article-lead">
                    <?= e($news['ringkasan']) ?>
                </p>
            <?php endif; ?>

            <div class="prose article-content">
                <?php foreach ($paragraphs as $paragraph): ?>
                    <?php if (trim($paragraph) !== ''): ?>
                        <p><?= text_to_html(trim($paragraph)) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="article-share">
                <a
                    class="button"
                    href="<?= e($shareWhatsapp) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Bagikan via WhatsApp
                </a>

                <a
                    class="button button-outline"
                    href="<?= e(url('berita.php')) ?>"
                >
                    Kembali ke Berita
                </a>
            </div>
        </article>

        <aside class="info-card">
            <h2>Berita Lainnya</h2>

            <?php if ($otherNews === []): ?>
                <p class="section-description">
                    Belum ada berita lainnya.
                </p>
            <?php else: ?>
                <ul class="related-news">
                    <?php foreach ($otherNews as $item): ?>
                        <?php
                        $otherPublished = new DateTimeImmutable(
                            $item['published_at'],
                            new DateTimeZone('Asia/Jakarta')
                        );
                        ?>

                        <li>
                            <time
                                datetime="<?= e(
                                    $otherPublished->format(DATE_ATOM)
                                ) ?>"
                            >
                                <?= e($otherPublished->format('d/m/Y')) ?>
                            </time>

                            <a
                                href="<?= e(url(
                                    'detail-berita.php?slug='
                                    . rawurlencode($item['slug'])
                                )) ?>"
                            >
                                <?= e($item['judul']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>