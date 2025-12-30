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

// DEBUGGING: Cek koneksi database
if (!$koneksi) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']);
    exit;
}

if (!isset($_FILES['file_csv'])) {
    echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan']);
    exit;
}

if ($_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Error upload file: ' . $_FILES['file_csv']['error']]);
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
    // FUNGSI CEK WARNA
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
    // DEFINISI KOLOM
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
    
    // DEBUGGING: Info file
    $debugLog[] = "=== FILE INFO ===";
    $debugLog[] = "Total baris: " . count($data);
    $debugLog[] = "Kolom tanggal: " . implode(', ', $colsTanggal);
    $debugLog[] = "Highest column: $highestCol";

    // ================================
    // LOOP PER BARIS
    // ================================
    $processedRows = 0;
    for ($i = 2; $i <= count($data); $i++) {
        $row = $data[$i];
        $nik_karyawan = isset($row[$colNIK]) ? trim($row[$colNIK]) : '';
        $nik_karyawan = str_replace(' ', '', $nik_karyawan);

        // DEBUGGING: Log setiap baris
        $debugLog[] = "\n=== BARIS $i ===";
        $debugLog[] = "NIK Raw: " . (isset($row[$colNIK]) ? "'{$row[$colNIK]}'" : 'NULL');
        $debugLog[] = "NIK Cleaned: '$nik_karyawan'";

        if (empty($nik_karyawan)) {
            $debugLog[] = "SKIP: NIK kosong";
            $skipCount++;
            continue;
        }

        // Get ID Karyawan dengan error handling
        $stmtK = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan=?");
        if (!$stmtK) {
            $debugLog[] = "ERROR: Prepare statement gagal - " . $koneksi->error;
            $errorCount++;
            continue;
        }
        
        $stmtK->bind_param("s", $nik_karyawan);
        $stmtK->execute();
        $stmtK->bind_result($id_karyawan);
        $stmtK->fetch();
        $stmtK->close();

        $debugLog[] = "ID Karyawan: " . ($id_karyawan ? $id_karyawan : 'TIDAK DITEMUKAN');

        if (!$id_karyawan) {
            $errorMessages[] = "NIK $nik_karyawan tidak ditemukan di database";
            $debugLog[] = "ERROR: NIK tidak ada di tb_karyawan";
            $errorCount++;
            continue;
        }

        $processedRows++;
        
        // Loop per kolom tanggal
        foreach ($colsTanggal as $col) {
            $cellAddress = $col . $i;
            $headerCell = $col . '1';
            $cellObj = $sheet->getCell($headerCell);
            $valTgl = $cellObj->getValue();

            // ================================
            // KONVERSI TANGGAL
            // ================================
            $tgl = null;
            try {
                if (Date::isDateTime($cellObj)) {
                    $dateObj = Date::excelToDateTimeObject($valTgl);
                    $tgl = $dateObj->format('Y-m-d');
                } 
                elseif (!empty($valTgl) && !is_numeric($valTgl)) {
                    $timestamp = strtotime(str_replace('/', '-', $valTgl));
                    if ($timestamp !== false) {
                        $tgl = date('Y-m-d', $timestamp);
                    }
                }
                elseif (is_numeric($valTgl) && $valTgl > 25569) {
                    $dateObj = Date::excelToDateTimeObject($valTgl);
                    $tgl = $dateObj->format('Y-m-d');
                }
            } catch (Exception $e) {
                $debugLog[] = "ERROR parsing tanggal di $col: " . $e->getMessage();
            }

            if (!$tgl) {
                $debugLog[] = "SKIP kolom $col: Tanggal tidak valid";
                continue;
            }

            $nilaiRaw = isset($row[$col]) ? trim($row[$col]) : '';
            $nilaiUpper = strtoupper($nilaiRaw);
            $isHariOff = isYellowCell($sheet, $cellAddress);

            $debugLog[] = "  Kolom $col | Tgl: $tgl | Cell: '$nilaiRaw' | Kuning: " . ($isHariOff ? 'YA' : 'TIDAK');

            $jamKerja = null;
            $ket = '';
            $isProcessed = false;

            // ================================
            // HANDLE M dan NEW
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
                        $debugLog[] = "  ✓ DELETE sukses";
                    } else {
                        $errorCount++;
                        $debugLog[] = "  ✗ DELETE gagal: " . $stmtDel->error;
                    }
                    $stmtDel->close();
                }
                continue;
            }

            // ================================
            // LOGIKA VALIDASI
            // ================================
            $mapKet = [
                'SD' => 'SAKIT', 
                'IZIN' => 'IZIN', 
                'CM' => 'CUTI',
                'SAKIT' => 'SAKIT',
                'CUTI' => 'CUTI'
            ];
            
            if (isset($mapKet[$nilaiUpper])) {
                $jamKerja = 0;
                $ket = $mapKet[$nilaiUpper];
                $isProcessed = true;
                $debugLog[] = "  → Match: KODE KHUSUS ($ket)";
            }
            elseif ($isHariOff && $nilaiRaw !== '' && is_numeric(str_replace(',', '.', $nilaiRaw))) {
                $jamKerja = floatval(str_replace(',', '.', $nilaiRaw));
                $ket = 'OFF';
                $isProcessed = true;
                $debugLog[] = "  → Match: KERJA DI HARI OFF (Jam: $jamKerja)";
            }
            elseif ($isHariOff || $nilaiUpper === 'OFF') {
                $jamKerja = 0;
                $ket = 'OFF';
                $isProcessed = true;
                $debugLog[] = "  → Match: HARI OFF";
            }
            elseif ($nilaiRaw !== '' && is_numeric(str_replace(',', '.', $nilaiRaw))) {
                $jamKerja = floatval(str_replace(',', '.', $nilaiRaw));
                $ket = '';
                $isProcessed = true;
                $debugLog[] = "  → Match: JAM KERJA ($jamKerja)";
            }
            elseif (!$isHariOff && $nilaiRaw === '') {
                $jamKerja = 0;
                $ket = 'ALPHA';
                $isProcessed = true;
                $debugLog[] = "  → Match: ALPHA";
            } else {
                $debugLog[] = "  → NO MATCH - Skip";
            }

            // ================================
            // EKSEKUSI DATABASE
            // ================================
            if ($isProcessed) {
                $debugLog[] = "  → Akan eksekusi: Jam=$jamKerja, Ket='$ket'";
                
                try {
                    // Check existing
                    $stmtCheck = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
                    if (!$stmtCheck) {
                        throw new Exception("Prepare check failed: " . $koneksi->error);
                    }
                    
                    $stmtCheck->bind_param("is", $id_karyawan, $tgl);
                    $stmtCheck->execute();
                    $stmtCheck->store_result();
                    $ada = $stmtCheck->num_rows;
                    $stmtCheck->close();

                    $debugLog[] = "  → Data existing: " . ($ada > 0 ? "ADA (UPDATE)" : "TIDAK ADA (INSERT)");

                    if ($ada == 0) {
                        // INSERT
                        $stmt = $koneksi->prepare("INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen) VALUES (?,?,?,?)");
                        if (!$stmt) {
                            throw new Exception("Prepare INSERT failed: " . $koneksi->error);
                        }
                        
                        // PENTING: Cek tipe kolom jam_kerja di database (DECIMAL, VARCHAR, atau INT?)
                        // Gunakan "d" untuk DECIMAL/FLOAT, "s" untuk VARCHAR
                        $stmt->bind_param("isds", $id_karyawan, $tgl, $jamKerja, $ket);
                        $action = "INSERT";
                    } else {
                        // UPDATE
                        $stmt = $koneksi->prepare("UPDATE tb_absen SET jam_kerja=?, ket_absen=? WHERE id_karyawan=? AND tgl_absen=?");
                        if (!$stmt) {
                            throw new Exception("Prepare UPDATE failed: " . $koneksi->error);
                        }
                        
                        $stmt->bind_param("dsis", $jamKerja, $ket, $id_karyawan, $tgl);
                        $action = "UPDATE";
                    }
                    
                    $debugLog[] = "  → Execute $action...";
                    
                    if ($stmt->execute()) {
                        $successCount++;
                        if ($action == "INSERT") $insertCount++;
                        if ($action == "UPDATE") $updateCount++;
                        $debugLog[] = "  ✓ $action SUKSES!";
                    } else {
                        $errorCount++;
                        $debugLog[] = "  ✗ $action GAGAL: " . $stmt->error;
                        $errorMessages[] = "$action gagal untuk NIK $nik_karyawan tgl $tgl: " . $stmt->error;
                    }
                    $stmt->close();
                    
                } catch (Exception $e) {
                    $errorCount++;
                    $debugLog[] = "  ✗ EXCEPTION: " . $e->getMessage();
                    $errorMessages[] = "Exception untuk NIK $nik_karyawan tgl $tgl: " . $e->getMessage();
                }
            }
        }
    }

    // Response
    echo json_encode([
        'status' => $successCount > 0 ? 'success' : 'error',
        'message' => $successCount > 0 ? "Berhasil memproses $successCount data" : "Tidak ada data yang berhasil diproses",
        'details' => [
            'success' => $successCount,
            'insert' => $insertCount,
            'update' => $updateCount,
            'delete' => $deleteCount,
            'error' => $errorCount,
            'skipped' => $skipCount,
            'total_rows' => count($data) - 1,
            'processed_rows' => $processedRows
        ],
        'debug_log' => $debugLog, // Tampilkan SEMUA log
        'errors' => $errorMessages
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Import Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error sistem: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>