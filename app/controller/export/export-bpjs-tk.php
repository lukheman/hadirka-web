<?php
/**
 * ============================================
 * EXPORT EXCEL - DATA BPJS KETENAGAKERJAAN
 * ============================================
 */

require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Ambil parameter filter dari URL
$siteFilter = isset($_GET['site']) ? $_GET['site'] : '';
$jabatanFilter = isset($_GET['jabatan']) ? $_GET['jabatan'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : ''; // Ini Status BPJS
$statusKarFilter = isset($_GET['status_kar']) ? $_GET['status_kar'] : 'AKTIF'; // Ini Status Karyawan

$where = [];

// Logika Status Karyawan
if ($statusKarFilter === 'AKTIF') {
    $where[] = "k.status_karyawan = 'Aktif'";
} else {
    $where[] = "k.status_karyawan != 'Aktif'";
}

// Logika Filter Lainnya
if ($siteFilter !== '') $where[] = "s.nama_site = '" . $koneksi->real_escape_string($siteFilter) . "'";
if ($jabatanFilter !== '') $where[] = "j.nama_jabatan = '" . $koneksi->real_escape_string($jabatanFilter) . "'";
if ($statusFilter === 'SUDAH') $where[] = "(i.bpjs_tk IS NOT NULL AND TRIM(i.bpjs_tk) != '' AND TRIM(i.bpjs_tk) != '-')";
if ($statusFilter === 'BELUM') $where[] = "(i.bpjs_tk IS NULL OR TRIM(i.bpjs_tk) = '' OR TRIM(i.bpjs_tk) = '-')";

$whereClause = implode(" AND ", $where);

// UBAHAN 1: Tambahkan i.nik_ktp pada query SELECT
$sql = "SELECT k.nik_karyawan, k.nama_lengkap, s.nama_site, j.nama_jabatan, i.bpjs_tk, i.nik_ktp 
        FROM tb_karyawan k
        LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_site s ON kr.id_site = s.id_site
        LEFT JOIN tb_jabatan j ON kr.id_jabatan = j.id_jabatan
        WHERE $whereClause
        ORDER BY k.nama_lengkap ASC";

$result = $koneksi->query($sql);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data BPJS TK');

// UBAHAN 2: Sesuaikan dan geser Header Kolom
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'ID Karyawan');
$sheet->setCellValue('C1', 'Nama Lengkap');
$sheet->setCellValue('D1', 'NIK KTP'); // Kolom Baru
$sheet->setCellValue('E1', 'Site');    // Bergeser dari D ke E
$sheet->setCellValue('F1', 'Jabatan'); // Bergeser dari E ke F
$sheet->setCellValue('G1', 'No BPJS Ketenagakerjaan'); // Bergeser dari F ke G

// Data
$rowNum = 2;
$no = 1;
while ($row = $result->fetch_assoc()) {
    $bpjs = ($row['bpjs_tk'] && trim($row['bpjs_tk']) !== '' && trim($row['bpjs_tk']) !== '-') ? $row['bpjs_tk'] : 'BELUM TERDAFTAR';
    
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValueExplicit('B' . $rowNum, $row['nik_karyawan'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $row['nama_lengkap']);
    
    // UBAHAN 3: Masukkan data NIK KTP dengan Explicit String
    $sheet->setCellValueExplicit('D' . $rowNum, $row['nik_ktp'] ?: '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    
    // Sesuaikan posisi kolom yang bergeser
    $sheet->setCellValue('E' . $rowNum, $row['nama_site'] ?: '-');
    $sheet->setCellValue('F' . $rowNum, $row['nama_jabatan'] ?: '-');
    $sheet->setCellValueExplicit('G' . $rowNum, $bpjs, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    
    $rowNum++;
}

// Auto-size untuk kolom NIK KTP dan BPJS agar lebarnya pas
$sheet->getColumnDimension('D')->setAutoSize(true);
$sheet->getColumnDimension('G')->setAutoSize(true);

// Set header file output ke browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Data_BPJS_Ketenagakerjaan.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>