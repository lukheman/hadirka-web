<?php
require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Log Bonus');

$sheet->setCellValue('A1', 'No')->setCellValue('B1', 'NIK')->setCellValue('C1', 'Nama Karyawan')
    ->setCellValue('D1', 'Tanggal Bonus')->setCellValue('E1', 'Nominal')->setCellValue('F1', 'Keterangan');

$sql = "SELECT b.*, k.nik_karyawan_internal, k.nama_karyawan FROM tb_bonus_internal b JOIN tb_karyawan_internal k ON b.id_karyawan_internal = k.id_karyawan_internal ORDER BY b.tgl_bonus DESC";
$result = $koneksi->query($sql);

$rowNum = 2;
$no = 1;
while ($d = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValueExplicit('B' . $rowNum, $d['nik_karyawan_internal'], DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $d['nama_karyawan']);
    $sheet->setCellValue('D' . $rowNum, date('Y-m-d', strtotime($d['tgl_bonus'])));
    $sheet->setCellValue('E' . $rowNum, floatval($d['nominal']));
    $sheet->setCellValue('F' . $rowNum, $d['ket_bonus']);
    $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $rowNum++;
}
foreach (range('A', 'F') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Log_Bonus_Internal_' . date('Ymd') . '.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
