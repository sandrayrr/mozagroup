<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Edit Produk';
$activeMenu = 'produk';

$id = positive_id($_GET['id'] ?? null);

if ($id === null) {
    set_flash('error', 'ID produk tidak valid.');
    redirect('admin/produk/index.php');
}

$statement = $pdo->prepare(
    'SELECT * FROM produk WHERE id = ? LIMIT 1'
);

$statement->execute([$id]);

$data = $statement->fetch();

if (!$data) {
    set_flash('error', 'Produk tidak ditemukan.');
    redirect('admin/produk/index.php');
}

$units = $pdo->query(
    'SELECT id, nama, aktif
     FROM unit_usaha
     ORDER BY urutan ASC, nama ASC'
)->fetchAll();

$validUnitIds = array_map(
    static fn (array $unit): int => (int) $unit['id'],
    $units
);

foreach (['unit_usaha_id', 'unggulan', 'aktif', 'urutan'] as $field) {
    $data[$field] = (string) $data[$field];
}

$data['harga'] = $data['harga'] === null
    ? ''
    : (string) $data['harga'];

$errors = [];

$fields = [
    'nama' => ['Nama produk', 150],
    'ringkasan' => ['Ringkasan', 1000],
    'deskripsi' => ['Deskripsi', 20000],
    'keterangan_harga' => ['Keterangan harga', 150],
];

