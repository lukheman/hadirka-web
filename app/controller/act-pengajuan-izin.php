<?php
header('Content-Type: application/json');
include __DIR__ . "/../config/koneksi.php";
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'update-status') {
    $id = $_POST['id'] ?? $_GET['id'];
    $status = $_POST['status'] ?? $_GET['status'];
    $ket_admin = $_POST['ket_admin'] ?? '';

    try {
        $st = $koneksi->prepare("UPDATE tb_pengajuan_izin SET status_izin = ?, ket_admin = ? WHERE id_pengajuan_izin = ?");
        $st->execute([$status, $ket_admin, $id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status.']);
    }
    exit;
}

if ($action == 'delete') {
    $id = $_GET['id'];

    // 1. Ambil nama file gambar dari database sebelum datanya dihapus
    $query_cari = $koneksi->prepare("SELECT bukti_izin FROM tb_pengajuan_izin WHERE id_pengajuan_izin = ?");
    $query_cari->execute([$id]);
    $data_izin = $query_cari->fetch();

    if ($data_izin) {
        $nama_file = $data_izin['bukti_izin'];

        // 2. Tentukan path/lokasi folder gambar
        // Sesuaikan path ini dengan struktur folder Anda (contoh: ../uploads/izin/)
        $path_file = "../" . $nama_file;

        // 3. Cek apakah file benar-benar ada, jika ada maka hapus file fisiknya
        if (!empty($nama_file) && file_exists($path_file)) {
            unlink($path_file); // Ini fungsi untuk menghapus file dari server
        }
    }

    // 4. Setelah file fisik terhapus, baru hapus data di database
    try {
        $st = $koneksi->prepare("DELETE FROM tb_pengajuan_izin WHERE id_pengajuan_izin = ?");
        $st->execute([$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit;
}
?>
