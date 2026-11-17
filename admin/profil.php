<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Profil Perusahaan';
$activeMenu = 'profil';

$statement = $pdo->query(
    'SELECT * FROM profil_perusahaan WHERE id = 1'
);

$profil = $statement->fetch();

if (!$profil) {
    http_response_code(500);
    exit('Data profil belum tersedia. Impor data awal database terlebih dahulu.');
}

$errors = [];

if (is_post()) {
    csrf_verify();

    $profil['nama_perusahaan'] = input_string($_POST, 'nama_perusahaan');
    $profil['ringkasan'] = input_string($_POST, 'ringkasan');
    $profil['deskripsi'] = input_string($_POST, 'deskripsi');
    $profil['sejarah'] = input_string($_POST, 'sejarah');
    $profil['visi'] = input_string($_POST, 'visi');
    $profil['misi'] = input_string($_POST, 'misi');

    $nameError = validate_required(
        $profil['nama_perusahaan'],
        'Nama perusahaan',
        150
    );

    if ($nameError !== null) {
        $errors[] = $nameError;
    }

    $limits = [
        'ringkasan' => ['Ringkasan', 1000],
        'deskripsi' => ['Deskripsi', 20000],
        'sejarah' => ['Sejarah', 20000],
        'visi' => ['Visi', 5000],
        'misi' => ['Misi', 10000],
    ];

    foreach ($limits as $field => [$label, $limit]) {
        if (text_length($profil[$field]) > $limit) {
            $errors[] = $label . ' maksimal ' . $limit . ' karakter.';
        }
    }

    if ($errors === []) {
        $newPhoto = null;
        $oldPhoto = $profil['foto'];

        try {
            $newPhoto = upload_image('foto', 'perusahaan');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $statement = $pdo->prepare(
                    'UPDATE profil_perusahaan
                     SET nama_perusahaan = ?,
                         ringkasan = ?,
                         deskripsi = ?,
                         sejarah = ?,
                         visi = ?,
                         misi = ?,
                         foto = ?
                     WHERE id = 1'
                );

                $statement->execute([
                    $profil['nama_perusahaan'],
                    $profil['ringkasan'],
                    $profil['deskripsi'],
                    $profil['sejarah'],
                    $profil['visi'],
                    $profil['misi'],
                    $newPhoto ?? $oldPhoto,
                ]);
            } catch (PDOException $exception) {
                // Bersihkan gambar baru jika penyimpanan database gagal.
                delete_uploaded_image($newPhoto, 'perusahaan');

                error_log(
                    'Simpan profil gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Data gagal disimpan. Silakan coba kembali.';
            }

            if ($errors === []) {
                if ($newPhoto !== null) {
                    delete_uploaded_image($oldPhoto, 'perusahaan');
                }

                set_flash('success', 'Profil perusahaan berhasil diperbarui.');

                redirect('admin/profil.php');
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Profil Perusahaan</h1>

    <p class="muted">
        Kelola informasi dasar dan profil Moza Group.
    </p>
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
        action="<?= e(url('admin/profil.php')) ?>"
        enctype="multipart/form-data"
    >
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="nama_perusahaan">Nama Perusahaan *</label>
            <input
                id="nama_perusahaan"
                name="nama_perusahaan"
                type="text"
                value="<?= e($profil['nama_perusahaan']) ?>"
                maxlength="150"
                required
            >
        </div>

        <div class="form-group">
            <label for="ringkasan">Ringkasan</label>
            <textarea
                id="ringkasan"
                name="ringkasan"
                rows="3"
                maxlength="1000"
            ><?= e($profil['ringkasan']) ?></textarea>

            <small class="form-help">
                Pengenalan singkat untuk ditampilkan di beranda.
            </small>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi Perusahaan</label>
            <textarea
                id="deskripsi"
                name="deskripsi"
                rows="7"
                maxlength="20000"
            ><?= e($profil['deskripsi']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="sejarah">Sejarah Perusahaan</label>
            <textarea
                id="sejarah"
                name="sejarah"
                rows="6"
                maxlength="20000"
            ><?= e($profil['sejarah']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="visi">Visi</label>
            <textarea
                id="visi"
                name="visi"
                rows="3"
                maxlength="5000"
            ><?= e($profil['visi']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="misi">Misi</label>
            <textarea
                id="misi"
                name="misi"
                rows="5"
                maxlength="10000"
            ><?= e($profil['misi']) ?></textarea>

            <small class="form-help">
                Tulis setiap poin misi pada baris baru.
            </small>
        </div>

        <div class="form-group">
            <label for="foto">Foto Perusahaan</label>

            <?php if (!empty($profil['foto'])): ?>
                <img
                    class="image-preview"
                    src="<?= e(upload_url($profil['foto'])) ?>"
                    alt="Foto perusahaan saat ini"
                >
            <?php endif; ?>

            <input
                id="foto"
                name="foto"
                type="file"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="form-help">
                JPG, PNG, atau WebP. Maksimal 2 MB.
                Kosongkan jika tidak ingin mengganti foto.
            </small>
        </div>

        <button class="button" type="submit">
            Simpan Profil
        </button>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>