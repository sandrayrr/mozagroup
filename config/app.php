<?php

declare(strict_types=1);

return [
    'nama_aplikasi' => 'Moza Group',

    // Alamat proyek di Laragon, tanpa "/" di akhir.
    'base_url' => 'https://mozagroup.my.id/',

    'timezone' => 'Asia/Jakarta',

    // Gunakan false ketika website sudah di hosting.
    'debug' => false,

    // Batas ukuran upload: 2 MB.
    'upload_max_size' => 2 * 1024 * 1024,

    'upload_allowed_mimes' => [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ],
];