<?php
header("Content-Type: application/json");
require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// ================================
// CEK FILE CSV / EXCEL
// ================================
if (!isset($_FILES['file_csv_rapel'])) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'File CSV/Excel tidak ditemukan'
    ]);
    exit;
}

$file = $_FILES['file_csv_rapel'];
$fileTmpPath = $file['tmp_name'];
$fileName = $file['name'];
$fileSize = $file['size'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validasi ekstensi file
$allowedExtensions = ['csv', 'xlsx', 'xls'];
if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Format file tidak didukung! Gunakan CSV, XLSX, atau XLS'
    ]);
    exit;
}

// Validasi ukuran file (max 10MB)
$maxFileSize = 10 * 1024 * 1024; // 10MB
if ($fileSize > $maxFileSize) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ukuran file terlalu besar! Maksimal 10MB'
    ]);
    exit;
}

try {
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal membaca file: ' . $e->getMessage()
    ]);
    exit;
}

// ================================
// DEFINISI KOLOM
// ================================
$colNIK = 'B';
$colTglRapel = 'E';
$colNominal = 'F';
$colKeterangan = 'G';

$successCount = 0;
$errorCount = 0;
$updateCount = 0;
$insertCount = 0;
$errors = [];
$skippedRows = [];

// ================================
// VALIDASI HEADER (OPSIONAL)
// ================================
$headerRow = $data[1];
if (empty($headerRow[$colNIK]) && empty($headerRow[$colTglRapel])) {
    // Kemungkinan baris 1 adalah header, mulai dari baris 2
    $startRow = 2;
} else {
    $startRow = 2; // Default mulai dari baris 2
}

// ================================
// LOOP PER BARIS
// ================================
for ($i = $startRow; $i <= count($data); $i++) {

    $row = $data[$i];

    // Skip jika baris kosong
    if (empty($row) || !isset($row[$colNIK])) {
        $skippedRows[] = $i;
        continue;
    }

    // Ambil nilai dari setiap kolom
    $nik_karyawan = trim($row[$colNIK]);
    $tglRapel = trim($row[$colTglRapel]);
    $nominal = trim($row[$colNominal]);
    $keterangan = isset($row[$colKeterangan]) ? trim($row[$colKeterangan]) : '';

    // Skip jika NIK kosong
    if (empty($nik_karyawan)) {
        $skippedRows[] = $i;
        continue;
    }

    // ================================
    // AMBIL ID KARYAWAN
    // ================================
    $stmtK = $koneksi->prepare("SELECT id_karyawan, nama_lengkap FROM tb_karyawan WHERE nik_karyawan=?");
    $stmtK->bind_param("s", $nik_karyawan);
    $stmtK->execute();
    $stmtK->bind_result($id_karyawan, $nama_lengkap);
    $stmtK->fetch();
    $stmtK->close();

    // Jika NIK tidak ditemukan → catat error
    if (!$id_karyawan) {
        $errorCount++;
        $errors[] = "Baris $i: NIK $nik_karyawan tidak ditemukan di database";
        continue;
    }

    // ================================
    // KONVERSI TANGGAL
    // ================================
    try {
        if (empty($tglRapel)) {
            $errorCount++;
            $errors[] = "Baris $i: Tanggal rapel kosong untuk NIK $nik_karyawan";
            continue;
        }

        if (is_numeric($tglRapel)) {
            // Jika tanggal dalam format Excel serial number
            $tgl_rapel = Date::excelToDateTimeObject($tglRapel)->format('Y-m-d');
        } else {
            // Jika tanggal dalam format string
            $timestamp = strtotime($tglRapel);
            if ($timestamp === false) {
                $errorCount++;
                $errors[] = "Baris $i: Format tanggal tidak valid untuk NIK $nik_karyawan";
                continue;
            }
            $tgl_rapel = date('Y-m-d', $timestamp);
        }
    } catch (Exception $e) {
        $errorCount++;
        $errors[] = "Baris $i: Error konversi tanggal untuk NIK $nik_karyawan - " . $e->getMessage();
        continue;
    }

    // ================================
    // KONVERSI NOMINAL (HAPUS KARAKTER NON-NUMERIC)
    // ================================
    if (empty($nominal)) {
        $errorCount++;
        $errors[] = "Baris $i: Nominal rapel kosong untuk NIK $nik_karyawan";
        continue;
    }

    // Hapus karakter non-numeric kecuali titik dan koma
    $nominal_clean = str_replace([',', '.'], ['', '.'], $nominal);
    $nominal_rapel = (float) preg_replace('/[^0-9.]/', '', $nominal_clean);

    if ($nominal_rapel <= 0) {
        $errorCount++;
        $errors[] = "Baris $i: Nominal rapel tidak valid untuk NIK $nik_karyawan";
        continue;
    }

    // ================================
    // CEK APAKAH DATA SUDAH ADA
    // ================================
    $stmtCheck = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_karyawan=? AND tgl_rapel=?");
    $stmtCheck->bind_param("is", $id_karyawan, $tgl_rapel);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows == 0) {
        // INSERT baru
        $stmtInsert = $koneksi->prepare("
            INSERT INTO tb_rapel (id_karyawan, tgl_rapel, nominal_rapel, ket_rapel)
            VALUES (?,?,?,?)
        ");
        $stmtInsert->bind_param("isds", $id_karyawan, $tgl_rapel, $nominal_rapel, $keterangan);
        
        if ($stmtInsert->execute()) {
            $successCount++;
            $insertCount++;
            $logData[] = ['status' => 'INSERT', 'nik' => $nik_karyawan, 'nama' => $nama_lengkap, 'tgl' => $tgl_rapel, 'nominal' => $nominal_rapel];
        } else {
            $errorCount++;
            $errors[] = "Baris $i: Gagal menyimpan data untuk NIK $nik_karyawan - " . $stmtInsert->error;
        }
        $stmtInsert->close();
    } else {
        // UPDATE data yang sudah ada
        $stmtUpdate = $koneksi->prepare("
            UPDATE tb_rapel 
            SET nominal_rapel=?, ket_rapel=? 
            WHERE id_karyawan=? AND tgl_rapel=?
        ");
        $stmtUpdate->bind_param("dsis", $nominal_rapel, $keterangan, $id_karyawan, $tgl_rapel);
        
        if ($stmtUpdate->execute()) {
            $successCount++;
            $updateCount++;
            $logData[] = ['status' => 'UPDATE', 'nik' => $nik_karyawan, 'nama' => $nama_lengkap, 'tgl' => $tgl_rapel, 'nominal' => $nominal_rapel];
        } else {
            $errorCount++;
            $errors[] = "Baris $i: Gagal mengupdate data untuk NIK $nik_karyawan - " . $stmtUpdate->error;
        }
        $stmtUpdate->close();
    }

    $stmtCheck->close();
}

// ================================
// RESPONSE DETAIL
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

// Ambil 5 error pertama untuk ditampilkan
$errorSample = array_slice($errors, 0, 5);

echo json_encode([
    'status' => $errorCount < $successCount ? 'success' : 'warning',
    'message' => $message,
    'success_count' => $successCount,
    'log_details' => $logData,
    'insert_count' => $insertCount,
    'update_count' => $updateCount,
    'error_count' => $errorCount,
    'skipped_count' => count($skippedRows),
    'errors' => $errorSample,
    'total_errors' => count($errors)
]);
?>