<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? '';
$response = ['status' => 'error', 'msg' => 'Terjadi kesalahan sistem'];

try {
    if ($action === 'delete') {
        $id_potongan = intval($_POST['id_potongan'] ?? 0);
        $stmt = $koneksi->prepare("DELETE FROM tb_potongan_internal WHERE id_potongan = ?");
        $stmt->bind_param("i", $id_potongan);
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'msg' => 'Potongan telah berhasil dihapus'];
        } else {
            $response = ['status' => 'error', 'msg' => 'Gagal menghapus potongan: ' . $stmt->error];
        }
        echo json_encode($response);
        exit;
    }

    if (in_array($action, ['add', 'edit'])) {
        $id_potongan= intval($_POST['id_potongan'] ?? 0);
        $id_karyawan= intval($_POST['id_karyawan_internal'] ?? 0);
        $tgl_potongan = $_POST['tgl_potongan'] ?? '';
        $nominal    = preg_replace('/\D/', '', $_POST['nominal'] ?? '0');
        $ket_potongan = trim($_POST['ket_potongan'] ?? '');

        if($id_karyawan <= 0 || empty($tgl_potongan) || $nominal <= 0) {
            throw new Exception("Lengkapi seluruh form input nominal & tanggal!");
        }

        if ($action === 'add') {
            $stmt = $koneksi->prepare("INSERT INTO tb_potongan_internal (id_karyawan_internal, tgl_potongan, nominal, ket_potongan) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isds", $id_karyawan, $tgl_potongan, $nominal, $ket_potongan);
            $msg = "Potongan berhasil disimpan";
        } else {
            $stmt = $koneksi->prepare("UPDATE tb_potongan_internal SET id_karyawan_internal=?, tgl_potongan=?, nominal=?, ket_potongan=? WHERE id_potongan=?");
            $stmt->bind_param("isdsi", $id_karyawan, $tgl_potongan, $nominal, $ket_potongan, $id_potongan);
            $msg = "Potongan berhasil diperbarui";
        }

        if($stmt->execute()) $response = ['status' => 'success', 'msg' => $msg];
        else throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'msg' => $e->getMessage()];
}

echo json_encode($response);
exit;