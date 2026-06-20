<?php
header('Content-Type: application/json');
include __DIR__."/../config/koneksi.php";

// Deteksi action dari POST atau GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'update-status') {
    // Karena menggunakan FormData dari Javascript, kita tangkap menggunakan $_POST
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $ket_admin = mysqli_real_escape_string($koneksi, $_POST['ket_admin'] ?? '');
    $tgl_diterima = date('Y-m-d H:i:s');

    // Tambahkan ket_admin ke Query
    $sql = "UPDATE tb_bukti_sks SET status = '$status', ket_admin = '$ket_admin', tgl_diterima ='$tgl_diterima' WHERE id_bukti_sks = '$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}

if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Hapus file fisik
    $q = mysqli_query($koneksi, "SELECT bukti_sks FROM tb_bukti_sks WHERE id_bukti_sks = '$id'");
    $data = mysqli_fetch_assoc($q);
    if ($data && !empty($data['bukti_sks'])) {
        $path = "../" . $data['bukti_sks'];
        if (file_exists($path)) unlink($path);
    }

    $sql = "DELETE FROM tb_bukti_sks WHERE id_bukti_sks = '$id'";
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}
?>