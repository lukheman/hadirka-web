<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    try {
        $sql = $koneksi->prepare("INSERT INTO m_jenis_pelanggaran (nama_pelanggaran, keterangan_default) VALUES (?, ?)");
        $sql->execute([$_POST['nama_pelanggaran'], $_POST['keterangan_default']]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log('pelanggaran add: ' . $e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal menambah data."]);
    }
}

if ($action == "edit") {
    try {
        $sql = $koneksi->prepare("UPDATE m_jenis_pelanggaran SET nama_pelanggaran=?, keterangan_default=? WHERE id_pelanggaran=?");
        $sql->execute([$_POST['nama_pelanggaran'], $_POST['keterangan_default'], $_POST['id_pelanggaran']]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log('pelanggaran edit: ' . $e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal mengubah data."]);
    }
}

if ($action == "delete") {
    try {
        $sql = $koneksi->prepare("DELETE FROM m_jenis_pelanggaran WHERE id_pelanggaran=?");
        $sql->execute([$_POST['id_pelanggaran']]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log('pelanggaran delete: ' . $e->getMessage());
        echo json_encode(["status" => "error"]);
    }
}
