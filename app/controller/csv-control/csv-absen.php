<?php
header("Content-Type: application/json");
// Sembunyikan error HTML agar tidak merusak format JSON, tapi tetap catat di log
error_reporting(E_ALL);
ini_set('display_errors', 0); 
ini_set('log_errors', 1);

require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// ================================
// 1. CEK FILE CSV / EXCEL
// ================================
if (!isset($_FILES['file_csv'])) {
    echo json_encode(['status' => 'error', 'message' => 'File CSV/Excel tidak ditemukan']);
    exit;
}

if ($_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Error saat upload file']);
    exit;
}

try {
    $fileTmpPath = $_FILES['file_csv']['tmp_name'];
    
    // Validasi file exists
    if (!file_exists($fileTmpPath)) {
        throw new Exception('File temporary tidak ditemukan');
    }
    
    $reader = IOFactory::createReaderForFile($fileTmpPath);
    // Kita butuh formatting & styles untuk cek warna kuning
    $reader->setReadDataOnly(false); 
    $spreadsheet = $reader->load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);

    // ================================
    // 2. FUNGSI CEK WARNA CELL
    // ================================
    function getCellColor($sheet, $cell) {
        try {
            $style = $sheet->getStyle($cell)->getFill();
            $bgColor = strtoupper($style->getStartColor()->getRGB());
            
            // Cek jika fill type tidak ada atau warna default putih/hitam kosong
            if ($style->getFillType() === Fill::FILL_NONE || 
                $bgColor === '000000' || 
                $bgColor === 'FFFFFF' || 
                $bgColor === '') {
                return null;
            }
            return $bgColor;
        } catch (Exception $e) {
            return null;
        }
    }

    function isYellowCell($sheet, $cell) {
        $color = getCellColor($sheet, $cell);
        if (!$color) return false;
        
        // Daftar kode warna kuning di Excel (diperluas)
        $yellowColors = [
            'FFFF00',      // Kuning standar
            'FFFF99',      // Kuning muda
            'FFFFCC',      // Kuning pucat
            'FFFFE0',      // Kuning sangat pucat
            'FFFACD',      // Lemon chiffon
            'FFF200',      // Kuning terang
            'FFED00',      // Kuning emas
            'FFFF0000',    // Kuning dengan alpha
            'FFFFFF00',    // Kuning penuh
            'FFFFC000',    // Emas/kuning tua
            'FFFFCC00',    // Kuning medium
            'FFFFE599',    // Kuning Excel theme
            'FFFFFFCC'     // Kuning sangat muda
        ];
        
        return in_array($color, $yellowColors);
    }

    // ================================
    // 3. DEFINISI KOLOM
    // ================================
    $colNIK = 'C';
    $startCol = 'D';
    $highestCol = $sheet->getHighestColumn();
    $colsTanggal = [];

    // Ambil semua kolom yang ada tanggalnya (Baris 1)
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
        throw new Exception('Tidak ada kolom tanggal yang ditemukan di file Excel');
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
    // 4. LOOP PER BARIS KARYAWAN
    // ================================
    for ($i = 2; $i <= count($data); $i++) {
        $row = $data[$i];
        $nik_karyawan = isset($row[$colNIK]) ? trim($row[$colNIK]) : '';
        
        // *** PERBAIKAN 1: Hapus semua spasi dari NIK ***
        $nik_karyawan = str_replace(' ', '', $nik_karyawan);

        // Skip baris kosong
        if (empty($nik_karyawan)) {
            $skipCount++;
            continue;
        }

        // Ambil ID Karyawan
        $stmtK = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan=?");
        $stmtK->bind_param("s", $nik_karyawan);
        $stmtK->execute();
        $stmtK->bind_result($id_karyawan);
        $stmtK->fetch();
        $stmtK->close();

        if (!$id_karyawan) {
            $errorMessages[] = "NIK $nik_karyawan tidak ditemukan di database";
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
            // KONVERSI TANGGAL HEADER
            // ================================
            try {
                // Cek apakah cell header adalah format date Excel
                if (Date::isDateTime($cellObj)) {
                    // Konversi dari Excel serial date
                    $dateObj = Date::excelToDateTimeObject($valTgl);
                    $tgl = $dateObj->format('Y-m-d');
                } 
                // Cek apakah string tanggal yang bisa di-parse
                elseif (!empty($valTgl) && !is_numeric($valTgl)) {
                    // Coba parse berbagai format tanggal
                    $timestamp = strtotime($valTgl);
                    if ($timestamp === false) {
                        // Coba format Indonesia: 01/12/2024 atau 01-12-2024
                        $valTgl = str_replace('/', '-', $valTgl);
                        $timestamp = strtotime($valTgl);
                    }
                    
                    if ($timestamp === false) {
                        error_log("Format tanggal tidak valid: '$valTgl' di kolom $col baris 1");
                        continue; // Skip kolom ini
                    }
                    $tgl = date('Y-m-d', $timestamp);
                }
                // Jika numeric tapi bukan Excel date (edge case)
                elseif (is_numeric($valTgl) && $valTgl > 25569) { // 25569 = 1 Jan 1970 di Excel
                    $dateObj = Date::excelToDateTimeObject($valTgl);
                    $tgl = $dateObj->format('Y-m-d');
                }
                else {
                    error_log("Tanggal tidak valid atau kosong di kolom $col baris 1: '$valTgl'");
                    continue; // Skip kolom ini
                }
            } catch (Exception $e) {
                error_log("Error parsing tanggal di kolom $col: " . $e->getMessage());
                continue; // Skip kolom ini jika gagal parse
            }

            $nilaiRaw = isset($row[$col]) ? trim($row[$col]) : '';
            $nilaiUpper = strtoupper($nilaiRaw);
            $isHariOff = isYellowCell($sheet, $cellAddress);

            $jamKerja = null;
            $ket = '';
            $isProcessed = false;

            // ================================
            // HANDLE "M" dan "NEW" (SKIP/DELETE)
            // ================================
            if ($nilaiUpper === 'M' || $nilaiUpper === 'NEW') {
                // Cek apakah data lama ada di database
                $stmtCheckM = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
                $stmtCheckM->bind_param("is", $id_karyawan, $tgl);
                $stmtCheckM->execute();
                $stmtCheckM->store_result();
                $adaDataLama = $stmtCheckM->num_rows;
                $stmtCheckM->close();

                // DEBUG LOG
                $debugLog[] = "NIK: $nik_karyawan | Tgl: $tgl | Cell: $nilaiUpper | Data Lama: " . ($adaDataLama > 0 ? 'ADA' : 'TIDAK ADA');

                // Jika ada data lama, HAPUS/RESET
                if ($adaDataLama > 0) {
                    $stmtDel = $koneksi->prepare("DELETE FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
                    $stmtDel->bind_param("is", $id_karyawan, $tgl);
                    if ($stmtDel->execute()) {
                        $deleteCount++;
                        $successCount++;
                        $debugLog[] = "✓ DELETE sukses untuk NIK: $nik_karyawan, Tgl: $tgl (Cell: $nilaiUpper)";
                    } else {
                        $errorCount++;
                        $debugLog[] = "✗ DELETE gagal: " . $stmtDel->error;
                    }
                    $stmtDel->close();
                }
                
                continue; // Lanjut ke tanggal berikutnya
            }

            // ================================
            // 5. LOGIKA VALIDASI (DIPERBAIKI)
            // ================================

            // PRIORITAS 1: Cek Kode Khusus (SD, IZIN, CM)
            $mapKet = [
                'SD' => 'SAKIT', 
                'IZIN' => 'IZIN', 
                'CM' => 'CUTI',
                'SAKIT' => 'SAKIT',
                'CUTI' => 'CUTI'
            ];
            
            if (isset($mapKet[$nilaiUpper])) {
                // Sakit/Izin/Cuti = 0 jam kerja
                $jamKerja = $mapKet[$nilaiUpper]; 
                $ket = $mapKet[$nilaiUpper];
                $isProcessed = true;
                $debugLog[] = "NIK: $nik_karyawan | Tgl: $tgl | Cell: $nilaiRaw | Proses: KODE KHUSUS ($ket)";
            }
            // *** PERBAIKAN 2: Cek Kuning + Angka = Jam Kerja dengan Ket OFF ***
            elseif ($isHariOff && $nilaiRaw !== '' && is_numeric(str_replace(',', '.', $nilaiRaw))) {
                // Cell kuning dengan angka = tetap catat jam kerja tapi ket OFF
                $jamKerja = floatval(str_replace(',', '.', $nilaiRaw));
                $ket = 'OFF';
                $isProcessed = true;
                $debugLog[] = "NIK: $nik_karyawan | Tgl: $tgl | Cell: $nilaiRaw | Proses: JAM KERJA HARI OFF (Kuning + Angka)";
            }
            // PRIORITAS 2: Cek Hari OFF (Kuning ATAU teks "OFF")
            elseif ($isHariOff || $nilaiUpper === 'OFF') {
                // OFF selalu 0 jam kerja, tidak peduli isi cell
                $jamKerja = 0;
                $ket = 'OFF';
                $isProcessed = true;
                $debugLog[] = "NIK: $nik_karyawan | Tgl: $tgl | Cell: $nilaiRaw | Proses: OFF (Kuning: " . ($isHariOff ? 'Ya' : 'Tidak') . ")";
            }
            // PRIORITAS 3: Cek Angka (Jam Kerja Normal)
            elseif ($nilaiRaw !== '' && is_numeric(str_replace(',', '.', $nilaiRaw))) {
                // Konversi koma ke titik untuk desimal
                $jamKerja = floatval(str_replace(',', '.', $nilaiRaw));
                $ket = ''; // Kerja normal = keterangan kosong
                $isProcessed = true;
                $debugLog[] = "NIK: $nik_karyawan | Tgl: $tgl | Cell: $nilaiRaw | Proses: JAM KERJA ($jamKerja)";
            }
            // PRIORITAS 4: Cell Kosong di Hari Kerja = ALPHA
            elseif (!$isHariOff && trim($nilaiRaw) === '') {
                $jamKerja = 0;
                $ket = 'ALPHA'; // Mangkir/Tidak hadir
                $isProcessed = true;
                $debugLog[] = "NIK: $nik_karyawan | Tgl: $tgl | Cell: (kosong) | Proses: ALPHA";
            }

            // ================================
            // 6. EKSEKUSI DATABASE
            // ================================
            if ($isProcessed) {
                try {
                    // Check Exist
                    $stmtCheck = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
                    $stmtCheck->bind_param("is", $id_karyawan, $tgl);
                    $stmtCheck->execute();
                    $stmtCheck->store_result();
                    $ada = $stmtCheck->num_rows;
                    $stmtCheck->close();

                    if ($ada == 0) {
                        // INSERT
                        $stmt = $koneksi->prepare("INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen) VALUES (?,?,?,?)");
                        $stmt->bind_param("isds", $id_karyawan, $tgl, $jamKerja, $ket);
                        $action = "INSERT";
                    } else {
                        // UPDATE
                        $stmt = $koneksi->prepare("UPDATE tb_absen SET jam_kerja=?, ket_absen=? WHERE id_karyawan=? AND tgl_absen=?");
                        $stmt->bind_param("dsis", $jamKerja, $ket, $id_karyawan, $tgl);
                        $action = "UPDATE";
                    }
                    
                    if ($stmt->execute()) {
                        $successCount++;
                        if ($action == "INSERT") $insertCount++;
                        if ($action == "UPDATE") $updateCount++;
                        $debugLog[] = "✓ $action sukses - NIK: $nik_karyawan | Tgl: $tgl | Jam: $jamKerja | Ket: $ket";
                    } else {
                        $errorCount++;
                        $debugLog[] = "✗ $action gagal: " . $stmt->error . " - NIK: $nik_karyawan | Tgl: $tgl";
                        error_log("Error execute: " . $stmt->error . " | NIK: $nik_karyawan | Tgl: $tgl");
                    }
                    $stmt->close();
                    
                } catch (Exception $e) {
                    $errorCount++;
                    $debugLog[] = "✗ Exception: " . $e->getMessage() . " - NIK: $nik_karyawan | Tgl: $tgl";
                    error_log("Database error: " . $e->getMessage() . " | NIK: $nik_karyawan | Tgl: $tgl");
                }
            } else {
                // Cell tidak diproses (bukan M, bukan angka, bukan kode khusus)
                $debugLog[] = "⊘ SKIP - NIK: $nik_karyawan | Tgl: $tgl | Cell: '$nilaiRaw' | Tidak memenuhi kriteria";
            }
        }
    }

    // Response
    $response = [
        'status' => 'success',
        'message' => "Berhasil memproses $successCount data absen.",
        'details' => [
            'success' => $successCount,
            'insert' => $insertCount,
            'update' => $updateCount,
            'delete' => $deleteCount,
            'error' => $errorCount,
            'skipped' => $skipCount,
            'total_rows' => count($data) - 1 // -1 untuk header
        ],
        'debug_log' => array_slice($debugLog, -50) // 50 log terakhir
    ];

    if (!empty($errorMessages)) {
        $response['errors'] = array_slice($errorMessages, 0, 10); // Max 10 error messages
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Import Absen Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
}
?>