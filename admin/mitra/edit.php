<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Edit Mitra';
$activeMenu = 'mitra';

$id = positive_id($_GET['id'] ?? null);

if ($id === null) {
    set_flash('error', 'ID mitra tidak valid.');
    redirect('admin/mitra/index.php');
}

$statement = $pdo->prepare(
    'SELECT * FROM mitra WHERE id = ? LIMIT 1'
);

$statement->execute([$id]);

$data = $statement->fetch();

if (!$data) {
    set_flash('error', 'Mitra tidak ditemukan.');
    redirect('admin/mitra/index.php');
}

$data['aktif'] = (string) $data['aktif'];
$data['urutan'] = (string) $data['urutan'];

$errors = [];

if (is_post()) {
    csrf_verify();

    foreach (['nama', 'website', 'aktif', 'urutan'] as $field) {
        $data[$field] = input_string($_POST, $field);
    }

    $nameError = validate_required($data['nama'], 'Nama mitra', 150);

    if ($nameError !== null) {
        $errors[] = $nameError;
    }

    $websiteError = validate_url($data['website'], 'Website mitra');

    if ($websiteError !== null) {
        $errors[] = $websiteError;
    }

    if (!in_array($data['aktif'], ['0', '1'], true)) {
        $errors[] = 'Status mitra tidak valid.';
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

    if ($errors === []) {
        $newLogo = null;
        $oldLogo = null;

        try {
            $newLogo = upload_image('logo', 'mitra');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $pdo->beginTransaction();

                $statement = $pdo->prepare(
                    'SELECT logo FROM mitra WHERE id = ? FOR UPDATE'
                );

                $statement->execute([$id]);

                $current = $statement->fetch();

                if (!$current) {
                    $pdo->rollBack();
                    $errors[] = 'Mitra sudah tidak tersedia.';
                } else {
                    $oldLogo = $current['logo'];

                    $statement = $pdo->prepare(
                        'UPDATE mitra
                         SET nama = ?,
                             logo = ?,
                             website = ?,
                             aktif = ?,
                             urutan = ?
                         WHERE id = ?'
                    );

                    $statement->execute([
                        $data['nama'],
                        $newLogo ?? $oldLogo,
                        $data['website'] !== '' ? $data['website'] : null,
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
                    'Edit mitra gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Mitra gagal diperbarui. Silakan coba kembali.';
            }
        }

        if ($errors !== []) {
            delete_uploaded_image($newLogo, 'mitra');
        } else {
            if ($newLogo !== null) {
                delete_uploaded_image($oldLogo, 'mitra');
            }

            set_flash('success', 'Mitra berhasil diperbarui.');
            redirect('admin/mitra/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Edit Mitra</h1>
        <p class="muted">Perbarui informasi dan logo mitra.</p>
    </div>

    <a
        class="button button-outline"
        href="<?= e(url('admin/mitra/index.php')) ?>"
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
        action="<?= e(url('admin/mitra/edit.php?id=' . $id)) ?>"
        enctype="multipart/form-data"
    >
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="nama">Nama Mitra *</label>

            <input
                type="text"
                id="nama"
                name="nama"
                value="<?= e($data['nama']) ?>"
                maxlength="150"
                required
            >
        </div>

        <div class="form-group">
            <label for="website">Website Mitra</label>

            <input
                type="url"
                id="website"
                name="website"
                value="<?= e($data['website']) ?>"
                maxlength="255"
                placeholder="https://..."
            >
        </div>

        <div class="form-group">
            <label for="logo">Ganti Logo</label>

            <img
                class="image-preview"
                src="<?= e(upload_url($data['logo'])) ?>"
                alt="Logo mitra saat ini"
            >

            <input
                type="file"
                id="logo"
                name="logo"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="form-help">
                Maksimal 2 MB. Kosongkan untuk mempertahankan logo.
            </small>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="aktif">Status *</label>

                <select id="aktif" name="aktif" required>
                    <option
                        value="1"
                        <?= $data['aktif'] === '1' ? 'selected' : '' ?>
                    >
                        Aktif
                    </option>

                    <option
                        value="0"
                        <?= $data['aktif'] === '0' ? 'selected' : '' ?>
                    >
                        Nonaktif
                    </option>
                </select>
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
        </div>

        <button class="button" type="submit">
            Simpan Perubahan
        </button>
    </form>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>