<?php
require_once __DIR__ . '/database.php';

date_default_timezone_set('Asia/Makassar');

$koneksi = db_connect();

function url() {
    $configured_url = rtrim((string)env('APP_URL', ''), '/') . '/';
    if ($configured_url !== '/') {
        return $configured_url;
    }

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $base_path = $script_dir === '/' || $script_dir === '.' ? '/' : rtrim($script_dir, '/') . '/';

    return $scheme . '://' . $host . $base_path;
}
?>
