<?php
require __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
    exit;
}

$id_izin = $_POST['id_izin'] ?? null;
$id_karyawan = $_POST['id_karyawan'] ?? null;
$id_site = $_POST['id_site'] ?? null;
$id_jabatan = $_POST['id_jabatan'] ?? null;
$tgl_awal = $_POST['tgl_awal_izin'] ?? null;
$tgl_akhir = $_POST['tgl_akhir_izin'] ?? null;
$ket = $_POST['ket_izin'] ?? '';

try {
    if ($action === 'add') {
        if (!$id_karyawan || !$tgl_awal || !$tgl_akhir) {
            throw new Exception("Data tidak lengkap");
        }

        $stmt = $koneksi->prepare("
            INSERT INTO tb_izin (id_karyawan, tgl_awal_izin, tgl_akhir_izin, ket_izin)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $id_karyawan, $tgl_awal, $tgl_akhir, $ket);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Izin berhasil ditambahkan']);
    } elseif ($action === 'edit') {
        if (!$id_izin || !$id_karyawan || !$tgl_awal || !$tgl_akhir) {
            throw new Exception("Data tidak lengkap");
        }

        $stmt = $koneksi->prepare("
            UPDATE tb_izin 
            SET id_karyawan = ?, tgl_awal_izin = ?, tgl_akhir_izin = ?, ket_izin = ?
            WHERE id_izin = ?
        ");
        $stmt->bind_param("isssi", $id_karyawan, $tgl_awal, $tgl_akhir, $ket, $id_izin);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Izin berhasil diperbarui']);
    } elseif ($action === 'delete') {
        if (!$id_izin) {
            throw new Exception("ID Izin tidak ditemukan");
        }

        $stmt = $koneksi->prepare("DELETE FROM tb_izin WHERE id_izin = ?");
        $stmt->bind_param("i", $id_izin);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Izin berhasil dihapus']);
    } else {
        throw new Exception("Action tidak valid");
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
