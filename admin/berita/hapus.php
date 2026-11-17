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
    set_flash('error', 'ID berita tidak valid.');
    redirect('admin/berita/index.php');
}

$news = null;
$error = null;

try {
    $pdo->beginTransaction();

    $statement = $pdo->prepare(
        'SELECT judul, foto
         FROM berita
         WHERE id = ?
         FOR UPDATE'
    );

    $statement->execute([$id]);

    $news = $statement->fetch();

    if (!$news) {
        $pdo->rollBack();
        $error = 'Berita tidak ditemukan.';
    } else {
        $statement = $pdo->prepare(
            'DELETE FROM berita WHERE id = ?'
        );

        $statement->execute([$id]);

        $pdo->commit();
    }
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Hapus berita gagal: ' . $exception->getMessage()
    );

    $error = 'Berita gagal dihapus. Silakan coba kembali.';
}

if ($error !== null) {
    set_flash('error', $error);
} else {
    // Hapus foto setelah data berhasil dihapus.
    delete_uploaded_image($news['foto'], 'berita');

    set_flash(
        'success',
        'Berita "' . $news['judul'] . '" berhasil dihapus.'
    );
}

redirect('admin/berita/index.php');