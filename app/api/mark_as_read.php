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
    try {
        $stmt = $koneksi->prepare("UPDATE tb_pengumuman SET is_dibaca = TRUE WHERE id = ?");
        $stmt->execute([(int)$notification_id]);
        // Jika berhasil diupdate
        $response = ['status' => 'success', 'message' => 'Notifikasi ID ' . $notification_id . ' berhasil ditandai sudah dibaca.'];
    } catch (PDOException $e) {
        // Jika gagal dieksekusi (detail hanya di log)
        error_log('mark_as_read gagal: ' . $e->getMessage());
        $response = ['status' => 'error', 'message' => 'Gagal memperbarui status. Terjadi kesalahan database.'];
    }
} else {
    // Jika ID tidak ditemukan di parameter GET
    $response = ['status' => 'error', 'message' => 'ID Notifikasi tidak ditemukan di parameter request.'];
}

// Mengembalikan response dalam format JSON
echo json_encode($response);
?>
