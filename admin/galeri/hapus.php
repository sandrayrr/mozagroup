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
    set_flash('error', 'ID foto tidak valid.');
    redirect('admin/galeri/index.php');
}

$photo = null;
$error = null;

try {
    $pdo->beginTransaction();

    $statement = $pdo->prepare(
        'SELECT judul, foto FROM galeri WHERE id = ? FOR UPDATE'
    );

    $statement->execute([$id]);

    $photo = $statement->fetch();

    if (!$photo) {
        $pdo->rollBack();
        $error = 'Foto tidak ditemukan.';
    } else {
        $statement = $pdo->prepare(
            'DELETE FROM galeri WHERE id = ?'
        );

        $statement->execute([$id]);

        $pdo->commit();
    }
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Hapus galeri gagal: ' . $exception->getMessage()
    );

    $error = 'Foto gagal dihapus. Silakan coba kembali.';
}

if ($error !== null) {
    set_flash('error', $error);
} else {
    delete_uploaded_image($photo['foto'], 'galeri');

    set_flash(
        'success',
        'Foto "' . $photo['judul'] . '" berhasil dihapus.'
    );
}

redirect('admin/galeri/index.php');