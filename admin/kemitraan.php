<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$admin = require_admin();

$pageTitle = 'Kemitraan';
$activeMenu = 'kemitraan';

$data = $pdo->query(
    'SELECT * FROM kemitraan WHERE id = 1'
)->fetch();

if (!$data) {
    http_response_code(500);

    exit('Data kemitraan belum tersedia. Impor data awal database.');
}

$errors = [];

// Nama field, label, batas karakter, wajib diisi.
$fields = [
    'judul' => ['Judul Halaman', 200, true],
    'pengantar' => ['Pengantar', 5000, false],
    'jenis_kerja_sama' => ['Jenis Kerja Sama', 10000, false],
    'manfaat' => ['Manfaat Kemitraan', 10000, false],
    'persyaratan' => ['Persyaratan', 10000, false],
    'alur_pengajuan' => ['Alur Pengajuan', 10000, false],
    'whatsapp' => ['WhatsApp Kemitraan', 20, false],
    'cta_judul' => ['Judul Ajakan', 200, true],
    'cta_deskripsi' => ['Deskripsi Ajakan', 5000, false],
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

    if ($data['whatsapp'] !== '') {
        $number = normalize_whatsapp($data['whatsapp']);

        if (!preg_match('/^[1-9][0-9]{7,14}$/', $number)) {
            $errors[] = 'Nomor WhatsApp kemitraan tidak valid.';
        } else {
            $data['whatsapp'] = $number;
        }
    }

    if ($errors === []) {
        try {
            $statement = $pdo->prepare(
                'UPDATE kemitraan
                 SET judul = ?,
                     pengantar = ?,
                     jenis_kerja_sama = ?,
                     manfaat = ?,
                     persyaratan = ?,
                     alur_pengajuan = ?,
                     whatsapp = ?,
                     cta_judul = ?,
                     cta_deskripsi = ?
                 WHERE id = 1'
            );

            $statement->execute([
                $data['judul'],
                $data['pengantar'],
                $data['jenis_kerja_sama'],
                $data['manfaat'],
                $data['persyaratan'],
                $data['alur_pengajuan'],
                $data['whatsapp'],
                $data['cta_judul'],
                $data['cta_deskripsi'],
            ]);
        } catch (PDOException $exception) {
            error_log(
                'Simpan kemitraan gagal: ' . $exception->getMessage()
            );

            $errors[] = 'Informasi kemitraan gagal disimpan. Coba kembali.';
        }

        if ($errors === []) {
            set_flash(
                'success',
                'Informasi kemitraan berhasil diperbarui.'
            );

            redirect('admin/kemitraan.php');
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h1>Kemitraan</h1>

    <p class="muted">
        Kelola informasi peluang kerja sama dengan Moza Group.
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
    action="<?= e(url('admin/kemitraan.php')) ?>"
>
    <?= csrf_field() ?>

    <section class="panel">
        <h2>Pengenalan Kemitraan</h2>

        <div class="form-group">
            <label for="judul">Judul Halaman *</label>

            <input
                type="text"
                id="judul"
                name="judul"
                value="<?= e($data['judul']) ?>"
                maxlength="200"
                placeholder="Kemitraan Moza Group"
                required
            >
        </div>

        <div class="form-group">
            <label for="pengantar">Pengantar</label>

            <textarea
                id="pengantar"
                name="pengantar"
                rows="5"
                maxlength="5000"
            ><?= e($data['pengantar']) ?></textarea>

            <small class="form-help">
                Jelaskan secara singkat peluang kerja sama yang tersedia.
            </small>
        </div>
    </section>

    <section class="panel">
        <h2>Detail Kerja Sama</h2>

        <?php
        $sections = [
            'jenis_kerja_sama' => [
                'Jenis Kerja Sama',
                'Tulis satu jenis kerja sama pada setiap baris.',
            ],
            'manfaat' => [
                'Manfaat Kemitraan',
                'Tulis satu manfaat pada setiap baris sesuai penawaran perusahaan.',
            ],
            'persyaratan' => [
                'Persyaratan',
                'Tulis satu persyaratan pada setiap baris.',
            ],
            'alur_pengajuan' => [
                'Alur Pengajuan',
                'Tulis satu langkah pada setiap baris, sesuai urutan pengajuan.',
            ],
        ];
        ?>

        <?php foreach ($sections as $field => [$label, $help]): ?>
            <div class="form-group">
                <label for="<?= e($field) ?>">
                    <?= e($label) ?>
                </label>

                <textarea
                    id="<?= e($field) ?>"
                    name="<?= e($field) ?>"
                    rows="5"
                    maxlength="10000"
                ><?= e($data[$field]) ?></textarea>

                <small class="form-help">
                    <?= e($help) ?>
                </small>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="panel">
        <h2>Ajakan dan Kontak Kemitraan</h2>

        <div class="form-group">
            <label for="cta_judul">Judul Ajakan *</label>

            <input
                type="text"
                id="cta_judul"
                name="cta_judul"
                value="<?= e($data['cta_judul']) ?>"
                maxlength="200"
                placeholder="Tertarik Menjadi Mitra?"
                required
            >
        </div>

        <div class="form-group">
            <label for="cta_deskripsi">Deskripsi Ajakan</label>

            <textarea
                id="cta_deskripsi"
                name="cta_deskripsi"
                rows="3"
                maxlength="5000"
            ><?= e($data['cta_deskripsi']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="whatsapp">WhatsApp Kemitraan</label>

            <input
                type="tel"
                id="whatsapp"
                name="whatsapp"
                value="<?= e($data['whatsapp']) ?>"
                maxlength="20"
                placeholder="6281234567890"
            >

            <small class="form-help">
                Gunakan nomor penanggung jawab kemitraan.
                Nomor berawalan 08 akan diubah menjadi 62.
                Jika dikosongkan, halaman publik akan menggunakan
                WhatsApp dari Pengaturan Website.
            </small>
        </div>
    </section>

    <div class="form-actions">
        <button type="submit" class="button">
            Simpan Kemitraan
        </button>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>