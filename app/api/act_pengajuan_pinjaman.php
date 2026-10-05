<?php
// ============================================
// DEBUGGING MODE & HEADERS
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ubah 1 menjadi 0 untuk Production
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$log_file = __DIR__ . '/debug_pinjaman.log';
file_put_contents($log_file, "\n\n=== " . date('Y-m-d H:i:s') . " ===\nPOST: " . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true), FILE_APPEND);

function sendResponse($status, $message, $data = null, $code = 200) {
    global $log_file;
    $response = ['status' => $status, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    file_put_contents($log_file, "RESPONSE: " . json_encode($response) . "\n", FILE_APPEND);
    http_response_code($code);
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Hanya menerima POST request', null, 405);
}

// KONEKSI DATABASE
require_once __DIR__ . '/koneksi-api.php';

// ============================================
// ROUTING BERDASARKAN ACTION
// ============================================
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

function uploadFile($file_input_name, $prefix, $nik, $tgl_format) {
            $file_tmp = $_FILES[$file_input_name]['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp'])) sendResponse('error', "Format $prefix tidak didukung.", null, 400);
            if ($_FILES[$file_input_name]['size'] > 5 * 1024 * 1024) sendResponse('error', "Ukuran foto $prefix melebihi 5MB.", null, 400);
            
            $upload_dir = dirname(__DIR__) . '/uploads/pinjaman/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $file_name = $prefix . '_' . $nik . '_' . $tgl_format . '_' . time() . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) return 'uploads/pinjaman/' . $file_name;
            sendResponse('error', "Gagal menyimpan foto $prefix.", null, 500);
        }

