<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Produk';
$activeMenu = 'produk';

$search = input_string($_GET, 'q');
$status = input_string($_GET, 'status');
$unitId = positive_id($_GET['unit_usaha_id'] ?? null);

if (text_length($search) > 150) {
    $search = '';
}

if (!in_array($status, ['', '1', '0'], true)) {
    $status = '';
}

// Admin dapat melihat unit aktif maupun nonaktif.
$units = $pdo->query(
    'SELECT id, nama
     FROM unit_usaha
     ORDER BY urutan ASC, nama ASC'
)->fetchAll();

$validUnitIds = array_map(
    static fn (array $unit): int => (int) $unit['id'],
    $units
);

if ($unitId !== null && !in_array($unitId, $validUnitIds, true)) {
    $unitId = null;
}

$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = 'LOCATE(?, p.nama) > 0';
    $params[] = $search;
}

if ($status !== '') {
    $conditions[] = 'p.aktif = ?';
    $params[] = (int) $status;
}

if ($unitId !== null) {
    $conditions[] = 'p.unit_usaha_id = ?';
    $params[] = $unitId;
}

$where = $conditions !== []
    ? ' WHERE ' . implode(' AND ', $conditions)
    : '';

$statement = $pdo->prepare(
    'SELECT COUNT(*)
     FROM produk p
     INNER JOIN unit_usaha u ON u.id = p.unit_usaha_id'
    . $where
);

$statement->execute($params);

$pagination = pagination(
    (int) $statement->fetchColumn(),
    10
);

$statement = $pdo->prepare(
    'SELECT
        p.id,
        p.nama,
        p.foto,
        p.harga,
        p.keterangan_harga,
        p.unggulan,
        p.aktif,
        p.urutan,
        u.nama AS nama_unit,
        u.aktif AS unit_aktif
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

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Produk</h1>

        <p class="muted">
            Kelola produk dan paket layanan setiap unit usaha.
        </p>
    </div>

    <a
        class="button"
        href="<?= e(url('admin/produk/tambah.php')) ?>"
    >
        + Tambah Produk
    </a>
</div>

<?php if ($units === []): ?>
    <div class="alert" role="status">
        Tambahkan unit usaha terlebih dahulu sebelum membuat produk.

        <a href="<?= e(url('admin/unit-usaha/tambah.php')) ?>">
            Tambah unit usaha
        </a>
    </div>
<?php endif; ?>

<section class="panel">
    <form
        class="filter-bar"
        method="get"
        action="<?= e(url('admin/produk/index.php')) ?>"
    >
        <div class="form-group">
            <label for="q">Cari Produk</label>

            <input
                type="search"
                id="q"
                name="q"
                value="<?= e($search) ?>"
                maxlength="150"
                placeholder="Nama produk atau paket"
            >
        </div>

        <div class="form-group">
            <label for="unit_usaha_id">Unit Usaha</label>

            <select id="unit_usaha_id" name="unit_usaha_id">
                <option value="">Semua Unit Usaha</option>

                <?php foreach ($units as $unit): ?>
                    <option
                        value="<?= e($unit['id']) ?>"
                        <?= $unitId === (int) $unit['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($unit['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status Produk</label>

            <select id="status" name="status">
                <option value="">Semua Status</option>

                <option
                    value="1"
                    <?= $status === '1' ? 'selected' : '' ?>
                >
                    Aktif
                </option>

                <option
                    value="0"
                    <?= $status === '0' ? 'selected' : '' ?>
                >
                    Nonaktif
                </option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="button">
                Cari
            </button>

            <a
                class="button button-outline"
                href="<?= e(url('admin/produk/index.php')) ?>"
            >
                Reset
            </a>
        </div>
    </form>

    <p class="muted result-count">
        Ditemukan <?= e($pagination['total']) ?> produk.
    </p>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Foto</th>
                    <th scope="col">Produk</th>
                    <th scope="col">Unit Usaha</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Status</th>
                    <th scope="col">Urutan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($products === []): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            Belum ada produk yang sesuai.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($products as $index => $product): ?>
                    <tr>
                        <td>
                            <?= e($pagination['offset'] + $index + 1) ?>
                        </td>

                        <td>
                            <?php if (!empty($product['foto'])): ?>
                                <img
                                    class="table-image"
                                    src="<?= e(upload_url($product['foto'])) ?>"
                                    alt="<?= e($product['nama']) ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <strong>
                                <?= e($product['nama']) ?>
                            </strong>

                            <?php if ((int) $product['unggulan'] === 1): ?>
                                <div>
                                    <span class="badge badge-success">
                                        Unggulan
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= e($product['nama_unit']) ?>

                            <?php if ((int) $product['unit_aktif'] !== 1): ?>
                                <div>
                                    <span class="badge badge-muted">
                                        Unit nonaktif
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= e(rupiah($product['harga'])) ?>

                            <?php if (!empty($product['keterangan_harga'])): ?>
                                <small class="form-help">
                                    <?= e($product['keterangan_harga']) ?>
                                </small>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span
                                class="badge <?= (int) $product['aktif'] === 1
                                    ? 'badge-success'
                                    : 'badge-muted' ?>"
                            >
                                <?= (int) $product['aktif'] === 1
                                    ? 'Aktif'
                                    : 'Nonaktif' ?>
                            </span>
                        </td>

                        <td>
                            <?= e($product['urutan']) ?>
                        </td>

                        <td>
                            <div class="table-actions">
                                <a
                                    class="button button-outline button-small"
                                    href="<?= e(url(
                                        'admin/produk/edit.php?id='
                                        . $product['id']
                                    )) ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    action="<?= e(url('admin/produk/hapus.php')) ?>"
                                    data-confirm="Yakin ingin menghapus produk ini?"
                                >
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= e($product['id']) ?>"
                                    >

                                    <button
                                        class="button button-small"
                                        type="submit"
                                    >
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    render_pagination(
        $pagination,
        'admin/produk/index.php',
        [
            'q' => $search,
            'status' => $status,
            'unit_usaha_id' => $unitId ?? '',
        ]
    );
    ?>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>