<?php

declare(strict_types=1);

/**
 * Menghitung pembagian halaman.
 */
function pagination(int $total, int $perPage = 10): array
{
    $perPage = max(1, min(100, $perPage));
    $total = max(0, $total);

    $totalPages = max(1, (int) ceil($total / $perPage));

    $requestedPage = positive_id($_GET['page'] ?? null) ?? 1;
    $page = min($requestedPage, $totalPages);

    return [
        'total' => $total,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'page' => $page,
        'offset' => ($page - 1) * $perPage,
    ];
}

/**
 * Membuat navigasi pagination.
 * Parameter filter dipertahankan ketika pindah halaman.
 */
function render_pagination(
    array $pagination,
    string $path,
    array $filters = []
): void {
    if ($pagination['total_pages'] <= 1) {
        return;
    }

    $current = $pagination['page'];
    $totalPages = $pagination['total_pages'];

    $makeUrl = static function (int $page) use ($path, $filters): string {
        return url($path) . '?' . http_build_query(
            array_merge($filters, ['page' => $page]),
            '',
            '&',
            PHP_QUERY_RFC3986
        );
    };

    $start = max(1, $current - 2);
    $end = min($totalPages, $current + 2);

    echo '<nav class="pagination" aria-label="Navigasi halaman">';

    if ($current > 1) {
        echo '<a href="' . e($makeUrl($current - 1)) . '">'
            . 'Sebelumnya</a>';
    }

    for ($page = $start; $page <= $end; $page++) {
        if ($page === $current) {
            echo '<span class="current" aria-current="page">'
                . $page . '</span>';
        } else {
            echo '<a href="' . e($makeUrl($page)) . '">'
                . $page . '</a>';
        }
    }

    if ($current < $totalPages) {
        echo '<a href="' . e($makeUrl($current + 1)) . '">'
            . 'Berikutnya</a>';
    }

    echo '</nav>';
}