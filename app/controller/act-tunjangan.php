<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

$action = $_POST['action'];

// ADD =======================================
if ($action == "add") {

    $id_karier = decrypt($_POST['id_karier']); // karena GET berisi ID terenkripsi
    $nama = $_POST['nama_tunjangan'];
    $jumlah = $_POST['jumlah_tunjangan'];

    $q = $koneksi->query("INSERT INTO tb_tunjangan (id_karier, nama_tunjangan, jumlah_tunjangan)
                          VALUES ('$id_karier', '$nama', '$jumlah')");

    echo json_encode(["status" => $q ? "success" : "error"]);
    exit;
}

// EDIT =======================================
if ($action == "edit") {

    // menerima encrypted ID
    $id = decrypt($_POST['id_tunjangan']);

    $nama = $_POST['nama_tunjangan'];
    $jumlah = $_POST['jumlah_tunjangan'];

    $q = $koneksi->query("UPDATE tb_tunjangan SET 
                            nama_tunjangan='$nama', 
                            jumlah_tunjangan='$jumlah'
                         WHERE id_tunjangan='$id'");

    echo json_encode(["status" => $q ? "success" : "error"]);
    exit;
}

// DELETE =======================================
if ($action == "delete") {

    // menerima ID plain → jangan decrypt
    $id = $_POST['id_tunjangan'];

    $q = $koneksi->query("DELETE FROM tb_tunjangan WHERE id_tunjangan='$id'");

    echo json_encode(["status" => $q ? "success" : "error"]);
    exit;
}

?>
