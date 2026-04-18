<?php
header('Content-Type: application/json');
include "../config/koneksi.php";
$action = $_GET['action'] ?? '';

if ($action == 'update-status') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $status = mysqli_real_escape_string($koneksi, $_GET['status']); 
    
    $sql = "UPDATE tb_pengajuan_apd SET status_pengajuan = '$status' WHERE id_pengajuan_apd = '$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        // (Opsional) Jika disetujui, Anda dapat menambahkan logika INSERT otomatis ke tabel pengambilan_apd di sini.
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}

if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    $query_cari = mysqli_query($koneksi, "SELECT bukti_rusak FROM tb_pengajuan_apd WHERE id_pengajuan_apd = '$id'");
    $data_apd = mysqli_fetch_assoc($query_cari);

    if ($data_apd && !empty($data_apd['bukti_rusak'])) {
        $path_file = "../" . $data_apd['bukti_rusak'];
        if (file_exists($path_file)) {
            unlink($path_file);
        }
    }

    $sql = "DELETE FROM tb_pengajuan_apd WHERE id_pengajuan_apd = '$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}
?>