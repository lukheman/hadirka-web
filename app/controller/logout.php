<?php
session_start();
require_once 'app/config/koneksi.php';

if (isset($_COOKIE['remember_me']) && strpos($_COOKIE['remember_me'], ':') !== false) {
    list($selector, $validator) = explode(':', $_COOKIE['remember_me']);
    $stmt = $koneksi->prepare("DELETE FROM tb_user_token WHERE selector = ?");
    $stmt->bind_param("s", $selector);
    $stmt->execute();

    setcookie('remember_me', '', time() - 3600, "/");
}

// Hapus semua session
$_SESSION = [];
session_destroy();

header("Location: login.php");
exit;
