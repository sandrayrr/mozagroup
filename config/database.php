<?php

declare(strict_types=1);

$host = '127.0.0.1';
$port = '3306';
$database = 'moza_group';
$username = 'root';
$password = 'root123';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $host,
    $port,
    $database
);

return new PDO(
    $dsn,
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);