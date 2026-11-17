<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$admin = require_admin();

if (!is_post()) {
    header('Allow: POST');
    http_response_code(405);

    exit('Penghapusan harus menggunakan metode POST.');
}

csrf_verify();

$id = positive_id($_POST['id'] ?? null);

if ($id === null) {
    set_flash('error', 'ID unit usaha tidak valid.');
    redirect('admin/unit-usaha/index.php');
}

$unit = null;
$error = null;
$transactionStarted = false;

try {
    $pdo->beginTransaction();
    $transactionStarted = true;

    $statement = $pdo->prepare(
        'SELECT id, nama, logo, foto
         FROM unit_usaha
         WHERE id = ?
         FOR UPDATE'
    );

    $statement->execute([$id]);

    $unit = $statement->fetch();

    if (!$unit) {
        $error = 'Unit usaha tidak ditemukan.';
    } else {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM produk WHERE unit_usaha_id = ?'
        );

        $statement->execute([$id]);

        if ((int) $statement->fetchColumn() > 0) {
            $error = 'Unit usaha masih memiliki produk. '
                . 'Pindahkan atau hapus produknya terlebih dahulu.';
        }
    }

    if ($error !== null) {
        $pdo->rollBack();
        $transactionStarted = false;
    } else {
        $statement = $pdo->prepare(
            'DELETE FROM unit_usaha WHERE id = ?'
        );

        $statement->execute([$id]);

        $pdo->commit();
        $transactionStarted = false;
    }
} catch (PDOException $exception) {
    if ($transactionStarted && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Hapus unit usaha gagal: ' . $exception->getMessage()
    );

    // Foreign key database tetap melindungi relasi produk.
    $error = (int) ($exception->errorInfo[1] ?? 0) === 1451
        ? 'Unit usaha masih digunakan oleh data lain dan tidak dapat dihapus.'
        : 'Unit usaha gagal dihapus. Silakan coba kembali.';
}

if ($error !== null) {
    set_flash('error', $error);
} else {
    // Gambar dihapus setelah penghapusan database berhasil.
    delete_uploaded_image($unit['logo'], 'unit-usaha');
    delete_uploaded_image($unit['foto'], 'unit-usaha');

    set_flash(
        'success',
        'Unit usaha "' . $unit['nama'] . '" berhasil dihapus.'
    );
}

redirect('admin/unit-usaha/index.php');