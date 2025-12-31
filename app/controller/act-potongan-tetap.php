<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    // Tambahkan dekripsi di sini
    $id_karier = decrypt($_POST['id_karier']); 
    $nama = $_POST['jenis_potongan'];
    $jumlah = $_POST['jumlah_potongan'];

    $stmt = $koneksi->prepare("INSERT INTO tb_potongan (id_karier, jenis_potongan, jumlah_potongan) VALUES (?, ?, ?)");
    // Pastikan bind_param sesuai (i = integer, s = string, d = double/decimal)
    $stmt->bind_param("isd", $id_karier, $nama, $jumlah);
    $status = $stmt->execute();

    echo json_encode(["status" => $status ? "success" : "error"]);
    exit;
}

if ($action == "edit") {
    $id = $_POST['id_potongan'];
    $nama = $_POST['jenis_potongan'];
    $jumlah = $_POST['jumlah_potongan'];

    $stmt = $koneksi->prepare("UPDATE tb_potongan SET jenis_potongan=?, jumlah_potongan=? WHERE id_potongan=?");
    $stmt->bind_param("sdi", $nama, $jumlah, $id);
    $status = $stmt->execute();

    echo json_encode(["status" => $status ? "success" : "error"]);
    exit;
}

if ($action == "delete") {
    $id = $_POST['id_potongan'];

    $stmt = $koneksi->prepare("DELETE FROM tb_potongan WHERE id_potongan=?");
    $stmt->bind_param("i", $id);
    $status = $stmt->execute();

    echo json_encode(["status" => $status ? "success" : "error"]);
    exit;
}
?>