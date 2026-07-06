<?php
require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$bulan_tahun = $_GET['bulan_tahun'] ?? ''; 
if (empty($bulan_tahun)) die("Error: Pilih Bulan & Tahun terlebih dahulu!");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Gaji Bersih Karyawan');

$sheet->setCellValue('A1', 'No')
      ->setCellValue('B1', 'NIK Karyawan')
      ->setCellValue('C1', 'Nama Karyawan')
      ->setCellValue('D1', 'Site (Lokasi)')
      ->setCellValue('E1', 'Jabatan')
      ->setCellValue('F1', 'Nama Bank')
      ->setCellValue('G1', 'Nomor Rekening')
      ->setCellValue('H1', 'Atas Nama Rekening')
      ->setCellValue('I1', 'Gaji Pokok (A)')
      ->setCellValue('J1', 'Total Bonus ' . $bulan_tahun . ' (B)')
      ->setCellValue('K1', 'GAJI BERSIH DITERIMA (A + B)');

// Query akumulasi bonus bulan terpilih & penarikan nama_site penugasan karyawan
$sql = "SELECT k.*, s.nama_site, IFNULL(SUM(b.nominal), 0) AS akumulasi_bonus
        FROM tb_karyawan_internal k
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        LEFT JOIN tb_bonus_internal b ON k.id_karyawan_internal = b.id_karyawan_internal 
        AND DATE_FORMAT(b.tgl_bonus, '%Y-%m') = '" . $koneksi->real_escape_string($bulan_tahun) . "'
        GROUP BY k.id_karyawan_internal
        ORDER BY k.nik_karyawan_internal ASC";
$result = $koneksi->query($sql);

$rowNum = 2;
$no = 1;
while ($d = $result->fetch_assoc()) {
    $gapok = floatval($d['gaji_pokok']);
    $bonus = floatval($d['akumulasi_bonus']);
    $gaji_bersih = $gapok + $bonus;

    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValueExplicit('B' . $rowNum, $d['nik_karyawan_internal'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $d['nama_karyawan']);
    $sheet->setCellValue('D' . $rowNum, $d['nama_site'] ?? '-');
    $sheet->setCellValue('E' . $rowNum, $d['jabatan']);
    $sheet->setCellValue('F' . $rowNum, $d['nama_bank'] ?: '-');
    $sheet->setCellValueExplicit('G' . $rowNum, $d['no_rek'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('H' . $rowNum, $d['atas_nama_rek'] ?: '-');
    $sheet->setCellValue('I' . $rowNum, $gapok);
    $sheet->setCellValue('J' . $rowNum, $bonus);
    $sheet->setCellValue('K' . $rowNum, $gaji_bersih);

    $sheet->getStyle('I' . $rowNum . ':K' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $rowNum++;
}

foreach (range('A', 'K') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Payroll_Gaji_Bersih_Internal_' . $bulan_tahun . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;