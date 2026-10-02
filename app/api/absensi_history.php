<?php
// File: app/api/absensi_history.php
// DRY: klasifikasi absen 100% memakai logic asli generateSlipGajiById()
// (app/helpers/slip_helper.php). File ini hanya adapter format respons.

include 'koneksi-api.php';
require_once __DIR__ . '/../helpers/slip_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function sendResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    exit;
}

global $koneksi;
if (!isset($koneksi) || $koneksi === false) {
    sendResponse(['error' => 'Koneksi database gagal.'], 500);
}

// =========================================================
// 1. AMBIL INPUT & VALIDASI (kontrak tidak berubah: POST JSON)
// =========================================================
$input = json_decode(file_get_contents('php://input'), true);
$id_karyawan = $input['id_karyawan'] ?? null;
$startDate = $input['startDate'] ?? null;
$endDate = $input['endDate'] ?? null;

if (!$id_karyawan || !$startDate || !$endDate) {
    sendResponse(['error' => 'id_karyawan, startDate, dan endDate wajib di body POST.'], 400);
}

// =========================================================
// 2. HITUNG SEKALI VIA CORE (DRY), PETAKAN KE FORMAT LAMA
// =========================================================
$core = generateSlipGajiById($id_karyawan, $startDate, $endDate);

if (!is_array($core) || isset($core['error']) || isset($core['skip'])) {
    $code = isset($core['skip']) ? 404 : 404;
    sendResponse(['error' => $core['error'] ?? 'Data tidak dapat diproses.'], $code);
}

$absenForUI = [];
$hadirPlusSpl = 0;
foreach (($core['absen'] ?? []) as $r) {
    $ket = $r['ket'] ?? '';
    if ($ket === 'HADIR' || $ket === 'SPL') {
        $hadirPlusSpl++;
    }
    $absenForUI[] = [
        'tanggal' => $r['tanggal'] ?? '',
        'jam' => $r['jam'] ?? 0,
        'wl' => $r['wl'] ?? 0,
        'ot' => $r['ot'] ?? 0,
        'ket' => $ket,
        'status_chip' => $ket,
    ];
}

// =========================================================
// 3. KIRIM RESPONSE (kontrak tidak berubah)
// =========================================================
$response = [
    'nik' => $core['nik'] ?? null,
    'nama' => $core['nama'] ?? 'N/A',
    'hari_kerja' => $hadirPlusSpl, // definisi endpoint ini: HADIR + SPL
    'izin' => $core['izin'] ?? 0,
    'sakit' => $core['sakit'] ?? 0,
    'cuti' => $core['cuti'] ?? 0,
    'mangkir' => $core['mangkir'] ?? 0,
    'new' => $core['new'] ?? 0,
    'resign' => $core['resign'] ?? 0,

    'total_jam_hadir_sum' => array_sum(array_column($absenForUI, 'jam')),

    'absen' => $absenForUI
];

sendResponse($response);
