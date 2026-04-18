<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

include 'koneksi-api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$id_karyawan = $_POST['id_karyawan'] ?? '';

if (empty($id_karyawan)) {
    echo json_encode(['status' => 'error', 'message' => 'ID karyawan wajib diisi']);
    exit;
}

// JOIN dengan master_apd untuk mendapatkan nama APD
$sql = "SELECT p.*, m.nama_apd 
        FROM tb_pengajuan_apd p
        JOIN master_apd m ON p.id_apd = m.id_apd
        WHERE p.id_karyawan = '$id_karyawan'
        ORDER BY p.tgl_pengajuan DESC";

$query = mysqli_query($koneksi, $sql);
$data = [];

while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
?>