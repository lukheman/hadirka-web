<?php
error_reporting(0);
header("Content-Type: application/json");

// SESUAIKAN JUGA PATH KONEKSINYA DI SINI
require __DIR__.'/../config/koneksi.php';

$action = $_POST['action'] ?? '';

// === FUNGSI ACC / TOLAK ===
if ($action == "update_status") {
    $id = intval($_POST['id_calon']);
    $status = $koneksi->real_escape_string($_POST['status']); // 'Diterima' atau 'Ditolak'
    
    $sql = $koneksi->prepare("UPDATE tb_calon_karyawan SET status_seleksi=? WHERE id_calon=?");
    $sql->bind_param("si", $status, $id);
    
    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}

// === FUNGSI HAPUS ===
if ($action == "delete") {
    $id = intval($_POST['id_calon']);
    
    // Ambil nama file sebelum dihapus untuk diclear dari storage
    $q = $koneksi->query("SELECT file_cv, file_ktp, file_kk, file_ijazah, file_skck, file_ak1, file_sim, file_sio FROM tb_calon_karyawan WHERE id_calon = '$id'");
    if ($r = $q->fetch_assoc()) {
        $dir = "../uploads/";
        foreach ($r as $file) {
            if (!empty($file) && file_exists($dir . $file)) {
                @unlink($dir . $file); // Hapus berkas fisik
            }
        }
    }

    $sql = $koneksi->prepare("DELETE FROM tb_calon_karyawan WHERE id_calon=?");
    $sql->bind_param("i", $id);
    echo json_encode(["status" => $sql->execute() ? "success" : "error"]);
    exit;
}