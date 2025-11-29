<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $id_departemen = $_POST['id_departemen'];
    $nama = $_POST['nama_divisi'];

    $sql = $koneksi->prepare("INSERT INTO tb_devisi (id_departemen, nama_divisi) VALUES (?, ?)");
    $sql->bind_param("is", $id_departemen, $nama);

    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

if ($action == "edit") {
    $id = $_POST['id_divisi'];
    $id_departemen = $_POST['id_departemen'];
    $nama = $_POST['nama_divisi'];

    $sql = $koneksi->prepare("UPDATE tb_devisi SET id_departemen=?, nama_divisi=? WHERE id_divisi=?");
    $sql->bind_param("isi", $id_departemen, $nama, $id);

    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

if ($action == "delete") {
    $id = $_POST['id_divisi'];

    $sql = $koneksi->prepare("DELETE FROM tb_devisi WHERE id_divisi=?");
    $sql->bind_param("i", $id);

    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

echo json_encode(["status" => "error"]);
