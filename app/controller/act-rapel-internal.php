<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? '';
$response = ['status' => 'error', 'msg' => 'Terjadi kesalahan sistem'];

try {
    if ($action === 'delete') {
        $id_rapel = intval($_POST['id_rapel'] ?? 0);
        $stmt = $koneksi->prepare("DELETE FROM tb_rapel_internal WHERE id_rapel = ?");
        $stmt->bind_param("i", $id_rapel);
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'msg' => 'Rapel telah berhasil dihapus'];
        } else {
            $response = ['status' => 'error', 'msg' => 'Gagal menghapus rapel: ' . $stmt->error];
        }
        echo json_encode($response);
        exit;
    }

    if (in_array($action, ['add', 'edit'])) {
        $id_rapel   = intval($_POST['id_rapel'] ?? 0);
        $id_karyawan= intval($_POST['id_karyawan_internal'] ?? 0);
        $tgl_rapel  = $_POST['tgl_rapel'] ?? '';
        $nominal    = preg_replace('/\D/', '', $_POST['nominal'] ?? '0');
        $ket_rapel  = trim($_POST['ket_rapel'] ?? '');

        if($id_karyawan <= 0 || empty($tgl_rapel) || $nominal <= 0) {
            throw new Exception("Lengkapi seluruh form input nominal & tanggal!");
        }

        if ($action === 'add') {
            $stmt = $koneksi->prepare("INSERT INTO tb_rapel_internal (id_karyawan_internal, tgl_rapel, nominal, ket_rapel) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isds", $id_karyawan, $tgl_rapel, $nominal, $ket_rapel);
            $msg = "Rapel berhasil disimpan";
        } else {
            $stmt = $koneksi->prepare("UPDATE tb_rapel_internal SET id_karyawan_internal=?, tgl_rapel=?, nominal=?, ket_rapel=? WHERE id_rapel=?");
            $stmt->bind_param("isdsi", $id_karyawan, $tgl_rapel, $nominal, $ket_rapel, $id_rapel);
            $msg = "Rapel berhasil diperbarui";
        }

        if($stmt->execute()) $response = ['status' => 'success', 'msg' => $msg];
        else throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'msg' => $e->getMessage()];
}

echo json_encode($response);
exit;