switch ($action) {
    
    // ---------------------------------------------------------
    // AKSI 1: AJUKAN PINJAMAN BARU
    // ---------------------------------------------------------
    case 'ajukan':
        // --- NEW: CEK APAKAH AKSES PENGAJUAN SEDANG DIBUKA/DITUTUP ---
        try {
            $q_status = $koneksi->query("SELECT nilai FROM tb_pengaturan WHERE nama_pengaturan = 'buka_pinjaman'");
            $row_status = $q_status->fetch();
        } catch (PDOException $e) {
            error_log('act_pengajuan_pinjaman cek status buka gagal: ' . $e->getMessage());
            sendResponse('error', 'Terjadi kesalahan database.', null, 500);
        }
        $is_open = $row_status ? $row_status['nilai'] : '1';

        if ($is_open == '0') {
            sendResponse('error', 'Maaf, pengajuan pinjaman saat ini sedang ditutup atau belum dibuka. Silakan cek kembali nanti.', null, 400);
        }

        $id_karyawan      = isset($_POST['id_karyawan']) ? trim($_POST['id_karyawan']) : '';
        $tgl_pengajuan    = isset($_POST['tgl_pengajuan']) ? trim($_POST['tgl_pengajuan']) : '';
        $nominal_pinjaman = isset($_POST['nominal_pinjaman']) ? trim($_POST['nominal_pinjaman']) : '';
        $alasan_pinjaman  = isset($_POST['alasan_pinjaman']) ? trim($_POST['alasan_pinjaman']) : '';

        if (empty($id_karyawan) || empty($tgl_pengajuan) || empty($nominal_pinjaman) || empty($alasan_pinjaman)) {
            sendResponse('error', 'Semua form teks wajib diisi', null, 400);
        }
        if ($nominal_pinjaman > 1000000) {
            sendResponse('error', 'Nominal pinjaman melebihi batas maksimal Rp 1.000.000', null, 400);
        }

        // --- TAMBAHKAN KODE INI UNTUK VALIDASI TANGGAL 10 - 25 ---
        $tanggal_hari_ini = (int)date('d', strtotime($tgl_pengajuan));
        if ($tanggal_hari_ini < 10 || $tanggal_hari_ini > 25) {
            sendResponse('error', 'Pengajuan pinjaman hanya dibuka pada tanggal 10 hingga 25 setiap bulannya.', null, 400);
        }

        try {
            // --- NEW: CEK STATUS KARYAWAN (HANYA AKTIF YANG BISA MENGAJUKAN) ---
            $st_cek_karyawan = $koneksi->prepare("SELECT nik_karyawan, status_karyawan FROM tb_karyawan WHERE id_karyawan = ?");
            $st_cek_karyawan->execute([$id_karyawan]);
            $data_karyawan = $st_cek_karyawan->fetch();

            if (!$data_karyawan) {
                sendResponse('error', 'Data karyawan tidak ditemukan.', null, 404);
            }

            // Jika statusnya bukan 'Aktif', tolak pengajuan
            if (strtolower($data_karyawan['status_karyawan']) !== 'aktif') {
                sendResponse('error', 'Maaf, pengajuan pinjaman hanya diperbolehkan untuk karyawan yang masih AKTIF.', null, 403);
            }

            // Simpan NIK untuk dipakai buat nama file foto nanti
            $nik_karyawan = $data_karyawan['nik_karyawan'];
            // ------------------------------------------------------------------

            // --- NEW: VALIDASI AKUMULASI LIMIT PINJAMAN 1 JUTA PER BULAN ---
            $tahun_target = date('Y', strtotime($tgl_pengajuan));
            $bulan_target = date('m', strtotime($tgl_pengajuan));

            // NEW: Hitung total pinjaman yang sudah diajukan bulan ini (yang pending atau disetujui)
            $st_check_limit = $koneksi->prepare("SELECT SUM(nominal_pinjaman) AS total_terpakai 
                                  FROM tb_pengajuan_pinjaman 
                                  WHERE id_karyawan = ? 
                                    AND status_pinjaman != 'ditolak' 
                                    AND YEAR(tgl_pengajuan) = ? 
                                    AND MONTH(tgl_pengajuan) = ?");
            $st_check_limit->execute([$id_karyawan, $tahun_target, $bulan_target]);
            $row_limit = $st_check_limit->fetch();
            $total_terpakai = (int)($row_limit['total_terpakai'] ?? 0);

            $limit_maksimal = 1000000; // Batas 1 Juta
            $sisa_kuota = $limit_maksimal - $total_terpakai;

            if ($sisa_kuota <= 0) {
                sendResponse('error', 'Batas maksimal pinjaman Anda bulan ini sudah habis (Maks Rp 1.000.000). Sisa kuota Anda: Rp 0', null, 400);
            }

            if ($nominal_pinjaman > $sisa_kuota) {
                sendResponse('error', 'Nominal melebihi sisa kuota bulan ini. Sisa kuota yang bisa Anda ajukan: Rp ' . number_format($sisa_kuota, 0, ',', '.'), null, 400);
            }
            // ----------------------------------------------------------

            $tgl_format_file = date('Ymd', strtotime($tgl_pengajuan));

            // Cek Duplikat Hari Ini
            $st_check = $koneksi->prepare("SELECT 1 FROM tb_pengajuan_pinjaman WHERE id_karyawan = ? AND DATE(tgl_pengajuan) = ? LIMIT 1");
            $st_check->execute([$id_karyawan, $tgl_pengajuan]);
            if ($st_check->fetch()) {
                sendResponse('error', 'Anda sudah mengajukan pinjaman hari ini.', null, 400);
            }
        } catch (PDOException $e) {
            error_log('act_pengajuan_pinjaman ajukan cek gagal: ' . $e->getMessage());
            sendResponse('error', 'Terjadi kesalahan database.', null, 500);
        }

        // Upload Validasi
        if (!isset($_FILES['foto_wajah']) || $_FILES['foto_wajah']['error'] === UPLOAD_ERR_NO_FILE) {
            sendResponse('error', 'Foto Wajah (Selfie) wajib diupload.', null, 400);
        }
        if (!isset($_FILES['foto_id_card']) || $_FILES['foto_id_card']['error'] === UPLOAD_ERR_NO_FILE) {
            sendResponse('error', 'Foto ID Card / KTP wajib diupload.', null, 400);
        }

        

        $path_wajah = uploadFile('foto_wajah', 'wajah', $nik_karyawan, $tgl_format_file);
        $path_id_card = uploadFile('foto_id_card', 'idcard', $nik_karyawan, $tgl_format_file);

        // Insert
        $tgl_pinjaman_timestamp = $tgl_pengajuan . ' ' . date('H:i:s');

        try {
            $st = $koneksi->prepare("INSERT INTO tb_pengajuan_pinjaman (id_karyawan, tgl_pengajuan, nominal_pinjaman, alasan_pinjaman, foto_wajah, foto_id_card, status_pinjaman)
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $st->execute([$id_karyawan, $tgl_pinjaman_timestamp, $nominal_pinjaman, $alasan_pinjaman, $path_wajah, $path_id_card]);
            // Berikan info sisa kuota baru di response sukses
            $sisa_kuota_baru = $sisa_kuota - $nominal_pinjaman;
            sendResponse('success', 'Pengajuan pinjaman berhasil disubmit! Sisa kuota bulan ini: Rp ' . number_format($sisa_kuota_baru, 0, ',', '.'));
        } catch (PDOException $e) {
            error_log('act_pengajuan_pinjaman ajukan insert gagal: ' . $e->getMessage());
            sendResponse('error', 'Gagal insert database', null, 500);
        }
        break;

    // ---------------------------------------------------------
    // AKSI 2: BATALKAN / HAPUS PENGAJUAN
    // ---------------------------------------------------------
    case 'batalkan':
        $id_pengajuan = $_POST['id_pengajuan'] ?? '';
        if (empty($id_pengajuan)) sendResponse('error', 'ID Pengajuan tidak ditemukan', null, 400);

        try {
            $st_cek = $koneksi->prepare("SELECT status_pinjaman, foto_wajah, foto_id_card FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = ?");
            $st_cek->execute([$id_pengajuan]);
            $row = $st_cek->fetch();
        } catch (PDOException $e) {
            error_log('act_pengajuan_pinjaman batalkan cek gagal: ' . $e->getMessage());
            sendResponse('error', 'Terjadi kesalahan database.', null, 500);
        }

        if ($row) {
            if ($row['status_pinjaman'] !== 'pending') {
                sendResponse('error', 'Gagal dibatalkan. Pengajuan sudah diproses oleh Admin.', null, 400);
            }

            // Hapus file fisik
            $base_dir = dirname(__DIR__) . '/';
            if (!empty($row['foto_wajah']) && file_exists($base_dir . $row['foto_wajah'])) unlink($base_dir . $row['foto_wajah']);
            if (!empty($row['foto_id_card']) && file_exists($base_dir . $row['foto_id_card'])) unlink($base_dir . $row['foto_id_card']);

            // Hapus dari database
            try {
                $st_del = $koneksi->prepare("DELETE FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = ?");
                $st_del->execute([$id_pengajuan]);
                sendResponse('success', 'Pengajuan berhasil dibatalkan dan dihapus.');
            } catch (PDOException $e) {
                error_log('act_pengajuan_pinjaman batalkan hapus gagal: ' . $e->getMessage());
                sendResponse('error', 'Gagal menghapus data dari database.', null, 500);
            }
        } else {
            sendResponse('error', 'Data pengajuan tidak ditemukan.', null, 404);
        }
        break;

    // ---------------------------------------------------------
    // DEFAULT
    // ---------------------------------------------------------
    default:
        sendResponse('error', 'Action tidak valid atau tidak ditemukan', null, 400);
        break;
}
?>
