<?php
require __DIR__ . '/../../../config/koneksi.php'; // sesuaikan path jika perlu

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
    exit;
}

$id_absen     = isset($_POST['id_absen']) && $_POST['id_absen'] !== '' ? intval($_POST['id_absen']) : null;
$id_karyawan  = isset($_POST['id_karyawan']) && $_POST['id_karyawan'] !== '' ? intval($_POST['id_karyawan']) : null;
$tgl_absen    = $_POST['tgl_absen'] ?? null;
$jam_kerja    = isset($_POST['jam_kerja']) && $_POST['jam_kerja'] !== '' ? floatval($_POST['jam_kerja']) : null;
$ket_absen    = $_POST['ket_absen'] ?? '';

try {

    /* ================================
       =========== ADD ================
       ================================ */
    if ($action === 'add') {
        if (!$id_karyawan || !$tgl_absen || $jam_kerja === null) {
            throw new Exception("Data tidak lengkap (add)");
        }

        // Optional: hindari duplikasi (satu karyawan - satu tanggal)
        $cek = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan = ? AND tgl_absen = ?");
        $cek->bind_param("is", $id_karyawan, $tgl_absen);
        $cek->execute();
        $cek->store_result();
        if ($cek->num_rows > 0) {
            $cek->close();
            throw new Exception("Data absen untuk tanggal ini sudah ada. Gunakan edit jika ingin mengubah.");
        }
        $cek->close();

        $stmt = $koneksi->prepare("
            INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isds", $id_karyawan, $tgl_absen, $jam_kerja, $ket_absen);
        if (!$stmt->execute()) {
            throw new Exception("Gagal menambah absen: " . $stmt->error);
        }
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Absen berhasil ditambahkan']);
        exit;
    }

    /* ================================
       =========== EDIT ===============
       ================================ */
    if ($action === 'edit') {
        if (!$id_absen || !$id_karyawan || !$tgl_absen || $jam_kerja === null) {
            throw new Exception("Data tidak lengkap (edit)");
        }

        $stmt = $koneksi->prepare("
            UPDATE tb_absen 
            SET id_karyawan = ?, tgl_absen = ?, jam_kerja = ?, ket_absen = ?
            WHERE id_absen = ?
        ");
        $stmt->bind_param("isdsi", $id_karyawan, $tgl_absen, $jam_kerja, $ket_absen, $id_absen);
        if (!$stmt->execute()) {
            throw new Exception("Gagal update absen: " . $stmt->error);
        }
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Absen berhasil diperbarui']);
        exit;
    }

    /* ================================
       ========== DELETE ==============
       ================================ */
    if ($action === 'delete') {
        if (!$id_absen) {
            throw new Exception("ID Absen tidak ditemukan (delete)");
        }

        $stmt = $koneksi->prepare("DELETE FROM tb_absen WHERE id_absen = ?");
        $stmt->bind_param("i", $id_absen);
        if (!$stmt->execute()) {
            throw new Exception("Gagal menghapus absen: " . $stmt->error);
        }
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Absen berhasil dihapus']);
        exit;
    }

    throw new Exception("Action tidak valid");
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
