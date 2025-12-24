<?php
header('Content-Type: application/json');
include "../config/koneksi.php";

$action = $_GET['action'] ?? '';

if ($action == 'update-status') {
    $id = $_GET['id'];
    $status = $_GET['status'];
    $tgl_diterima = date('Y-m-d H:i:s');

    $sql = "UPDATE tb_bukti_sks SET status = '$status', tgl_diterima ='$tgl_diterima' WHERE id_bukti_sks = '$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
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
        echo json_encode(['status' => 'error']);
    }
}
?>