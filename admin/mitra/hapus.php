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
    set_flash('error', 'ID mitra tidak valid.');
    redirect('admin/mitra/index.php');
}

$partner = null;
$error = null;

try {
    $pdo->beginTransaction();

    $statement = $pdo->prepare(
        'SELECT nama, logo FROM mitra WHERE id = ? FOR UPDATE'
    );

    $statement->execute([$id]);

    $partner = $statement->fetch();

    if (!$partner) {
        $pdo->rollBack();
        $error = 'Mitra tidak ditemukan.';
    } else {
        $statement = $pdo->prepare(
            'DELETE FROM mitra WHERE id = ?'
        );

        $statement->execute([$id]);

        $pdo->commit();
    }
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Hapus mitra gagal: ' . $exception->getMessage()
    );

    $error = 'Mitra gagal dihapus. Silakan coba kembali.';
}

if ($error !== null) {
    set_flash('error', $error);
} else {
    delete_uploaded_image($partner['logo'], 'mitra');

    set_flash(
        'success',
        'Mitra "' . $partner['nama'] . '" berhasil dihapus.'
    );
}

redirect('admin/mitra/index.php');