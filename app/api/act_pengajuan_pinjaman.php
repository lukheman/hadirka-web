<?php
// ============================================
// DEBUGGING MODE & HEADERS
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
if (mysqli_connect_errno()) {
    sendResponse('error', 'Database error: ' . mysqli_connect_error(), null, 500);
}

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

        $id_karyawan_safe      = mysqli_real_escape_string($koneksi, $id_karyawan);
        $tgl_pengajuan_safe    = mysqli_real_escape_string($koneksi, $tgl_pengajuan);
        $nominal_pinjaman_safe = mysqli_real_escape_string($koneksi, $nominal_pinjaman);
        $alasan_pinjaman_safe  = mysqli_real_escape_string($koneksi, $alasan_pinjaman);

        // Ambil NIK
        $nik_karyawan = 'UNKNOWN';
        $query_nik = mysqli_query($koneksi, "SELECT nik_karyawan FROM tb_karyawan WHERE id_karyawan = '$id_karyawan_safe'");
        if ($row_nik = mysqli_fetch_assoc($query_nik)) $nik_karyawan = $row_nik['nik_karyawan'];

        $tgl_format_file = date('Ymd', strtotime($tgl_pengajuan_safe));

        // Cek Duplikat Hari Ini
        $check_sql = "SELECT id_pengajuan_pinjaman FROM tb_pengajuan_pinjaman WHERE id_karyawan = '$id_karyawan_safe' AND DATE(tgl_pengajuan) = '$tgl_pengajuan_safe'";
        if (mysqli_num_rows(mysqli_query($koneksi, $check_sql)) > 0) {
            sendResponse('error', 'Anda sudah mengajukan pinjaman hari ini.', null, 400);
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
        $tgl_pinjaman_timestamp = $tgl_pengajuan_safe . ' ' . date('H:i:s');
        $sql = "INSERT INTO tb_pengajuan_pinjaman (id_karyawan, tgl_pengajuan, nominal_pinjaman, alasan_pinjaman, foto_wajah, foto_id_card, status_pinjaman)
                VALUES ('$id_karyawan_safe', '$tgl_pinjaman_timestamp', '$nominal_pinjaman_safe', '$alasan_pinjaman_safe', '$path_wajah', '$path_id_card', 'pending')";

        if (mysqli_query($koneksi, $sql)) sendResponse('success', 'Pengajuan pinjaman berhasil disubmit!');
        else sendResponse('error', 'Gagal insert database', null, 500);
        break;

    // ---------------------------------------------------------
    // AKSI 2: BATALKAN / HAPUS PENGAJUAN
    // ---------------------------------------------------------
    case 'batalkan':
        $id_pengajuan = mysqli_real_escape_string($koneksi, $_POST['id_pengajuan'] ?? '');
        if (empty($id_pengajuan)) sendResponse('error', 'ID Pengajuan tidak ditemukan', null, 400);

        $cek_sql = "SELECT status_pinjaman, foto_wajah, foto_id_card FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = '$id_pengajuan'";
        $cek_result = mysqli_query($koneksi, $cek_sql);

        if ($row = mysqli_fetch_assoc($cek_result)) {
            if ($row['status_pinjaman'] !== 'pending') {
                sendResponse('error', 'Gagal dibatalkan. Pengajuan sudah diproses oleh Admin.', null, 400);
            }

            // Hapus file fisik
            $base_dir = dirname(__DIR__) . '/';
            if (!empty($row['foto_wajah']) && file_exists($base_dir . $row['foto_wajah'])) unlink($base_dir . $row['foto_wajah']);
            if (!empty($row['foto_id_card']) && file_exists($base_dir . $row['foto_id_card'])) unlink($base_dir . $row['foto_id_card']);

            // Hapus dari database
            if (mysqli_query($koneksi, "DELETE FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = '$id_pengajuan'")) {
                sendResponse('success', 'Pengajuan berhasil dibatalkan dan dihapus.');
            } else {
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

mysqli_close($koneksi);
?>