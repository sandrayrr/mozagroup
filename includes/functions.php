<?php

declare(strict_types=1);

/**
 * Membaca konfigurasi aplikasi.
 */
function config(string $key, mixed $default = null): mixed
{
    global $app;

    return $app[$key] ?? $default;
}

/**
 * Mengamankan teks saat ditampilkan dalam HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Membuat URL dari alamat dasar aplikasi.
 *
 * Contoh: url('produk.php')
 */
function url(string $path = ''): string
{
    $baseUrl = rtrim((string) config('base_url'), '/');

    return $baseUrl . '/' . ltrim($path, '/');
}

/**
 * Membuat URL aset.
 *
 * Contoh: asset('css/style.css')
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Membuat URL gambar upload.
 *
 * Path yang disimpan di database:
 * produk/nama-gambar.webp
 */
function upload_url(?string $path): string
{
    if ($path === null || trim($path) === '') {
        return asset('images/placeholder.jpg');
    }

    $path = str_replace('\\', '/', trim($path));

    // Tolak path di luar folder upload.
    if (
        str_starts_with($path, '/')
        || preg_match('~(^|/)\.\.?(/|$)~', $path)
        || !preg_match('~^[a-zA-Z0-9_./-]+$~', $path)
    ) {
        return asset('images/placeholder.jpg');
    }

    if (!is_file(BASE_PATH . '/uploads/' . $path)) {
        return asset('images/placeholder.jpg');
    }

    return url('uploads/' . $path);
}

/**
 * Mengalihkan ke halaman internal aplikasi.
 */
function redirect(string $path = ''): never
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Memeriksa metode request.
 */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Menyimpan pesan untuk request berikutnya.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Mengambil pesan, lalu menghapusnya dari sesi.
 */
function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;

    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

/**
 * Membuat slug dasar.
 *
 * Keunikan slug tetap diperiksa saat menyimpan data.
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));

    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

    $text = trim($text, '-');

    return $text !== ''
        ? $text
        : 'item-' . bin2hex(random_bytes(4));
}

/**
 * Memformat harga rupiah.
 */
function rupiah(string|int|float|null $value): string
{
    if ($value === null || $value === '') {
        return 'Hubungi kami';
    }

    return 'Rp' . number_format((float) $value, 0, ',', '.');
}

/**
 * Mengubah nomor Indonesia menjadi format internasional.
 *
 * 081234567890 -> 6281234567890
 */
function normalize_whatsapp(?string $number): string
{
    $number = preg_replace('/\D+/', '', $number ?? '') ?? '';

    if (str_starts_with($number, '0')) {
        $number = '62' . substr($number, 1);
    } elseif (str_starts_with($number, '8')) {
        $number = '62' . $number;
    }

    return $number;
}

/**
 * Membuat tautan WhatsApp.
 *
 * Mengembalikan null jika nomor belum valid.
 */
function whatsapp_url(
    ?string $number,
    string $message = ''
): ?string {
    $number = normalize_whatsapp($number);

    if (!preg_match('/^[1-9][0-9]{7,14}$/', $number)) {
        return null;
    }

    $link = 'https://wa.me/' . $number;

    if ($message !== '') {
        $link .= '?text=' . rawurlencode($message);
    }

    return $link;
}

/**
 * Menampilkan teks biasa dengan baris baru.
 *
 * Gunakan untuk deskripsi yang tidak menerima HTML.
 */
function text_to_html(?string $text): string
{
    return nl2br(e($text));
}

/**
 * Menandai menu aktif berdasarkan nama file.
 */
function active_menu(string $filename): string
{
    $currentFile = basename($_SERVER['SCRIPT_NAME'] ?? '');

    return $currentFile === $filename ? 'active' : '';
}