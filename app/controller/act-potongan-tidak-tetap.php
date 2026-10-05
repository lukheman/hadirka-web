<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $id_karyawan = $_POST['id_karyawan'] ?? '';
        $tgl_potongan = $_POST['tgl_potongan'] ?? '';
        $jenis_potongan = $_POST['jenis_potongan'] ?? '';
        $nominal = isset($_POST['nominal_potongan']) ? floatval(str_replace('.', '', $_POST['nominal_potongan'])) : 0;
        $ket = $_POST['ket_potongan'] ?? '';

        if (!$id_karyawan || !$tgl_potongan || !$nominal || !$jenis_potongan) {
            echo json_encode(['status' => 'error', 'msg' => 'Field wajib diisi']);
            exit;
        }

        $stmt = $koneksi->prepare("INSERT INTO tb_potongan_tidak_tetap (id_karyawan, tgl_potongan, jenis_potongan, nominal_potongan, ket_potongan) VALUES (?, ?, ?, ?, ?)");
        try {
            echo $stmt->execute([$id_karyawan, $tgl_potongan, $jenis_potongan, $nominal, $ket]) ? json_encode(['status' => 'success']) : json_encode(['status' => 'error', 'msg' => 'Gagal menyimpan data.']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['status' => 'error', 'msg' => 'Gagal menyimpan data.']);
        }
        break;

    case 'edit':
        $id = $_POST['id_potongan_tidak_tetap'] ?? '';
        $id_karyawan = $_POST['id_karyawan'] ?? '';
        $tgl_potongan = $_POST['tgl_potongan'] ?? '';
        $jenis_potongan = $_POST['jenis_potongan'] ?? '';
        $nominal = isset($_POST['nominal_potongan']) ? floatval(str_replace('.', '', $_POST['nominal_potongan'])) : 0;
        $ket = $_POST['ket_potongan'] ?? '';

        if (!$id || !$id_karyawan || !$tgl_potongan || !$nominal || !$jenis_potongan) {
            echo json_encode(['status' => 'error', 'msg' => 'Field wajib diisi']);
            exit;
        }

        $stmt = $koneksi->prepare("UPDATE tb_potongan_tidak_tetap SET id_karyawan=?, tgl_potongan=?, jenis_potongan=?, nominal_potongan=?, ket_potongan=? WHERE id_potongan_tidak_tetap=?");
        try {
            echo $stmt->execute([$id_karyawan, $tgl_potongan, $jenis_potongan, $nominal, $ket, $id]) ? json_encode(['status' => 'success']) : json_encode(['status' => 'error', 'msg' => 'Gagal memperbarui data.']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['status' => 'error', 'msg' => 'Gagal memperbarui data.']);
        }
        break;

    case 'delete':
        $id = $_POST['id_potongan_tidak_tetap'] ?? '';
        if (!$id) {
            echo json_encode(['status' => 'error', 'msg' => 'ID tidak ditemukan']);
            exit;
        }

        // Hapus potongan langsung tanpa validasi relasi pengajuan
        $stmt = $koneksi->prepare("DELETE FROM tb_potongan_tidak_tetap WHERE id_potongan_tidak_tetap=?");
        try {
            echo $stmt->execute([$id]) ? json_encode(['status' => 'success']) : json_encode(['status' => 'error', 'msg' => 'Gagal menghapus data.']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['status' => 'error', 'msg' => 'Gagal menghapus data.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Action tidak valid']);
        break;
}
?>
