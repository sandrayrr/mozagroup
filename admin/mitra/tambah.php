<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Tambah Mitra';
$activeMenu = 'mitra';

$errors = [];

$data = [
    'nama' => '',
    'website' => '',
    'aktif' => '1',
    'urutan' => '0',
];

if (is_post()) {
    csrf_verify();

    foreach (array_keys($data) as $field) {
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
        $logo = null;

        try {
            $logo = upload_image('logo', 'mitra');

            if ($logo === null) {
                $errors[] = 'Logo mitra wajib diunggah.';
            }
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO mitra (
                        nama, logo, website, aktif, urutan
                     ) VALUES (?, ?, ?, ?, ?)'
                );

                $statement->execute([
                    $data['nama'],
                    $logo,
                    $data['website'] !== '' ? $data['website'] : null,
                    (int) $data['aktif'],
                    $urutan,
                ]);
            } catch (PDOException $exception) {
                error_log(
                    'Tambah mitra gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Mitra gagal disimpan. Silakan coba kembali.';
            }
        }

        if ($errors !== []) {
            delete_uploaded_image($logo, 'mitra');
        } else {
            set_flash('success', 'Mitra berhasil ditambahkan.');
            redirect('admin/mitra/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Tambah Mitra</h1>
        <p class="muted">Masukkan informasi mitra perusahaan.</p>
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
        action="<?= e(url('admin/mitra/tambah.php')) ?>"
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
            <label for="logo">Logo Mitra *</label>

            <input
                type="file"
                id="logo"
                name="logo"
                accept="image/jpeg,image/png,image/webp"
                required
            >

            <small class="form-help">
                JPG, PNG, atau WebP. Maksimal 2 MB.
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
            Simpan Mitra
        </button>
    </form>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>