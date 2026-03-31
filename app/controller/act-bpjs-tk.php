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
    
    // Pastikan data identitas karyawan sudah ada
    $cek = $koneksi->query("SELECT id_karyawan FROM tb_identitas WHERE id_karyawan = $id_karyawan");
    
    if ($cek->num_rows > 0) {
        // Lakukan UPDATE jika datanya ada
        $stmt = $koneksi->prepare("UPDATE tb_identitas SET bpjs_tk = ? WHERE id_karyawan = ?");
        $stmt->bind_param("si", $bpjs_tk, $id_karyawan);
    } else {
        // Lakukan INSERT jika data identitas untuk karyawan ini belum ada di tabel
        $stmt = $koneksi->prepare("INSERT INTO tb_identitas (id_karyawan, bpjs_tk) VALUES (?, ?)");
        $stmt->bind_param("is", $id_karyawan, $bpjs_tk);
    }
    
    if ($stmt->execute()) {
        sendResponse('success', 'Nomor BPJS Ketenagakerjaan berhasil diperbarui.');
    } else {
        sendResponse('error', 'Gagal menyimpan: ' . $stmt->error);
    }
    
} else {
    sendResponse('error', 'Aksi tidak valid.');
}

$koneksi->close();
?>