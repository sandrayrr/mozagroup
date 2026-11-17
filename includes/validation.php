<?php

declare(strict_types=1);

/**
 * Mengambil input teks dari form.
 * Nilai array ditolak dan dikembalikan sebagai teks kosong.
 */
function input_string(array $data, string $key): string
{
    $value = $data[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

/**
 * Menghitung panjang teks UTF-8.
 */
function text_length(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    return preg_match_all('/./us', $value) ?: 0;
}

/**
 * Validasi teks wajib.
 */
function validate_required(
    string $value,
    string $label,
    int $maxLength = 255
): ?string {
    if ($value === '') {
        return $label . ' wajib diisi.';
    }

    if (text_length($value) > $maxLength) {
        return $label . ' maksimal ' . $maxLength . ' karakter.';
    }

    return null;
}

/**
 * Validasi email.
 */
function validate_email(string $email): ?string
{
    if (
        strlen($email) > 191
        || !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        return 'Alamat email tidak valid.';
    }

    return null;
}

/**
 * Validasi password baru.
 * Password tidak boleh di-trim agar karakter aslinya tetap utuh.
 */
function validate_password(
    string $password,
    string $confirmation
): ?string {
    if (strlen($password) < 12) {
        return 'Password minimal 12 karakter.';
    }

    // password_hash dengan bcrypt memiliki batas 72 byte.
    if (strlen($password) > 72) {
        return 'Password maksimal 72 byte.';
    }

    if (str_contains($password, "\0")) {
        return 'Password mengandung karakter yang tidak diperbolehkan.';
    }

    if ($password !== $confirmation) {
        return 'Konfirmasi password tidak cocok.';
    }

    return null;
}

/**
 * Validasi URL website atau media sosial.
 */
function validate_url(
    string $value,
    string $label = 'URL',
    bool $required = false
): ?string {
    if ($value === '') {
        return $required ? $label . ' wajib diisi.' : null;
    }

    $scheme = strtolower(
        (string) parse_url($value, PHP_URL_SCHEME)
    );

    if (
        strlen($value) > 255
        || !filter_var($value, FILTER_VALIDATE_URL)
        || !in_array($scheme, ['http', 'https'], true)
    ) {
        return $label . ' harus berupa alamat HTTP atau HTTPS yang valid.';
    }

    return null;
}

/**
 * Mengambil ID bilangan bulat positif.
 */
function positive_id(mixed $value): ?int
{
    if (!is_string($value) && !is_int($value)) {
        return null;
    }

    $id = filter_var(
        $value,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    return $id === false ? null : $id;
}