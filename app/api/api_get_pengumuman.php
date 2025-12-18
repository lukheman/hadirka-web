<?php
header('Content-Type: application/json');
include 'koneksi-api.php';
// --- Query untuk mengambil 3 pengumuman terbaru dan aktif ---
// Sesuaikan query jika Anda ingin filter/limit yang berbeda
$sql = "SELECT judul, detail_konten, waktu_buat, warna_kode 
        FROM tb_pengumuman 
        ORDER BY waktu_buat DESC 
        LIMIT 3";

$result = $koneksi->query($sql);
$pengumuman_data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pengumuman_data[] = $row;
    }
    
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

$koneksi->close();
?>