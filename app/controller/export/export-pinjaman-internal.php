<?php
error_reporting(0);
ini_set('display_errors', 0);
if (ob_get_length()) ob_end_clean();

require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$tgl_awal = $_GET['tgl_awal'] ?? ''; 
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

if (empty($tgl_awal) || empty($tgl_akhir)) {
    die("Error: Pilih Range Tanggal terlebih dahulu!");
}

$where = "p.tgl_bayar_pinjaman BETWEEN '" . $koneksi->real_escape_string($tgl_awal) . "' AND '" . $koneksi->real_escape_string($tgl_akhir) . "'";

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Log Pinjaman Internal');

$headers = ['A1' => 'No', 'B1' => 'NIK', 'C1' => 'Nama Karyawan', 'D1' => 'Tanggal Cair', 'E1' => 'Tanggal Bayar', 'F1' => 'Nominal', 'G1' => 'Keterangan'];
foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

$sql = "SELECT p.*, k.nik_karyawan_internal, k.nama_karyawan 
        FROM tb_pinjaman_internal p 
        JOIN tb_karyawan_internal k ON p.id_karyawan_internal = k.id_karyawan_internal 
        WHERE $where 
        ORDER BY p.tgl_bayar_pinjaman DESC, p.tgl_pinjaman DESC";
$result = $koneksi->query($sql);

$rowNum = 2;
$no = 1;
while ($d = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValueExplicit('B' . $rowNum, $d['nik_karyawan_internal'], DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $d['nama_karyawan']);
    $sheet->setCellValue('D' . $rowNum, date('Y-m-d', strtotime($d['tgl_pinjaman'])));
    $sheet->setCellValue('E' . $rowNum, date('Y-m-d', strtotime($d['tgl_bayar_pinjaman'])));
    $sheet->setCellValue('F' . $rowNum, floatval($d['nominal_pinjaman']));
    $sheet->setCellValue('G' . $rowNum, $d['ket_pinjaman']);
    
    $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $rowNum++;
}

foreach (range('A', 'G') as $col) { 
    $sheet->getColumnDimension($col)->setAutoSize(true); 
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
$nama_file = "Log_Pinjaman_Internal_{$tgl_awal}_sd_{$tgl_akhir}.xlsx";
header('Content-Disposition: attachment;filename="' . $nama_file . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;