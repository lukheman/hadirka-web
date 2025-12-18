<?php
header('Content-Type: application/json');

include 'koneksi-api.php';

// ---------------------------------------------------
// 2. LOGIKA API (Mark As Read)
// ---------------------------------------------------

// Ambil ID dari request GET
// Menggunakan null coalescing operator (??) untuk fallback
$notification_id = $_GET['id'] ?? null; 

if ($notification_id) {
    // Menggunakan Prepared Statements untuk mencegah SQL Injection
    $stmt = $koneksi->prepare("UPDATE tb_pengumuman SET is_dibaca = TRUE WHERE id = ?");
    
    // 'i' menunjukkan tipe data integer untuk parameter $notification_id
    $stmt->bind_param("i", $notification_id);
    
    if ($stmt->execute()) {
        // Jika berhasil diupdate
        $response = ['status' => 'success', 'message' => 'Notifikasi ID ' . $notification_id . ' berhasil ditandai sudah dibaca.'];
    } else {
        // Jika gagal dieksekusi
        $response = ['status' => 'error', 'message' => 'Gagal memperbarui status: ' . $stmt->error];
    }
    
    $stmt->close();
} else {
    // Jika ID tidak ditemukan di parameter GET
    $response = ['status' => 'error', 'message' => 'ID Notifikasi tidak ditemukan di parameter request.'];
}

// Mengembalikan response dalam format JSON
echo json_encode($response);

// Tutup koneksi database
$koneksi->close();
?>