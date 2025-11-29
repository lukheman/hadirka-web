<?php
require_once __DIR__ . '/../config/koneksi.php';
header("Content-Type: application/json; charset=UTF-8");

// Cegah Notis muncul agar JSON tidak rusak
error_reporting(E_ERROR | E_PARSE);

$action = $_POST['action'] ?? '';

$response = ["status" => "error"];

switch ($action) {

    case "add":
        $nama = $_POST['nama_site'] ?? '';
        $lokasi = $_POST['lokasi'] ?? '';

        if ($nama == "" || $lokasi == "") {
            echo json_encode(["status" => "error", "msg" => "Data tidak lengkap"]);
            exit;
        }

        $sql = $koneksi->prepare("INSERT INTO tb_site (nama_site, lokasi) VALUES (?, ?)");
        $sql->bind_param("ss", $nama, $lokasi);

        $response["status"] = $sql->execute() ? "success" : "error";
        echo json_encode($response);
        exit;


    case "edit":
        $id = $_POST['id_site'] ?? '';
        $nama = $_POST['nama_site'] ?? '';
        $lokasi = $_POST['lokasi'] ?? '';

        $sql = $koneksi->prepare("UPDATE tb_site SET nama_site=?, lokasi=? WHERE id_site=?");
        $sql->bind_param("ssi", $nama, $lokasi, $id);

        $response["status"] = $sql->execute() ? "success" : "error";
        echo json_encode($response);
        exit;


    case "delete":
        $id = $_POST['id_site'] ?? '';

        $sql = $koneksi->prepare("DELETE FROM tb_site WHERE id_site=?");
        $sql->bind_param("i", $id);

        $response["status"] = $sql->execute() ? "success" : "error";
        echo json_encode($response);
        exit;
}

echo json_encode(["status" => "error", "msg" => "Invalid action"]);
