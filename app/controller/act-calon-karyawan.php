<?php
error_reporting(0);
header("Content-Type: application/json");

// SESUAIKAN JUGA PATH KONEKSINYA DI SINI
require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

// === FUNGSI ACC / TOLAK ===
if ($action == "update_status") {
    $id = intval($_POST['id_calon']);
    $status = $_POST['status']; // 'Diterima' atau 'Ditolak'

    $sql = $koneksi->prepare("UPDATE tb_calon_karyawan SET status_seleksi=? WHERE id_calon=?");
    try {
        $sql->execute([$status, $id]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error"]);
    }
    exit;
}

// === FUNGSI HAPUS ===
if ($action == "delete") {
    $id = intval($_POST['id_calon']);

    // Ambil nama file sebelum dihapus untuk diclear dari storage
    $q = $koneksi->prepare("SELECT file_cv, file_ktp, file_kk, file_ijazah, file_skck, file_ak1, file_sim, file_sio FROM tb_calon_karyawan WHERE id_calon = ?");
    $q->execute([$id]);
    if ($r = $q->fetch()) {
        $dir = "../uploads/";
        foreach ($r as $file) {
            if (!empty($file) && file_exists($dir . $file)) {
                @unlink($dir . $file); // Hapus berkas fisik
            }
        }
    }

    $sql = $koneksi->prepare("DELETE FROM tb_calon_karyawan WHERE id_calon=?");
    try {
        $sql->execute([$id]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error"]);
    }
    exit;
}
