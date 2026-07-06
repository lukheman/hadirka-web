<?php
require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Master Karyawan Internal');

$sheet->setCellValue('A1', 'No')
      ->setCellValue('B1', 'NIK Karyawan')
      ->setCellValue('C1', 'Nama Karyawan')
      ->setCellValue('D1', 'Site (Lokasi)')
      ->setCellValue('E1', 'Jabatan')
      ->setCellValue('F1', 'Gaji Pokok')
      ->setCellValue('G1', 'Nama Bank')
      ->setCellValue('H1', 'Nomor Rekening')
      ->setCellValue('I1', 'Atas Nama Rekening');

$sql = "SELECT k.*, s.nama_site 
        FROM tb_karyawan_internal k 
        LEFT JOIN tb_site s ON k.id_site = s.id_site 
        ORDER BY k.nik_karyawan_internal ASC";
$result = $koneksi->query($sql);

$rowNum = 2;
$no = 1;
while ($d = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValueExplicit('B' . $rowNum, $d['nik_karyawan_internal'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $d['nama_karyawan']);
    $sheet->setCellValue('D' . $rowNum, $d['nama_site'] ?? '-');
    $sheet->setCellValue('E' . $rowNum, $d['jabatan']);
    $sheet->setCellValue('F' . $rowNum, floatval($d['gaji_pokok']));
    $sheet->setCellValue('G' . $rowNum, $d['nama_bank'] ?? '-');
    $sheet->setCellValueExplicit('H' . $rowNum, $d['no_rek'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('I' . $rowNum, $d['atas_nama_rek'] ?? '-');

    $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $rowNum++;
}

foreach (range('A', 'I') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Database_Karyawan_Internal_' . date('Ymd') . '.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;