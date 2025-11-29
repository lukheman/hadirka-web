<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $nama = $_POST['nama_jabatan'];

    $sql = $koneksi->prepare("INSERT INTO tb_jabatan (nama_jabatan) VALUES (?)");
    $sql->bind_param("s", $nama);
    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

if ($action == "edit") {
    $id = $_POST['id_jabatan'];
    $nama = $_POST['nama_jabatan'];

    $sql = $koneksi->prepare("UPDATE tb_jabatan SET nama_jabatan=? WHERE id_jabatan=?");
    $sql->bind_param("si", $nama, $id);
    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

if ($action == "delete") {
    $id = $_POST['id_jabatan'];

    $sql = $koneksi->prepare("DELETE FROM tb_jabatan WHERE id_jabatan=?");
    $sql->bind_param("i", $id);
    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

echo json_encode(["status" => "error"]);
