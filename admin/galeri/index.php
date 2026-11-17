<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Galeri';
$activeMenu = 'galeri';

$search = input_string($_GET, 'q');
$unitFilter = input_string($_GET, 'unit');

if (text_length($search) > 150) {
    $search = '';
}

$units = $pdo->query(
    'SELECT id, nama
     FROM unit_usaha
     ORDER BY urutan ASC, nama ASC'
)->fetchAll();

$validUnitIds = array_map(
    static fn (array $unit): int => (int) $unit['id'],
    $units
);

// Nilai kosong = semua, umum = tanpa unit usaha.
$unitId = null;

if ($unitFilter !== '' && $unitFilter !== 'umum') {
    $unitId = positive_id($unitFilter);

    if (
        $unitId === null
        || !in_array($unitId, $validUnitIds, true)
    ) {
        $unitFilter = '';
        $unitId = null;
    } else {
        $unitFilter = (string) $unitId;
    }
}

$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = 'LOCATE(?, g.judul) > 0';
    $params[] = $search;
}

if ($unitFilter === 'umum') {
    $conditions[] = 'g.unit_usaha_id IS NULL';
} elseif ($unitId !== null) {
    $conditions[] = 'g.unit_usaha_id = ?';
    $params[] = $unitId;
}

$where = $conditions !== []
    ? ' WHERE ' . implode(' AND ', $conditions)
    : '';

$statement = $pdo->prepare(
    'SELECT COUNT(*) FROM galeri g' . $where
);

$statement->execute($params);

$pagination = pagination(
    (int) $statement->fetchColumn(),
    10
);

$statement = $pdo->prepare(
    'SELECT
        g.id,
        g.judul,
        g.foto,
        g.urutan,
        u.nama AS nama_unit
     FROM galeri g
     LEFT JOIN unit_usaha u ON u.id = g.unit_usaha_id'
    . $where
    . ' ORDER BY g.urutan ASC, g.id DESC
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

$photos = $statement->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Galeri</h1>

        <p class="muted">
            Kelola dokumentasi perusahaan dan unit usaha.
        </p>
    </div>

    <a
        class="button"
        href="<?= e(url('admin/galeri/tambah.php')) ?>"
    >
        + Tambah Foto
    </a>
</div>

<section class="panel">
    <form
        class="filter-bar"
        method="get"
        action="<?= e(url('admin/galeri/index.php')) ?>"
    >
        <div class="form-group">
            <label for="q">Cari Judul Foto</label>

            <input
                type="search"
                id="q"
                name="q"
                value="<?= e($search) ?>"
                maxlength="150"
                placeholder="Masukkan judul foto"
            >
        </div>

        <div class="form-group">
            <label for="unit">Unit Usaha</label>

            <select id="unit" name="unit">
                <option value="">Semua Foto</option>

                <option
                    value="umum"
                    <?= $unitFilter === 'umum' ? 'selected' : '' ?>
                >
                    Umum / Perusahaan
                </option>

                <?php foreach ($units as $unit): ?>
                    <option
                        value="<?= e($unit['id']) ?>"
                        <?= $unitFilter === (string) $unit['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($unit['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-actions">
            <button class="button" type="submit">
                Cari
            </button>

            <a
                class="button button-outline"
                href="<?= e(url('admin/galeri/index.php')) ?>"
            >
                Reset
            </a>
        </div>
    </form>

    <p class="muted result-count">
        Ditemukan <?= e($pagination['total']) ?> foto.
    </p>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Foto</th>
                    <th scope="col">Judul</th>
                    <th scope="col">Unit Usaha</th>
                    <th scope="col">Urutan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($photos === []): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            Belum ada foto yang sesuai.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($photos as $index => $photo): ?>
                    <tr>
                        <td>
                            <?= e($pagination['offset'] + $index + 1) ?>
                        </td>

                        <td>
                            <a
                                href="<?= e(upload_url($photo['foto'])) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="<?= e(
                                    'Lihat foto ' . $photo['judul']
                                ) ?>"
                            >
                                <img
                                    class="table-image"
                                    src="<?= e(upload_url($photo['foto'])) ?>"
                                    alt="<?= e($photo['judul']) ?>"
                                    loading="lazy"
                                >
                            </a>
                        </td>

                        <td>
                            <strong><?= e($photo['judul']) ?></strong>
                        </td>

                        <td>
                            <?= e(
                                $photo['nama_unit'] ?? 'Umum / Perusahaan'
                            ) ?>
                        </td>

                        <td><?= e($photo['urutan']) ?></td>

                        <td>
                            <div class="table-actions">
                                <a
                                    class="button button-outline button-small"
                                    href="<?= e(url(
                                        'admin/galeri/edit.php?id='
                                        . $photo['id']
                                    )) ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    action="<?= e(url('admin/galeri/hapus.php')) ?>"
                                    data-confirm="Yakin ingin menghapus foto ini?"
                                >
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= e($photo['id']) ?>"
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
        'admin/galeri/index.php',
        [
            'q' => $search,
            'unit' => $unitFilter,
        ]
    );
    ?>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>