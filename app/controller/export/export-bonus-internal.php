<?php
require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Log Riwayat Bonus');

$sheet->setCellValue('A1', 'No')
      ->setCellValue('B1', 'NIK Karyawan')
      ->setCellValue('C1', 'Nama Karyawan')
      ->setCellValue('D1', 'Jabatan')
      ->setCellValue('E1', 'Tanggal Bonus')
      ->setCellValue('F1', 'Nominal Bonus')
      ->setCellValue('G1', 'Keterangan');

$sql = "SELECT b.*, k.nik_karyawan_internal, k.nama_karyawan, k.jabatan 
        FROM tb_bonus_internal b
        INNER JOIN tb_karyawan_internal k ON b.id_karyawan_internal = k.id_karyawan_internal
        ORDER BY b.tgl_bonus DESC";
$result = $koneksi->query($sql);

$rowNum = 2;
$no = 1;
while ($d = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValueExplicit('B' . $rowNum, $d['nik_karyawan_internal'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $d['nama_karyawan']);
    $sheet->setCellValue('D' . $rowNum, $d['jabatan']);
    $sheet->setCellValue('E' . $rowNum, date('d/m/Y', strtotime($d['tgl_bonus'])));
    $sheet->setCellValue('F' . $rowNum, floatval($d['nominal']));
    $sheet->setCellValue('G' . $rowNum, $d['ket_bonus'] ?: '-');

    $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $rowNum++;
}

foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Rekap_Seluruh_Bonus_Internal.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;