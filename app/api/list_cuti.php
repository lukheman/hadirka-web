<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/koneksi-api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$id_karyawan = $_POST['id_karyawan'] ?? '';

if (empty($id_karyawan)) {
    echo json_encode(['status' => 'error', 'message' => 'ID Karyawan diperlukan']);
    exit;
}

// Ambil data riwayat cuti
$sql = "SELECT * FROM tb_pengajuan_cuti 
        WHERE id_karyawan = ? 
        ORDER BY id_pengajuan_cuti DESC";

try {
    $stmt = $koneksi->prepare($sql);
    $stmt->execute([$id_karyawan]);
    $data_cuti = [];
    while ($row = $stmt->fetch()) {
        $data_cuti[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Data riwayat cuti berhasil dimuat',
        'data' => $data_cuti
    ]);
} catch (PDOException $e) {
    error_log('list_cuti gagal: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan database.'
    ]);
}
?>
