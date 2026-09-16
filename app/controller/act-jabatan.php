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
    $sql->bind_param("ss", $kode, $nama);
    try {
        $sql->execute();
        echo json_encode(["status" => "success"]);
    } catch (mysqli_sql_exception $e) {
        $msg = $e->getCode() === 1062 ? "Kode jabatan sudah dipakai" : "Gagal menyimpan";
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
    $sql->bind_param("ssi", $kode, $nama, $id);
    try {
        $sql->execute();
        echo json_encode(["status" => "success"]);
    } catch (mysqli_sql_exception $e) {
        $msg = $e->getCode() === 1062 ? "Kode jabatan sudah dipakai" : "Gagal mengupdate";
        echo json_encode(["status" => "error", "msg" => $msg]);
    }
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
