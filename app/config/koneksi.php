<?php
date_default_timezone_set('Asia/Makassar');

// saya meng-uncentang intelephense undefined variables di vs code biar variabel $koneksi di file lain tidak merah
// $koneksi = mysqli_connect('localhost', 'root', '', 'db_tjs_os');
$koneksi = mysqli_connect('localhost', 'root', 'akmal', 'tjs_hris');
if (mysqli_connect_errno()) {
    echo 'Koneksi database gagal : ' . mysqli_connect_error();
}

function url() {
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $base_path = $script_dir === '/' || $script_dir === '.' ? '/' : rtrim($script_dir, '/') . '/';

    return $scheme . '://' . $host . $base_path;
}
?>
