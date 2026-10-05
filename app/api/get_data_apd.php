<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include 'koneksi-api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan'
    ]);
    exit;
}

$id_karyawan = $_POST['id_karyawan'] ?? '';

if ($id_karyawan === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID karyawan wajib diisi'
    ]);
    exit;
}

$sql = "
    SELECT 
        ma.nama_apd,
        DATE_FORMAT(pa.tanggal_pengambilan, '%d-%m-%Y') AS tanggal_pengambilan
    FROM pengambilan_apd pa
    JOIN master_apd ma ON pa.id_apd = ma.id_apd
    WHERE pa.id_karyawan = ?
    ORDER BY pa.tanggal_pengambilan DESC
";

try {
    $stmt = $koneksi->prepare($sql);
    $stmt->execute([$id_karyawan]);

    $data = [];
    while ($row = $stmt->fetch()) {
        $data[] = $row;
    }
} catch (PDOException $e) {
    error_log('get_data_apd gagal: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan database.'
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
