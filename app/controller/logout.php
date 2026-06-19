<?php
// app/controller/logout.php

// Karena auth.php sudah menjalankan session_start(), kita tidak perlu menjalankannya lagi di sini

// Pastikan koneksi database tersedia (karena sudah di-include di index.php)
if (!isset($koneksi)) {
    require_once __DIR__ . '/../../app/config/koneksi.php';
}

// Hapus token remember me jika ada di database & cookie browser
if (isset($_COOKIE['remember_me']) && strpos($_COOKIE['remember_me'], ':') !== false) {
    list($selector, $validator) = explode(':', $_COOKIE['remember_me']);
    $stmt = $koneksi->prepare("DELETE FROM tb_user_token WHERE selector = ?");
    $stmt->bind_param("s", $selector);
    $stmt->execute();

    setcookie('remember_me', '', time() - 3600, "/");
}

// Kosongkan array session dan hancurkan session
$_SESSION = [];
session_destroy();

// SASARAN REDIRECT DISAMAKAN: Menggunakan 'login' (tanpa .php) sesuai auth.php
header("Location: login");
exit;