<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Pengaturan Website';
$activeMenu = 'pengaturan';

$data = $pdo->query(
    'SELECT * FROM pengaturan WHERE id = 1'
)->fetch();

if (!$data) {
    http_response_code(500);
    exit('Pengaturan belum tersedia. Impor data awal database.');
}

$errors = [];

// Field, label, batas karakter.
$fields = [
    'alamat' => ['Alamat kantor', 2000],
    'email' => ['Email', 191],
    'whatsapp' => ['WhatsApp', 20],
    'maps_url' => ['Tautan Google Maps', 2000],
    'instagram' => ['Instagram', 255],
    'facebook' => ['Facebook', 255],
    'tiktok' => ['TikTok', 255],
    'youtube' => ['YouTube', 255],
    'seo_judul' => ['Judul SEO', 200],
    'seo_deskripsi' => ['Deskripsi SEO', 300],
];

if (is_post()) {
    csrf_verify();

    foreach ($fields as $field => [$label, $limit]) {
        $data[$field] = input_string($_POST, $field);

        if (text_length($data[$field]) > $limit) {
            $errors[] = $label . ' maksimal ' . $limit . ' karakter.';
        }
    }

    if ($data['email'] !== '') {
        $error = validate_email($data['email']);

        if ($error !== null) {
            $errors[] = $error;
        }
    }

    if ($data['whatsapp'] !== '') {
        $number = normalize_whatsapp($data['whatsapp']);

        if (!preg_match('/^[1-9][0-9]{7,14}$/', $number)) {
            $errors[] = 'Nomor WhatsApp tidak valid.';
        } else {
            $data['whatsapp'] = $number;
        }
    }

    foreach (
        ['instagram', 'facebook', 'tiktok', 'youtube'] as $field
    ) {
        $error = validate_url($data[$field], $fields[$field][0]);

        if ($error !== null) {
            $errors[] = $error;
        }
    }

    // Peta disimpan sebagai tautan, bukan kode iframe atau HTML.
    if ($data['maps_url'] !== '') {
        $scheme = strtolower(
            (string) parse_url($data['maps_url'], PHP_URL_SCHEME)
        );

        if (
            !filter_var($data['maps_url'], FILTER_VALIDATE_URL)
            || !in_array($scheme, ['http', 'https'], true)
        ) {
            $errors[] = 'Tautan peta harus berupa URL HTTP atau HTTPS.';
        }
    }

    if ($data['seo_judul'] === '') {
        $errors[] = 'Judul SEO wajib diisi.';
    }

    if ($errors === []) {
        $newFiles = [];
        $oldFiles = [
            'logo' => $data['logo'],
            'favicon' => $data['favicon'],
        ];

        try {
            foreach (['logo', 'favicon'] as $field) {
                $newFiles[$field] = upload_image($field, 'perusahaan');
            }
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $assignments = [];
                $values = [];

                // Nama kolom berasal dari daftar tetap di atas.
                foreach (array_keys($fields) as $field) {
                    $assignments[] = '`' . $field . '` = ?';
                    $values[] = $data[$field];
                }

                foreach (['logo', 'favicon'] as $field) {
                    $assignments[] = '`' . $field . '` = ?';
                    $values[] = $newFiles[$field] ?? $oldFiles[$field];
                }

                $statement = $pdo->prepare(
                    'UPDATE pengaturan SET '
                    . implode(', ', $assignments)
                    . ' WHERE id = 1'
                );

                $statement->execute($values);
            } catch (PDOException $exception) {
                error_log(
                    'Simpan pengaturan gagal: '
                    . $exception->getMessage()
                );

                $errors[] = 'Pengaturan gagal disimpan. Coba kembali.';
            }
        }

        if ($errors !== []) {
            // Bersihkan semua upload baru jika proses gagal.
            foreach ($newFiles as $path) {
                delete_uploaded_image($path, 'perusahaan');
            }
        } else {
            foreach ($newFiles as $field => $path) {
                if ($path !== null) {
                    delete_uploaded_image(
                        $oldFiles[$field],
                        'perusahaan'
                    );
                }
            }

            set_flash('success', 'Pengaturan website berhasil disimpan.');

            redirect('admin/pengaturan.php');
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Pengaturan Website</h1>

    <p class="muted">
        Kelola identitas website, informasi kontak, dan metadata dasar.
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

<form
    method="post"
    action="<?= e(url('admin/pengaturan.php')) ?>"
    enctype="multipart/form-data"
>
    <?= csrf_field() ?>

    <section class="panel">
        <h2>Identitas Website</h2>

        <div class="form-grid">
            <?php foreach (['logo' => 'Logo', 'favicon' => 'Favicon'] as $field => $label): ?>
                <div class="form-group">
                    <label for="<?= e($field) ?>">
                        <?= e($label) ?>
                    </label>

                    <?php if (!empty($data[$field])): ?>
                        <img
                            class="image-preview"
                            src="<?= e(upload_url($data[$field])) ?>"
                            alt="<?= e($label) ?> saat ini"
                        >
                    <?php endif; ?>

                    <input
                        type="file"
                        id="<?= e($field) ?>"
                        name="<?= e($field) ?>"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small class="form-help">
                        JPG, PNG, atau WebP, maksimal 2 MB.
                        Kosongkan untuk mempertahankan gambar.
                        <?= $field === 'favicon'
                            ? 'Gunakan gambar persegi, misalnya 128 × 128 piksel.'
                            : '' ?>
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <h2>Informasi Kontak</h2>

        <div class="form-group">
            <label for="alamat">Alamat Kantor</label>

            <textarea
                id="alamat"
                name="alamat"
                rows="4"
                maxlength="2000"
            ><?= e($data['alamat']) ?></textarea>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="email">Email Perusahaan</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= e($data['email']) ?>"
                    maxlength="191"
                    placeholder="info@perusahaan.com"
                >
            </div>

            <div class="form-group">
                <label for="whatsapp">Nomor WhatsApp</label>

                <input
                    type="tel"
                    id="whatsapp"
                    name="whatsapp"
                    value="<?= e($data['whatsapp']) ?>"
                    maxlength="20"
                    placeholder="6281234567890"
                >

                <small class="form-help">
                    Nomor berawalan 08 akan diubah menjadi 62.
                </small>
            </div>
        </div>

        <div class="form-group">
            <label for="maps_url">Tautan Google Maps</label>

            <input
                type="url"
                id="maps_url"
                name="maps_url"
                value="<?= e($data['maps_url']) ?>"
                maxlength="2000"
                placeholder="https://maps.app.goo.gl/..."
            >

            <small class="form-help">
                Tempel tautan lokasi dari Google Maps, bukan kode iframe.
                Tautan ini akan digunakan untuk tombol “Buka Peta”.
            </small>
        </div>
    </section>

    <section class="panel">
        <h2>Media Sosial</h2>

        <p class="muted">
            Masukkan URL lengkap. Kosongkan jika belum tersedia.
        </p>

        <div class="form-grid">
            <?php
            $socials = [
                'instagram' => ['Instagram', 'https://www.instagram.com/username/'],
                'facebook' => ['Facebook', 'https://www.facebook.com/username'],
                'tiktok' => ['TikTok', 'https://www.tiktok.com/@username'],
                'youtube' => ['YouTube', 'https://www.youtube.com/@username'],
            ];
            ?>

            <?php foreach ($socials as $field => [$label, $placeholder]): ?>
                <div class="form-group">
                    <label for="<?= e($field) ?>">
                        <?= e($label) ?>
                    </label>

                    <input
                        type="url"
                        id="<?= e($field) ?>"
                        name="<?= e($field) ?>"
                        value="<?= e($data[$field]) ?>"
                        maxlength="255"
                        placeholder="<?= e($placeholder) ?>"
                    >
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <h2>SEO Dasar</h2>

        <div class="form-group">
            <label for="seo_judul">Judul Website *</label>

            <input
                type="text"
                id="seo_judul"
                name="seo_judul"
                value="<?= e($data['seo_judul']) ?>"
                maxlength="200"
                required
            >

            <small class="form-help">
                Contoh: Moza Group | Profil Perusahaan dan Unit Usaha
            </small>
        </div>

        <div class="form-group">
            <label for="seo_deskripsi">Deskripsi Website</label>

            <textarea
                id="seo_deskripsi"
                name="seo_deskripsi"
                rows="4"
                maxlength="300"
            ><?= e($data['seo_deskripsi']) ?></textarea>

            <small class="form-help">
                Tulis ringkasan perusahaan secara jelas.
                Deskripsi ini akan menjadi metadata bawaan website.
            </small>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="button">
            Simpan Pengaturan
        </button>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>