if (is_post()) {
    csrf_verify();

    foreach ($fields as $field => [$label, $limit]) {
        $data[$field] = input_string($_POST, $field);

        if (text_length($data[$field]) > $limit) {
            $errors[] = $label . ' maksimal ' . $limit . ' karakter.';
        }
    }

    foreach (
        ['unit_usaha_id', 'harga', 'unggulan', 'aktif', 'urutan'] as $field
    ) {
        $data[$field] = input_string($_POST, $field);
    }

    if ($data['nama'] === '') {
        $errors[] = 'Nama produk wajib diisi.';
    }

    $unitId = positive_id($data['unit_usaha_id']);

    if ($unitId === null || !in_array($unitId, $validUnitIds, true)) {
        $errors[] = 'Pilih unit usaha yang valid.';
    }

    $harga = null;

    if ($data['harga'] !== '') {
        if (!preg_match(
            '/^(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?$/D',
            $data['harga']
        )) {
            $errors[] = 'Harga harus berupa angka positif atau nol, '
                . 'maksimal 13 digit dan 2 angka desimal.';
        } else {
            $harga = $data['harga'];
        }
    }

    $urutan = filter_var(
        $data['urutan'],
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 0,
                'max_range' => 1000000,
            ],
        ]
    );

    if ($urutan === false) {
        $errors[] = 'Urutan harus berupa angka 0 sampai 1000000.';
    }

    foreach (['aktif', 'unggulan'] as $field) {
        if (!in_array($data[$field], ['0', '1'], true)) {
            $errors[] = 'Pilihan status atau unggulan tidak valid.';
        }
    }

    if ($errors === []) {
        $newPhoto = null;
        $oldPhoto = null;

        try {
            $newPhoto = upload_image('foto', 'produk');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $pdo->beginTransaction();

                $statement = $pdo->prepare(
                    'SELECT foto FROM produk WHERE id = ? FOR UPDATE'
                );

                $statement->execute([$id]);

                $current = $statement->fetch();

                if (!$current) {
                    $pdo->rollBack();
                    $errors[] = 'Produk sudah tidak tersedia.';
                } else {
                    $oldPhoto = $current['foto'];

                    $statement = $pdo->prepare(
                        'UPDATE produk
                         SET unit_usaha_id = ?,
                             nama = ?,
                             ringkasan = ?,
                             deskripsi = ?,
                             foto = ?,
                             harga = ?,
                             keterangan_harga = ?,
                             unggulan = ?,
                             aktif = ?,
                             urutan = ?
                         WHERE id = ?'
                    );

                    $statement->execute([
                        $unitId,
                        $data['nama'],
                        $data['ringkasan'],
                        $data['deskripsi'],
                        $newPhoto ?? $oldPhoto,
                        $harga,
                        $data['keterangan_harga'],
                        (int) $data['unggulan'],
                        (int) $data['aktif'],
                        $urutan,
                        $id,
                    ]);

                    $pdo->commit();
                }
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log(
                    'Edit produk gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Produk gagal diperbarui. '
                    . 'Pastikan unit usaha masih tersedia, lalu coba kembali.';
            }
        }

        if ($errors !== []) {
            delete_uploaded_image($newPhoto, 'produk');
        } else {
            if ($newPhoto !== null) {
                delete_uploaded_image($oldPhoto, 'produk');
            }

            set_flash('success', 'Produk berhasil diperbarui.');
            redirect('admin/produk/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Edit Produk</h1>

        <p class="muted">
            Perbarui informasi, harga, dan foto produk.
        </p>
    </div>

    <a
        class="button button-outline"
        href="<?= e(url('admin/produk/index.php')) ?>"
    >
        Kembali
    </a>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert-error" role="alert">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form
    method="post"
    action="<?= e(url('admin/produk/edit.php?id=' . $id)) ?>"
    enctype="multipart/form-data"
>
    <?= csrf_field() ?>

    <section class="panel">
        <h2>Informasi Produk</h2>

        <div class="form-group">
            <label for="unit_usaha_id">Unit Usaha *</label>

            <select id="unit_usaha_id" name="unit_usaha_id" required>
                <option value="">Pilih Unit Usaha</option>

                <?php foreach ($units as $unit): ?>
                    <option
                        value="<?= e($unit['id']) ?>"
                        <?= $data['unit_usaha_id'] === (string) $unit['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($unit['nama']) ?>
                        <?= (int) $unit['aktif'] === 1 ? '' : ' (Nonaktif)' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <small class="form-help">
                Produk hanya tampil jika produk dan unit usahanya aktif.
            </small>
        </div>

        <div class="form-group">
            <label for="nama">Nama Produk *</label>

            <input
                type="text"
                id="nama"
                name="nama"
                value="<?= e($data['nama']) ?>"
                maxlength="150"
                required
            >

            <small class="form-help">
                Slug: <?= e($data['slug']) ?>.
                Mengubah nama tidak mengubah slug.
            </small>
        </div>

        <?php
        $textareas = [
            'ringkasan' => ['Ringkasan', 3, 1000],
            'deskripsi' => ['Deskripsi Lengkap', 7, 20000],
        ];
        ?>

        <?php foreach ($textareas as $field => [$label, $rows, $limit]): ?>
            <div class="form-group">
                <label for="<?= e($field) ?>">
                    <?= e($label) ?>
                </label>

                <textarea
                    id="<?= e($field) ?>"
                    name="<?= e($field) ?>"
                    rows="<?= $rows ?>"
                    maxlength="<?= $limit ?>"
                ><?= e($data[$field]) ?></textarea>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="panel">
        <h2>Foto Produk</h2>

        <div class="form-group">
            <label for="foto">Ganti Foto</label>

            <?php if (!empty($data['foto'])): ?>
                <img
                    class="image-preview"
                    src="<?= e(upload_url($data['foto'])) ?>"
                    alt="Foto produk saat ini"
                >
            <?php endif; ?>

            <input
                type="file"
                id="foto"
                name="foto"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="form-help">
                JPG, PNG, atau WebP. Maksimal 2 MB.
                Kosongkan untuk mempertahankan foto.
            </small>
        </div>
    </section>

    <section class="panel">
        <h2>Harga</h2>

        <div class="form-grid">
            <div class="form-group">
                <label for="harga">Harga dalam Rupiah</label>

                <input
                    type="number"
                    id="harga"
                    name="harga"
                    value="<?= e($data['harga']) ?>"
                    min="0"
                    max="9999999999999.99"
                    step="0.01"
                >

                <small class="form-help">
                    Tanpa pemisah ribuan.
                    Kosongkan untuk menampilkan “Hubungi kami”.
                </small>
            </div>

            <div class="form-group">
                <label for="keterangan_harga">Keterangan Harga</label>

                <input
                    type="text"
                    id="keterangan_harga"
                    name="keterangan_harga"
                    value="<?= e($data['keterangan_harga']) ?>"
                    maxlength="150"
                    placeholder="Contoh: per kg / per orang"
                >
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Pengaturan Tampilan</h2>

        <div class="form-grid">
            <?php
            $selects = [
                'aktif' => [
                    'Status',
                    ['1' => 'Aktif', '0' => 'Nonaktif'],
                ],
                'unggulan' => [
                    'Produk Unggulan',
                    ['0' => 'Tidak', '1' => 'Ya'],
                ],
            ];
            ?>

            <?php foreach ($selects as $field => [$label, $options]): ?>
                <div class="form-group">
                    <label for="<?= e($field) ?>">
                        <?= e($label) ?> *
                    </label>

                    <select
                        id="<?= e($field) ?>"
                        name="<?= e($field) ?>"
                        required
                    >
                        <?php foreach ($options as $value => $text): ?>
                            <option
                                value="<?= e($value) ?>"
                                <?= $data[$field] === (string) $value
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= e($text) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-group">
            <label for="urutan">Urutan Tampil *</label>

            <input
                type="number"
                id="urutan"
                name="urutan"
                value="<?= e($data['urutan']) ?>"
                min="0"
                max="1000000"
                step="1"
                required
            >
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="button">
            Simpan Perubahan
        </button>
    </div>
</form>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>