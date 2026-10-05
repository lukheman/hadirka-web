<?php
ini_set('memory_limit', '-1'); // Hati-hati, gunakan secukupnya (misal '512M')
set_time_limit(0); // Unlimited execution time
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (!$koneksi) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']);
    exit;
}

if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan atau error upload']);
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

    // ================================
    // FUNGSI CEK WARNA (DIPERLUAS)
    // ================================
    function getCellColorInfo($sheet, $cell)
    {
        try {
            $style = $sheet->getStyle($cell)->getFill();
            $fillType = $style->getFillType();
            $bgColor = strtoupper($style->getStartColor()->getRGB());

            return [
                'color' => $bgColor,
                'fillType' => $fillType,
                'isYellow' => false
            ];
        } catch (Exception $e) {
            return ['color' => 'ERROR', 'fillType' => 'ERROR', 'isYellow' => false];
        }
    }

    function isYellowCell($sheet, $cell)
    {
        try {
            $style = $sheet->getStyle($cell)->getFill();
            $bgColor = strtoupper($style->getStartColor()->getRGB());
            $fillType = $style->getFillType();

            // Jika tidak ada fill, langsung false
            if (
                $fillType === Fill::FILL_NONE ||
                $bgColor === '000000' ||
                $bgColor === 'FFFFFF' ||
                $bgColor === ''
            ) {
                return false;
            }

            // List warna kuning DIPERLUAS
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
                'FFFFFFCC',    // Kuning sangat muda
                'FFC000',      // Kuning tua (tanpa FF prefix)
                'FFFE00',      // Kuning cerah
                'FFD700',      // Gold
                'FFEF00',      // Kuning neon
            ];

            return in_array($bgColor, $yellowColors);
        } catch (Exception $e) {
            return false;
        }
    }

    // ================================
    // FUNGSI AMBIL NILAI CELL (DIPERBAIKI)
    // ================================
    function getCellValue($sheet, $cell)
    {
        try {
            $cellObj = $sheet->getCell($cell);

            // Cek apakah cell adalah formula
            $isFormula = false;
            try {
                $formula = $cellObj->getValue();
                if (is_string($formula) && strpos($formula, '=') === 0) {
                    $isFormula = true;
                    // Ambil calculated value (hasil formula)
                    $value = $cellObj->getCalculatedValue();
                } else {
                    $value = $cellObj->getValue();
                }
            } catch (Exception $e) {
                $value = $cellObj->getValue();
            }

            // Pastikan null otomatis menjadi string kosong
            if ($value === null) {
                $value = '';
            }

            // Trim whitespace
            if (is_string($value)) {
                $value = trim($value);
                // Hapus non-breaking space (ASCII 160)
                $value = str_replace("\xc2\xa0", '', $value);
                $value = preg_replace('/\s+/', ' ', $value); // Normalisasi spasi
            }

            return [
                'value' => $value,
                'isFormula' => $isFormula,
                'dataType' => $cellObj->getDataType()
            ];
        } catch (Exception $e) {
            return [
                'value' => '',
                'isFormula' => false,
                'dataType' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    // ================================
    // DEFINISI KOLOM
    // ================================
    $colNIK = 'C';
    $startCol = 'D';
    $highestCol = $sheet->getHighestColumn();
    $highestRow = $sheet->getHighestRow();

    // Ambil kolom tanggal
    $colsTanggal = [];
    $startIdx = Coordinate::columnIndexFromString($startCol);
    $endIdx = Coordinate::columnIndexFromString($highestCol);
    for ($colIdx = $startIdx; $colIdx <= $endIdx; $colIdx++) {
        $currentCol = Coordinate::stringFromColumnIndex($colIdx);
        $cellValue = getCellValue($sheet, $currentCol . '1');
        if ($cellValue['value'] !== '') {
            $colsTanggal[] = $currentCol;
        }
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
    $anomalyLog = []; // Log khusus untuk kasus aneh

    $debugLog[] = "=== FILE INFO ===";
    $debugLog[] = "Total baris: $highestRow";
    $debugLog[] = "Kolom tanggal: " . implode(', ', $colsTanggal);

    // ================================
    // PREPARE SEKALI (di luar loop) + TRANSAKSI
    // Mencegah timeout (293 baris x 31 kolom = ~9000 query)
    // dan mencegah data setengah masuk saat script mati di tengah
    // ================================
    try {
        $stmtK = $koneksi->prepare("SELECT id_karyawan, nama_lengkap FROM tb_karyawan WHERE nik_karyawan=?");
        $stmtCheckM = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
        $stmtDel = $koneksi->prepare("DELETE FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
        $stmtCheck = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
        $stmtInsert = $koneksi->prepare("INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen) VALUES (?,?,?,?)");
        $stmtUpdate = $koneksi->prepare("UPDATE tb_absen SET jam_kerja=?, ket_absen=? WHERE id_karyawan=? AND tgl_absen=?");
    } catch (PDOException $e) {
        error_log('csv-absen prepare: ' . $e->getMessage());
        throw new Exception('Gagal menyiapkan database.');
    }
    $koneksi->beginTransaction();
    $missingNik = [];

    // ================================
    // LOOP PER BARIS
    // ================================
    for ($i = 2; $i <= $highestRow; $i++) {
        $nikData = getCellValue($sheet, $colNIK . $i);
        $nik_karyawan = (string) $nikData['value'];
        $nik_karyawan = str_replace(' ', '', $nik_karyawan);

        if (empty($nik_karyawan)) {
            $skipCount++;
            continue;
        }

        // Get ID Karyawan (fetch langsung: bila NIK tidak ada, $krow=false
        // sehingga tidak ada nilai baris sebelumnya yang bocor)
        $stmtK->execute([$nik_karyawan]);
        $krow = $stmtK->fetch();
        if (!$krow) {
            $errorMessages[] = "NIK $nik_karyawan tidak ditemukan";
            $missingNik[] = $nik_karyawan;
            $errorCount++;
            continue;
        }
        $id_karyawan = $krow['id_karyawan'];
        $nama_karyawan = $krow['nama_lengkap'];

        // Loop per kolom tanggal
        foreach ($colsTanggal as $col) {
            $cellAddress = $col . $i;
            $headerCell = $col . '1';

            // AMBIL TANGGAL HEADER
            $headerData = getCellValue($sheet, $headerCell);
            $valTgl = $headerData['value'];

            // Konversi tanggal
            $tgl = null;
            try {
                $cellObj = $sheet->getCell($headerCell);
                if (Date::isDateTime($cellObj)) {
                    $dateObj = Date::excelToDateTimeObject($valTgl);
                    $tgl = $dateObj->format('Y-m-d');
                } elseif (!empty($valTgl) && !is_numeric($valTgl)) {
                    $timestamp = strtotime(str_replace('/', '-', (string) $valTgl));
                    if ($timestamp !== false) {
                        $tgl = date('Y-m-d', $timestamp);
                    }
                } elseif (is_numeric($valTgl) && $valTgl > 25569) {
                    $dateObj = Date::excelToDateTimeObject($valTgl);
                    $tgl = $dateObj->format('Y-m-d');
                }
            } catch (Exception $e) {
                // Skip kolom ini
                continue;
            }

            if (!$tgl) continue;

            // AMBIL NILAI CELL DENGAN DETAIL
            $cellData = getCellValue($sheet, $cellAddress);
            $nilaiRaw = trim((string)$cellData['value']);
            $nilaiUpper = strtoupper($nilaiRaw);
            $isHariOff = isYellowCell($sheet, $cellAddress);
            $colorInfo = getCellColorInfo($sheet, $cellAddress);

            $jamKerja = 0;
            $ket = '';
            $isProcessed = false;
            $reason = ''; // Alasan kenapa diproses/tidak

            // ================================
            // LOGIKA VALIDASI (DENGAN LOGGING)
            // ================================
            $mapKet = [
                'SD' => 'SAKIT',
                'IZIN' => 'IZIN',
                'CM' => 'CUTI',
                'CT' => 'CUTI',
                'SAKIT' => 'SAKIT',
                'CUTI' => 'CUTI',
                'R' => 'RESIGN',
                'RESIGN' => 'RESIGN',
                'NEW' => 'NEW'
            ];

            // ================================
            // HANDLE M
            // ================================
            if ($nilaiUpper === 'M') {
                $stmtCheckM->execute([$id_karyawan, $tgl]);
                $adaDataLama = $stmtCheckM->fetch() ? 1 : 0;

                if ($adaDataLama > 0) {
                    $stmtDel->execute([$id_karyawan, $tgl]);
                    $deleteCount++;
                    $successCount++;
                }
                continue;
            }

            // PRIORITAS 1: Kode Khusus
            if (isset($mapKet[$nilaiUpper])) {
                $ket = $mapKet[$nilaiUpper];
                $jamKerja = 0;
                $isProcessed = true;
                $reason = "Kode khusus: $ket";
            }
            // PRIORITAS 2: Kuning + Angka = Kerja di Hari OFF
            elseif ($isHariOff && $nilaiRaw !== '' && is_numeric(str_replace(',', '.', $nilaiRaw))) {
                $jamKerja = floatval(str_replace(',', '.', $nilaiRaw));
                $ket = 'OFF';
                $isProcessed = true;
                $reason = "SPL: Kerja di hari OFF (kuning + angka)";
            }
            // PRIORITAS 3: Hari OFF (Kuning atau text OFF)
            elseif ($isHariOff || $nilaiUpper === 'OFF') {
                $jamKerja = 0;
                $ket = 'OFF';
                $isProcessed = true;
                $reason = $isHariOff ? "Hari OFF (cell kuning)" : "Hari OFF (text)";
            }
            // PRIORITAS 4: Angka (Jam Kerja Normal)
            elseif ($nilaiRaw !== '' && is_numeric(str_replace(',', '.', $nilaiRaw))) {
                $jamKerja = floatval(str_replace(',', '.', $nilaiRaw));
                $ket = '';
                $isProcessed = true;
                $reason = "Jam kerja normal: $jamKerja";
            }
            // PRIORITAS 5: Cell Kosong = ALPHA
            elseif (!$isHariOff && $nilaiRaw === '') {
                $jamKerja = 0;
                $ket = 'ALPHA';
                $isProcessed = true;
                $reason = "Cell kosong = Alpha";
            } else {
                $reason = "TIDAK DIPROSES - Cell: '$nilaiRaw' | Kuning: " . ($isHariOff ? 'Ya' : 'Tidak');

                // LOG ANOMALI untuk debugging
                $anomalyLog[] = [
                    'nik' => $nik_karyawan,
                    'nama' => $nama_karyawan,
                    'tanggal' => $tgl,
                    'cell_address' => $cellAddress,
                    'cell_value' => $nilaiRaw,
                    'cell_upper' => $nilaiUpper,
                    'is_yellow' => $isHariOff,
                    'color_code' => $colorInfo['color'],
                    'fill_type' => $colorInfo['fillType'],
                    'is_formula' => $cellData['isFormula'],
                    'data_type' => $cellData['dataType'],
                    'reason' => $reason
                ];
            }

            // ================================
            // EKSEKUSI DATABASE
            // ================================
            if ($isProcessed) {
                try {
                    // Check existing
                    $stmtCheck->execute([$id_karyawan, $tgl]);
                    $ada = $stmtCheck->fetch() ? 1 : 0;

                    if ($ada == 0) {
                        // INSERT
                        $action = "INSERT";
                        $stmtInsert->execute([$id_karyawan, $tgl, $jamKerja, $ket]);
                    } else {
                        // UPDATE
                        $action = "UPDATE";
                        $stmtUpdate->execute([$jamKerja, $ket, $id_karyawan, $tgl]);
                    }

                    $successCount++;
                    if ($action == "INSERT") $insertCount++;
                    if ($action == "UPDATE") $updateCount++;

                    // Log detail untuk tracking
                    $debugLog[] = "✓ $action | NIK: $nik_karyawan | Tgl: $tgl | Jam: $jamKerja | Ket: '$ket' | Alasan: $reason";
                } catch (PDOException $e) {
                    error_log("csv-absen DB NIK $nik_karyawan tgl $tgl: " . $e->getMessage());
                    $errorCount++;
                    $errorMessages[] = "Database gagal NIK $nik_karyawan tgl $tgl.";
                } catch (Exception $e) {
                    $errorCount++;
                    $errorMessages[] = "Exception NIK $nik_karyawan tgl $tgl: " . $e->getMessage();
                }
            }
        }
    }

    // Response dengan anomaly log
    $koneksi->commit();
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
            'missing_nik' => count($missingNik),
            'total_rows' => $highestRow - 1
        ],
        'missing_nik_list' => array_slice(array_values(array_unique($missingNik)), 0, 50),
        'debug_log' => array_slice($debugLog, -100),
        'anomaly_log' => $anomalyLog, // LOG KHUSUS UNTUK CEK MASALAH
        'errors' => array_slice($errorMessages, 0, 20)
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    if (isset($koneksi) && $koneksi instanceof PDO && $koneksi->inTransaction()) {
        try { $koneksi->rollBack(); } catch (Throwable $t) {}
    }
    error_log("Import Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error sistem: ' . $e->getMessage()
    ]);
}
