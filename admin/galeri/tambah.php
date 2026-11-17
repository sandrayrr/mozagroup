<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Tambah Foto';
$activeMenu = 'galeri';

$units = $pdo->query(
    'SELECT id, nama FROM unit_usaha ORDER BY urutan ASC, nama ASC'
)->fetchAll();

$validUnitIds = array_map(
    static fn (array $unit): int => (int) $unit['id'],
    $units
);

$data = [
    'judul' => '',
    'unit_usaha_id' => '',
    'urutan' => '0',
];

$errors = [];

if (is_post()) {
    csrf_verify();

    foreach (array_keys($data) as $field) {
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
        $photo = null;

        try {
            $photo = upload_image('foto', 'galeri');

            if ($photo === null) {
                $errors[] = 'Foto wajib diunggah.';
            }
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO galeri (
                        unit_usaha_id, judul, foto, urutan
                     ) VALUES (?, ?, ?, ?)'
                );

                $statement->execute([
                    $unitId,
                    $data['judul'],
                    $photo,
                    $urutan,
                ]);
            } catch (PDOException $exception) {
                error_log(
                    'Tambah galeri gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Foto gagal disimpan. '
                    . 'Periksa unit usaha yang dipilih dan coba kembali.';
            }
        }

        if ($errors !== []) {
            delete_uploaded_image($photo, 'galeri');
        } else {
            set_flash('success', 'Foto berhasil ditambahkan.');
            redirect('admin/galeri/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Tambah Foto</h1>
        <p class="muted">Tambahkan dokumentasi perusahaan atau unit usaha.</p>
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
        action="<?= e(url('admin/galeri/tambah.php')) ?>"
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
            <label for="foto">Foto *</label>

            <input
                type="file"
                id="foto"
                name="foto"
                accept="image/jpeg,image/png,image/webp"
                required
            >

            <small class="form-help">
                JPG, PNG, atau WebP. Maksimal 2 MB.
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

            <small class="form-help">
                Angka lebih kecil ditampilkan lebih dahulu.
            </small>
        </div>

        <button class="button" type="submit">
            Simpan Foto
        </button>
    </form>
</section>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>