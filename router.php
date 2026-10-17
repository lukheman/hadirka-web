<?php
// router.php — Router untuk PHP built-in server (`php -S` mengabaikan .htaccess).
//
// Jalankan:  php -S localhost:8000 router.php
// Meniru aturan rewrite di .htaccess:
//   ^karier/([^/\.]+)/?$          -> index.php?page=karier&id=$1
//   ^([a-zA-Z0-9_-]+)/?$          -> index.php?page=$1

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rawurldecode($uri === '' ? '/' : $uri);

// 1. File yang benar-benar ada (aset, skrip .php langsung) → sajikan apa adanya.
//    Termasuk varian tanpa ekstensi ala .htaccess ("login" -> "login.php").
if ($path !== '/') {
    $file = __DIR__ . $path;
    if (is_file($file)) {
        return false;
    }
    if (is_file($file . '.php')) {
        require $file . '.php';
        return true;
    }
}

// 1b. API aplikasi absensi mobile -> SATU front controller (app/api/index.php).
if (preg_match('#^/api(/|$)#', $path)) {
    require __DIR__ . '/app/api/index.php';
    return true;
}

// 2. Pretty URL -> $_GET (urutan sama seperti .htaccess).
$pretty = [
    '#^/karier/([^/\.]+)/?$#'         => ['page' => 'karier', 'id' => 1],
    '#^/([a-zA-Z0-9_-]+)/?$#'         => ['page' => 1],
];
foreach ($pretty as $re => $map) {
    if (preg_match($re, $path, $m)) {
        foreach ($map as $k => $v) {
            $_GET[$k] = is_int($v) ? $m[$v] : $v;
        }
        break;
    }
}

// 3. Fallback: index.php (di dalamnya default ?page=home bila tak cocok).
require __DIR__ . '/index.php';
