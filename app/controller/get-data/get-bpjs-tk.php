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

// Ambil seluruh data karyawan aktif beserta site, jabatan, dan nomor BPJS
$sql = "SELECT 
            k.id_karyawan,
            k.nik_karyawan, 
            k.nama_lengkap,
            s.nama_site,
            j.nama_jabatan,
            i.bpjs_tk
        FROM tb_karyawan k
        LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_site s ON kr.id_site = s.id_site
        LEFT JOIN tb_jabatan j ON kr.id_jabatan = j.id_jabatan
        WHERE k.status_karyawan = 'Aktif' 
        ORDER BY k.nama_lengkap ASC";

$result = $koneksi->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
?>