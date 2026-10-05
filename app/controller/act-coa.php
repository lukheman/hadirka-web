<?php
header("Content-Type: application/json");
require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $sql = $koneksi->prepare("INSERT INTO tb_kategori_keuangan (kode_akun, nama_akun, kelompok) VALUES (?, ?, ?)");
    try {
        $ok = $sql->execute([$_POST['kode_akun'], $_POST['nama_akun'], $_POST['kelompok']]);
        echo json_encode(["status" => $ok ? "success" : "error"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error"]);
    }
}

if ($action == "edit") {
    $sql = $koneksi->prepare("UPDATE tb_kategori_keuangan SET kode_akun=?, nama_akun=?, kelompok=? WHERE id_kategori=?");
    try {
        $ok = $sql->execute([$_POST['kode_akun'], $_POST['nama_akun'], $_POST['kelompok'], $_POST['id_kategori']]);
        echo json_encode(["status" => $ok ? "success" : "error"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error"]);
    }
}

if ($action == "delete") {
    $sql = $koneksi->prepare("DELETE FROM tb_kategori_keuangan WHERE id_kategori=?");
    try {
        $ok = $sql->execute([$_POST['id_kategori']]);
        echo json_encode(["status" => $ok ? "success" : "error"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error"]);
    }
}
