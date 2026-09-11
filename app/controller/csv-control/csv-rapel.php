<?php
/**
 * ============================================
 * CSV/EXCEL IMPORT - RAPEL DATA
 * ============================================
 * File: csv-rapel.php
 * Purpose: Import rapel data from CSV/Excel files
 * Format: B=NIK | E=Tanggal | F=Nominal | G=Keterangan
 */

header("Content-Type: application/json; charset=utf-8");
require '../../config/koneksi.php';
require_once __DIR__ . '/../../config/Excel.php'; // Excel::json()/Excel::cleanRupiah() terpusat
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// ================================
// CHECK DATABASE CONNECTION
// ================================
if (!isset($koneksi) || !$koneksi) {
    Excel::json('error', 'Koneksi database gagal');
}

// ================================
// CHECK FILE UPLOAD
// ================================
if (!isset($_FILES['file_csv_rapel'])) {
    Excel::json('error', 'File CSV/Excel tidak ditemukan');
}

$file = $_FILES['file_csv_rapel'];

// Check upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
        UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
        UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
        UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
    ];
    
    $errorMsg = isset($uploadErrors[$file['error']]) ? $uploadErrors[$file['error']] : 'Unknown upload error';
    Excel::json('error', 'Upload gagal: ' . $errorMsg);
}

$fileTmpPath = $file['tmp_name'];
$fileName = $file['name'];
$fileSize = $file['size'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// ================================
// VALIDATE FILE EXTENSION
// ================================
$allowedExtensions = ['csv', 'xlsx', 'xls'];
if (!in_array($fileExtension, $allowedExtensions)) {
    Excel::json('error', 'Format file tidak didukung! Gunakan CSV, XLSX, atau XLS');
}

// ================================
// VALIDATE FILE SIZE (max 10MB)
// ================================
$maxFileSize = 10 * 1024 * 1024; // 10MB
if ($fileSize > $maxFileSize) {
    $fileSizeMB = round($fileSize / (1024 * 1024), 2);
    Excel::json('error', "Ukuran file terlalu besar ({$fileSizeMB}MB)! Maksimal 10MB");
}

// Check if file is empty
if ($fileSize == 0) {
    Excel::json('error', 'File kosong atau tidak valid');
}

// ================================
// VALIDATE FILE MIME TYPE
// ================================
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $fileTmpPath);
finfo_close($finfo);

$allowedMimeTypes = [
    'text/csv',
    'text/plain',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/octet-stream'
];

if (!in_array($mimeType, $allowedMimeTypes)) {
    Excel::json('error', 'Tipe file tidak valid');
}

// ================================
// READ FILE WITH PHPSPREADSHEET
// ================================
try {
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    
    // PERBAIKAN PENTING:
    // Parameter ke-3 (formatData) diubah jadi FALSE agar Excel tidak 
    // memaksa mengambil string berformat "Rp", melainkan angka aslinya.
    $data = $sheet->toArray(null, true, false, true);
} catch (Exception $e) {
    Excel::json('error', 'Gagal membaca file: ' . $e->getMessage());
}

// Check if data is empty
if (empty($data) || count($data) < 2) {
    Excel::json('error', 'File tidak berisi data atau hanya berisi header');
}

// ================================
// COLUMN DEFINITIONS
// ================================
$colNIK = 'B';
$colTglRapel = 'E';
$colNominal = 'F';
$colKeterangan = 'G';

// ================================
// INITIALIZE COUNTERS
// ================================
$successCount = 0;
$errorCount = 0;
$updateCount = 0;
$insertCount = 0;
$errors = [];
$skippedRows = [];
$logData = []; // Initialize log data array

// ================================
// DETECT HEADER ROW
// ================================
$headerRow = $data[1] ?? [];
$startRow = 2; // Default start from row 2

// Check if first row looks like header
if (isset($headerRow[$colNIK])) {
    $firstCellValue = strtolower(trim($headerRow[$colNIK]));
    if (in_array($firstCellValue, ['nik', 'nik karyawan', 'no', 'nomor'])) {
        $startRow = 2; // Skip header
    }
}

// ================================
// PROCESS EACH ROW
// ================================
$processedRows = 0;
$maxProcessRows = 1000; // Limit untuk mencegah timeout

