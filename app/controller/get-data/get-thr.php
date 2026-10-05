<?php
/**
 * ============================================
 * GET THR DATA - JSON ENDPOINT
 * ============================================
 */

require '../../config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 1. JIKA KONEKSI GAGAL
if (!isset($koneksi) || !$koneksi) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']);
    exit;
}

// 2. Tangkap parameter periode (jika ada)
$periode = isset($_GET['periode']) ? trim($_GET['periode']) : '';
$whereClause = "";

$paramsThr = [];
if (!empty($periode)) {
    $periode = intval($periode);
    $whereClause = " WHERE r.periode = ? ";
    $paramsThr[] = $periode;
}

// 3. Query dengan JOIN ke tabel Identitas (Bank), Karier, Site, dan Jabatan
$sql = "SELECT 
            r.*, 
            k.nik_karyawan, 
            k.nama_lengkap,
            s.nama_site,
            j.nama_jabatan,
            i.atas_nama_rek,
            i.no_rek,
            i.nama_bank
        FROM tb_thr r
        INNER JOIN tb_karyawan k ON r.id_karyawan = k.id_karyawan
        LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_site s ON kr.id_site = s.id_site
        LEFT JOIN tb_jabatan j ON kr.id_jabatan = j.id_jabatan
        $whereClause
        ORDER BY r.periode DESC, r.id_thr DESC";

$stThr = $koneksi->prepare($sql);
$stThr->execute($paramsThr);
$data = [];

// 4. Masukkan ke array
foreach ($stThr->fetchAll() as $row) {
    $data[] = $row;
}

// 5. Cetak JSON
echo json_encode([
    'status' => 'success',
    'data' => $data
]);