<?php
error_reporting(0);
header('Content-Type: application/json');
include "../config/koneksi.php";

$action = $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Gagal memproses permintaan'];

if ($action == 'get-single') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $res = mysqli_query($koneksi, "SELECT * FROM tb_priode WHERE id_priode = '$id'");
    echo json_encode(mysqli_fetch_assoc($res));
    exit;
}

if ($action == 'save') {
    $id      = mysqli_real_escape_string($koneksi, $_POST['id_priode']);
    $type    = mysqli_real_escape_string($koneksi, $_POST['type_priode']);
    $mulai   = mysqli_real_escape_string($koneksi, $_POST['tgl_mulai']);
    $selesai = mysqli_real_escape_string($koneksi, $_POST['tgl_selesai']);

    if (empty($id)) {
        $sql = "INSERT INTO tb_priode (type_priode, tgl_mulai, tgl_selesai, status_priode) 
                VALUES ('$type', '$mulai', '$selesai', 'Open')";
    } else {
        $sql = "UPDATE tb_priode SET type_priode='$type', tgl_mulai='$mulai', tgl_selesai='$selesai' WHERE id_priode='$id'";
    }

    if (mysqli_query($koneksi, $sql)) $response = ['status' => 'success'];
}

if ($action == 'toggle-status') {
    $input  = json_decode(file_get_contents('php://input'), true);
    $id     = mysqli_real_escape_string($koneksi, $input['id']);
    $status = mysqli_real_escape_string($koneksi, $input['status']); 

    // Jika meng-Open, tutup yang lain dengan tipe yang sama
    if ($status === 'Open') {
        $q_info = mysqli_query($koneksi, "SELECT type_priode FROM tb_priode WHERE id_priode='$id'");
        $d_info = mysqli_fetch_assoc($q_info);
        $type   = $d_info['type_priode'];
        mysqli_query($koneksi, "UPDATE tb_priode SET status_priode = 'Closed' WHERE type_priode = '$type'");
    }

    $sql = "UPDATE tb_priode SET status_priode = '$status' WHERE id_priode = '$id'";
    if (mysqli_query($koneksi, $sql)) $response = ['status' => 'success'];
}

if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    if (mysqli_query($koneksi, "DELETE FROM tb_priode WHERE id_priode='$id'")) $response = ['status' => 'success'];
}

echo json_encode($response);