for ($i = $startRow; $i <= count($data) && $processedRows < $maxProcessRows; $i++) {
    
    if (!isset($data[$i])) {
        continue;
    }
    
    $row = $data[$i];
    $processedRows++;

    $id_karyawan = null;
    $nama_lengkap = null;

    // ================================
    // SKIP EMPTY ROWS
    // ================================
    if (empty($row) || !isset($row[$colNIK])) {
        $skippedRows[] = $i;
        continue;
    }

    // ================================
    // EXTRACT DATA FROM COLUMNS
    // ================================
    $nik_karyawan = isset($row[$colNIK]) ? trim($row[$colNIK]) : '';
    $tglRapel = isset($row[$colTglRapel]) ? trim($row[$colTglRapel]) : '';
    $nominal = isset($row[$colNominal]) ? trim($row[$colNominal]) : '';
    $keterangan = isset($row[$colKeterangan]) ? trim($row[$colKeterangan]) : '';

    // Skip if NIK is empty
    if (empty($nik_karyawan)) {
        $skippedRows[] = $i;
        continue;
    }

    // ================================
    // GET EMPLOYEE ID BY NIK
    // ================================
    $stmtK = $koneksi->prepare("SELECT id_karyawan, nama_lengkap FROM tb_karyawan WHERE nik_karyawan = ?");
    
    if (!$stmtK) {
        $errorCount++;
        $errors[] = "Baris $i: Error prepare statement - " . $koneksi->error;
        continue;
    }
    
    $stmtK->bind_param("s", $nik_karyawan);
    $stmtK->execute();
    $stmtK->bind_result($id_karyawan, $nama_lengkap);
    $stmtK->fetch();
    $stmtK->close();

    // Check if employee exists
    if (!$id_karyawan) {
        $errorCount++;
        $errors[] = "Baris $i: NIK $nik_karyawan tidak ditemukan di database";
        continue;
    }

    // ================================
    // CONVERT DATE
    // ================================
    try {
        if (empty($tglRapel)) {
            $errorCount++;
            $errors[] = "Baris $i: Tanggal rapel kosong untuk NIK $nik_karyawan";
            continue;
        }

        $tgl_rapel = null;

        // 1. Handle format Serial Number bawaan Excel (misal: 45280)
        if (is_numeric($tglRapel)) {
            $tgl_rapel = Date::excelToDateTimeObject($tglRapel)->format('Y-m-d');
        } else {
            // 2. Daftar tebakan format tanggal yang umum digunakan
            $dateFormats = [
                'Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 
                'd.m.Y', 'j/n/Y', 'j-n-Y', 'd/m/y'
            ];
            
            foreach ($dateFormats as $format) {
                $dateObj = DateTime::createFromFormat($format, $tglRapel);
                // Validasi ketat (menghindari PHP menganggap 32/01/2023 jadi 01/02/2023)
                if ($dateObj && $dateObj->format($format) === $tglRapel) {
                    $tgl_rapel = $dateObj->format('Y-m-d');
                    break;
                }
            }
            
            // 3. Fallback: Jika tidak cocok dengan array di atas, paksa baca pakai strtotime
            if (!$tgl_rapel) {
                // Ubah semua simbol garis miring atau titik menjadi strip (-)
                $tglNormal = str_replace(['/', '.'], '-', $tglRapel);
                $timestamp = strtotime($tglNormal);
                
                if ($timestamp !== false) {
                    $tgl_rapel = date('Y-m-d', $timestamp);
                } else {
                    $errorCount++;
                    $errors[] = "Baris $i: Format tanggal tidak dipahami '$tglRapel' (Gunakan yyyy-mm-dd)";
                    continue;
                }
            }
        }
        
        // Validate date range (Pastikan tidak salah ketik tahun 1900 atau 2050)
        $dateObj = new DateTime($tgl_rapel);
        $minDate = new DateTime('2000-01-01'); // Dibatasi masuk akal mulai thn 2000
        $maxDate = new DateTime('+2 year');
        
        if ($dateObj < $minDate || $dateObj > $maxDate) {
            $errorCount++;
            $errors[] = "Baris $i: Tahun pada tanggal '$tglRapel' di luar batas untuk NIK $nik_karyawan";
            continue;
        }
        
    } catch (Exception $e) {
        $errorCount++;
        $errors[] = "Baris $i: Error membaca tanggal untuk NIK $nik_karyawan";
        continue;
    }

    // ================================
    // CONVERT NOMINAL (PERBAIKAN DI SINI)
    // ================================
    if (empty($nominal) && $nominal !== '0') {
        $errorCount++;
        $errors[] = "Baris $i: Nominal rapel kosong untuk NIK $nik_karyawan";
        continue;
    }

    // Menggunakan fungsi cleanRupiah agar fleksibel membaca format Rp ataupun angka murni
    $nominal_rapel = Excel::cleanRupiah($nominal);

    if ($nominal_rapel <= 0) {
        $errorCount++;
        $errors[] = "Baris $i: Nominal tidak dapat dibaca '$nominal' untuk NIK $nik_karyawan";
        continue;
    }
    
    // Check maximum nominal
    if ($nominal_rapel > 999999999) {
        $errorCount++;
        $errors[] = "Baris $i: Nominal rapel terlalu besar untuk NIK $nik_karyawan";
        continue;
    }

    // Normalisasi dan Sanitize Keterangan
    $keterangan = trim($keterangan); 
    $keterangan = preg_replace('/\s+/', ' ', $keterangan); 
    $keterangan = substr($keterangan, 0, 500); 
    $keterangan = strtoupper($keterangan);

    // ================================
    // CHECK IF DATA EXISTS (Cek Kombinasi ID, Tgl, dan Keterangan)
    // ================================
    $stmtCheck = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_karyawan = ? AND tgl_rapel = ? AND ket_rapel = ?");
    
    if (!$stmtCheck) {
        $errorCount++;
        $errors[] = "Baris $i: Error prepare check statement - " . $koneksi->error;
        continue;
    }
    
    $stmtCheck->bind_param("iss", $id_karyawan, $tgl_rapel, $keterangan);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows == 0) {
        // ================================
        // INSERT NEW DATA
        // ================================
        $stmtCheck->close();
        
        $stmtInsert = $koneksi->prepare("
            INSERT INTO tb_rapel (id_karyawan, tgl_rapel, nominal_rapel, ket_rapel)
            VALUES (?, ?, ?, ?)
        ");
        
        if (!$stmtInsert) {
            $errorCount++;
            $errors[] = "Baris $i: Error prepare insert statement - " . $koneksi->error;
            continue;
        }
        
        $stmtInsert->bind_param("isds", $id_karyawan, $tgl_rapel, $nominal_rapel, $keterangan);
        
        if ($stmtInsert->execute()) {
            $successCount++;
            $insertCount++;
            $logData[] = [
                'status' => 'INSERT',
                'row' => $i,
                'nik' => $nik_karyawan,
                'nama' => $nama_lengkap,
                'tgl' => $tgl_rapel,
                'nominal' => $nominal_rapel
            ];
        } else {
            $errorCount++;
            $errors[] = "Baris $i: Gagal menyimpan data untuk NIK $nik_karyawan - " . $stmtInsert->error;
        }
        $stmtInsert->close();
        
    } else {
        // ================================
        // UPDATE EXISTING DATA
        // ================================
        $stmtCheck->close();
        
        $stmtUpdate = $koneksi->prepare("
            UPDATE tb_rapel 
            SET nominal_rapel = ? 
            WHERE id_karyawan = ? AND tgl_rapel = ? AND ket_rapel = ?
        ");
        
        if (!$stmtUpdate) {
            $errorCount++;
            $errors[] = "Baris $i: Error prepare update statement - " . $koneksi->error;
            continue;
        }
        
        $stmtUpdate->bind_param("diss", $nominal_rapel, $id_karyawan, $tgl_rapel, $keterangan);
        
        if ($stmtUpdate->execute()) {
            $successCount++;
            $updateCount++;
            $logData[] = [
                'status' => 'UPDATE',
                'row' => $i,
                'nik' => $nik_karyawan,
                'nama' => $nama_lengkap,
                'tgl' => $tgl_rapel,
                'nominal' => $nominal_rapel
            ];
        } else {
            $errorCount++;
            $errors[] = "Baris $i: Gagal mengupdate data untuk NIK $nik_karyawan - " . $stmtUpdate->error;
        }
        $stmtUpdate->close();
    }
}

// ================================
// BUILD RESPONSE MESSAGE
// ================================
$message = "Import selesai! ";
$details = [];

if ($insertCount > 0) {
    $details[] = "$insertCount data baru ditambahkan";
}

if ($updateCount > 0) {
    $details[] = "$updateCount data diperbarui";
}

if ($errorCount > 0) {
    $details[] = "$errorCount data gagal diproses";
}

if (count($skippedRows) > 0) {
    $details[] = count($skippedRows) . " baris dilewati (kosong)";
}

$message .= implode(", ", $details);

// Determine overall status
$status = 'success';
if ($errorCount > 0 && $successCount == 0) {
    $status = 'error';
    $message = "Import gagal! Semua data gagal diproses.";
} elseif ($errorCount > 0) {
    $status = 'warning';
}

// Get sample errors
$errorSample = array_slice($errors, 0, 50);

// ================================
// SEND RESPONSE
// ================================
echo json_encode([
    'status' => $status,
    'message' => $message,
    'success_count' => $successCount,
    'insert_count' => $insertCount,
    'update_count' => $updateCount,
    'error_count' => $errorCount,
    'skipped_count' => count($skippedRows),
    'errors' => $errorSample,
    'total_errors' => count($errors),
    'log_details' => $logData,
    'processed_rows' => $processedRows
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Close connection
if (isset($koneksi)) {
    $koneksi->close();
}
?>