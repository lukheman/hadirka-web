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

// Ambil data izin
$sql = "SELECT *
        FROM tb_pengajuan_izin 
        WHERE id_karyawan = ? 
        ORDER BY tgl_izin DESC";

try {
    $stmt = $koneksi->prepare($sql);
    $stmt->execute([$id_karyawan]);
    $data_izin = [];
    while ($row = $stmt->fetch()) {
        $data_izin[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Data riwayat izin berhasil dimuat',
        'data' => $data_izin
    ]);
} catch (PDOException $e) {
    error_log('list_izin gagal: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan database.'
    ]);
}
?>
