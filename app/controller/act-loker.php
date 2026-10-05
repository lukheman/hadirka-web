<?php
error_reporting(0);
header("Content-Type: application/json");
require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $id_jabatan = $_POST['id_jabatan'];
    $status = $_POST['status_loker'];

    $sql = $koneksi->prepare("INSERT INTO tb_loker (id_jabatan, status_loker) VALUES (?, ?)");
    try {
        if ($sql->execute([$id_jabatan, $status])) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Gagal menambah data."]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal menambah data."]);
    }
    exit;
}

if ($action == "edit") {
    $id_loker = $_POST['id_loker'];
    $id_jabatan = $_POST['id_jabatan'];
    $status = $_POST['status_loker'];

    $sql = $koneksi->prepare("UPDATE tb_loker SET id_jabatan=?, status_loker=? WHERE id_loker=?");
    try {
        if ($sql->execute([$id_jabatan, $status, $id_loker])) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Gagal memperbarui data."]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal memperbarui data."]);
    }
    exit;
}

if ($action == "delete") {
    $id_loker = $_POST['id_loker'];

    $sql = $koneksi->prepare("DELETE FROM tb_loker WHERE id_loker=?");
    try {
        if ($sql->execute([$id_loker])) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Gagal menghapus data."]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal menghapus data."]);
    }
    exit;
}

echo json_encode(["status" => "error", "msg" => "Invalid action"]);
