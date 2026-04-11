<?php
// ============================================
// DEBUGGING MODE - TAMPILKAN SEMUA ERROR
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// ============================================
// LOG FILE untuk debugging
// ============================================
$log_file = __DIR__ . '/debug_pinjaman.log';
$log_msg = "\n\n=== " . date('Y-m-d H:i:s') . " ===\n";
$log_msg .= "METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
$log_msg .= "POST DATA:\n" . print_r($_POST, true);
$log_msg .= "FILES DATA:\n" . print_r($_FILES, true);
file_put_contents($log_file, $log_msg, FILE_APPEND);

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

// ============================================
// KONEKSI DATABASE
// ============================================
try {
    if (!file_exists(__DIR__ . '/koneksi-api.php')) {
        sendResponse('error', 'File koneksi-api.php tidak ditemukan', null, 500);
    }
    require_once __DIR__ . '/koneksi-api.php';
    
    if (mysqli_connect_errno()) {
        sendResponse('error', 'Database connection error: ' . mysqli_connect_error(), null, 500);
    }
} catch (Exception $e) {
    sendResponse('error', 'Exception saat load koneksi: ' . $e->getMessage(), null, 500);
}

// ============================================
// AMBIL INPUT TEXT & ESCAPE STRING
// ============================================
$id_karyawan      = isset($_POST['id_karyawan']) ? trim($_POST['id_karyawan']) : '';
$tgl_pengajuan    = isset($_POST['tgl_pengajuan']) ? trim($_POST['tgl_pengajuan']) : '';
$nominal_pinjaman = isset($_POST['nominal_pinjaman']) ? trim($_POST['nominal_pinjaman']) : '';
$alasan_pinjaman  = isset($_POST['alasan_pinjaman']) ? trim($_POST['alasan_pinjaman']) : '';

// Validasi Text
if (empty($id_karyawan) || empty($tgl_pengajuan) || empty($nominal_pinjaman) || empty($alasan_pinjaman)) {
    sendResponse('error', 'Semua form teks wajib diisi', null, 400);
}
if ($nominal_pinjaman > 1000000) {
    sendResponse('error', 'Nominal pinjaman melebihi batas maksimal Rp 1.000.000', null, 400);
}

// ESCAPE STRING
$id_karyawan_safe      = mysqli_real_escape_string($koneksi, $id_karyawan);
$tgl_pengajuan_safe    = mysqli_real_escape_string($koneksi, $tgl_pengajuan);
$nominal_pinjaman_safe = mysqli_real_escape_string($koneksi, $nominal_pinjaman);
$alasan_pinjaman_safe  = mysqli_real_escape_string($koneksi, $alasan_pinjaman);

// AMBIL NIK KARYAWAN UNTUK NAMA FILE
$nik_karyawan = 'UNKNOWN';
$query_nik = mysqli_query($koneksi, "SELECT nik_karyawan FROM tb_karyawan WHERE id_karyawan = '$id_karyawan_safe'");
if ($row_nik = mysqli_fetch_assoc($query_nik)) {
    $nik_karyawan = $row_nik['nik_karyawan'];
}

// Format Tanggal untuk nama file (Contoh: 20260411)
$tgl_format_file = date('Ymd', strtotime($tgl_pengajuan_safe));

// Cek Duplikat
$check_sql = "SELECT id_pengajuan_pinjaman FROM tb_pengajuan_pinjaman 
              WHERE id_karyawan = '$id_karyawan_safe' AND DATE(tgl_pengajuan) = '$tgl_pengajuan_safe'";
$check_result = mysqli_query($koneksi, $check_sql);
if (mysqli_num_rows($check_result) > 0) {
    sendResponse('error', 'Anda sudah mengajukan pinjaman hari ini. Tunggu proses sebelumnya selesai.', null, 400);
}

// ============================================
// VALIDASI & FUNGSI UPLOAD FILE
// ============================================
if (!isset($_FILES['foto_wajah']) || $_FILES['foto_wajah']['error'] === UPLOAD_ERR_NO_FILE) {
    sendResponse('error', 'Foto Wajah (Selfie) wajib diupload.', null, 400);
}
if (!isset($_FILES['foto_id_card']) || $_FILES['foto_id_card']['error'] === UPLOAD_ERR_NO_FILE) {
    sendResponse('error', 'Foto ID Card / KTP wajib diupload.', null, 400);
}

function uploadFile($file_input_name, $prefix, $nik, $tgl_format) {
    global $log_file;
    $file_tmp = $_FILES[$file_input_name]['tmp_name'];
    $file_name_original = $_FILES[$file_input_name]['name'];
    $file_size = $_FILES[$file_input_name]['size'];
    
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $file_ext = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        sendResponse('error', "Format $prefix tidak didukung. Gunakan JPG/PNG/WEBP.", null, 400);
    }
    if ($file_size > 5 * 1024 * 1024) {
        sendResponse('error', "Ukuran foto $prefix melebihi 5MB.", null, 400);
    }
    
    $upload_dir = dirname(__DIR__) . '/uploads/pinjaman/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
    
    // FORMAT NAMA FILE BARU: wajah_123456789_20260411_1623456789.jpg
    // time() ditambahkan di akhir untuk mencegah bentrok nama file jika mengupload 2x di hari yg sama
    $file_name = $prefix . '_' . $nik . '_' . $tgl_format . '_' . time() . '.' . $file_ext;
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($file_tmp, $target_path)) {
        return 'uploads/pinjaman/' . $file_name;
    } else {
        sendResponse('error', "Gagal menyimpan foto $prefix ke server.", null, 500);
    }
}

// Proses Upload Kedua File (Kirim NIK dan Tanggal)
$path_wajah = uploadFile('foto_wajah', 'wajah', $nik_karyawan, $tgl_format_file);
$path_id_card = uploadFile('foto_id_card', 'idcard', $nik_karyawan, $tgl_format_file);

// ============================================
// INSERT KE DATABASE
// ============================================
$waktu_saat_ini = date('H:i:s');
$tgl_pinjaman_timestamp = $tgl_pengajuan_safe . ' ' . $waktu_saat_ini;
$status_awal = 'pending';

$sql = "INSERT INTO tb_pengajuan_pinjaman 
        (id_karyawan, tgl_pengajuan, nominal_pinjaman, alasan_pinjaman, foto_wajah, foto_id_card, status_pinjaman)
        VALUES (
            '$id_karyawan_safe',
            '$tgl_pinjaman_timestamp', 
            '$nominal_pinjaman_safe',
            '$alasan_pinjaman_safe',
            '" . mysqli_real_escape_string($koneksi, $path_wajah) . "',
            '" . mysqli_real_escape_string($koneksi, $path_id_card) . "',
            '$status_awal'
        )";

if (mysqli_query($koneksi, $sql)) {
    sendResponse('success', 'Pengajuan pinjaman berhasil disubmit!', [
        'id_pengajuan' => mysqli_insert_id($koneksi),
        'nominal' => $nominal_pinjaman
    ]);
} else {
    sendResponse('error', 'Gagal insert database: ' . mysqli_error($koneksi), null, 500);
}

mysqli_close($koneksi);
?>