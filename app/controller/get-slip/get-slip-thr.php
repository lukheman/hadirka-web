<?php
// File: app/controller/get-slip/get-slip-thr.php

// 1. TAHAN SEMUA OUTPUT HTML/WARNING
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. TANGKAP FATAL ERROR JIKA ADA
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        echo json_encode(['error' => 'Fatal Error: ' . $err['message']]);
        exit;
    }
});

header("Content-Type: application/json; charset=utf-8");
require_once '../../config/koneksi.php';

$input = json_decode(file_get_contents('php://input'), true);
$id_karyawan = $input['id_karyawan'] ?? null;
$tahun = $input['tahun'] ?? null;

if (!$id_karyawan || !$tahun) {
    ob_clean();
    http_response_code(400);
    echo json_encode(['error' => 'Karyawan dan Tahun wajib dipilih']);
    exit;
}

// 3. QUERY DIPERBAIKI (Abaikan tb_jabatan dulu agar tidak crash)
$sql = "SELECT r.*, k.nik_karyawan, k.nama_lengkap 
        FROM tb_thr r
        INNER JOIN tb_karyawan k ON r.id_karyawan = k.id_karyawan
        WHERE r.id_karyawan = ? AND r.periode = ?";

$stmt = $koneksi->prepare($sql);

// Jaga-jaga kalau query masih salah/kolom tidak ditemukan
if (!$stmt) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Query SQL Error: ' . $koneksi->error]);
    exit;
}

$stmt->bind_param("is", $id_karyawan, $tahun);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    ob_clean();
    http_response_code(404);
    echo json_encode(['error' => 'Data THR untuk karyawan ini tidak ditemukan.']);
    exit;
}

$data = $result->fetch_assoc();

// Susun data untuk dikirim ke Javascript
$response = [
    'nik' => $data['nik_karyawan'],
    'nama' => $data['nama_lengkap'],
    'jabatan' => 'Karyawan', // Kita hardcode dulu biar aman
    'tgl_mulai' => $data['tgl_mulai'],
    'tgl_raya' => $data['tgl_hari_raya'],
    'periode' => $data['periode'],
    'masa_kerja' => $data['masa_kerja'],
    'gaji_pokok' => $data['gaji_pokok'],
    'tunjangan_lokasi' => $data['tunjangan_lokasi'],
    'tunjangan_kebutuhan' => $data['tunjangan_kebutuhan'],
    'nominal_thr' => $data['nominal_thr']
];

// 4. BERSIHKAN BUFFER LALU KIRIM JSON MURNI
ob_clean();
echo json_encode($response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

$stmt->close();
$koneksi->close();
?>