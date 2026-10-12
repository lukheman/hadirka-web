<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $nama = trim($_POST['nama_jabatan'] ?? '');
    $kode = trim($_POST['kode_jabatan'] ?? '');
    if ($kode !== '' && strlen($kode) > 50) {
        echo json_encode(["status" => "error", "msg" => "Kode jabatan maksimal 50 karakter"]);
        exit;
    }
    if ($kode === '') $kode = null;

    $sql = $koneksi->prepare("INSERT INTO tb_jabatan (kode_jabatan, nama_jabatan) VALUES (?, ?)");
    try {
        $sql->execute([$kode, $nama]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $msg = (($e->errorInfo[1] ?? null) == 1062) ? "Kode jabatan sudah dipakai" : "Gagal menyimpan";
        echo json_encode(["status" => "error", "msg" => $msg]);
    }
    exit;
}

if ($action == "edit") {
    $id = $_POST['id_jabatan'];
    $nama = trim($_POST['nama_jabatan'] ?? '');
    $kode = trim($_POST['kode_jabatan'] ?? '');
    if ($kode !== '' && strlen($kode) > 50) {
        echo json_encode(["status" => "error", "msg" => "Kode jabatan maksimal 50 karakter"]);
        exit;
    }
    if ($kode === '') $kode = null;

    $sql = $koneksi->prepare("UPDATE tb_jabatan SET kode_jabatan=?, nama_jabatan=? WHERE id_jabatan=?");
    try {
        $sql->execute([$kode, $nama, $id]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $msg = (($e->errorInfo[1] ?? null) == 1062) ? "Kode jabatan sudah dipakai" : "Gagal mengupdate";
        echo json_encode(["status" => "error", "msg" => $msg]);
    }
    exit;
}

if ($action == "delete") {
    $id = $_POST['id_jabatan'];

    $sql = $koneksi->prepare("DELETE FROM tb_jabatan WHERE id_jabatan=?");
    try {
        $sql->execute([$id]);
        if ($sql->rowCount() === 0) {
            echo json_encode(["status" => "error", "msg" => "Data jabatan tidak ditemukan"]);
        } else {
            echo json_encode(["status" => "success"]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $msg = (($e->errorInfo[1] ?? null) == 1451)
            ? "Jabatan masih dipakai data lain (karyawan/loker/kontrak), tidak bisa dihapus"
            : "Gagal menghapus jabatan";
        echo json_encode(["status" => "error", "msg" => $msg]);
    }
    exit;
}

echo json_encode(["status" => "error"]);
