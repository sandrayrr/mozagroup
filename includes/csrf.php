<?php

declare(strict_types=1);

/**
 * Membuat atau mengambil token CSRF dari sesi.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Input tersembunyi untuk form POST.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="'
        . e(csrf_token())
        . '">';
}

/**
 * Memeriksa token dan metode request.
 */
function csrf_verify(): void
{
    $token = $_POST['_token'] ?? null;

    if (
        !is_post()
        || !is_string($token)
        || empty($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);

        exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
    }
}