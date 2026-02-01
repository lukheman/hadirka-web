<?php
require_once __DIR__ . '/../config/koneksi.php';
header('Content-Type: application/json');
ini_set('display_errors', 0);

$action = $_POST['action'] ?? '';

if (!$koneksi) {
    echo json_encode(['status' => 'error', 'msg' => 'Koneksi database gagal']);
    exit;
}

switch ($action) {
    case 'add':
        $id_k = intval($_POST['id_karyawan'] ?? 0);
        $tgl = $_POST['tgl_rapel'] ?? '';
        $nom = floatval($_POST['nominal_rapel'] ?? 0);
        $ket = trim($_POST['ket_rapel'] ?? '');

        if ($id_k <= 0 || empty($tgl) || $nom <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Data tidak lengkap atau tidak valid']);
            exit;
        }

        $stmt = $koneksi->prepare("INSERT INTO tb_rapel (id_karyawan, tgl_rapel, nominal_rapel, ket_rapel) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $id_k, $tgl, $nom, $ket);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'msg' => 'Data rapel berhasil ditambahkan']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal simpan: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'edit':
        $id = intval($_POST['id_rapel'] ?? 0);
        $id_k = intval($_POST['id_karyawan'] ?? 0);
        $tgl = $_POST['tgl_rapel'] ?? '';
        $nom = floatval($_POST['nominal_rapel'] ?? 0);
        $ket = trim($_POST['ket_rapel'] ?? '');

        if ($id <= 0 || $id_k <= 0 || empty($tgl) || $nom <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Data tidak lengkap atau tidak valid']);
            exit;
        }

        $stmt = $koneksi->prepare("UPDATE tb_rapel SET id_karyawan=?, tgl_rapel=?, nominal_rapel=?, ket_rapel=? WHERE id_rapel=?");
        $stmt->bind_param("isdsi", $id_k, $tgl, $nom, $ket, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'msg' => 'Data rapel berhasil diperbarui']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal update: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = intval($_POST['id_rapel'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'ID tidak valid']);
            exit;
        }
        
        $stmt = $koneksi->prepare("DELETE FROM tb_rapel WHERE id_rapel = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'msg' => 'Data rapel berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal menghapus data']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Aksi tidak valid']);
        break;
}

$koneksi->close();
?>