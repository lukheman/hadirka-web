<?php
/**
 * ============================================
 * ACTION CONTROLLER: DATA BPJS TK
 * ============================================
 */

require_once __DIR__ . '/../config/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

function sendResponse($status, $message) {
    echo json_encode(['status' => $status, 'msg' => $message]);
    exit;
}

if (!isset($koneksi) || !$koneksi) {
    sendResponse('error', 'Koneksi database gagal.');
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($action === 'update_bpjs') {
    $id_karyawan = intval($_POST['id_karyawan']);
    $bpjs_tk = trim($_POST['bpjs_tk']);

    try {
        // Pastikan data identitas karyawan sudah ada
        $cek = $koneksi->prepare("SELECT id_karyawan FROM tb_identitas WHERE id_karyawan = ?");
        $cek->execute([$id_karyawan]);
        $ada = $cek->fetch();

        if ($ada) {
            // Lakukan UPDATE jika datanya ada
            $stmt = $koneksi->prepare("UPDATE tb_identitas SET bpjs_tk = ? WHERE id_karyawan = ?");
            $stmt->execute([$bpjs_tk, $id_karyawan]);
        } else {
            // Lakukan INSERT jika data identitas untuk karyawan ini belum ada di tabel
            $stmt = $koneksi->prepare("INSERT INTO tb_identitas (id_karyawan, bpjs_tk) VALUES (?, ?)");
            $stmt->execute([$id_karyawan, $bpjs_tk]);
        }

        sendResponse('success', 'Nomor BPJS Ketenagakerjaan berhasil diperbarui.');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        sendResponse('error', 'Gagal menyimpan data.');
    }

} else {
    sendResponse('error', 'Aksi tidak valid.');
}
?>
