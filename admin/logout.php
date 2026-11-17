<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (!is_post()) {
    header('Allow: POST');
    http_response_code(405);

    exit('Logout harus menggunakan metode POST.');
}

csrf_verify();
logout_admin();

redirect('admin/login.php');