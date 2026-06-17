<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/koneksi-api.php';

if (mysqli_connect_errno()) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi Database Gagal: ' . mysqli_connect_error()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$id_karyawan = mysqli_real_escape_string($koneksi, $_POST['id_karyawan'] ?? '');

if (empty($id_karyawan)) {
    echo json_encode(['status' => 'error', 'message' => 'ID Karyawan diperlukan']);
    exit;
}

// Ambil data riwayat cuti
$sql = "SELECT * FROM tb_pengajuan_cuti 
        WHERE id_karyawan = '$id_karyawan' 
        ORDER BY id_pengajuan_cuti DESC";

$result = mysqli_query($koneksi, $sql);
$data_cuti = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_cuti[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Data riwayat cuti berhasil dimuat',
        'data' => $data_cuti
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
    ]);
}

mysqli_close($koneksi);
?>