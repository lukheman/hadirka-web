<?php
/**
 * ============================================
 * CSV/EXCEL IMPORT - DATA BPJS TK
 * ============================================
 */

ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Sistem Crash: ' . $error['message'] . ' di baris ' . $error['line']]);
        exit;
    }
});

header("Content-Type: application/json; charset=utf-8");
require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function sendJsonResponse($status, $message, $data = []) {
    ob_clean();
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data));
    exit;
}

if (!isset($koneksi) || !$koneksi) sendJsonResponse('error', 'Koneksi database gagal');
if (!isset($_FILES['file_csv_bpjs'])) sendJsonResponse('error', 'File tidak ditemukan');

$file = $_FILES['file_csv_bpjs'];
if ($file['error'] !== UPLOAD_ERR_OK) sendJsonResponse('error', 'Upload gagal: Kode error ' . $file['error']);

try {
    $spreadsheet = IOFactory::load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);
} catch (Exception $e) {
    sendJsonResponse('error', 'Excel Error: ' . $e->getMessage());
}

if (empty($data) || count($data) < 2) sendJsonResponse('error', 'File kosong atau hanya header');

// SETTING KOLOM EXCEL
// Sesuaikan dengan format Anda: Kolom B = NIK KTP, Kolom C = Nomor BPJS
$colNIK_KTP = 'B'; 
$colBPJS = 'D'; 

$successCount = 0; $errorCount = 0; $updateCount = 0;
$errors = [];

for ($i = 2; $i <= count($data); $i++) {
    if (!isset($data[$i])) continue;
    $row = $data[$i];

    $nik_ktp = isset($row[$colNIK_KTP]) ? trim($row[$colNIK_KTP]) : '';
    $bpjs_tk = isset($row[$colBPJS]) ? trim($row[$colBPJS]) : '';

    if (empty($nik_ktp)) continue; // Lewati baris jika NIK KTP kosong

    // 1. Cek apakah NIK KTP tersebut terdaftar di tb_identitas
    $stmtCheck = $koneksi->prepare("SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = ?");
    $stmtCheck->bind_param("s", $nik_ktp);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    $isExists = $stmtCheck->num_rows > 0;
    $stmtCheck->close();

    // 2. Eksekusi Update jika ada, catat error jika tidak ada
    if ($isExists) {
        // Langsung hajar UPDATE karena keduanya ada di tabel yang sama
        $stmtUpdate = $koneksi->prepare("UPDATE tb_identitas SET bpjs_tk = ? WHERE nik_ktp = ?");
        $stmtUpdate->bind_param("ss", $bpjs_tk, $nik_ktp);
        
        if ($stmtUpdate->execute()) {
            $successCount++; 
            $updateCount++;
        } else {
            $errorCount++; 
            $errors[] = "Baris $i: Gagal update - " . $stmtUpdate->error;
        }
        $stmtUpdate->close();
    } else {
        // Jika NIK KTP tidak ditemukan di tb_identitas
        $errorCount++;
        $errors[] = "Baris $i: NIK KTP '$nik_ktp' tidak ditemukan di sistem.";
    }
}

// 3. Output Response
$status = ($errorCount > 0 && $successCount == 0) ? 'error' : (($errorCount > 0) ? 'warning' : 'success');
ob_clean(); 

echo json_encode([
    'status' => $status,
    'message' => "Selesai! $successCount data berhasil diupdate. $errorCount error.",
    'errors' => array_slice($errors, 0, 5) // Tampilkan maksimal 5 error agar tidak kepanjangan
]);

$koneksi->close();
?>