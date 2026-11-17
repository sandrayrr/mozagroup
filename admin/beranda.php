<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Konten Beranda';
$activeMenu = 'beranda';

$statement = $pdo->query(
    'SELECT * FROM pengaturan WHERE id = 1'
);

$data = $statement->fetch();

if (!$data) {
    http_response_code(500);
    exit('Pengaturan belum tersedia. Impor data awal database.');
}

$errors = [];

// Nama field, label, batas panjang, wajib diisi.
$fields = [
    'hero_judul' => ['Judul hero', 200, true],
    'hero_deskripsi' => ['Deskripsi hero', 2000, false],
    'hero_tombol_utama_teks' => ['Teks tombol utama', 100, true],
    'hero_tombol_utama_url' => ['Tujuan tombol utama', 255, true],
    'hero_tombol_kedua_teks' => ['Teks tombol kedua', 100, true],
    'hero_tombol_kedua_url' => ['Tujuan tombol kedua', 255, true],

    'keunggulan_judul' => ['Judul bagian keunggulan', 200, true],
    'keunggulan_1_judul' => ['Judul keunggulan pertama', 100, false],
    'keunggulan_1_deskripsi' => ['Deskripsi keunggulan pertama', 2000, false],
    'keunggulan_2_judul' => ['Judul keunggulan kedua', 100, false],
    'keunggulan_2_deskripsi' => ['Deskripsi keunggulan kedua', 2000, false],
    'keunggulan_3_judul' => ['Judul keunggulan ketiga', 100, false],
    'keunggulan_3_deskripsi' => ['Deskripsi keunggulan ketiga', 2000, false],

    'cta_judul' => ['Judul ajakan', 200, true],
    'cta_deskripsi' => ['Deskripsi ajakan', 2000, false],
    'cta_tombol_teks' => ['Teks tombol WhatsApp', 100, true],
];

// Pilihan halaman internal untuk tombol hero.
$destinations = [
    'tentang.php' => 'Tentang Kami',
    'unit-usaha.php' => 'Unit Usaha',
    'produk.php' => 'Produk',
    'kemitraan.php' => 'Kemitraan',
    'berita.php' => 'Berita',
    'galeri.php' => 'Galeri',
    'kontak.php' => 'Kontak',
];

