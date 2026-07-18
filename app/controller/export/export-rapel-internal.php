<?php
error_reporting(0);
ini_set('display_errors', 0);
if (ob_get_length()) ob_end_clean(); // Mencegah file excel corrupt

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

$where = "r.tgl_rapel BETWEEN '" . $koneksi->real_escape_string($tgl_awal) . "' AND '" . $koneksi->real_escape_string($tgl_akhir) . "'";

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Log Rapel Internal');

$headers = ['A1' => 'No', 'B1' => 'NIK', 'C1' => 'Nama Karyawan', 'D1' => 'Tanggal Rapel', 'E1' => 'Nominal', 'F1' => 'Keterangan'];
foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

$sql = "SELECT r.*, k.nik_karyawan_internal, k.nama_karyawan 
        FROM tb_rapel_internal r 
        JOIN tb_karyawan_internal k ON r.id_karyawan_internal = k.id_karyawan_internal 
        WHERE $where 
        ORDER BY r.tgl_rapel DESC";
$result = $koneksi->query($sql);

$rowNum = 2;
$no = 1;
while ($d = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValueExplicit('B' . $rowNum, $d['nik_karyawan_internal'], DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $d['nama_karyawan']);
    $sheet->setCellValue('D' . $rowNum, date('Y-m-d', strtotime($d['tgl_rapel'])));
    $sheet->setCellValue('E' . $rowNum, floatval($d['nominal']));
    $sheet->setCellValue('F' . $rowNum, $d['ket_rapel']);
    
    $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $rowNum++;
}

foreach (range('A', 'F') as $col) { 
    $sheet->getColumnDimension($col)->setAutoSize(true); 
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
$nama_file = "Log_Rapel_Internal_{$tgl_awal}_sd_{$tgl_akhir}.xlsx";
header('Content-Disposition: attachment;filename="' . $nama_file . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;