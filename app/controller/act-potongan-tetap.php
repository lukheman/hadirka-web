<?php
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'];

// ADD
if ($action == "add") {
    $id_karier = $_POST['id_karier'];
    $nama = $_POST['jenis_potongan'];
    $jumlah = $_POST['jumlah_potongan'];

    $q = $koneksi->query("INSERT INTO tb_potongan (id_karier, jenis_potongan, jumlah_potongan)
                          VALUES ('$id_karier', '$nama', '$jumlah')");

    echo json_encode(["status" => $q ? "success" : "error"]);
    exit;
}

// EDIT
if ($action == "edit") {
    $id = $_POST['id_potongan'];
    $nama = $_POST['jenis_potongan'];
    $jumlah = $_POST['jumlah_potongan'];

    $q = $koneksi->query("UPDATE tb_potongan SET 
                            jenis_potongan='$nama', 
                            jumlah_potongan='$jumlah'
                         WHERE id_potongan='$id'");

    echo json_encode(["status" => $q ? "success" : "error"]);
    exit;
}

// DELETE
if ($action == "delete") {
    $id = $_POST['id_potongan'];

    $q = $koneksi->query("DELETE FROM tb_potongan WHERE id_potongan='$id'");

    echo json_encode(["status" => $q ? "success" : "error"]);
    exit;
}
?>