if (is_post()) {
    csrf_verify();

    foreach ($fields as $field => [$label, $limit, $required]) {
        $data[$field] = input_string($_POST, $field);

        if ($required && $data[$field] === '') {
            $errors[] = $label . ' wajib diisi.';
        }

        if (text_length($data[$field]) > $limit) {
            $errors[] = $label . ' maksimal ' . $limit . ' karakter.';
        }
    }

    foreach (
        ['hero_tombol_utama_url', 'hero_tombol_kedua_url'] as $field
    ) {
        if (!array_key_exists($data[$field], $destinations)) {
            $errors[] = 'Tujuan tombol hero tidak valid.';
        }
    }

    if ($errors === []) {
        $newPhoto = null;
        $oldPhoto = $data['hero_foto'];

        try {
            $newPhoto = upload_image('hero_foto', 'perusahaan');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                // Nama kolom hanya berasal dari daftar tetap di atas.
                $assignments = [];
                $values = [];

                foreach (array_keys($fields) as $field) {
                    $assignments[] = '`' . $field . '` = ?';
                    $values[] = $data[$field];
                }

                $assignments[] = 'hero_foto = ?';
                $values[] = $newPhoto ?? $oldPhoto;

                $statement = $pdo->prepare(
                    'UPDATE pengaturan SET '
                    . implode(', ', $assignments)
                    . ' WHERE id = 1'
                );

                $statement->execute($values);
            } catch (PDOException $exception) {
                delete_uploaded_image($newPhoto, 'perusahaan');

                error_log(
                    'Simpan beranda gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Konten beranda gagal disimpan.';
            }

            if ($errors === []) {
                if ($newPhoto !== null) {
                    delete_uploaded_image($oldPhoto, 'perusahaan');
                }

                set_flash(
                    'success',
                    'Konten beranda berhasil diperbarui.'
                );

                redirect('admin/beranda.php');
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Konten Beranda</h1>
    <p class="muted">
        Atur banner utama, keunggulan perusahaan, dan ajakan menghubungi.
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
    action="<?= e(url('admin/beranda.php')) ?>"
    enctype="multipart/form-data"
>
    <?= csrf_field() ?>

    <section class="panel">
        <h2>Banner Utama</h2>

        <div class="form-group">
            <label for="hero_judul">Judul Hero *</label>
            <input
                type="text"
                id="hero_judul"
                name="hero_judul"
                value="<?= e($data['hero_judul']) ?>"
                maxlength="200"
                required
            >
        </div>

        <div class="form-group">
            <label for="hero_deskripsi">Deskripsi Hero</label>
            <textarea
                id="hero_deskripsi"
                name="hero_deskripsi"
                rows="4"
                maxlength="2000"
            ><?= e($data['hero_deskripsi']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="hero_foto">Foto Banner</label>

            <?php if (!empty($data['hero_foto'])): ?>
                <img
                    class="image-preview"
                    src="<?= e(upload_url($data['hero_foto'])) ?>"
                    alt="Banner saat ini"
                >
            <?php endif; ?>

            <input
                type="file"
                id="hero_foto"
                name="hero_foto"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="form-help">
                Maksimal 2 MB. Disarankan foto horizontal.
                Kosongkan untuk mempertahankan foto saat ini.
            </small>
        </div>

        <?php
        $buttons = [
            'utama' => 'Utama',
            'kedua' => 'Kedua',
        ];
        ?>

        <?php foreach ($buttons as $key => $label): ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="hero_tombol_<?= e($key) ?>_teks">
                        Teks Tombol <?= e($label) ?> *
                    </label>

                    <input
                        type="text"
                        id="hero_tombol_<?= e($key) ?>_teks"
                        name="hero_tombol_<?= e($key) ?>_teks"
                        value="<?= e($data['hero_tombol_' . $key . '_teks']) ?>"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="hero_tombol_<?= e($key) ?>_url">
                        Tujuan Tombol <?= e($label) ?> *
                    </label>

                    <select
                        id="hero_tombol_<?= e($key) ?>_url"
                        name="hero_tombol_<?= e($key) ?>_url"
                        required
                    >
                        <?php foreach ($destinations as $path => $title): ?>
                            <option
                                value="<?= e($path) ?>"
                                <?= $data['hero_tombol_' . $key . '_url'] === $path
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= e($title) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="panel">
        <h2>Keunggulan Perusahaan</h2>

        <div class="form-group">
            <label for="keunggulan_judul">Judul Bagian *</label>
            <input
                type="text"
                id="keunggulan_judul"
                name="keunggulan_judul"
                value="<?= e($data['keunggulan_judul']) ?>"
                maxlength="200"
                required
            >
        </div>

        <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="form-group">
                <label for="keunggulan_<?= $i ?>_judul">
                    Judul Keunggulan <?= $i ?>
                </label>

                <input
                    type="text"
                    id="keunggulan_<?= $i ?>_judul"
                    name="keunggulan_<?= $i ?>_judul"
                    value="<?= e($data['keunggulan_' . $i . '_judul']) ?>"
                    maxlength="100"
                >
            </div>

            <div class="form-group">
                <label for="keunggulan_<?= $i ?>_deskripsi">
                    Deskripsi Keunggulan <?= $i ?>
                </label>

                <textarea
                    id="keunggulan_<?= $i ?>_deskripsi"
                    name="keunggulan_<?= $i ?>_deskripsi"
                    rows="3"
                    maxlength="2000"
                ><?= e($data['keunggulan_' . $i . '_deskripsi']) ?></textarea>
            </div>
        <?php endfor; ?>

        <small class="form-help">
            Isi keunggulan sesuai layanan nyata perusahaan.
            Kosongkan judul jika keunggulan tersebut tidak digunakan.
        </small>
    </section>

    <section class="panel">
        <h2>Ajakan Menghubungi</h2>

        <div class="form-group">
            <label for="cta_judul">Judul Ajakan *</label>
            <input
                type="text"
                id="cta_judul"
                name="cta_judul"
                value="<?= e($data['cta_judul']) ?>"
                maxlength="200"
                required
            >
        </div>

        <div class="form-group">
            <label for="cta_deskripsi">Deskripsi Ajakan</label>
            <textarea
                id="cta_deskripsi"
                name="cta_deskripsi"
                rows="3"
                maxlength="2000"
            ><?= e($data['cta_deskripsi']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="cta_tombol_teks">Teks Tombol WhatsApp *</label>
            <input
                type="text"
                id="cta_tombol_teks"
                name="cta_tombol_teks"
                value="<?= e($data['cta_tombol_teks']) ?>"
                maxlength="100"
                required
            >

            <small class="form-help">
                Nomor tujuan akan diatur melalui Pengaturan Website.
            </small>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="button">
            Simpan Konten Beranda
        </button>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>