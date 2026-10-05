<?php
error_reporting(0);
header('Content-Type: application/json');
include "../config/koneksi.php";

$action = $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Gagal memproses permintaan'];

if ($action == 'get-single') {
    $id = $_GET['id'];
    $res = $koneksi->prepare("SELECT * FROM tb_priode WHERE id_priode = ?");
    $res->execute([$id]);
    $row = $res->fetch();
    echo json_encode($row === false ? null : $row);
    exit;
}

if ($action == 'save') {
    $id      = $_POST['id_priode'];
    $type    = $_POST['type_priode'];
    $mulai   = $_POST['tgl_mulai'];
    $selesai = $_POST['tgl_selesai'];

    try {
        if (empty($id)) {
            $st = $koneksi->prepare("INSERT INTO tb_priode (type_priode, tgl_mulai, tgl_selesai, status_priode)
                    VALUES (?, ?, ?, 'Open')");
            $st->execute([$type, $mulai, $selesai]);
        } else {
            $st = $koneksi->prepare("UPDATE tb_priode SET type_priode=?, tgl_mulai=?, tgl_selesai=? WHERE id_priode=?");
            $st->execute([$type, $mulai, $selesai, $id]);
        }
        $response = ['status' => 'success'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $response = ['status' => 'error', 'message' => 'Gagal menyimpan data.'];
    }
}

if ($action == 'toggle-status') {
    $input  = json_decode(file_get_contents('php://input'), true);
    $id     = $input['id'];
    $status = $input['status'];

    // Jika meng-Open, tutup yang lain dengan tipe yang sama
    // (logika lama, tetap dinonaktifkan)
    // if ($status === 'Open') { ... }

    try {
        $st = $koneksi->prepare("UPDATE tb_priode SET status_priode = ? WHERE id_priode = ?");
        $st->execute([$status, $id]);
        $response = ['status' => 'success'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $response = ['status' => 'error', 'message' => 'Gagal memperbarui status.'];
    }
}

if ($action == 'delete') {
    $id = $_GET['id'];
    try {
        $st = $koneksi->prepare("DELETE FROM tb_priode WHERE id_priode=?");
        $st->execute([$id]);
        $response = ['status' => 'success'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $response = ['status' => 'error', 'message' => 'Gagal menghapus data.'];
    }
}

echo json_encode($response);
