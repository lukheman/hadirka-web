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

// --- LOGIKA HITUNG SISA KUOTA PINJAMAN BULAN INI ---
$tahun_sekarang = date('Y');
$bulan_sekarang = date('m');

$sql_limit = "SELECT SUM(nominal_pinjaman) AS total_terpakai 
              FROM tb_pengajuan_pinjaman 
              WHERE id_karyawan = '$id_karyawan' 
                AND status_pinjaman != 'ditolak' 
                AND YEAR(tgl_pengajuan) = '$tahun_sekarang' 
                AND MONTH(tgl_pengajuan) = '$bulan_sekarang'";

$res_limit = mysqli_query($koneksi, $sql_limit);
$row_limit = mysqli_fetch_assoc($res_limit);
$total_terpakai = (int)($row_limit['total_terpakai'] ?? 0);

$limit_maksimal = 1000000;
$sisa_pinjaman = $limit_maksimal - $total_terpakai;
if ($sisa_pinjaman < 0) $sisa_pinjaman = 0; // Mengantisipasi nilai minus
// ---------------------------------------------------

// Ambil data pinjaman
$sql = "SELECT *
        FROM tb_pengajuan_pinjaman 
        WHERE id_karyawan = '$id_karyawan' 
        ORDER BY tgl_pengajuan DESC";

$result = mysqli_query($koneksi, $sql);
$data_pinjaman = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_pinjaman[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Data riwayat pinjaman berhasil dimuat',
        'limit_maksimal' => $limit_maksimal,
        'total_terpakai' => $total_terpakai,
        'sisa_pinjaman_bulan_ini' => $sisa_pinjaman, // Elemen baru untuk dibaca oleh Flutter/Mobile App
        'data' => $data_pinjaman
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data dari database: ' . mysqli_error($koneksi)
    ]);
}

mysqli_close($koneksi);
?>