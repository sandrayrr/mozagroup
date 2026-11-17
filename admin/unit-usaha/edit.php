<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Edit Unit Usaha';
$activeMenu = 'unit-usaha';

$id = positive_id($_GET['id'] ?? null);

if ($id === null) {
    set_flash('error', 'ID unit usaha tidak valid.');
    redirect('admin/unit-usaha/index.php');
}

$statement = $pdo->prepare(
    'SELECT * FROM unit_usaha WHERE id = ? LIMIT 1'
);

$statement->execute([$id]);

$data = $statement->fetch();

if (!$data) {
    set_flash('error', 'Unit usaha tidak ditemukan.');
    redirect('admin/unit-usaha/index.php');
}

$data['aktif'] = (string) $data['aktif'];
$data['urutan'] = (string) $data['urutan'];

$errors = [];

$fields = [
    'nama' => ['Nama unit usaha', 150],
    'ringkasan' => ['Ringkasan', 1000],
    'deskripsi' => ['Deskripsi', 20000],
    'layanan' => ['Layanan', 10000],
    'alamat' => ['Alamat', 2000],
    'jam_operasional' => ['Jam operasional', 255],
    'whatsapp' => ['WhatsApp', 20],
    'website' => ['Website', 255],
];

if (is_post()) {
    csrf_verify();

    foreach ($fields as $field => [$label, $limit]) {
        $data[$field] = input_string($_POST, $field);

        if (text_length($data[$field]) > $limit) {
            $errors[] = $label . ' maksimal ' . $limit . ' karakter.';
        }
    }

    if ($data['nama'] === '') {
        $errors[] = 'Nama unit usaha wajib diisi.';
    }

    $data['urutan'] = input_string($_POST, 'urutan');
    $data['aktif'] = input_string($_POST, 'aktif');

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

    if (!in_array($data['aktif'], ['0', '1'], true)) {
        $errors[] = 'Status unit usaha tidak valid.';
    }

    if ($data['whatsapp'] !== '') {
        $number = normalize_whatsapp($data['whatsapp']);

        if (!preg_match('/^[1-9][0-9]{7,14}$/', $number)) {
            $errors[] = 'Nomor WhatsApp tidak valid.';
        } else {
            $data['whatsapp'] = $number;
        }
    }

    $websiteError = validate_url($data['website'], 'Website');

    if ($websiteError !== null) {
        $errors[] = $websiteError;
    }

    if ($errors === []) {
        $newFiles = [];

        try {
            $newFiles['logo'] = upload_image('logo', 'unit-usaha');
            $newFiles['foto'] = upload_image('foto', 'unit-usaha');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        $oldFiles = [];
        $transactionStarted = false;

        if ($errors === []) {
            try {
                $pdo->beginTransaction();
                $transactionStarted = true;

                // Ambil gambar terkini dan kunci data saat disimpan.
                $statement = $pdo->prepare(
                    'SELECT logo, foto
                     FROM unit_usaha
                     WHERE id = ?
                     FOR UPDATE'
                );

                $statement->execute([$id]);

                $current = $statement->fetch();

                if (!$current) {
                    $errors[] = 'Unit usaha sudah tidak tersedia.';
                    $pdo->rollBack();
                    $transactionStarted = false;
                } else {
                    $oldFiles = $current;

                    $statement = $pdo->prepare(
                        'UPDATE unit_usaha
                         SET nama = ?,
                             ringkasan = ?,
                             deskripsi = ?,
                             layanan = ?,
                             logo = ?,
                             foto = ?,
                             alamat = ?,
                             jam_operasional = ?,
                             whatsapp = ?,
                             website = ?,
                             urutan = ?,
                             aktif = ?
                         WHERE id = ?'
                    );

                    $statement->execute([
                        $data['nama'],
                        $data['ringkasan'],
                        $data['deskripsi'],
                        $data['layanan'],
                        $newFiles['logo'] ?? $current['logo'],
                        $newFiles['foto'] ?? $current['foto'],
                        $data['alamat'],
                        $data['jam_operasional'],
                        $data['whatsapp'],
                        $data['website'],
                        $urutan,
                        (int) $data['aktif'],
                        $id,
                    ]);

                    $pdo->commit();
                    $transactionStarted = false;
                }
            } catch (PDOException $exception) {
                if ($transactionStarted && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log(
                    'Edit unit usaha gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Perubahan gagal disimpan. Silakan coba kembali.';
            }
        }

        if ($errors !== []) {
            foreach ($newFiles as $path) {
                delete_uploaded_image($path, 'unit-usaha');
            }
        } else {
            foreach ($newFiles as $field => $path) {
                if ($path !== null) {
                    delete_uploaded_image(
                        $oldFiles[$field],
                        'unit-usaha'
                    );
                }
            }

            set_flash('success', 'Unit usaha berhasil diperbarui.');
            redirect('admin/unit-usaha/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Edit Unit Usaha</h1>

        <p class="muted">
            Perbarui informasi, gambar, dan status unit usaha.
        </p>
    </div>

    <a
        class="button button-outline"
        href="<?= e(url('admin/unit-usaha/index.php')) ?>"
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
    action="<?= e(url('admin/unit-usaha/edit.php?id=' . $id)) ?>"
    enctype="multipart/form-data"
>
    <?= csrf_field() ?>

    <section class="panel">
        <h2>Informasi Unit Usaha</h2>

        <div class="form-group">
            <label for="nama">Nama Unit Usaha *</label>

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
            'layanan' => ['Daftar Layanan', 5, 10000],
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

                <?php if ($field === 'layanan'): ?>
                    <small class="form-help">
                        Tulis satu layanan pada setiap baris.
                    </small>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="panel">
        <h2>Logo dan Foto</h2>

        <div class="form-grid">
            <?php foreach (['logo' => 'Logo Unit Usaha', 'foto' => 'Foto Utama'] as $field => $label): ?>
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
                        JPG, PNG, atau WebP. Maksimal 2 MB.
                        Kosongkan untuk mempertahankan gambar.
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <h2>Lokasi dan Kontak</h2>

        <div class="form-group">
            <label for="alamat">Alamat</label>

            <textarea
                id="alamat"
                name="alamat"
                rows="3"
                maxlength="2000"
            ><?= e($data['alamat']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="jam_operasional">Jam Operasional</label>

            <input
                type="text"
                id="jam_operasional"
                name="jam_operasional"
                value="<?= e($data['jam_operasional']) ?>"
                maxlength="255"
            >
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="whatsapp">WhatsApp Unit Usaha</label>

                <input
                    type="tel"
                    id="whatsapp"
                    name="whatsapp"
                    value="<?= e($data['whatsapp']) ?>"
                    maxlength="20"
                    placeholder="6281234567890"
                >
            </div>

            <div class="form-group">
                <label for="website">Website Unit Usaha</label>

                <input
                    type="url"
                    id="website"
                    name="website"
                    value="<?= e($data['website']) ?>"
                    maxlength="255"
                    placeholder="https://..."
                >
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Pengaturan Tampilan</h2>

        <div class="form-grid">
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

                <small class="form-help">
                    Unit nonaktif beserta produknya harus disembunyikan
                    pada halaman publik.
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