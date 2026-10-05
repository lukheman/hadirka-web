<?php
header('Content-Type: application/json');
include __DIR__."/../config/koneksi.php";

// Deteksi action dari POST atau GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'update-status') {
    // Karena menggunakan FormData dari Javascript, kita tangkap menggunakan $_POST
    $id = $_POST['id'];
    $status = $_POST['status'];
    $ket_admin = $_POST['ket_admin'] ?? '';
    $tgl_diterima = date('Y-m-d H:i:s');

    try {
        // Tambahkan ket_admin ke Query
        $st = $koneksi->prepare("UPDATE tb_bukti_sks SET status = ?, ket_admin = ?, tgl_diterima = ? WHERE id_bukti_sks = ?");
        $st->execute([$status, $ket_admin, $tgl_diterima, $id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status.']);
    }
    exit;
}

if ($action == 'delete') {
    $id = $_GET['id'];

    // Hapus file fisik
    $q = $koneksi->prepare("SELECT bukti_sks FROM tb_bukti_sks WHERE id_bukti_sks = ?");
    $q->execute([$id]);
    $data = $q->fetch();
    if ($data && !empty($data['bukti_sks'])) {
        $path = "../" . $data['bukti_sks'];
        if (file_exists($path)) unlink($path);
    }

    try {
        $st = $koneksi->prepare("DELETE FROM tb_bukti_sks WHERE id_bukti_sks = ?");
        $st->execute([$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit;
}
?>
