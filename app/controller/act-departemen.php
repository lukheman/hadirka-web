<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $nama = $_POST['nama_departemen'];

    $sql = $koneksi->prepare("INSERT INTO tb_departemen (nama_departemen) VALUES (?)");
    try {
        $sql->execute([$nama]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error"]);
    }
    exit;
}

if ($action == "edit") {
    $id = $_POST['id_departemen'];
    $nama = $_POST['nama_departemen'];

    $sql = $koneksi->prepare("UPDATE tb_departemen SET nama_departemen=? WHERE id_departemen=?");
    try {
        $sql->execute([$nama, $id]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error"]);
    }
    exit;
}

if ($action == "delete") {
    $id = $_POST['id_departemen'];

    $sql = $koneksi->prepare("DELETE FROM tb_departemen WHERE id_departemen=?");
    try {
        $sql->execute([$id]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error"]);
    }
    exit;
}

echo json_encode(["status" => "error"]);
