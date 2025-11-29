<?php
require __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
    exit;
}

$id_sakit = $_POST['id_sakit'] ?? null;
$id_karyawan = $_POST['id_karyawan'] ?? null;
$id_site = $_POST['id_site'] ?? null;
$id_jabatan = $_POST['id_jabatan'] ?? null;
$tgl_awal = $_POST['tgl_awal_sakit'] ?? null;
$tgl_akhir = $_POST['tgl_akhir_sakit'] ?? null;
$ket = $_POST['ket_sakit'] ?? '';

try {
    if ($action === 'add') {
        if (!$id_karyawan || !$tgl_awal || !$tgl_akhir) {
            throw new Exception("Data tidak lengkap");
        }

        $stmt = $koneksi->prepare("
            INSERT INTO tb_sakit (id_karyawan, tgl_awal_sakit, tgl_akhir_sakit, ket_sakit)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $id_karyawan, $tgl_awal, $tgl_akhir, $ket);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Sakit berhasil ditambahkan']);
    } elseif ($action === 'edit') {
        if (!$id_sakit || !$id_karyawan || !$tgl_awal || !$tgl_akhir) {
            throw new Exception("Data tidak lengkap");
        }

        $stmt = $koneksi->prepare("
            UPDATE tb_sakit 
            SET id_karyawan = ?, tgl_awal_sakit = ?, tgl_akhir_sakit = ?, ket_sakit = ?
            WHERE id_sakit = ?
        ");
        $stmt->bind_param("isssi", $id_karyawan, $tgl_awal, $tgl_akhir, $ket, $id_sakit);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Sakit berhasil diperbarui']);
    } elseif ($action === 'delete') {
        if (!$id_sakit) {
            throw new Exception("ID Sakit tidak ditemukan");
        }

        $stmt = $koneksi->prepare("DELETE FROM tb_sakit WHERE id_sakit = ?");
        $stmt->bind_param("i", $id_sakit);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Sakit berhasil dihapus']);
    } else {
        throw new Exception("Action tidak valid");
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
