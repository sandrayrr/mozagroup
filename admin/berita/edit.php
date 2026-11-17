<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Edit Berita';
$activeMenu = 'berita';

$id = positive_id($_GET['id'] ?? null);

if ($id === null) {
    set_flash('error', 'ID berita tidak valid.');
    redirect('admin/berita/index.php');
}

$statement = $pdo->prepare(
    'SELECT * FROM berita WHERE id = ? LIMIT 1'
);

$statement->execute([$id]);

$data = $statement->fetch();

if (!$data) {
    set_flash('error', 'Berita tidak ditemukan.');
    redirect('admin/berita/index.php');
}

// Ubah format database menjadi format input datetime-local.
$data['published_at'] = !empty($data['published_at'])
    ? str_replace(' ', 'T', substr($data['published_at'], 0, 16))
    : '';

$errors = [];

$fields = [
    'judul' => ['Judul berita', 200],
    'ringkasan' => ['Ringkasan', 1000],
    'isi' => ['Isi berita', 100000],
];

if (is_post()) {
    csrf_verify();

    foreach ($fields as $field => [$label, $limit]) {
        $data[$field] = input_string($_POST, $field);

        if (text_length($data[$field]) > $limit) {
            $errors[] = $label . ' maksimal ' . $limit . ' karakter.';
        }
    }

    $data['status'] = input_string($_POST, 'status');
    $data['published_at'] = input_string($_POST, 'published_at');

    if ($data['judul'] === '') {
        $errors[] = 'Judul berita wajib diisi.';
    }

    if ($data['isi'] === '') {
        $errors[] = 'Isi berita wajib diisi.';
    }

    if (!in_array($data['status'], ['draft', 'terbit'], true)) {
        $errors[] = 'Status berita tidak valid.';
    }

    $publishedAt = null;

    if ($data['published_at'] !== '') {
        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d\TH:i',
            $data['published_at'],
            new DateTimeZone('Asia/Jakarta')
        );

        if (
            !$date
            || $date->format('Y-m-d\TH:i') !== $data['published_at']
            || (int) $date->format('Y') < 1000
            || (int) $date->format('Y') > 9999
        ) {
            $errors[] = 'Tanggal publikasi tidak valid.';
        } else {
            $publishedAt = $date->format('Y-m-d H:i:s');
        }
    }

    if ($data['status'] === 'terbit' && $data['published_at'] === '') {
        $publishedAt = date('Y-m-d H:i:s');
    }

    if ($data['status'] === 'draft') {
        $publishedAt = null;
    }

    if ($errors === []) {
        $newPhoto = null;
        $oldPhoto = null;

        try {
            $newPhoto = upload_image('foto', 'berita');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $pdo->beginTransaction();

                $statement = $pdo->prepare(
                    'SELECT foto
                     FROM berita
                     WHERE id = ?
                     FOR UPDATE'
                );

                $statement->execute([$id]);

                $current = $statement->fetch();

                if (!$current) {
                    $pdo->rollBack();
                    $errors[] = 'Berita sudah tidak tersedia.';
                } else {
                    $oldPhoto = $current['foto'];

                    $statement = $pdo->prepare(
                        'UPDATE berita
                         SET judul = ?,
                             ringkasan = ?,
                             isi = ?,
                             foto = ?,
                             status = ?,
                             published_at = ?
                         WHERE id = ?'
                    );

                    $statement->execute([
                        $data['judul'],
                        $data['ringkasan'],
                        $data['isi'],
                        $newPhoto ?? $oldPhoto,
                        $data['status'],
                        $publishedAt,
                        $id,
                    ]);

                    $pdo->commit();
                }
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log(
                    'Edit berita gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Berita gagal diperbarui. Silakan coba kembali.';
            }
        }

        if ($errors !== []) {
            delete_uploaded_image($newPhoto, 'berita');
        } else {
            if ($newPhoto !== null) {
                delete_uploaded_image($oldPhoto, 'berita');
            }

            set_flash('success', 'Berita berhasil diperbarui.');
            redirect('admin/berita/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Edit Berita</h1>

        <p class="muted">
            Perbarui konten, foto, dan pengaturan publikasi.
        </p>
    </div>

    <a
        class="button button-outline"
        href="<?= e(url('admin/berita/index.php')) ?>"
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
    action="<?= e(url('admin/berita/edit.php?id=' . $id)) ?>"
    enctype="multipart/form-data"
>
    <?= csrf_field() ?>

    <section class="panel">
        <h2>Konten Berita</h2>

        <div class="form-group">
            <label for="judul">Judul Berita *</label>

            <input
                type="text"
                id="judul"
                name="judul"
                value="<?= e($data['judul']) ?>"
                maxlength="200"
                required
            >

            <small class="form-help">
                Slug: <?= e($data['slug']) ?>.
                Mengubah judul tidak mengubah slug.
            </small>
        </div>

        <div class="form-group">
            <label for="ringkasan">Ringkasan</label>

            <textarea
                id="ringkasan"
                name="ringkasan"
                rows="3"
                maxlength="1000"
            ><?= e($data['ringkasan']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="isi">Isi Berita *</label>

            <textarea
                id="isi"
                name="isi"
                rows="15"
                maxlength="100000"
                required
            ><?= e($data['isi']) ?></textarea>

            <small class="form-help">
                Gunakan teks biasa. Pisahkan paragraf dengan baris kosong.
            </small>
        </div>
    </section>

    <section class="panel">
        <h2>Foto Berita</h2>

        <div class="form-group">
            <label for="foto">Ganti Foto Utama</label>

            <?php if (!empty($data['foto'])): ?>
                <img
                    class="image-preview"
                    src="<?= e(upload_url($data['foto'])) ?>"
                    alt="Foto berita saat ini"
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
        <h2>Publikasi</h2>

        <div class="form-grid">
            <div class="form-group">
                <label for="status">Status *</label>

                <select id="status" name="status" required>
                    <option
                        value="draft"
                        <?= $data['status'] === 'draft' ? 'selected' : '' ?>
                    >
                        Draft
                    </option>

                    <option
                        value="terbit"
                        <?= $data['status'] === 'terbit' ? 'selected' : '' ?>
                    >
                        Terbit
                    </option>
                </select>

                <small class="form-help">
                    Mengubah status ke Draft menyembunyikan berita
                    dari pengunjung dan mengosongkan tanggal publikasinya.
                </small>
            </div>

            <div class="form-group">
                <label for="published_at">Tanggal Publikasi — WIB</label>

                <input
                    type="datetime-local"
                    id="published_at"
                    name="published_at"
                    value="<?= e($data['published_at']) ?>"
                    min="1000-01-01T00:00"
                    max="9999-12-31T23:59"
                    step="60"
                >

                <small class="form-help">
                    Untuk status Terbit, kosongkan agar menggunakan
                    waktu sekarang. Pilih waktu mendatang untuk menjadwalkan.
                </small>
            </div>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="button">
            Simpan Perubahan
        </button>
    </div>
</form>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>