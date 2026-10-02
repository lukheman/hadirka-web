<?php
// app/controller/logout.php — dipanggil dari index.php?page=logout, SEBELUM output apa pun.
// Menjamin: sesi + cookie sesi + token remember-me dihapus, lalu direct ke login.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Pastikan koneksi database tersedia (sudah di-include di index.php)
if (!isset($koneksi)) {
    require_once __DIR__ . '/../config/koneksi.php';
}

// 1. Hapus token remember-me di database & cookie browser.
//    Flag cookie disamakan dengan saat dibuat di app/controller/login.php
//    agar penghapusan benar-benar berlaku (terutama di HTTPS).
if (isset($_COOKIE['remember_me']) && strpos($_COOKIE['remember_me'], ':') !== false) {
    $parts = explode(':', (string) $_COOKIE['remember_me'], 2);
    $selector = $parts[0];

    $stmt = $koneksi->prepare("DELETE FROM tb_user_token WHERE selector = ?");
    if ($stmt) {
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE['remember_me']);
}

// 2. Kosongkan data sesi dan hapus cookie sesi PHP.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'] ?? '/',
        'domain' => $params['domain'] ?? '',
        'secure' => $params['secure'] ?? false,
        'httponly' => $params['httponly'] ?? true,
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}
session_destroy();

// 3. Direct ke halaman login (URL absolut agar benar di pretty URL maupun ?page=).
header('Location: ' . url() . 'login.php');
exit;
