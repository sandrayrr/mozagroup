<?php

declare(strict_types=1);

/**
 * Mengunggah gambar.
 * Mengembalikan null jika tidak ada file yang dipilih.
 */
function upload_image(string $field, string $folder): ?string
{
    $allowedFolders = [
        'perusahaan',
        'unit-usaha',
        'produk',
        'berita',
        'galeri',
        'mitra',
    ];

    if (!in_array($folder, $allowedFolders, true)) {
        throw new RuntimeException('Folder upload tidak valid.');
    }

    $file = $_FILES[$field] ?? null;

    if ($file === null) {
        return null;
    }

    if (
        !is_array($file)
        || !isset($file['error'])
        || !is_int($file['error'])
    ) {
        throw new RuntimeException('Data upload tidak valid.');
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException(
            'Upload gagal. Periksa ukuran file dan coba kembali.'
        );
    }

    $temporary = $file['tmp_name'] ?? null;

    if (!is_string($temporary) || !is_uploaded_file($temporary)) {
        throw new RuntimeException('File upload tidak valid.');
    }

    $size = filesize($temporary);

    if (
        $size === false
        || $size <= 0
        || $size > (int) config('upload_max_size', 2097152)
    ) {
        throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
    $allowedMimes = config('upload_allowed_mimes', []);

    if (!is_string($mime) || !isset($allowedMimes[$mime])) {
        throw new RuntimeException('Gunakan gambar JPG, PNG, atau WebP.');
    }

    $dimensions = @getimagesize($temporary);

    if (
        $dimensions === false
        || $dimensions[0] > 8000
        || $dimensions[1] > 8000
    ) {
        throw new RuntimeException(
            'Gambar tidak valid atau dimensinya melebihi 8000 piksel.'
        );
    }

    $directory = BASE_PATH . '/uploads/' . $folder;

    if (
        !is_dir($directory)
        && !mkdir($directory, 0755, true)
        && !is_dir($directory)
    ) {
        throw new RuntimeException('Folder upload tidak dapat dibuat.');
    }

    $filename = bin2hex(random_bytes(16))
        . '.' . $allowedMimes[$mime];

    if (!move_uploaded_file($temporary, $directory . '/' . $filename)) {
        throw new RuntimeException('Gambar gagal disimpan.');
    }

    return $folder . '/' . $filename;
}

/**
 * Menghapus gambar upload yang berada dalam folder tujuan.
 */
function delete_uploaded_image(?string $path, string $folder): void
{
    if (!$path) {
        return;
    }

    $directory = realpath(BASE_PATH . '/uploads/' . $folder);
    $file = realpath(BASE_PATH . '/uploads/' . $path);

    if ($directory === false || $file === false) {
        return;
    }

    if (dirname($file) === $directory && is_file($file)) {
        if (!unlink($file)) {
            error_log('Gagal menghapus gambar upload: ' . $path);
        }
    }
}