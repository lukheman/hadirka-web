<?php
error_reporting(0);
header("Content-Type: application/json");
require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $id_jabatan = $_POST['id_jabatan'];
    $status = $_POST['status_loker'];

    $sql = $koneksi->prepare("INSERT INTO tb_loker (id_jabatan, status_loker) VALUES (?, ?)");
    $sql->bind_param("is", $id_jabatan, $status);
    
    if ($sql->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Gagal Tambah: " . $sql->error]);
    }
    exit;
}

if ($action == "edit") {
    $id_loker = $_POST['id_loker'];
    $id_jabatan = $_POST['id_jabatan'];
    $status = $_POST['status_loker'];

    $sql = $koneksi->prepare("UPDATE tb_loker SET id_jabatan=?, status_loker=? WHERE id_loker=?");
    $sql->bind_param("isi", $id_jabatan, $status, $id_loker);
    
    if ($sql->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Gagal Update: " . $sql->error]);
    }
    exit;
}

if ($action == "delete") {
    $id_loker = $_POST['id_loker'];
    
    $sql = $koneksi->prepare("DELETE FROM tb_loker WHERE id_loker=?");
    $sql->bind_param("i", $id_loker);
    
    if ($sql->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Gagal Hapus: " . $sql->error]);
    }
    exit;
}

echo json_encode(["status" => "error", "msg" => "Invalid action"]);