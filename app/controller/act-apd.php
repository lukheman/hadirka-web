<?php
header('Content-Type: application/json');
include "../config/koneksi.php";

$action = $_GET['action'] ?? '';

if ($action == 'get-single') {
    $id = $_GET['id'];
    $res = $koneksi->prepare("SELECT * FROM master_apd WHERE id_apd = ?");
    $res->execute([$id]);
    $row = $res->fetch();
    echo json_encode($row === false ? null : $row);
    exit;
}

if ($action == 'save') {
    $id      = $_POST['id_apd'];
    $nama    = $_POST['nama_apd'];
    $kat     = $_POST['kategori_apd'];
    $desc    = $_POST['deskripsi'];
    $masa    = $_POST['masa_pakai_hari'];
    $stok    = (int)$_POST['stok'];
    $status  = $_POST['status_aktif'];

    try {
        if (empty($id)) {
            $st = $koneksi->prepare("INSERT INTO master_apd (nama_apd, kategori_apd, deskripsi, masa_pakai_hari, stok, status_aktif)
                    VALUES (?, ?, ?, ?, ?, ?)");
            $st->execute([$nama, $kat, $desc, $masa, $stok, $status]);
        } else {
            $st = $koneksi->prepare("UPDATE master_apd SET nama_apd=?, kategori_apd=?, deskripsi=?,
                    masa_pakai_hari=?, stok=?, status_aktif=? WHERE id_apd=?");
            $st->execute([$nama, $kat, $desc, $masa, $stok, $status, $id]);
        }
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data.']);
    }
}

if ($action == 'delete') {
    $id = $_GET['id'];
    try {
        $st = $koneksi->prepare("DELETE FROM master_apd WHERE id_apd = ?");
        $st->execute([$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
}
