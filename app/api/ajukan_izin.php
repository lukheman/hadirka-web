<?php
// ============================================
// DEBUGGING MODE - TAMPILKAN SEMUA ERROR
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set header dulu sebelum output apapun
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// ============================================
// LOG FILE untuk debugging
// ============================================
$log_file = __DIR__ . '/debug_izin.log';
$log_msg = "\n\n=== " . date('Y-m-d H:i:s') . " ===\n";
$log_msg .= "METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
$log_msg .= "POST DATA:\n" . print_r($_POST, true);
$log_msg .= "FILES DATA:\n" . print_r($_FILES, true);
file_put_contents($log_file, $log_msg, FILE_APPEND);

// ============================================
// FUNCTION HELPER
// ============================================
function sendResponse($status, $message, $data = null, $code = 200) {
    global $log_file;
    
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    // Log response
    file_put_contents($log_file, "RESPONSE: " . json_encode($response) . "\n", FILE_APPEND);
    
    http_response_code($code);
    echo json_encode($response);
    exit;
}

// ============================================
// VALIDASI METHOD
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Hanya menerima POST request', null, 405);
}

// ============================================
// TEST KONEKSI DATABASE
// ============================================
try {
    file_put_contents($log_file, "Step 1: Loading koneksi-api.php\n", FILE_APPEND);
    
    if (!file_exists(__DIR__ . '/koneksi-api.php')) {
        sendResponse('error', 'File koneksi-api.php tidak ditemukan', null, 500);
    }
    
    require_once __DIR__ . '/koneksi-api.php';
    
    file_put_contents($log_file, "Step 2: koneksi-api.php loaded\n", FILE_APPEND);
    
    // Cek koneksi
    if (!isset($koneksi)) {
        sendResponse('error', 'Variable $koneksi tidak terdefinisi di koneksi-api.php', null, 500);
    }
    
    if (mysqli_connect_errno()) {
        sendResponse('error', 'Database connection error: ' . mysqli_connect_error(), null, 500);
    }
    
    file_put_contents($log_file, "Step 3: Database connected successfully\n", FILE_APPEND);
    
} catch (Exception $e) {
    sendResponse('error', 'Exception saat load koneksi: ' . $e->getMessage(), null, 500);
}

// ============================================
// AMBIL INPUT
// ============================================
$id_karyawan = isset($_POST['id_karyawan']) ? trim($_POST['id_karyawan']) : '';
$tgl_izin    = isset($_POST['tgl_izin']) ? trim($_POST['tgl_izin']) : '';
$alasan_izin = isset($_POST['alasan_izin']) ? trim($_POST['alasan_izin']) : '';

file_put_contents($log_file, "Step 4: Input parsed - ID:$id_karyawan, Tgl:$tgl_izin\n", FILE_APPEND);

// ============================================
// VALIDASI INPUT
// ============================================
if (empty($id_karyawan)) {
    sendResponse('error', 'ID Karyawan kosong', null, 400);
}

if (empty($tgl_izin)) {
    sendResponse('error', 'Tanggal izin kosong', null, 400);
}

if (empty($alasan_izin)) {
    sendResponse('error', 'Alasan izin kosong', null, 400);
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_izin)) {
    sendResponse('error', 'Format tanggal salah: ' . $tgl_izin, null, 400);
}

// ============================================
// ESCAPE STRING untuk SQL
// ============================================
$id_karyawan_safe = mysqli_real_escape_string($koneksi, $id_karyawan);
$tgl_izin_safe    = mysqli_real_escape_string($koneksi, $tgl_izin);
$alasan_izin_safe = mysqli_real_escape_string($koneksi, $alasan_izin);

file_put_contents($log_file, "Step 5: Input escaped for SQL\n", FILE_APPEND);

// ============================================
// CEK DUPLIKAT
// ============================================
$check_sql = "SELECT id_pengajuan_izin 
              FROM tb_pengajuan_izin 
              WHERE id_karyawan = '$id_karyawan_safe' 
              AND DATE(tgl_izin) = '$tgl_izin_safe'";

file_put_contents($log_file, "Step 6: Check duplicate SQL: $check_sql\n", FILE_APPEND);

$check_result = mysqli_query($koneksi, $check_sql);

if (!$check_result) {
    sendResponse('error', 'Query check duplicate error: ' . mysqli_error($koneksi), null, 500);
}

if (mysqli_num_rows($check_result) > 0) {
    sendResponse('error', 'Anda sudah mengajukan izin pada tanggal ' . $tgl_izin, null, 400);
}

file_put_contents($log_file, "Step 7: No duplicate found\n", FILE_APPEND);

// ============================================
// PROSES UPLOAD FILE
// ============================================
$bukti_izin = null;

