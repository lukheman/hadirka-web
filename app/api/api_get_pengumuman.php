<?php
header('Content-Type: application/json');
include 'koneksi-api.php';
// --- Query untuk mengambil 3 pengumuman terbaru dan aktif ---
// Sesuaikan query jika Anda ingin filter/limit yang berbeda
$sql = "SELECT judul, detail_konten, waktu_buat, warna_kode 
        FROM tb_pengumuman 
        ORDER BY waktu_buat DESC 
        LIMIT 3";

try {
    $stmt = $koneksi->query($sql);
    $pengumuman_data = $stmt->fetchAll();

    if (count($pengumuman_data) > 0) {
        // Sukses mengambil data
        echo json_encode([
            'status' => 'success',
            'message' => 'Data pengumuman berhasil dimuat.',
            'data_pengumuman' => $pengumuman_data
        ]);

    } else {
        // Tidak ada data ditemukan
        echo json_encode([
            'status' => 'success',
            'message' => 'Tidak ada pengumuman aktif.',
            'data_pengumuman' => []
        ]);
    }
} catch (PDOException $e) {
    error_log('api_get_pengumuman gagal: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan database.',
        'data_pengumuman' => []
    ]);
}
?>
