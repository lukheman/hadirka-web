<?php
/**
 * ============================================
 * CSV/EXCEL IMPORT - THR DATA (DEBUG MODE)
 * ============================================
 */

// 1. TAHAN SEMUA OUTPUT KOTOR
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. JEBAKAN BATMAN UNTUK MENANGKAP FATAL ERROR (500)
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean(); // Bersihkan output kotor
        echo json_encode([
            'status' => 'error',
            'message' => 'Sistem Crash (Fatal Error): ' . $error['message'] . ' di baris ' . $error['line']
        ]);
        exit;
    }
});

header("Content-Type: application/json; charset=utf-8");
require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function sendJsonResponse($status, $message, $data = [])
{
    ob_clean(); // Wajib bersihkan sebelum kirim JSON
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data));
    exit;
}

// --- FUNGSI PEMBERSIH FORMAT MATA UANG (TERSTANDAR) ---
function cleanRupiah($val) {
    if (empty($val)) return 0;
    
    // Jika dari Excel sudah terbaca sebagai angka murni, langsung return
    if (is_numeric($val)) return floatval($val);
    
    $val = (string)$val;
    // Hapus karakter huruf (Rp, rp) dan spasi
    $val = preg_replace('/[a-zA-Z\s]/', '', $val);
    
    // Deteksi pemisah ribuan dan desimal (Contoh: 36.881,30)
    if (strpos($val, ',') !== false && strpos($val, '.') !== false) {
        // Jika koma berada setelah titik, berarti koma adalah desimal (Format Indo)
        if (strrpos($val, ',') > strrpos($val, '.')) {
            $val = str_replace('.', '', $val); // hapus titik ribuan
            $val = str_replace(',', '.', $val); // jadikan koma sebagai desimal
        } else {
            // Jika format English (Contoh: 36,881.30)
            $val = str_replace(',', '', $val); // hapus koma ribuan
        }
    } elseif (strpos($val, ',') !== false) {
        // Jika cuma ada koma (Contoh: 36881,30), asumsikan format Indo (koma = desimal)
        $val = str_replace(',', '.', $val);
    }
    
    // Konversi akhir ke tipe Float
    return floatval($val);
}

if (!isset($koneksi) || !$koneksi) sendJsonResponse('error', 'Koneksi database gagal');
if (!isset($_FILES['file_csv_thr'])) sendJsonResponse('error', 'File tidak ditemukan');

$file = $_FILES['file_csv_thr'];
if ($file['error'] !== UPLOAD_ERR_OK) sendJsonResponse('error', 'Upload gagal: Kode error ' . $file['error']);

try {
    $spreadsheet = IOFactory::load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    
    // PERBAIKAN PENTING 1:
    // Parameter ke-3 (formatData) diubah jadi FALSE agar Excel tidak 
    // memaksa mengambil string berformat "Rp", melainkan angka aslinya.
    $data = $sheet->toArray(null, true, false, true);
} catch (Exception $e) {
    sendJsonResponse('error', 'Excel Error: ' . $e->getMessage());
}

if (empty($data) || count($data) < 2) sendJsonResponse('error', 'File kosong atau hanya header');

$colNIK = 'B';
$colTglMulai = 'E';
$colTglHariRaya = 'F';
$colGapok = 'G';
$colTunjLokasi = 'H';
$colTunjKebutuhan = 'I';
$colMasaKerja = 'J';
$colNominalTHR = 'K';

$successCount = 0; $errorCount = 0; $updateCount = 0; $insertCount = 0;
$errors = []; $skippedRows = [];

