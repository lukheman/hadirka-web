<?php
require_once __DIR__ . '/../config/env.php';

date_default_timezone_set('Asia/Makassar');

$db_host = env('DB_HOST', '127.0.0.1');
$db_port = (int)env('DB_PORT', 3306);
$db_user = env('DB_USERNAME');
$db_pass = env('DB_PASSWORD', '');
$db_name = env('DB_DATABASE');

if (!$db_user || !$db_name) {
    throw new RuntimeException('Database configuration missing.');
}

$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
if (mysqli_connect_errno()) {
    error_log('API database connection failed: ' . mysqli_connect_error());
    throw new RuntimeException('Database connection failed.');
}
$koneksi->set_charset('utf8mb4');
?>
