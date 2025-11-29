<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $nama = $_POST['nama_departemen'];

    $sql = $koneksi->prepare("INSERT INTO tb_departemen (nama_departemen) VALUES (?)");
    $sql->bind_param("s", $nama);
    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

if ($action == "edit") {
    $id = $_POST['id_departemen'];
    $nama = $_POST['nama_departemen'];

    $sql = $koneksi->prepare("UPDATE tb_departemen SET nama_departemen=? WHERE id_departemen=?");
    $sql->bind_param("si", $nama, $id);
    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

if ($action == "delete") {
    $id = $_POST['id_departemen'];

    $sql = $koneksi->prepare("DELETE FROM tb_departemen WHERE id_departemen=?");
    $sql->bind_param("i", $id);
    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

echo json_encode(["status" => "error"]);
