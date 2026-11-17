<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Tambah Unit Usaha';
$activeMenu = 'unit-usaha';

$errors = [];

$data = [
    'nama' => '',
    'ringkasan' => '',
    'deskripsi' => '',
    'layanan' => '',
    'alamat' => '',
    'jam_operasional' => '',
    'whatsapp' => '',
    'website' => '',
    'urutan' => '0',
    'aktif' => '1',
];

// Nama field, label, batas panjang.
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

    $slug = '';

    if ($errors === []) {
        // Batasi slug dasar agar masih tersedia ruang untuk akhiran.
        $baseSlug = substr(slugify($data['nama']), 0, 160);
        $baseSlug = rtrim($baseSlug, '-');

        $slug = $baseSlug;
        $suffix = 2;

        $statement = $pdo->prepare(
            'SELECT id FROM unit_usaha WHERE slug = ? LIMIT 1'
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
        $newFiles = [];

        try {
            $newFiles['logo'] = upload_image('logo', 'unit-usaha');
            $newFiles['foto'] = upload_image('foto', 'unit-usaha');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO unit_usaha (
                        nama,
                        slug,
                        ringkasan,
                        deskripsi,
                        layanan,
                        logo,
                        foto,
                        alamat,
                        jam_operasional,
                        whatsapp,
                        website,
                        urutan,
                        aktif
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );

                $statement->execute([
                    $data['nama'],
                    $slug,
                    $data['ringkasan'],
                    $data['deskripsi'],
                    $data['layanan'],
                    $newFiles['logo'],
                    $newFiles['foto'],
                    $data['alamat'],
                    $data['jam_operasional'],
                    $data['whatsapp'],
                    $data['website'],
                    $urutan,
                    (int) $data['aktif'],
                ]);
            } catch (PDOException $exception) {
                error_log(
                    'Tambah unit usaha gagal: '
                    . $exception->getMessage()
                );

                $errors[] = 'Unit usaha gagal disimpan. Silakan coba kembali.';
            }
        }

        if ($errors !== []) {
            // Hapus upload baru jika salah satu proses gagal.
            foreach ($newFiles as $path) {
                delete_uploaded_image($path, 'unit-usaha');
            }
        } else {
            set_flash('success', 'Unit usaha berhasil ditambahkan.');

            redirect('admin/unit-usaha/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Tambah Unit Usaha</h1>

        <p class="muted">
            Masukkan informasi bisnis di bawah Moza Group.
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
    action="<?= e(url('admin/unit-usaha/tambah.php')) ?>"
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
                placeholder="Contoh: Moza Laundry"
                required
            >

            <small class="form-help">
                Slug alamat detail akan dibuat otomatis dari nama.
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
                Deskripsi singkat untuk kartu unit usaha.
            </small>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi Lengkap</label>

            <textarea
                id="deskripsi"
                name="deskripsi"
                rows="7"
                maxlength="20000"
            ><?= e($data['deskripsi']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="layanan">Daftar Layanan</label>

            <textarea
                id="layanan"
                name="layanan"
                rows="5"
                maxlength="10000"
            ><?= e($data['layanan']) ?></textarea>

            <small class="form-help">
                Tulis satu layanan pada setiap baris.
            </small>
        </div>
    </section>

    <section class="panel">
        <h2>Logo dan Foto</h2>

        <div class="form-grid">
            <div class="form-group">
                <label for="logo">Logo Unit Usaha</label>

                <input
                    type="file"
                    id="logo"
                    name="logo"
                    accept="image/jpeg,image/png,image/webp"
                >

                <small class="form-help">
                    JPG, PNG, atau WebP. Maksimal 2 MB.
                    Disarankan logo PNG dengan latar transparan.
                </small>
            </div>

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
                placeholder="Contoh: Senin–Minggu, 08.00–20.00 WIB"
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

                <small class="form-help">
                    Angka lebih kecil ditampilkan lebih dahulu.
                </small>
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
                    Unit nonaktif tidak ditampilkan di website publik.
                </small>
            </div>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="button">
            Simpan Unit Usaha
        </button>
    </div>
</form>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>