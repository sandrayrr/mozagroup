<?php

declare(strict_types=1);

// File ini berada dua tingkat di bawah folder utama.
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Unit Usaha';
$activeMenu = 'unit-usaha';

$search = input_string($_GET, 'q');
$status = input_string($_GET, 'status');

// Batasi panjang pencarian.
if (text_length($search) > 150) {
    $search = '';
}

if (!in_array($status, ['', '1', '0'], true)) {
    $status = '';
}

$conditions = [];
$params = [];

if ($search !== '') {
    // LOCATE memperlakukan karakter % dan _ sebagai teks biasa.
    $conditions[] = 'LOCATE(?, nama) > 0';
    $params[] = $search;
}

if ($status !== '') {
    $conditions[] = 'aktif = ?';
    $params[] = (int) $status;
}

$where = $conditions !== []
    ? ' WHERE ' . implode(' AND ', $conditions)
    : '';

$statement = $pdo->prepare(
    'SELECT COUNT(*) FROM unit_usaha' . $where
);

$statement->execute($params);

$pagination = pagination((int) $statement->fetchColumn(), 10);

$sql = 'SELECT id, nama, logo, whatsapp, aktif, urutan
        FROM unit_usaha'
    . $where
    . ' ORDER BY urutan ASC, id DESC
        LIMIT ? OFFSET ?';

$statement = $pdo->prepare($sql);

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

$units = $statement->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Unit Usaha</h1>

        <p class="muted">
            Kelola bisnis yang berada di bawah Moza Group.
        </p>
    </div>

    <a
        class="button"
        href="<?= e(url('admin/unit-usaha/tambah.php')) ?>"
    >
        + Tambah Unit Usaha
    </a>
</div>

<section class="panel">
    <form
        class="filter-bar"
        method="get"
        action="<?= e(url('admin/unit-usaha/index.php')) ?>"
    >
        <div class="form-group">
            <label for="q">Cari Nama</label>

            <input
                type="search"
                id="q"
                name="q"
                value="<?= e($search) ?>"
                maxlength="150"
                placeholder="Contoh: Moza Laundry"
            >
        </div>

        <div class="form-group">
            <label for="status">Status</label>

            <select id="status" name="status">
                <option value="">Semua Status</option>

                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>
                    Aktif
                </option>

                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>
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
                href="<?= e(url('admin/unit-usaha/index.php')) ?>"
            >
                Reset
            </a>
        </div>
    </form>

    <p class="muted result-count">
        Ditemukan <?= e($pagination['total']) ?> unit usaha.
    </p>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Logo</th>
                    <th scope="col">Nama Unit Usaha</th>
                    <th scope="col">WhatsApp</th>
                    <th scope="col">Status</th>
                    <th scope="col">Urutan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($units === []): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            Belum ada unit usaha yang sesuai.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($units as $index => $unit): ?>
                    <tr>
                        <td>
                            <?= e($pagination['offset'] + $index + 1) ?>
                        </td>

                        <td>
                            <?php if (!empty($unit['logo'])): ?>
                                <img
                                    class="table-image"
                                    src="<?= e(upload_url($unit['logo'])) ?>"
                                    alt="Logo <?= e($unit['nama']) ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <strong><?= e($unit['nama']) ?></strong>
                        </td>

                        <td>
                            <?= e($unit['whatsapp'] ?: '—') ?>
                        </td>

                        <td>
                            <span
                                class="badge <?= (int) $unit['aktif'] === 1
                                    ? 'badge-success'
                                    : 'badge-muted' ?>"
                            >
                                <?= (int) $unit['aktif'] === 1
                                    ? 'Aktif'
                                    : 'Nonaktif' ?>
                            </span>
                        </td>

                        <td><?= e($unit['urutan']) ?></td>

                        <td>
                            <div class="table-actions">
                                <a
                                    class="button button-outline button-small"
                                    href="<?= e(url(
                                        'admin/unit-usaha/edit.php?id='
                                        . $unit['id']
                                    )) ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    action="<?= e(url('admin/unit-usaha/hapus.php')) ?>"
                                    data-confirm="Hapus unit usaha ini? Unit yang masih memiliki produk tidak dapat dihapus."
                                >
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= e($unit['id']) ?>"
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
        'admin/unit-usaha/index.php',
        [
            'q' => $search,
            'status' => $status,
        ]
    );
    ?>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>