for ($i = 2; $i <= count($data); $i++) {
    if (!isset($data[$i])) continue;
    $row = $data[$i];

    // PERBAIKAN PENTING 2 (PENGAMAN LOOP):
    // Selalu reset variabel penampung data sql fetch di awal loop 
    // agar data baris sebelumnya tidak bocor ke baris yang salah/error.
    $id_karyawan = null;

    $nik_karyawan = isset($row[$colNIK]) ? trim($row[$colNIK]) : '';
    if (empty($nik_karyawan)) {
        $skippedRows[] = $i;
        continue;
    }

    $stmtK = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = ?");
    if (!$stmtK) sendJsonResponse('error', "Query Karyawan Error: " . $koneksi->error);
    
    $stmtK->bind_param("s", $nik_karyawan);
    $stmtK->execute();
    $stmtK->bind_result($id_karyawan);
    $stmtK->fetch();
    $stmtK->close();

    if (!$id_karyawan) {
        $errorCount++;
        $errors[] = "Baris $i: NIK $nik_karyawan tidak ada.";
        continue;
    }

    try {
        $tgl_m_raw = $row[$colTglMulai] ?? '';
        $tgl_r_raw = $row[$colTglHariRaya] ?? '';

        $tgl_mulai = is_numeric($tgl_m_raw) ? Date::excelToDateTimeObject($tgl_m_raw)->format('Y-m-d') : date('Y-m-d', strtotime(str_replace('/', '-', $tgl_m_raw)));
        $tgl_raya = is_numeric($tgl_r_raw) ? Date::excelToDateTimeObject($tgl_r_raw)->format('Y-m-d') : date('Y-m-d', strtotime(str_replace('/', '-', $tgl_r_raw)));

        $periode = date('Y', strtotime($tgl_raya));
    } catch (Exception $e) {
        $errorCount++;
        $errors[] = "Baris $i: Format tanggal salah.";
        continue;
    }

    // PERBAIKAN PENTING 3: Menggunakan fungsi cleanRupiah() yang baru
    $str_gapok = (string) cleanRupiah($row[$colGapok] ?? 0);
    $str_lokasi = (string) cleanRupiah($row[$colTunjLokasi] ?? 0);
    $str_kebutuhan = (string) cleanRupiah($row[$colTunjKebutuhan] ?? 0);
    $str_nominal = (string) cleanRupiah($row[$colNominalTHR] ?? 0);
    $str_masa_kerja = trim((string)($row[$colMasaKerja] ?? '0'));

    $stmtCheck = $koneksi->prepare("SELECT id_thr FROM tb_thr WHERE id_karyawan = ? AND periode = ?");
    if (!$stmtCheck) sendJsonResponse('error', "Query Check Error: " . $koneksi->error);
    
    $stmtCheck->bind_param("is", $id_karyawan, $periode); 
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows == 0) {
        $stmtCheck->close();

        $stmtInsert = $koneksi->prepare("
            INSERT INTO tb_thr (id_karyawan, periode, tgl_mulai, tgl_hari_raya, gaji_pokok, tunjangan_lokasi, tunjangan_kebutuhan, masa_kerja, nominal_thr)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmtInsert) sendJsonResponse('error', 'Query Insert Error: ' . $koneksi->error);

        $stmtInsert->bind_param("issssssss", $id_karyawan, $periode, $tgl_mulai, $tgl_raya, $str_gapok, $str_lokasi, $str_kebutuhan, $str_masa_kerja, $str_nominal);

        if ($stmtInsert->execute()) {
            $successCount++; $insertCount++;
        } else {
            $errorCount++; $errors[] = "Baris $i: Gagal insert - " . $stmtInsert->error;
        }
        $stmtInsert->close();
    } else {
        $stmtCheck->close();

        $stmtUpdate = $koneksi->prepare("
            UPDATE tb_thr 
            SET tgl_mulai = ?, tgl_hari_raya = ?, gaji_pokok = ?, tunjangan_lokasi = ?, tunjangan_kebutuhan = ?, masa_kerja = ?, nominal_thr = ? 
            WHERE id_karyawan = ? AND periode = ?
        ");

        if (!$stmtUpdate) sendJsonResponse('error', 'Query Update Error: ' . $koneksi->error);

        $stmtUpdate->bind_param("sssssssis", $tgl_mulai, $tgl_raya, $str_gapok, $str_lokasi, $str_kebutuhan, $str_masa_kerja, $str_nominal, $id_karyawan, $periode);

        if ($stmtUpdate->execute()) {
            $successCount++; $updateCount++;
        } else {
            $errorCount++; $errors[] = "Baris $i: Gagal update - " . $stmtUpdate->error;
        }
        $stmtUpdate->close();
    }
}

// 3. SAPU BERSIH BUFFER TERAKHIR SEBELUM FINAL JSON
$status = ($errorCount > 0 && $successCount == 0) ? 'error' : (($errorCount > 0) ? 'warning' : 'success');
ob_clean(); 

echo json_encode([
    'status' => $status,
    'message' => "Selesai! $successCount sukses ($insertCount baru, $updateCount update). $errorCount error.",
    'errors' => array_slice($errors, 0, 5)
]);

if (isset($koneksi)) $koneksi->close();
?>