<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'koneksi-api.php';

// Pengecekan Koneksi Database (Debugging)
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

// Ambil data izin
$sql = "SELECT *
        FROM tb_pengajuan_izin 
        WHERE id_karyawan = '$id_karyawan' 
        ORDER BY tgl_izin DESC";

$result = mysqli_query($koneksi, $sql);
$data_izin = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_izin[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Data riwayat izin berhasil dimuat',
        'data' => $data_izin
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data dari database. Pastikan semua field (termasuk keterangan_admin) ada di tabel: ' . mysqli_error($koneksi)
    ]);
}

mysqli_close($koneksi);
?>