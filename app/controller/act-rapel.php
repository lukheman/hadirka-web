<?php
// Menggunakan path yang lebih aman untuk naik 2 level dari app/controller ke root
require_once __DIR__ . '/../config/koneksi.php';

// Set header agar output selalu JSON
header('Content-Type: application/json');

// Matikan display error HTML agar tidak merusak format JSON, 
// tapi tetap catat jika ada error (opsional untuk debug)
ini_set('display_errors', 0); 

$action = $_POST['action'] ?? '';

// Pastikan koneksi variabelnya bernama $koneksi sesuai file koneksi.php Anda
if (!$koneksi) {
    echo json_encode(['status' => 'error', 'msg' => 'Koneksi database gagal']);
    exit;
}

switch ($action) {
    case 'add':
        $id_k = $_POST['id_karyawan'] ?? '';
        $tgl = $_POST['tgl_rapel'] ?? '';
        $nom = $_POST['nominal_rapel'] ?? 0;
        $ket = $_POST['ket_rapel'] ?? '';

        // Gunakan Prepared Statement untuk keamanan
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
        $id = $_POST['id_rapel'] ?? '';
        $id_k = $_POST['id_karyawan'] ?? '';
        $tgl = $_POST['tgl_rapel'] ?? '';
        $nom = $_POST['nominal_rapel'] ?? 0;
        $ket = $_POST['ket_rapel'] ?? '';

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
        $id = $_POST['id_rapel'] ?? '';
        
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