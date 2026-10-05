<?php
header('Content-Type: application/json');

// Pengaturan Database
include 'koneksi-api.php';

// Query mengambil shortcut yang aktif berdasarkan urutan
$sql = "SELECT nama_shortcut, ikon_nama, tipe_aksi, nilai_aksi, warna_ikon 
        FROM footer_shortcuts 
        WHERE is_active = 1 
        ORDER BY urutan ASC";

try {
    $stmt = $koneksi->query($sql);
    $shortcuts = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "data_shortcuts" => $shortcuts
    ]);
} catch (PDOException $e) {
    error_log('get_icon_footer gagal: ' . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Terjadi kesalahan database.",
        "data_shortcuts" => []
    ]);
}
?>
