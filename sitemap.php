<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$entries = [];

$addEntry = static function (
    string $location,
    ?string $modified = null
) use (&$entries): void {
    $entry = ['loc' => $location];

    if ($modified !== null && $modified !== '') {
        try {
            $date = new DateTimeImmutable(
                $modified,
                new DateTimeZone('Asia/Jakarta')
            );

            $entry['lastmod'] = $date->format(DATE_ATOM);
        } catch (Exception $exception) {
            // Tanggal tidak valid tidak dimasukkan ke sitemap.
        }
    }

    $entries[] = $entry;
};

// Halaman utama.
$pages = [
    '',
    'tentang.php',
    'unit-usaha.php',
    'produk.php',
    'kemitraan.php',
    'berita.php',
    'galeri.php',
    'kontak.php',
];

foreach ($pages as $path) {
    $addEntry(url($path));
}

// Detail unit usaha aktif.
$units = $pdo->query(
    'SELECT slug, updated_at
     FROM unit_usaha
     WHERE aktif = 1'
)->fetchAll();

foreach ($units as $unit) {
    $addEntry(
        url('detail-unit.php?slug=' . rawurlencode($unit['slug'])),
        $unit['updated_at']
    );
}

// Detail produk aktif dari unit usaha aktif.
$products = $pdo->query(
    'SELECT p.slug, p.updated_at
     FROM produk p
     INNER JOIN unit_usaha u ON u.id = p.unit_usaha_id
     WHERE p.aktif = 1 AND u.aktif = 1'
)->fetchAll();

foreach ($products as $product) {
    $addEntry(
        url('detail-produk.php?slug=' . rawurlencode($product['slug'])),
        $product['updated_at']
    );
}

// Berita yang waktu publikasinya sudah tiba.
$statement = $pdo->prepare(
    'SELECT slug, updated_at, published_at
     FROM berita
     WHERE status = ?
       AND published_at <= ?'
);

$statement->execute([
    'terbit',
    date('Y-m-d H:i:s'),
]);

foreach ($statement->fetchAll() as $news) {
    // Tanggal publikasi dapat lebih baru dari tanggal edit
    // ketika berita sebelumnya dijadwalkan.
    $modified = max($news['updated_at'], $news['published_at']);

    $addEntry(
        url('detail-berita.php?slug=' . rawurlencode($news['slug'])),
        $modified
    );
}

$xmlEscape = static fn (string $value): string => htmlspecialchars(
    $value,
    ENT_XML1 | ENT_QUOTES,
    'UTF-8'
);

header('Content-Type: application/xml; charset=UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
echo "\n";

foreach ($entries as $entry) {
    echo "  <url>\n";
    echo '    <loc>' . $xmlEscape($entry['loc']) . "</loc>\n";

    if (isset($entry['lastmod'])) {
        echo '    <lastmod>'
            . $xmlEscape($entry['lastmod'])
            . "</lastmod>\n";
    }

    echo "  </url>\n";
}

echo "</urlset>\n";