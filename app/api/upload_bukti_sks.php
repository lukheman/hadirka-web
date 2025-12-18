<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'koneksi-api.php'; // Hubungkan ke database

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ]);
    exit;
}

// ==============================================
// AMBIL & SANITASI INPUT
// ==============================================
$id_karyawan = mysqli_real_escape_string($koneksi, $_POST['id_karyawan'] ?? '');
$tgl_sks     = mysqli_real_escape_string($koneksi, $_POST['tgl_sks'] ?? ''); // Format: YYYY-MM-DD

// Validasi input wajib
if (empty($id_karyawan) || empty($tgl_sks)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID Karyawan dan Tanggal SKS harus diisi.'
    ]);
    exit;
}

// ===============================================
// 🚨 VALIDASI DUPLIKAT BERDASARKAN TGL_SKS
// ===============================================
$check_sql = "SELECT id_bukti_sks 
              FROM tb_bukti_sks 
              WHERE id_karyawan = '$id_karyawan' 
              AND DATE(tgl_sks) = '$tgl_sks'";

$check_result = mysqli_query($koneksi, $check_sql);

if (!$check_result) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal melakukan pengecekan duplikat SKS.']);
    mysqli_close($koneksi);
    exit;
}

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal: Anda sudah mengunggah Bukti SKS/Sakit untuk tanggal ' . $tgl_sks . '.'
    ]);
    mysqli_close($koneksi);
    exit;
}

// ==============================================
// UPLOAD BUKTI SKS (FILE WAJIB)
// ==============================================
$bukti_sks_url = null;

if (!isset($_FILES['bukti_sks']) || $_FILES['bukti_sks']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'File bukti SKS tidak ditemukan atau gagal diunggah (Wajib).']);
    exit;
}

$file_tmp = $_FILES['bukti_sks']['tmp_name'];
$file_name_original = $_FILES['bukti_sks']['name'];
$file_size = $_FILES['bukti_sks']['size'];

// Validasi Ekstensi & Ukuran (Max 5MB)
$allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
$file_ext  = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed_ext)) {
    echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung. Harap gunakan gambar.']);
    exit;
}
if ($file_size > 5 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'Ukuran file maksimal 5MB.']);
    exit;
}

// Lokasi Upload
$upload_dir = dirname(__DIR__) . '/uploads/sks/';
if (!file_exists($upload_dir)) { mkdir($upload_dir, 0755, true); }

// Proses Pindah File
$file_name = 'sks_' . $id_karyawan . '_' . time() . '.' . $file_ext;
$target_path = $upload_dir . $file_name;

if (move_uploaded_file($file_tmp, $target_path)) {
    $bukti_sks_url = '/uploads/sks/' . $file_name;
    
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file ke server.']);
    exit;
}

// ==============================================
// PREPARE DATA FOR INSERT
// ==============================================
$tgl_kirim_timestamp = date('Y-m-d H:i:s'); // TIMESTAMP Otomatis server
$tgl_sks_timestamp = $tgl_sks . ' 00:00:00'; // Tanggal SKS dari input
$status_awal = 'pending';
$tgl_diterima_value = "NULL"; 

// ==============================================
// INSERT DATABASE
// ==============================================
$sql = "INSERT INTO tb_bukti_sks 
        (id_karyawan, tgl_sks, bukti_sks, tgl_kirim, tgl_diterima, status)
        VALUES (
            '$id_karyawan',
            '$tgl_sks_timestamp',
            '$bukti_sks_url',
            '$tgl_kirim_timestamp',
            $tgl_diterima_value,
            '$status_awal'
        )";

if (mysqli_query($koneksi, $sql)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Bukti SKS berhasil diunggah dan diajukan.',
        'id_bukti_sks' => mysqli_insert_id($koneksi)
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan data ke database: ' . mysqli_error($koneksi)
    ]);
}

mysqli_close($koneksi);
?>