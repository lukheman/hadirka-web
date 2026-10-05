<?php
/**
 * ============================================
 * GET DATA BPJS KETENAGAKERJAAN
 * ============================================
 */

require '../../config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

if (!isset($koneksi) || !$koneksi) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']);
    exit;
}

$statusKarFilter = isset($_GET['status_kar']) ? $_GET['status_kar'] : 'AKTIF';

// Tentukan kondisi status karyawan
if ($statusKarFilter === 'AKTIF') {
    $whereStatus = "k.status_karyawan = 'Aktif'";
} else {
    // Semua yang bukan 'Aktif'
    $whereStatus = "k.status_karyawan != 'Aktif'";
}

$sql = "SELECT 
            k.id_karyawan,
            k.nik_karyawan, 
            k.nama_lengkap,
            k.status_karyawan, -- Optional: Jika ingin ditampilkan di tabel
            s.nama_site,
            j.nama_jabatan,
            i.bpjs_tk
        FROM tb_karyawan k
        LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_site s ON kr.id_site = s.id_site
        LEFT JOIN tb_jabatan j ON kr.id_jabatan = j.id_jabatan
        WHERE $whereStatus 
        ORDER BY k.nama_lengkap ASC";

$rowsBpjs = $koneksi->query($sql)->fetchAll();
$data = [];

foreach ($rowsBpjs as $row) {
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
?>