<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Mitra Perusahaan';
$activeMenu = 'mitra';

$search = input_string($_GET, 'q');
$status = input_string($_GET, 'status');

if (text_length($search) > 150) {
    $search = '';
}

if (!in_array($status, ['', '0', '1'], true)) {
    $status = '';
}

$conditions = [];
$params = [];

if ($search !== '') {
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
    'SELECT COUNT(*) FROM mitra' . $where
);

$statement->execute($params);

$pagination = pagination(
    (int) $statement->fetchColumn(),
    10
);

$statement = $pdo->prepare(
    'SELECT id, nama, logo, website, aktif, urutan
     FROM mitra'
    . $where
    . ' ORDER BY urutan ASC, id DESC
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

$partners = $statement->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Mitra Perusahaan</h1>

        <p class="muted">
            Kelola nama, logo, dan website mitra Moza Group.
        </p>
    </div>

    <a
        class="button"
        href="<?= e(url('admin/mitra/tambah.php')) ?>"
    >
        + Tambah Mitra
    </a>
</div>

<section class="panel">
    <form
        class="filter-bar"
        method="get"
        action="<?= e(url('admin/mitra/index.php')) ?>"
    >
        <div class="form-group">
            <label for="q">Cari Nama Mitra</label>

            <input
                type="search"
                id="q"
                name="q"
                value="<?= e($search) ?>"
                maxlength="150"
                placeholder="Masukkan nama mitra"
            >
        </div>

        <div class="form-group">
            <label for="status">Status</label>

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
            <button class="button" type="submit">
                Cari
            </button>

            <a
                class="button button-outline"
                href="<?= e(url('admin/mitra/index.php')) ?>"
            >
                Reset
            </a>
        </div>
    </form>

    <p class="muted result-count">
        Ditemukan <?= e($pagination['total']) ?> mitra.
    </p>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Logo</th>
                    <th scope="col">Nama Mitra</th>
                    <th scope="col">Website</th>
                    <th scope="col">Status</th>
                    <th scope="col">Urutan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($partners === []): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            Belum ada mitra yang sesuai.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($partners as $index => $partner): ?>
                    <tr>
                        <td>
                            <?= e($pagination['offset'] + $index + 1) ?>
                        </td>

                        <td>
                            <img
                                class="table-image"
                                src="<?= e(upload_url($partner['logo'])) ?>"
                                alt="Logo <?= e($partner['nama']) ?>"
                                loading="lazy"
                            >
                        </td>

                        <td>
                            <strong><?= e($partner['nama']) ?></strong>
                        </td>

                        <td>
                            <?php
                            $website = (string) ($partner['website'] ?? '');

                            $validWebsite = $website !== ''
                                && validate_url($website) === null;
                            ?>

                            <?php if ($validWebsite): ?>
                                <a
                                    href="<?= e($website) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Buka Website
                                </a>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span
                                class="badge <?= (int) $partner['aktif'] === 1
                                    ? 'badge-success'
                                    : 'badge-muted' ?>"
                            >
                                <?= (int) $partner['aktif'] === 1
                                    ? 'Aktif'
                                    : 'Nonaktif' ?>
                            </span>
                        </td>

                        <td><?= e($partner['urutan']) ?></td>

                        <td>
                            <div class="table-actions">
                                <a
                                    class="button button-outline button-small"
                                    href="<?= e(url(
                                        'admin/mitra/edit.php?id='
                                        . $partner['id']
                                    )) ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    action="<?= e(url('admin/mitra/hapus.php')) ?>"
                                    data-confirm="Yakin ingin menghapus mitra ini?"
                                >
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= e($partner['id']) ?>"
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
        'admin/mitra/index.php',
        [
            'q' => $search,
            'status' => $status,
        ]
    );
    ?>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>