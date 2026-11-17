<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Edit Foto';
$activeMenu = 'galeri';

$id = positive_id($_GET['id'] ?? null);

if ($id === null) {
    set_flash('error', 'ID foto tidak valid.');
    redirect('admin/galeri/index.php');
}

$statement = $pdo->prepare(
    'SELECT * FROM galeri WHERE id = ? LIMIT 1'
);

$statement->execute([$id]);

$data = $statement->fetch();

if (!$data) {
    set_flash('error', 'Foto tidak ditemukan.');
    redirect('admin/galeri/index.php');
}

$data['unit_usaha_id'] = $data['unit_usaha_id'] === null
    ? ''
    : (string) $data['unit_usaha_id'];

$data['urutan'] = (string) $data['urutan'];

$units = $pdo->query(
    'SELECT id, nama FROM unit_usaha ORDER BY urutan ASC, nama ASC'
)->fetchAll();

$validUnitIds = array_map(
    static fn (array $unit): int => (int) $unit['id'],
    $units
);

$errors = [];

if (is_post()) {
    csrf_verify();

    foreach (['judul', 'unit_usaha_id', 'urutan'] as $field) {
        $data[$field] = input_string($_POST, $field);
    }

    $titleError = validate_required($data['judul'], 'Judul foto', 150);

    if ($titleError !== null) {
        $errors[] = $titleError;
    }

    $unitId = null;

    if ($data['unit_usaha_id'] !== '') {
        $unitId = positive_id($data['unit_usaha_id']);

        if ($unitId === null || !in_array($unitId, $validUnitIds, true)) {
            $errors[] = 'Unit usaha tidak valid.';
        }
    }

    $urutan = filter_var(
        $data['urutan'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => 1000000]]
    );

    if ($urutan === false) {
        $errors[] = 'Urutan harus berupa angka 0 sampai 1000000.';
    }

    if ($errors === []) {
        $newPhoto = null;
        $oldPhoto = null;

        try {
            $newPhoto = upload_image('foto', 'galeri');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $pdo->beginTransaction();

                $statement = $pdo->prepare(
                    'SELECT foto FROM galeri WHERE id = ? FOR UPDATE'
                );

                $statement->execute([$id]);

                $current = $statement->fetch();

                if (!$current) {
                    $pdo->rollBack();
                    $errors[] = 'Foto sudah tidak tersedia.';
                } else {
                    $oldPhoto = $current['foto'];

                    $statement = $pdo->prepare(
                        'UPDATE galeri
                         SET unit_usaha_id = ?,
                             judul = ?,
                             foto = ?,
                             urutan = ?
                         WHERE id = ?'
                    );

                    $statement->execute([
                        $unitId,
                        $data['judul'],
                        $newPhoto ?? $oldPhoto,
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
                    'Edit galeri gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Foto gagal diperbarui. '
                    . 'Periksa unit usaha yang dipilih dan coba kembali.';
            }
        }

        if ($errors !== []) {
            delete_uploaded_image($newPhoto, 'galeri');
        } else {
            if ($newPhoto !== null) {
                delete_uploaded_image($oldPhoto, 'galeri');
            }

            set_flash('success', 'Foto berhasil diperbarui.');
            redirect('admin/galeri/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Edit Foto</h1>
        <p class="muted">Perbarui foto dan informasi dokumentasi.</p>
    </div>

    <a
        class="button button-outline"
        href="<?= e(url('admin/galeri/index.php')) ?>"
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

<section class="panel">
    <form
        method="post"
        action="<?= e(url('admin/galeri/edit.php?id=' . $id)) ?>"
        enctype="multipart/form-data"
    >
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="judul">Judul Foto *</label>

            <input
                type="text"
                id="judul"
                name="judul"
                value="<?= e($data['judul']) ?>"
                maxlength="150"
                required
            >
        </div>

        <div class="form-group">
            <label for="unit_usaha_id">Unit Usaha</label>

            <select id="unit_usaha_id" name="unit_usaha_id">
                <option value="">Umum / Perusahaan</option>

                <?php foreach ($units as $unit): ?>
                    <option
                        value="<?= e($unit['id']) ?>"
                        <?= $data['unit_usaha_id'] === (string) $unit['id']
                            ? 'selected' : '' ?>
                    >
                        <?= e($unit['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="foto">Ganti Foto</label>

            <img
                class="image-preview"
                src="<?= e(upload_url($data['foto'])) ?>"
                alt="<?= e($data['judul']) ?>"
            >

            <input
                type="file"
                id="foto"
                name="foto"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="form-help">
                Maksimal 2 MB. Kosongkan untuk mempertahankan foto.
            </small>
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

        <button class="button" type="submit">
            Simpan Perubahan
        </button>
    </form>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>