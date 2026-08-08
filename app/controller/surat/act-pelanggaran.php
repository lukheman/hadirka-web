<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $sql = $koneksi->prepare("INSERT INTO m_jenis_pelanggaran (nama_pelanggaran, keterangan_default) VALUES (?, ?)");
    $sql->bind_param("ss", $_POST['nama_pelanggaran'], $_POST['keterangan_default']);
    echo json_encode(["status" => $sql->execute() ? "success" : "error", "msg" => $koneksi->error]);
}

if ($action == "edit") {
    $sql = $koneksi->prepare("UPDATE m_jenis_pelanggaran SET nama_pelanggaran=?, keterangan_default=? WHERE id_pelanggaran=?");
    $sql->bind_param("ssi", $_POST['nama_pelanggaran'], $_POST['keterangan_default'], $_POST['id_pelanggaran']);
    echo json_encode(["status" => $sql->execute() ? "success" : "error", "msg" => $koneksi->error]);
}

if ($action == "delete") {
    $sql = $koneksi->prepare("DELETE FROM m_jenis_pelanggaran WHERE id_pelanggaran=?");
    $sql->bind_param("i", $_POST['id_pelanggaran']);
    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
}