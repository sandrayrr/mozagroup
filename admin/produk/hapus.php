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
    set_flash('error', 'ID produk tidak valid.');
    redirect('admin/produk/index.php');
}

$product = null;
$error = null;

try {
    $pdo->beginTransaction();

    $statement = $pdo->prepare(
        'SELECT nama, foto FROM produk WHERE id = ? FOR UPDATE'
    );

    $statement->execute([$id]);

    $product = $statement->fetch();

    if (!$product) {
        $pdo->rollBack();
        $error = 'Produk tidak ditemukan.';
    } else {
        $statement = $pdo->prepare(
            'DELETE FROM produk WHERE id = ?'
        );

        $statement->execute([$id]);

        $pdo->commit();
    }
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Hapus produk gagal: ' . $exception->getMessage()
    );

    $error = 'Produk gagal dihapus. Silakan coba kembali.';
}

if ($error !== null) {
    set_flash('error', $error);
} else {
    // Hapus foto setelah penghapusan database berhasil.
    delete_uploaded_image($product['foto'], 'produk');

    set_flash(
        'success',
        'Produk "' . $product['nama'] . '" berhasil dihapus.'
    );
}

redirect('admin/produk/index.php');