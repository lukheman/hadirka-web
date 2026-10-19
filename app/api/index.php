<?php

declare(strict_types=1);

/**
 * API mobile — SATU pintu masuk (single route) untuk aplikasi absensi mobile.
 *
 * Seluruh file API lain (sistem v1 `app/api/*.php` dan v2 `app/api/v2/`)
 * sudah dihapus; semua endpoint kini melewati front controller ini + tabel
 * rute di bawah.
 *
 * Dipasang di URL /api/... melalui rewrite:
 *   - Apache produksi : aturan di .htaccess root repo
 *   - Dev php -S      : penanganan khusus di router.php
 *
 * Front controller ini juga bisa dipanggil langsung untuk debugging:
 *   app/api/index.php?__route=auth/login
 *
 * Daftar endpoint & contoh request: docs/INTEGRASI-ABSEN-MOBILE.md
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

date_default_timezone_set('Asia/Makassar');

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/handlers_auth.php';
require_once __DIR__ . '/handlers_attendance.php';
require_once __DIR__ . '/handlers_services.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$path = api_resolve_path();

$routes = [
    ['POST', '#^/auth/login$#', 'api_auth_login'],
    ['POST', '#^/auth/check$#', 'api_auth_check'],
    ['POST', '#^/auth/register$#', 'api_auth_register'],
    ['PUT', '#^/user/profile$#', 'api_user_profile'],
    ['PUT', '#^/user/password$#', 'api_user_password'],

    ['GET', '#^/attendance$#', 'api_attendance_list'],
    ['POST', '#^/attendance/submit$#', 'api_attendance_submit'],

    ['GET', '#^/leave$#', 'api_leave_list'],
    ['POST', '#^/leave$#', 'api_leave_create'],
    ['PUT', '#^/leave/(\d+)/status$#', 'api_leave_status'],

    ['GET', '#^/loan$#', 'api_loan_list'],
    ['POST', '#^/loan$#', 'api_loan_create'],
    ['PUT', '#^/loan/(\d+)/status$#', 'api_loan_status'],

    ['GET', '#^/geofence$#', 'api_geofence_list'],
    ['PUT', '#^/geofence/(\d+)$#', 'api_geofence_update'],

    ['GET', '#^/payslip$#', 'api_payslip_list'],

    ['GET', '#^/users$#', 'api_users_list'],
    ['POST', '#^/users$#', 'api_users_create'],
    ['DELETE', '#^/users/(\d+)$#', 'api_users_delete'],

    ['GET', '#^/notifications$#', 'api_notifications_list'],
];

foreach ($routes as [$m, $re, $fn]) {
    if ($m !== $method) {
        continue;
    }

    if (preg_match($re, $path, $mch)) {
        try {
            $fn(array_slice($mch, 1));
        } catch (Throwable $e) {
            error_log('[api] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            api_error(500, 'Terjadi kesalahan pada server. Coba lagi.');
        }
        exit;
    }
}

api_error(404, 'Endpoint tidak ditemukan: ' . $method . ' ' . $path);

/**
 * Tentukan path route dari REQUEST_URI.
 *
 * Mendukung mount di /api/... (via .htaccess / router.php) dan
 * ?__route=... untuk debugging manual.
 */
function api_resolve_path(): string
{
    if (isset($_GET['__route'])) {
        return '/' . trim((string)$_GET['__route'], '/');
    }

    $uri = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $uri = rawurldecode($uri);

    $pos = strpos($uri, '/api');
    if ($pos !== false) {
        $rest = trim(substr($uri, $pos + strlen('/api')), '/');

        return $rest === '' ? '/' : '/' . $rest;
    }

    return '/' . trim($uri, '/');
}
