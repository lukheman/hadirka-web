<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? '';
$response = ['status' => 'error', 'msg' => 'Terjadi kesalahan sistem'];

try {
    // === PERBAIKAN DI BLOK DELETE INI ===
    if ($action === 'delete') {
        $id_bonus = intval($_POST['id_bonus'] ?? 0);
        $stmt = $koneksi->prepare("DELETE FROM tb_bonus_internal WHERE id_bonus = ?");
        $stmt->bind_param("i", $id_bonus);
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'msg' => 'Bonus telah berhasil dihapus'];
        } else {
            $response = ['status' => 'error', 'msg' => 'Gagal menghapus bonus: ' . $stmt->error];
        }
        
        // PENTING: Cetak JSON sebelum exit
        echo json_encode($response);
        exit;
    }
    // ===================================

    if (in_array($action, ['add', 'edit'])) {
        $id_bonus   = intval($_POST['id_bonus'] ?? 0);
        $id_karyawan= intval($_POST['id_karyawan_internal'] ?? 0);
        $tgl_bonus  = $_POST['tgl_bonus'] ?? '';
        $nominal    = preg_replace('/\D/', '', $_POST['nominal'] ?? '0');
        $ket_bonus  = trim($_POST['ket_bonus'] ?? '');

        if($id_karyawan <= 0 || empty($tgl_bonus) || $nominal <= 0) {
            throw new Exception("Lengkapi seluruh form input nominal & tanggal!");
        }

        if ($action === 'add') {
            $stmt = $koneksi->prepare("INSERT INTO tb_bonus_internal (id_karyawan_internal, tgl_bonus, nominal, ket_bonus) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isds", $id_karyawan, $tgl_bonus, $nominal, $ket_bonus);
            $msg = "Bonus berhasil disimpan";
        } else {
            $stmt = $koneksi->prepare("UPDATE tb_bonus_internal SET id_karyawan_internal=?, tgl_bonus=?, nominal=?, ket_bonus=? WHERE id_bonus=?");
            $stmt->bind_param("isdsi", $id_karyawan, $tgl_bonus, $nominal, $ket_bonus, $id_bonus);
            $msg = "Bonus berhasil diperbarui";
        }

        if($stmt->execute()) $response = ['status' => 'success', 'msg' => $msg];
        else throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'msg' => $e->getMessage()];
}

echo json_encode($response);
exit;