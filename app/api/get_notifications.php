<?php
include 'koneksi-api.php';

// Query untuk mengambil semua notifikasi, diurutkan berdasarkan waktu terbaru
$sql = "SELECT * FROM tb_pengumuman ORDER BY waktu_buat DESC";
$result = $koneksi->query($sql);

$notifications = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Konversi waktu_buat menjadi format yang mudah dibaca (opsional, bisa dilakukan di Flutter)
        // Untuk contoh ini, kita kirimkan sebagai string waktu
        $row['time_display'] = time_ago($row['waktu_buat']); // Asumsi ada fungsi time_ago()
        
        $notifications[] = $row;
    }
}

// Fungsi dummy untuk menghitung selisih waktu (Anda bisa menggunakan library yang lebih solid)
function time_ago($timestamp) {
    $diff = time() - strtotime($timestamp);
    if ($diff < 60) return $diff . " detik yang lalu";
    $diff = round($diff / 60);
    if ($diff < 60) return $diff . " menit yang lalu";
    $diff = round($diff / 60);
    if ($diff < 24) return $diff . " jam yang lalu";
    $diff = round($diff / 24);
    if ($diff < 7) return $diff . " hari yang lalu";
    return date("d M Y", strtotime($timestamp));
}


header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'data' => $notifications]);

$koneksi->close();
?>