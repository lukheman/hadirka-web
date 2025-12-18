<?php
header('Content-Type: application/json');

// Pengaturan Database
include 'koneksi-api.php';

// Query mengambil shortcut yang aktif berdasarkan urutan
$sql = "SELECT nama_shortcut, ikon_nama, tipe_aksi, nilai_aksi, warna_ikon 
        FROM footer_shortcuts 
        WHERE is_active = 1 
        ORDER BY urutan ASC";

$result = $koneksi->query($sql);

$shortcuts = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $shortcuts[] = $row;
    }
    
    echo json_encode([
        "status" => "success",
        "data_shortcuts" => $shortcuts
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "data_shortcuts" => []
    ]);
}

$koneksi->close();
?>