if (isset($_FILES['bukti_izin']) && $_FILES['bukti_izin']['error'] === UPLOAD_ERR_OK) {
    
    file_put_contents($log_file, "Step 8: Processing file upload\n", FILE_APPEND);
    
    $file_tmp = $_FILES['bukti_izin']['tmp_name'];
    $file_name_original = $_FILES['bukti_izin']['name'];
    $file_size = $_FILES['bukti_izin']['size'];
    
    // Validasi ekstensi
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $file_ext = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        sendResponse('error', 'Format file tidak didukung: ' . $file_ext, null, 400);
    }
    
    if ($file_size > 5 * 1024 * 1024) {
        sendResponse('error', 'Ukuran file terlalu besar: ' . round($file_size/1024/1024, 2) . 'MB', null, 400);
    }
    
    // Lokasi upload - SESUAIKAN DENGAN STRUKTUR FOLDER ANDA
    $upload_dir = dirname(__DIR__) . '/uploads/izin/';
    
    file_put_contents($log_file, "Step 9: Upload dir: $upload_dir\n", FILE_APPEND);
    
    // Buat folder jika belum ada
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            sendResponse('error', 'Gagal membuat folder upload', null, 500);
        }
        file_put_contents($log_file, "Step 10: Upload dir created\n", FILE_APPEND);
    }
    
    // Cek permission
    if (!is_writable($upload_dir)) {
        sendResponse('error', 'Folder upload tidak writable. Path: ' . $upload_dir, null, 500);
    }
    
    // Generate nama file
    $file_name = 'izin_' . $id_karyawan . '_' . time() . '.' . $file_ext;
    $target_path = $upload_dir . $file_name;
    
    file_put_contents($log_file, "Step 11: Target path: $target_path\n", FILE_APPEND);
    
    // Upload file
    if (move_uploaded_file($file_tmp, $target_path)) {
        $bukti_izin = 'uploads/izin/' . $file_name;
        file_put_contents($log_file, "Step 12: File uploaded successfully: $bukti_izin\n", FILE_APPEND);
    } else {
        sendResponse('error', 'Gagal move_uploaded_file. Check permission.', null, 500);
    }
    
} elseif (isset($_FILES['bukti_izin']) && $_FILES['bukti_izin']['error'] !== UPLOAD_ERR_NO_FILE) {
    // Error upload
    $error_msgs = [
        UPLOAD_ERR_INI_SIZE => 'File > upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File > MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File ter-upload sebagian',
        UPLOAD_ERR_NO_TMP_DIR => 'Tmp dir tidak ada',
        UPLOAD_ERR_CANT_WRITE => 'Gagal write ke disk',
        UPLOAD_ERR_EXTENSION => 'Extension stopped upload'
    ];
    
    $err_code = $_FILES['bukti_izin']['error'];
    $err_msg = isset($error_msgs[$err_code]) ? $error_msgs[$err_code] : 'Unknown error';
    sendResponse('error', 'Upload error: ' . $err_msg . ' (Code: ' . $err_code . ')', null, 400);
}

file_put_contents($log_file, "Step 13: File processing done. Bukti: " . ($bukti_izin ?: 'NULL') . "\n", FILE_APPEND);

// ============================================
// PREPARE INSERT DATA
// ============================================
$waktu_saat_ini = date('H:i:s');
$tgl_izin_timestamp = $tgl_izin_safe . ' ' . $waktu_saat_ini;
$status_awal = 'pending';

// ============================================
// INSERT KE DATABASE (TANPA tgl_pengajuan)
// ============================================
$sql = "INSERT INTO tb_pengajuan_izin 
        (id_karyawan, tgl_izin, alasan_izin, bukti_izin, status_izin)
        VALUES (
            '$id_karyawan_safe',
            '$tgl_izin_timestamp', 
            '$alasan_izin_safe',
            " . ($bukti_izin ? "'" . mysqli_real_escape_string($koneksi, $bukti_izin) . "'" : "NULL") . ",
            '$status_awal'
        )";

file_put_contents($log_file, "Step 14: SQL Insert: $sql\n", FILE_APPEND);

if (mysqli_query($koneksi, $sql)) {
    $insert_id = mysqli_insert_id($koneksi);
    file_put_contents($log_file, "Step 15: Insert SUCCESS. ID: $insert_id\n", FILE_APPEND);
    
    sendResponse('success', 'Pengajuan izin berhasil disubmit!', [
        'id_pengajuan' => $insert_id,
        'id_karyawan' => $id_karyawan,
        'tgl_izin' => $tgl_izin,
        'status' => $status_awal
    ]);
} else {
    $error = mysqli_error($koneksi);
    file_put_contents($log_file, "Step 15: Insert FAILED: $error\n", FILE_APPEND);
    sendResponse('error', 'Gagal insert: ' . $error, null, 500);
}

mysqli_close($koneksi);