<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Tambah Produk';
$activeMenu = 'produk';

$units = $pdo->query(
    'SELECT id, nama, aktif
     FROM unit_usaha
     ORDER BY urutan ASC, nama ASC'
)->fetchAll();

if ($units === []) {
    set_flash('error', 'Tambahkan unit usaha terlebih dahulu.');
    redirect('admin/unit-usaha/tambah.php');
}

$validUnitIds = array_map(
    static fn (array $unit): int => (int) $unit['id'],
    $units
);

$errors = [];

$data = [
    'unit_usaha_id' => '',
    'nama' => '',
    'ringkasan' => '',
    'deskripsi' => '',
    'harga' => '',
    'keterangan_harga' => '',
    'unggulan' => '0',
    'aktif' => '1',
    'urutan' => '0',
];

$fields = [
    'nama' => ['Nama produk', 150],
    'ringkasan' => ['Ringkasan', 1000],
    'deskripsi' => ['Deskripsi', 20000],
    'keterangan_harga' => ['Keterangan harga', 150],
];

if (is_post()) {
    csrf_verify();

    foreach ($fields as $field => [$label, $limit]) {
        $data[$field] = input_string($_POST, $field);

        if (text_length($data[$field]) > $limit) {
            $errors[] = $label . ' maksimal ' . $limit . ' karakter.';
        }
    }

    foreach (
        ['unit_usaha_id', 'harga', 'unggulan', 'aktif', 'urutan'] as $field
    ) {
        $data[$field] = input_string($_POST, $field);
    }

    if ($data['nama'] === '') {
        $errors[] = 'Nama produk wajib diisi.';
    }

    $unitId = positive_id($data['unit_usaha_id']);

    if ($unitId === null || !in_array($unitId, $validUnitIds, true)) {
        $errors[] = 'Pilih unit usaha yang valid.';
    }

    // DECIMAL(15,2): maksimal 13 angka sebelum titik.
    // Harga disimpan sebagai string agar presisi tetap terjaga.
    $harga = null;

    if ($data['harga'] !== '') {
        if (!preg_match(
            '/^(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?$/D',
            $data['harga']
        )) {
            $errors[] = 'Harga harus berupa angka positif atau nol, '
                . 'maksimal 13 digit dan 2 angka desimal.';
        } else {
            $harga = $data['harga'];
        }
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

    if (!in_array($data['aktif'], ['0', '1'], true)) {
        $errors[] = 'Status produk tidak valid.';
    }

    if (!in_array($data['unggulan'], ['0', '1'], true)) {
        $errors[] = 'Pilihan produk unggulan tidak valid.';
    }

    $slug = '';

    if ($errors === []) {
        $baseSlug = rtrim(
            substr(slugify($data['nama']), 0, 160),
            '-'
        );

        $slug = $baseSlug;
        $suffix = 2;

        $statement = $pdo->prepare(
            'SELECT id FROM produk WHERE slug = ? LIMIT 1'
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
            $newPhoto = upload_image('foto', 'produk');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        if ($errors === []) {
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO produk (
                        unit_usaha_id,
                        nama,
                        slug,
                        ringkasan,
                        deskripsi,
                        foto,
                        harga,
                        keterangan_harga,
                        unggulan,
                        aktif,
                        urutan
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );

                $statement->execute([
                    $unitId,
                    $data['nama'],
                    $slug,
                    $data['ringkasan'],
                    $data['deskripsi'],
                    $newPhoto,
                    $harga,
                    $data['keterangan_harga'],
                    (int) $data['unggulan'],
                    (int) $data['aktif'],
                    $urutan,
                ]);
            } catch (PDOException $exception) {
                error_log(
                    'Tambah produk gagal: ' . $exception->getMessage()
                );

                $errors[] = 'Produk gagal disimpan. '
                    . 'Pastikan unit usaha masih tersedia, lalu coba kembali.';
            }
        }

        if ($errors !== []) {
            delete_uploaded_image($newPhoto, 'produk');
        } else {
            set_flash('success', 'Produk berhasil ditambahkan.');
            redirect('admin/produk/index.php');
        }
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="page-heading page-heading-actions">
    <div>
        <h1>Tambah Produk</h1>

        <p class="muted">
            Tambahkan produk atau paket layanan dari unit usaha.
        </p>
    </div>

    <a
        class="button button-outline"
        href="<?= e(url('admin/produk/index.php')) ?>"
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
    action="<?= e(url('admin/produk/tambah.php')) ?>"
    enctype="multipart/form-data"
>
    <?= csrf_field() ?>

    <section class="panel">
        <h2>Informasi Produk</h2>

        <div class="form-group">
            <label for="unit_usaha_id">Unit Usaha *</label>

            <select id="unit_usaha_id" name="unit_usaha_id" required>
                <option value="">Pilih Unit Usaha</option>

                <?php foreach ($units as $unit): ?>
                    <option
                        value="<?= e($unit['id']) ?>"
                        <?= $data['unit_usaha_id'] === (string) $unit['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($unit['nama']) ?>
                        <?= (int) $unit['aktif'] === 1 ? '' : ' (Nonaktif)' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <small class="form-help">
                Produk hanya tampil di website jika produk dan unit usahanya aktif.
            </small>
        </div>

        <div class="form-group">
            <label for="nama">Nama Produk *</label>

            <input
                type="text"
                id="nama"
                name="nama"
                value="<?= e($data['nama']) ?>"
                maxlength="150"
                placeholder="Contoh: Paket Cuci Setrika"
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
            ><?= e($data['ringkasan']) ?></textarea>

            <small class="form-help">
                Penjelasan singkat untuk kartu produk.
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

            <small class="form-help">
                Tuliskan detail layanan, fasilitas, atau ketentuan produk.
                Gunakan teks biasa.
            </small>
        </div>
    </section>

    <section class="panel">
        <h2>Foto Produk</h2>

        <div class="form-group">
            <label for="foto">Upload Foto</label>

            <input
                type="file"
                id="foto"
                name="foto"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="form-help">
                JPG, PNG, atau WebP. Maksimal 2 MB.
            </small>
        </div>
    </section>

    <section class="panel">
        <h2>Harga</h2>

        <div class="form-grid">
            <div class="form-group">
                <label for="harga">Harga dalam Rupiah</label>

                <input
                    type="number"
                    id="harga"
                    name="harga"
                    value="<?= e($data['harga']) ?>"
                    min="0"
                    max="9999999999999.99"
                    step="0.01"
                    placeholder="Contoh: 7000"
                >

                <small class="form-help">
                    Masukkan angka tanpa pemisah ribuan.
                    Kosongkan untuk menampilkan “Hubungi kami”.
                </small>
            </div>

            <div class="form-group">
                <label for="keterangan_harga">Keterangan Harga</label>

                <input
                    type="text"
                    id="keterangan_harga"
                    name="keterangan_harga"
                    value="<?= e($data['keterangan_harga']) ?>"
                    maxlength="150"
                    placeholder="Contoh: per kg / per orang / mulai dari"
                >
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Pengaturan Tampilan</h2>

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
                <label for="unggulan">Produk Unggulan *</label>

                <select id="unggulan" name="unggulan" required>
                    <option
                        value="0"
                        <?= $data['unggulan'] === '0' ? 'selected' : '' ?>
                    >
                        Tidak
                    </option>

                    <option
                        value="1"
                        <?= $data['unggulan'] === '1' ? 'selected' : '' ?>
                    >
                        Ya
                    </option>
                </select>

                <small class="form-help">
                    Produk unggulan dapat ditampilkan pada beranda.
                </small>
            </div>
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
    </section>

    <div class="form-actions">
        <button type="submit" class="button">
            Simpan Produk
        </button>
    </div>
</form>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>