<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Berita';
$activeMenu = 'berita';

$search = input_string($_GET, 'q');
$status = input_string($_GET, 'status');

if (text_length($search) > 200) {
    $search = '';
}

if (!in_array($status, ['', 'draft', 'terbit'], true)) {
    $status = '';
}

$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = 'LOCATE(?, judul) > 0';
    $params[] = $search;
}

if ($status !== '') {
    $conditions[] = 'status = ?';
    $params[] = $status;
}

$where = $conditions !== []
    ? ' WHERE ' . implode(' AND ', $conditions)
    : '';

$statement = $pdo->prepare(
    'SELECT COUNT(*) FROM berita' . $where
);

$statement->execute($params);

$pagination = pagination(
    (int) $statement->fetchColumn(),
    10
);

$statement = $pdo->prepare(
    'SELECT
        id,
        judul,
        foto,
        status,
        published_at,
        created_at
     FROM berita'
    . $where
    . ' ORDER BY created_at DESC, id DESC
        LIMIT ? OFFSET ?'
);

$position = 1;

foreach ($params as $value) {
    $statement->bindValue(
        $position++,
        $value,
        PDO::PARAM_STR
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

$news = $statement->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Berita</h1>

        <p class="muted">
            Kelola berita dan kegiatan perusahaan.
        </p>
    </div>

    <a
        class="button"
        href="<?= e(url('admin/berita/tambah.php')) ?>"
    >
        + Tambah Berita
    </a>
</div>

<section class="panel">
    <form
        class="filter-bar"
        method="get"
        action="<?= e(url('admin/berita/index.php')) ?>"
    >
        <div class="form-group">
            <label for="q">Cari Judul Berita</label>

            <input
                type="search"
                id="q"
                name="q"
                value="<?= e($search) ?>"
                maxlength="200"
                placeholder="Masukkan judul berita"
            >
        </div>

        <div class="form-group">
            <label for="status">Status</label>

            <select id="status" name="status">
                <option value="">Semua Status</option>

                <option
                    value="draft"
                    <?= $status === 'draft' ? 'selected' : '' ?>
                >
                    Draft
                </option>

                <option
                    value="terbit"
                    <?= $status === 'terbit' ? 'selected' : '' ?>
                >
                    Terbit
                </option>
            </select>
        </div>

        <div class="filter-actions">
            <button class="button" type="submit">
                Cari
            </button>

            <a
                class="button button-outline"
                href="<?= e(url('admin/berita/index.php')) ?>"
            >
                Reset
            </a>
        </div>
    </form>

    <p class="muted result-count">
        Ditemukan <?= e($pagination['total']) ?> berita.
    </p>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Foto</th>
                    <th scope="col">Judul</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tanggal Publikasi</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($news === []): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            Belum ada berita yang sesuai.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($news as $index => $item): ?>
                    <?php
                    $publishedTimestamp = !empty($item['published_at'])
                        ? strtotime($item['published_at'])
                        : false;

                    $isScheduled = $item['status'] === 'terbit'
                        && $publishedTimestamp !== false
                        && $publishedTimestamp > time();

                    $isPublished = $item['status'] === 'terbit'
                        && $publishedTimestamp !== false
                        && !$isScheduled;

                    $statusLabel = 'Draft';

                    if ($item['status'] === 'terbit') {
                        $statusLabel = $publishedTimestamp === false
                            ? 'Tanggal belum diatur'
                            : ($isScheduled ? 'Terjadwal' : 'Terbit');
                    }
                    ?>

                    <tr>
                        <td>
                            <?= e($pagination['offset'] + $index + 1) ?>
                        </td>

                        <td>
                            <?php if (!empty($item['foto'])): ?>
                                <img
                                    class="table-image"
                                    src="<?= e(upload_url($item['foto'])) ?>"
                                    alt="<?= e($item['judul']) ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <strong><?= e($item['judul']) ?></strong>
                        </td>

                        <td>
                            <span
                                class="badge <?= $isPublished
                                    ? 'badge-success'
                                    : 'badge-muted' ?>"
                            >
                                <?= e($statusLabel) ?>
                            </span>
                        </td>

                        <td>
                            <?php if ($publishedTimestamp !== false): ?>
                                <?= e(date(
                                    'd/m/Y H:i',
                                    $publishedTimestamp
                                )) ?> WIB
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="table-actions">
                                <a
                                    class="button button-outline button-small"
                                    href="<?= e(url(
                                        'admin/berita/edit.php?id='
                                        . $item['id']
                                    )) ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    action="<?= e(url('admin/berita/hapus.php')) ?>"
                                    data-confirm="Yakin ingin menghapus berita ini?"
                                >
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= e($item['id']) ?>"
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
        'admin/berita/index.php',
        [
            'q' => $search,
            'status' => $status,
        ]
    );
    ?>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>