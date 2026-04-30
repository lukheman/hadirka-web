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
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// ================================
// FUNCTION: Send JSON Response
// ================================
function sendJsonResponse($status, $message, $data = []) {
    echo json_encode(array_merge([
        'status' => $status,
        'message' => $message
    ], $data));
    exit;
}

// ================================
// CHECK DATABASE CONNECTION
// ================================
if (!isset($koneksi) || !$koneksi) {
    sendJsonResponse('error', 'Koneksi database gagal');
}

// ================================
// CHECK FILE UPLOAD
// ================================
if (!isset($_FILES['file_csv_rapel'])) {
    sendJsonResponse('error', 'File CSV/Excel tidak ditemukan');
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
    sendJsonResponse('error', 'Upload gagal: ' . $errorMsg);
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
    sendJsonResponse('error', 'Format file tidak didukung! Gunakan CSV, XLSX, atau XLS');
}

// ================================
// VALIDATE FILE SIZE (max 10MB)
// ================================
$maxFileSize = 10 * 1024 * 1024; // 10MB
if ($fileSize > $maxFileSize) {
    $fileSizeMB = round($fileSize / (1024 * 1024), 2);
    sendJsonResponse('error', "Ukuran file terlalu besar ({$fileSizeMB}MB)! Maksimal 10MB");
}

// Check if file is empty
if ($fileSize == 0) {
    sendJsonResponse('error', 'File kosong atau tidak valid');
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
    sendJsonResponse('error', 'Tipe file tidak valid');
}

// ================================
// READ FILE WITH PHPSPREADSHEET
// ================================
try {
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);
} catch (Exception $e) {
    sendJsonResponse('error', 'Gagal membaca file: ' . $e->getMessage());
}

// Check if data is empty
if (empty($data) || count($data) < 2) {
    sendJsonResponse('error', 'File tidak berisi data atau hanya berisi header');
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
    // CONVERT NOMINAL
    // ================================
    if (empty($nominal) && $nominal !== '0') {
        $errorCount++;
        $errors[] = "Baris $i: Nominal rapel kosong untuk NIK $nik_karyawan";
        continue;
    }

    // Bersihkan semua karakter KECUALI angka, titik, dan koma (Rp, spasi, minus akan hilang)
    $nominal_clean = preg_replace('/[^0-9.,]/', '', $nominal);
    
    $hasDot = strpos($nominal_clean, '.') !== false;
    $hasComma = strpos($nominal_clean, ',') !== false;

    // Skenario 1: Ada Titik DAN Koma (misal: 1.000.000,50 atau 1,000,000.56)
    if ($hasDot && $hasComma) {
        $lastDot = strrpos($nominal_clean, '.');
        $lastComma = strrpos($nominal_clean, ',');
        
        if ($lastDot > $lastComma) {
            // Titik di akhir -> Format Internasional (1,000.56)
            $nominal_clean = str_replace(',', '', $nominal_clean);
        } else {
            // Koma di akhir -> Format Indonesia (1.000,5)
            $nominal_clean = str_replace('.', '', $nominal_clean); // buang pemisah ribuan
            $nominal_clean = str_replace(',', '.', $nominal_clean); // ubah koma jadi titik desimal
        }
    } 
    // Skenario 2: Hanya ada TITIK (misal: 1.000.000 atau 1000.56)
    elseif ($hasDot) {
        // Cek berapa banyak titiknya
        if (substr_count($nominal_clean, '.') > 1) {
            // Jika lebih dari 1 titik (1.000.000), ini PASTI pemisah ribuan
            $nominal_clean = str_replace('.', '', $nominal_clean);
        } else {
            // Jika cuma ada 1 titik, lihat digit di belakang titik ada berapa
            $parts = explode('.', $nominal_clean);
            if (strlen(end($parts)) === 3) {
                // Kalau pas 3 digit belakang (1.000), ini pemisah ribuan (Seribu)
                $nominal_clean = str_replace('.', '', $nominal_clean);
            } else {
                // Kalau bukan 3 digit (misal .5 atau .56), ini adalah desimal
                // Biarkan titiknya tetap ada
            }
        }
    } 
    // Skenario 3: Hanya ada KOMA (misal: 1,000,000 atau 1000,5)
    elseif ($hasComma) {
        if (substr_count($nominal_clean, ',') > 1) {
            // Lebih dari 1 koma, pasti format US/Internasional (1,000,000)
            $nominal_clean = str_replace(',', '', $nominal_clean);
        } else {
            $parts = explode(',', $nominal_clean);
            if (strlen(end($parts)) === 3) {
                // Pas 3 digit (1,000), ini ribuannya orang bule
                $nominal_clean = str_replace(',', '', $nominal_clean);
            } else {
                // Bukan 3 digit (1000,5), berarti koma ini adalah desimal Indonesia
                $nominal_clean = str_replace(',', '.', $nominal_clean);
            }
        }
    }
    
    // Konversi string yang sudah sangat bersih menjadi angka pecahan riil (float)
    $nominal_rapel = (float) $nominal_clean;

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

    // Sanitize keterangan
    $keterangan = substr($keterangan, 0, 500); // Limit length

    // ================================
    // CHECK IF DATA EXISTS
    // ================================
    $stmtCheck = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_karyawan = ? AND tgl_rapel = ? AND ket_rapel = ?");
    
    if (!$stmtCheck) {
        $errorCount++;
        $errors[] = "Baris $i: Error prepare check statement - " . $koneksi->error;
        continue;
    }
    
    // Tambahkan variabel $keterangan ke dalam pengecekan (iss = integer, string, string)
    $stmtCheck->bind_param("iss", $id_karyawan, $tgl_rapel, $keterangan);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows == 0) {
        // ================================
        // INSERT NEW DATA (Jika Keterangan beda, buat baru)
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
        // UPDATE EXISTING DATA (Jika ID, Tgl, dan Keterangan sama persis)
        // ================================
        $stmtCheck->close();
        
        // Kita hanya perlu update nominalnya saja, karena keterangannya sudah pasti sama
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
        
        // diss = double (nominal), integer (id), string (tgl), string (ket)
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

// Get sample errors (Tampilkan hingga 50 rincian error pertama)
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