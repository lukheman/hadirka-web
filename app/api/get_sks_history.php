
<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__.'/koneksi-api.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan. Gunakan POST.']);
    exit;
}

// ✅ FIX: Ambil dari $_POST (bukan JSON body) sesuai dengan Flutter
$id_karyawan = $_POST['id_karyawan'] ?? '';

if (empty($id_karyawan)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Karyawan wajib diisi.']);
    exit;
}

// Tambahkan ket_sks di query SELECT
$sql = "SELECT id_bukti_sks, id_karyawan, tgl_sks, bukti_sks, tgl_kirim, tgl_diterima, status, ket_admin, ket_sks 
        FROM tb_bukti_sks 
        WHERE id_karyawan = ?
        ORDER BY tgl_kirim DESC";

try {
    $stmt = $koneksi->prepare($sql);
    $stmt->execute([$id_karyawan]);
} catch (PDOException $e) {
    error_log('get_sks_history gagal: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database.']);
    exit;
}

$data_sks = [];
while ($row = $stmt->fetch()) {
    $data_sks[] = [
        'id_bukti_sks' => (int)$row['id_bukti_sks'],
        'id_karyawan'  => $row['id_karyawan'], 
        'tgl_sks'      => $row['tgl_sks'], 
        'bukti_sks'    => $row['bukti_sks'],
        'tgl_kirim'    => $row['tgl_kirim'],
        'tgl_diterima' => $row['tgl_diterima'], 
        'status'       => $row['status'],
        'ket_admin'    => $row['ket_admin'],
        'ket_sks'      => $row['ket_sks'],
    ];
}

// ✅ FIX: Key 'data_sks' sesuai Flutter (bukan 'history')
echo json_encode([
    'status' => 'success',
    'message' => 'Data riwayat SKS berhasil diambil.',
    'data_sks' => $data_sks
]);
?>
