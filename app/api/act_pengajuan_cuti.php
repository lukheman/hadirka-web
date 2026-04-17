<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$log_file = __DIR__ . '/debug_cuti.log';
file_put_contents($log_file, "\n\n=== " . date('Y-m-d H:i:s') . " ===\nPOST: " . print_r($_POST, true), FILE_APPEND);

function sendResponse($status, $message, $data = null, $code = 200) {
    $response = ['status' => $status, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    http_response_code($code);
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Hanya menerima POST request', null, 405);
}

require_once __DIR__ . '/koneksi-api.php';
if (mysqli_connect_errno()) {
    sendResponse('error', 'Database error', null, 500);
}

function uploadBuktiCuti($file_input_name, $nik, $tgl_format) {
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] === UPLOAD_ERR_NO_FILE) {
        return NULL; // Bukti cuti bersifat opsional
    }

    $file_tmp = $_FILES[$file_input_name]['tmp_name'];
    $file_name_original = $_FILES[$file_input_name]['name'];
    $file_size = $_FILES[$file_input_name]['size'];
    
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $file_ext = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) sendResponse('error', "Format file tidak didukung.", null, 400);
    if ($file_size > 5 * 1024 * 1024) sendResponse('error', "Ukuran file melebihi 5MB.", null, 400);
    
    $upload_dir = dirname(__DIR__) . '/uploads/cuti/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
    
    $file_name = 'cuti_' . $nik . '_' . $tgl_format . '_' . time() . '.' . $file_ext;
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($file_tmp, $target_path)) {
        return 'uploads/cuti/' . $file_name;
    } else {
        sendResponse('error', "Gagal menyimpan file.", null, 500);
    }
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

switch ($action) {
    case 'ajukan':
        $id_karyawan = trim($_POST['id_karyawan'] ?? '');
        $tgl_awal    = trim($_POST['tgl_awal'] ?? '');
        $tgl_akhir   = trim($_POST['tgl_akhir'] ?? '');
        $lama_cuti   = (int)trim($_POST['lama_cuti'] ?? 0);
        $alasan_cuti = trim($_POST['alasan_cuti'] ?? '');

        if (empty($id_karyawan) || empty($tgl_awal) || empty($tgl_akhir) || empty($alasan_cuti) || $lama_cuti <= 0) {
            sendResponse('error', 'Semua form wajib diisi dengan benar', null, 400);
        }

        $id_karyawan_safe = mysqli_real_escape_string($koneksi, $id_karyawan);
        $tahun_cuti = date('Y', strtotime($tgl_awal));

        // 1. CEK SISA KUOTA SEBELUM MENGAJUKAN
        $q_jatah = mysqli_query($koneksi, "SELECT sisa_cuti FROM tb_jatah_cuti WHERE id_karyawan = '$id_karyawan_safe' AND tahun = '$tahun_cuti'");
        $sisa_kuota = 12; // Default jika belum di-generate oleh admin
        if ($row_jatah = mysqli_fetch_assoc($q_jatah)) {
            $sisa_kuota = (int)$row_jatah['sisa_cuti'];
        }

        if ($lama_cuti > $sisa_kuota) {
            sendResponse('error', "Maaf, sisa kuota cuti Anda tahun $tahun_cuti tinggal $sisa_kuota hari. Pengajuan $lama_cuti hari ditolak.", null, 400);
        }

        // 2. LANJUTKAN PROSES JIKA KUOTA CUKUP
        $q_kar = mysqli_query($koneksi, "SELECT nik_karyawan FROM tb_karyawan WHERE id_karyawan = '$id_karyawan_safe'");
        $nik_karyawan = ($r = mysqli_fetch_assoc($q_kar)) ? $r['nik_karyawan'] : 'UNKNOWN';

        $bukti_cuti = uploadBuktiCuti('bukti_cuti', $nik_karyawan, date('Ymd', strtotime($tgl_awal)));
        $bukti_val = $bukti_cuti ? "'$bukti_cuti'" : "NULL";

        $sql = "INSERT INTO tb_pengajuan_cuti (id_karyawan, tgl_awal, tgl_akhir, lama_cuti, alasan_cuti, bukti_cuti, status_cuti)
                VALUES ('$id_karyawan_safe', '$tgl_awal', '$tgl_akhir', $lama_cuti, '" . mysqli_real_escape_string($koneksi, $alasan_cuti) . "', $bukti_val, 'pending')";

        if (mysqli_query($koneksi, $sql)) {
            sendResponse('success', 'Pengajuan cuti berhasil disubmit dan menunggu persetujuan HRD!');
        } else {
            sendResponse('error', 'Gagal menyimpan ke database.', null, 500);
        }
        break;

    case 'batalkan':
        $id_pengajuan = mysqli_real_escape_string($koneksi, $_POST['id_pengajuan'] ?? '');
        $cek_sql = "SELECT status_cuti, bukti_cuti FROM tb_pengajuan_cuti WHERE id_pengajuan_cuti = '$id_pengajuan'";
        $cek_result = mysqli_query($koneksi, $cek_sql);

        if ($row = mysqli_fetch_assoc($cek_result)) {
            if ($row['status_cuti'] !== 'pending') {
                sendResponse('error', 'Gagal. Cuti sudah diproses Admin.', null, 400);
            }

            $base_dir = dirname(__DIR__) . '/';
            if (!empty($row['bukti_cuti']) && file_exists($base_dir . $row['bukti_cuti'])) {
                unlink($base_dir . $row['bukti_cuti']);
            }

            if (mysqli_query($koneksi, "DELETE FROM tb_pengajuan_cuti WHERE id_pengajuan_cuti = '$id_pengajuan'")) {
                sendResponse('success', 'Pengajuan cuti berhasil dibatalkan.');
            }
        }
        sendResponse('error', 'Gagal menghapus data.', null, 400);
        break;

    default:
        sendResponse('error', 'Action tidak valid', null, 400);
        break;
}
?>