<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Tambah Berita';
$activeMenu = 'berita';

$errors = [];

$data = [
    'judul' => '',
    'ringkasan' => '',
    'isi' => '',
    'status' => 'draft',
    'published_at' => '',
];

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
        // Input datetime-local menggunakan format YYYY-MM-DDTHH:MM.
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

    // Status terbit tanpa tanggal berarti diterbitkan sekarang.
    if ($data['status'] === 'terbit' && $data['published_at'] === '') {
        $publishedAt = date('Y-m-d H:i:s');
    }

    // Draft tidak memiliki tanggal publikasi.
    if ($data['status'] === 'draft') {
        $publishedAt = null;
    }

    $slug = '';

    if ($errors === []) {
        $baseSlug = rtrim(
            substr(slugify($data['judul']), 0, 160),
            '-'
        );

        $slug = $baseSlug;
        $suffix = 2;

        $statement = $pdo->prepare(
            'SELECT id FROM berita WHERE slug = ? LIMIT 1'
        );

        while (true) {
            $statement->execute([$slug]);

            if (!$statement->fetch()) {
                break;
            }

            $slug = $baseSlug . '-' . $suffix++;
        }
    }

    if ($errors === []) {
        $newPhoto = null;

        try {
            $newPhoto = upload_image('foto', 'berita');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO berita (
                        judul,
                        slug,
                        ringkasan,
                        isi,
                        foto,
                        status,
                        published_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)'
                );

                $statement->execute([
                    $data['judul'],
                    $slug,
                    $data['ringkasan'],
                    $data['isi'],
                    $newPhoto,
                    $data['status'],
                    $publishedAt,
                ]);
            } catch (PDOException $exception) {
                error_log(
                    'Tambah berita gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Berita gagal disimpan. Silakan coba kembali.';
            }
        }

        if ($errors !== []) {
            delete_uploaded_image($newPhoto, 'berita');
        } else {
            set_flash('success', 'Berita berhasil disimpan.');
            redirect('admin/berita/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Tambah Berita</h1>

        <p class="muted">
            Tulis berita atau informasi kegiatan Moza Group.
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
    action="<?= e(url('admin/berita/tambah.php')) ?>"
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
                Slug alamat berita dibuat otomatis dari judul.
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

            <small class="form-help">
                Pengantar singkat untuk kartu berita.
            </small>
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
            <label for="foto">Foto Utama</label>

            <input
                type="file"
                id="foto"
                name="foto"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="form-help">
                JPG, PNG, atau WebP. Maksimal 2 MB.
                Disarankan foto horizontal.
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
                    Draft tidak ditampilkan kepada pengunjung.
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
                    Untuk status Terbit: kosongkan agar terbit sekarang,
                    atau pilih waktu mendatang untuk menjadwalkan.
                    Tanggal tidak disimpan jika statusnya Draft.
                </small>
            </div>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="button">
            Simpan Berita
        </button>
    </div>
</form>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>