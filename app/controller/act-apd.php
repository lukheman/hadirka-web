<?php
header('Content-Type: application/json');
include "../config/koneksi.php";

$action = $_GET['action'] ?? '';

if ($action == 'get-single') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $res = mysqli_query($koneksi, "SELECT * FROM master_apd WHERE id_apd = '$id'");
    echo json_encode(mysqli_fetch_assoc($res));
    exit;
}

if ($action == 'save') {
    $id      = $_POST['id_apd'];
    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama_apd']);
    $kat     = mysqli_real_escape_string($koneksi, $_POST['kategori_apd']);
    $desc    = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $masa    = $_POST['masa_pakai_hari'];
    $stok    = (int)$_POST['stok'];
    $status  = $_POST['status_aktif'];

    if (empty($id)) {
        $sql = "INSERT INTO master_apd (nama_apd, kategori_apd, deskripsi, masa_pakai_hari, stok, status_aktif) 
                VALUES ('$nama', '$kat', '$desc', '$masa', '$stok', '$status')";
    } else {
        $sql = "UPDATE master_apd SET nama_apd='$nama', kategori_apd='$kat', deskripsi='$desc', 
                masa_pakai_hari='$masa', stok='$stok', status_aktif='$status' WHERE id_apd='$id'";
    }

    if (mysqli_query($koneksi, $sql)) echo json_encode(['status' => 'success']);
}

if ($action == 'delete') {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM master_apd WHERE id_apd = '$id'");
    echo json_encode(['status' => 'success']);
}