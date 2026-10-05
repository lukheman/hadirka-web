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

try {
    // --- LOGIKA HITUNG SISA KUOTA PINJAMAN BULAN INI ---
    $tahun_sekarang = date('Y');
    $bulan_sekarang = date('m');

    $st_limit = $koneksi->prepare("SELECT SUM(nominal_pinjaman) AS total_terpakai 
                  FROM tb_pengajuan_pinjaman 
                  WHERE id_karyawan = ? 
                    AND status_pinjaman != 'ditolak' 
                    AND YEAR(tgl_pengajuan) = ? 
                    AND MONTH(tgl_pengajuan) = ?");
    $st_limit->execute([$id_karyawan, $tahun_sekarang, $bulan_sekarang]);
    $row_limit = $st_limit->fetch();
    $total_terpakai = (int)($row_limit['total_terpakai'] ?? 0);

    $limit_maksimal = 1000000;
    $sisa_pinjaman = $limit_maksimal - $total_terpakai;
    if ($sisa_pinjaman < 0) $sisa_pinjaman = 0; // Mengantisipasi nilai minus
    // ---------------------------------------------------

    // Ambil data pinjaman
    $st = $koneksi->prepare("SELECT *
            FROM tb_pengajuan_pinjaman 
            WHERE id_karyawan = ? 
            ORDER BY tgl_pengajuan DESC");
    $st->execute([$id_karyawan]);

    // Tambahkan query ini di bagian atas atau sebelum meng-echo json_encode
    $q_status = $koneksi->query("SELECT nilai FROM tb_pengaturan WHERE nama_pengaturan = 'buka_pinjaman'");
    $row_status = $q_status->fetch();
    $is_open = $row_status ? (int)$row_status['nilai'] : 1;

    $data_pinjaman = [];
    while ($row = $st->fetch()) {
        $data_pinjaman[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Data riwayat pinjaman berhasil dimuat',
        'limit_maksimal' => $limit_maksimal,
        'total_terpakai' => $total_terpakai,
        'sisa_pinjaman_bulan_ini' => $sisa_pinjaman,
        'is_open' => $is_open, // <-- TAMBAHKAN PARAMETER INI UNTUK DIBACA FLUTTER (1 = Buka, 0 = Tutup)
        'data' => $data_pinjaman
    ]);
} catch (PDOException $e) {
    error_log('list_pinjaman gagal: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan database.'
    ]);
}
?>
