<?php
header('Content-Type: application/json');
include "../config/koneksi.php";
$action = $_GET['action'] ?? '';

if ($action == 'update-status') {
    $id = $_GET['id'];
    $status = $_GET['status']; // pending, disetujui, ditolak
    $sql = "UPDATE tb_pengajuan_izin SET status_izin = '$status' WHERE id_pengajuan_izin = '$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // 1. Ambil nama file gambar dari database sebelum datanya dihapus
    $query_cari = mysqli_query($koneksi, "SELECT bukti_izin FROM tb_pengajuan_izin WHERE id_pengajuan_izin = '$id'");
    $data_izin = mysqli_fetch_assoc($query_cari);

    if ($data_izin) {
        $nama_file = $data_izin['bukti_izin'];
        
        // 2. Tentukan path/lokasi folder gambar
        // Sesuaikan path ini dengan struktur folder Anda (contoh: ../uploads/izin/)
        $path_file = "../" . $nama_file;

        // 3. Cek apakah file benar-benar ada, jika ada maka hapus file fisiknya
        if (!empty($nama_file) && file_exists($path_file)) {
            unlink($path_file); // Ini fungsi untuk menghapus file dari server
        }
    }

    // 4. Setelah file fisik terhapus, baru hapus data di database
    $sql = "DELETE FROM tb_pengajuan_izin WHERE id_pengajuan_izin = '$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}
?>