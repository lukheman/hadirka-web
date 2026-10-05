<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__.'/koneksi-api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan. Gunakan POST.']);
    exit;
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

switch ($action) {

    // ==============================================
    // AKSI 1: AJUKAN SKS
    // ==============================================
    case 'ajukan':
        $id_karyawan = $_POST['id_karyawan'] ?? '';
        $tgl_sks     = $_POST['tgl_sks'] ?? '';
        $ket_sks     = $_POST['ket_sks'] ?? ''; // Tangkap ini

        if (empty($id_karyawan) || empty($tgl_sks)) {
            echo json_encode(['status' => 'error', 'message' => 'ID Karyawan dan Tanggal SKS harus diisi.']);
            exit;
        }

        // Cek Duplikat
        try {
            $st_check = $koneksi->prepare("SELECT id_bukti_sks FROM tb_bukti_sks WHERE id_karyawan = ? AND DATE(tgl_sks) = ? LIMIT 1");
            $st_check->execute([$id_karyawan, $tgl_sks]);
            $dup = $st_check->fetch();

            if ($dup) {
                echo json_encode(['status' => 'error', 'message' => 'Anda sudah mengunggah Bukti SKS/Sakit untuk tanggal ' . $tgl_sks . '.']);
                exit;
            }
        } catch (PDOException $e) {
            error_log('act_bukti_sks ajukan cek duplikat gagal: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database.']);
            exit;
        }

        // Proses File
        if (!isset($_FILES['bukti_sks']) || $_FILES['bukti_sks']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'File bukti SKS wajib diunggah.']);
            exit;
        }

        $file_tmp = $_FILES['bukti_sks']['tmp_name'];
        $file_name_original = $_FILES['bukti_sks']['name'];
        $file_size = $_FILES['bukti_sks']['size'];

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
        $file_ext  = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) {
            echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung.']);
            exit;
        }
        if ($file_size > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'Ukuran file maksimal 5MB.']);
            exit;
        }

        $upload_dir = dirname(__DIR__) . '/uploads/sks/';
        if (!file_exists($upload_dir)) { mkdir($upload_dir, 0755, true); }

        $file_name = 'sks_' . $id_karyawan . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_path)) {
            $bukti_sks_url = '/uploads/sks/' . $file_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file ke server.']);
            exit;
        }

        // Insert ke DB
        $tgl_kirim_timestamp = date('Y-m-d H:i:s'); 
        $tgl_sks_timestamp = $tgl_sks . ' 00:00:00'; 

        try {
            $st = $koneksi->prepare("INSERT INTO tb_bukti_sks (id_karyawan, tgl_sks, bukti_sks, tgl_kirim, tgl_diterima, status, ket_sks)
                    VALUES (?, ?, ?, ?, NULL, 'pending', ?)");
            $st->execute([$id_karyawan, $tgl_sks_timestamp, $bukti_sks_url, $tgl_kirim_timestamp, $ket_sks]);
            echo json_encode(['status' => 'success', 'message' => 'Bukti SKS berhasil diunggah dan diajukan.']);
        } catch (PDOException $e) {
            error_log('act_bukti_sks ajukan insert gagal: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database.']);
        }
        break;

    // ==============================================
    // AKSI 2: BATALKAN SKS
    // ==============================================
    case 'batalkan':
        $id_bukti_sks = $_POST['id_bukti_sks'] ?? '';

        if (empty($id_bukti_sks)) {
            echo json_encode(['status' => 'error', 'message' => 'ID SKS tidak valid.']);
            exit;
        }

        try {
            $st_cek = $koneksi->prepare("SELECT status, bukti_sks FROM tb_bukti_sks WHERE id_bukti_sks = ?");
            $st_cek->execute([$id_bukti_sks]);
            $row = $st_cek->fetch();
        } catch (PDOException $e) {
            error_log('act_bukti_sks batalkan cek gagal: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database.']);
            exit;
        }

        if ($row) {
            if (strtolower($row['status']) !== 'pending') {
                echo json_encode(['status' => 'error', 'message' => 'Gagal dibatalkan. Pengajuan SKS sudah diproses admin.']);
                exit;
            }

            // Hapus file fisik
            $base_dir = dirname(__DIR__); 
            if (!empty($row['bukti_sks']) && file_exists($base_dir . $row['bukti_sks'])) {
                unlink($base_dir . $row['bukti_sks']);
            }

            // Hapus record dari database
            try {
                $st_del = $koneksi->prepare("DELETE FROM tb_bukti_sks WHERE id_bukti_sks = ?");
                $st_del->execute([$id_bukti_sks]);
                echo json_encode(['status' => 'success', 'message' => 'Pengajuan SKS berhasil dibatalkan.']);
            } catch (PDOException $e) {
                error_log('act_bukti_sks batalkan hapus gagal: ' . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data di database.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data pengajuan tidak ditemukan.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid.']);
        break;
}
?>
