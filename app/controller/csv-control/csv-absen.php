<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); 
ini_set('log_errors', 1);

require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// ================================
// 1. CEK FILE
// ================================
if (!isset($_FILES['file_csv'])) {
    echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan']);
    exit;
}

if ($_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Error upload file']);
    exit;
}

try {
    $fileTmpPath = $_FILES['file_csv']['tmp_name'];
    
    if (!file_exists($fileTmpPath)) {
        throw new Exception('File temporary tidak ditemukan');
    }
    
    $reader = IOFactory::createReaderForFile($fileTmpPath);
    $reader->setReadDataOnly(false); 
    $spreadsheet = $reader->load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);

    // ================================
    // 2. FUNGSI CEK WARNA
    // ================================
    function isYellowCell($sheet, $cell) {
        try {
            $style = $sheet->getStyle($cell)->getFill();
            $bgColor = strtoupper($style->getStartColor()->getRGB());
            
            if ($style->getFillType() === Fill::FILL_NONE || 
                $bgColor === '000000' || 
                $bgColor === 'FFFFFF' || 
                $bgColor === '') {
                return false;
            }
            
            $yellowColors = [
                'FFFF00', 'FFFF99', 'FFFFCC', 'FFFFE0', 'FFFACD',
                'FFF200', 'FFED00', 'FFFF0000', 'FFFFFF00', 'FFFFC000',
                'FFFFCC00', 'FFFFE599', 'FFFFFFCC'
            ];
            
            return in_array($bgColor, $yellowColors);
        } catch (Exception $e) {
            return false;
        }
    }

    // ================================
    // 3. DEFINISI KOLOM
    // ================================
    $colNIK = 'C';
    $startCol = 'D';
    $highestCol = $sheet->getHighestColumn();
    $colsTanggal = [];

    $currentCol = $startCol;
    while (true) {
        $val = trim($sheet->getCell($currentCol . '1')->getValue());
        if ($val !== '') {
            $colsTanggal[] = $currentCol;
        }
        if ($currentCol === $highestCol) break;
        $currentCol++;
    }

    if (empty($colsTanggal)) {
        throw new Exception('Tidak ada kolom tanggal di file Excel');
    }

    $successCount = 0;
    $errorCount = 0;
    $skipCount = 0;
    $deleteCount = 0;
    $updateCount = 0;
    $insertCount = 0;
    $errorMessages = [];
    $debugLog = [];

    // ================================
    // 4. LOOP PER BARIS
    // ================================
    for ($i = 2; $i <= count($data); $i++) {
        $row = $data[$i];
        $nik_karyawan = isset($row[$colNIK]) ? trim($row[$colNIK]) : '';
        $nik_karyawan = str_replace(' ', '', $nik_karyawan);

        if (empty($nik_karyawan)) {
            $skipCount++;
            continue;
        }

        // Get ID Karyawan
        $stmtK = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan=?");
        $stmtK->bind_param("s", $nik_karyawan);
        $stmtK->execute();
        $stmtK->bind_result($id_karyawan);
        $stmtK->fetch();
        $stmtK->close();

        if (!$id_karyawan) {
            $errorMessages[] = "NIK $nik_karyawan tidak ditemukan";
            $errorCount++;
            continue;
        }

        // Loop per kolom tanggal
        foreach ($colsTanggal as $col) {
            $cellAddress = $col . $i;
            $headerCell = $col . '1';
            $cellObj = $sheet->getCell($headerCell);
            $valTgl = $cellObj->getValue();

            // ================================
            // KONVERSI TANGGAL
            // ================================
            try {
                if (Date::isDateTime($cellObj)) {
                    $dateObj = Date::excelToDateTimeObject($valTgl);
                    $tgl = $dateObj->format('Y-m-d');
                } 
                elseif (!empty($valTgl) && !is_numeric($valTgl)) {
                    $timestamp = strtotime(str_replace('/', '-', $valTgl));
                    if ($timestamp === false) {
                        error_log("Format tanggal tidak valid: '$valTgl' di kolom $col");
                        continue;
                    }
                    $tgl = date('Y-m-d', $timestamp);
                }
                elseif (is_numeric($valTgl) && $valTgl > 25569) {
                    $dateObj = Date::excelToDateTimeObject($valTgl);
                    $tgl = $dateObj->format('Y-m-d');
                }
                else {
                    error_log("Tanggal tidak valid di kolom $col: '$valTgl'");
                    continue;
                }
            } catch (Exception $e) {
                error_log("Error parsing tanggal di kolom $col: " . $e->getMessage());
                continue;
            }

            $nilaiRaw = isset($row[$col]) ? trim($row[$col]) : '';
            $nilaiUpper = strtoupper($nilaiRaw);
            $isHariOff = isYellowCell($sheet, $cellAddress);

            $jamKerja = null;
            $ket = '';
            $isProcessed = false;

            // ================================
            // HANDLE M dan NEW (DELETE)
            // ================================
            if ($nilaiUpper === 'M' || $nilaiUpper === 'NEW') {
                $stmtCheckM = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
                $stmtCheckM->bind_param("is", $id_karyawan, $tgl);
                $stmtCheckM->execute();
                $stmtCheckM->store_result();
                $adaDataLama = $stmtCheckM->num_rows;
                $stmtCheckM->close();

                if ($adaDataLama > 0) {
                    $stmtDel = $koneksi->prepare("DELETE FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
                    $stmtDel->bind_param("is", $id_karyawan, $tgl);
                    if ($stmtDel->execute()) {
                        $deleteCount++;
                        $successCount++;
                        $debugLog[] = "✓ DELETE - NIK: $nik_karyawan | Tgl: $tgl | Cell: $nilaiUpper";
                    } else {
                        $errorCount++;
                        $debugLog[] = "✗ DELETE gagal: " . $stmtDel->error;
                    }
                    $stmtDel->close();
                }
                continue;
            }

            // ================================
            // 5. LOGIKA VALIDASI (FIXED)
            // ================================

            // PRIORITAS 1: Kode Khusus (SD, IZIN, CM, SAKIT, CUTI)
            $mapKet = [
                'SD' => 'SAKIT', 
                'IZIN' => 'IZIN', 
                'CM' => 'CUTI',
                'SAKIT' => 'SAKIT',
                'CUTI' => 'CUTI'
            ];
            
            if (isset($mapKet[$nilaiUpper])) {
                $jamKerja = '0'; // String untuk konsistensi
                $ket = $mapKet[$nilaiUpper];
                $isProcessed = true;
                $debugLog[] = "Proses: KODE KHUSUS - NIK: $nik_karyawan | Tgl: $tgl | Ket: $ket";
            }
            // PRIORITAS 2: Angka di Cell Kuning = Kerja di Hari OFF
            elseif ($isHariOff && $nilaiRaw !== '' && is_numeric(str_replace(',', '.', $nilaiRaw))) {
                $jamKerja = str_replace(',', '.', $nilaiRaw); // Simpan sebagai string
                $ket = 'OFF';
                $isProcessed = true;
                $debugLog[] = "Proses: KERJA DI HARI OFF - NIK: $nik_karyawan | Tgl: $tgl | Jam: $jamKerja";
            }
            // PRIORITAS 3: Cell Kuning Kosong atau Text "OFF" = OFF Penuh
            elseif ($isHariOff || $nilaiUpper === 'OFF') {
                $jamKerja = '0';
                $ket = 'OFF';
                $isProcessed = true;
                $debugLog[] = "Proses: HARI OFF - NIK: $nik_karyawan | Tgl: $tgl | Kuning: " . ($isHariOff ? 'Ya' : 'Tidak');
            }
            // PRIORITAS 4: Angka (Jam Kerja Normal)
            elseif ($nilaiRaw !== '' && is_numeric(str_replace(',', '.', $nilaiRaw))) {
                $jamKerja = str_replace(',', '.', $nilaiRaw);
                $ket = ''; // Kosong = kerja normal
                $isProcessed = true;
                $debugLog[] = "Proses: JAM KERJA - NIK: $nik_karyawan | Tgl: $tgl | Jam: $jamKerja";
            }
            // PRIORITAS 5: Cell Kosong di Hari Kerja = ALPHA
            elseif (!$isHariOff && $nilaiRaw === '') {
                $jamKerja = '0';
                $ket = 'ALPHA';
                $isProcessed = true;
                $debugLog[] = "Proses: ALPHA - NIK: $nik_karyawan | Tgl: $tgl";
            }

            // ================================
            // 6. EKSEKUSI DATABASE (FIXED)
            // ================================
            if ($isProcessed) {
                try {
                    // Check existing data
                    $stmtCheck = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
                    $stmtCheck->bind_param("is", $id_karyawan, $tgl);
                    $stmtCheck->execute();
                    $stmtCheck->store_result();
                    $ada = $stmtCheck->num_rows;
                    $stmtCheck->close();

                    if ($ada == 0) {
                        // INSERT
                        $stmt = $koneksi->prepare("INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen) VALUES (?,?,?,?)");
                        $stmt->bind_param("isss", $id_karyawan, $tgl, $jamKerja, $ket);
                        $action = "INSERT";
                    } else {
                        // UPDATE - FIXED: Urutan parameter sesuai query
                        $stmt = $koneksi->prepare("UPDATE tb_absen SET jam_kerja=?, ket_absen=? WHERE id_karyawan=? AND tgl_absen=?");
                        $stmt->bind_param("ssis", $jamKerja, $ket, $id_karyawan, $tgl);
                        $action = "UPDATE";
                    }
                    
                    if ($stmt->execute()) {
                        $successCount++;
                        if ($action == "INSERT") $insertCount++;
                        if ($action == "UPDATE") $updateCount++;
                        $debugLog[] = "✓ $action - NIK: $nik_karyawan | Tgl: $tgl | Jam: $jamKerja | Ket: $ket";
                    } else {
                        $errorCount++;
                        $debugLog[] = "✗ $action gagal: " . $stmt->error;
                        error_log("Error: " . $stmt->error . " | NIK: $nik_karyawan | Tgl: $tgl");
                    }
                    $stmt->close();
                    
                } catch (Exception $e) {
                    $errorCount++;
                    $debugLog[] = "✗ Exception: " . $e->getMessage();
                    error_log("DB error: " . $e->getMessage() . " | NIK: $nik_karyawan | Tgl: $tgl");
                }
            }
        }
    }

    // Response
    echo json_encode([
        'status' => 'success',
        'message' => "Berhasil memproses $successCount data absen",
        'details' => [
            'success' => $successCount,
            'insert' => $insertCount,
            'update' => $updateCount,
            'delete' => $deleteCount,
            'error' => $errorCount,
            'skipped' => $skipCount,
            'total_rows' => count($data) - 1
        ],
        'debug_log' => array_slice($debugLog, -100),
        'errors' => !empty($errorMessages) ? array_slice($errorMessages, 0, 10) : []
    ]);

} catch (Exception $e) {
    error_log("Import Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error sistem: ' . $e->getMessage()
    ]);
}
?>