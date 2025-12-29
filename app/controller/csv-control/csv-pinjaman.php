<?php
// Matikan output selain JSON untuk mencegah error "Unexpected Token"
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require '../../config/koneksi.php'; 
require '../../../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

try {
    // Pastikan request adalah POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Metode request tidak valid.");
    }

    if (!isset($_FILES['file_csv_pinjaman'])) {
        throw new Exception("File tidak ditemukan atau belum dipilih.");
    }

    $fileTmpPath = $_FILES['file_csv_pinjaman']['tmp_name'];
    $spreadsheet = IOFactory::load($fileTmpPath);
    $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    $startRow = 2; // Lewati header

    for ($i = $startRow; $i <= count($data); $i++) {
        $row = $data[$i];
        
        $nik = trim($row['B'] ?? '');
        if (empty($nik)) continue;

        // 1. Cari ID Karyawan
        $stmtK = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = ?");
        $stmtK->bind_param("s", $nik);
        $stmtK->execute();
        $resK = $stmtK->get_result()->fetch_assoc();
        $stmtK->close();

        if (!$resK) {
            $errors[] = "Baris $i: NIK $nik tidak ada di database.";
            $errorCount++;
            continue;
        }

        $id_karyawan = $resK['id_karyawan'];

        // 2. Bersihkan Nominal (Hapus titik/koma/simbol)
        $nominal = trim($row['E'] ?? '0');
        $nominal_pinjaman = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $nominal));

        // 3. Konversi Tanggal Pinjaman (Kolom F)
        $tglPinjamRaw = $row['F'] ?? '';
        $tgl_pinjaman = null;
        if (!empty($tglPinjamRaw)) {
            if (is_numeric($tglPinjamRaw)) {
                $tgl_pinjaman = Date::excelToDateTimeObject($tglPinjamRaw)->format('Y-m-d');
            } else {
                $tgl_pinjaman = date('Y-m-d', strtotime($tglPinjamRaw));
            }
        }

        // 4. Konversi Tanggal Bayar (Kolom G) - Opsional
        $tglBayarRaw = $row['G'] ?? '';
        $tgl_bayar = null;
        if (!empty($tglBayarRaw)) {
            if (is_numeric($tglBayarRaw)) {
                $tgl_bayar = Date::excelToDateTimeObject($tglBayarRaw)->format('Y-m-d');
            } else {
                $tgl_bayar = date('Y-m-d', strtotime($tglBayarRaw));
            }
        }

        $ket = trim($row['H'] ?? '');

        // 5. Cek Duplikat atau Update
        $stmtCheck = $koneksi->prepare("SELECT id_pinjaman FROM tb_pinjaman WHERE id_karyawan = ? AND tgl_bayar_pinjaman = ? and ket_pinjaman = ? ");
        $stmtCheck->bind_param("iss", $id_karyawan, $tgl_bayar, $ket);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        $isDuplicate = $resCheck->num_rows > 0;
        $stmtCheck->close();

        if ($isDuplicate) {
            $stmtUpd = $koneksi->prepare("UPDATE tb_pinjaman SET nominal_pinjaman=?, tgl_bayar_pinjaman=?, ket_pinjaman=? WHERE id_karyawan=? AND tgl_pinjaman=?");
            $stmtUpd->bind_param("dssis", $nominal_pinjaman, $tgl_bayar, $ket, $id_karyawan, $tgl_pinjaman);
            $stmtUpd->execute();
            $stmtUpd->close();
        } else {
            $stmtIns = $koneksi->prepare("INSERT INTO tb_pinjaman (id_karyawan, nominal_pinjaman, tgl_pinjaman, tgl_bayar_pinjaman, ket_pinjaman) VALUES (?,?,?,?,?)");
            $stmtIns->bind_param("idsss", $id_karyawan, $nominal_pinjaman, $tgl_pinjaman, $tgl_bayar, $ket);
            $stmtIns->execute();
            $stmtIns->close();
        }
        $successCount++;
    }

    echo json_encode([
        'status' => ($errorCount > 0 && $successCount == 0) ? 'error' : (($errorCount > 0) ? 'warning' : 'success'),
        'message' => "Selesai. Berhasil: $successCount, Gagal: $errorCount. " . implode(" ", array_slice($errors, 0, 2))
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
}