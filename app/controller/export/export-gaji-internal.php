<?php
error_reporting(0);
ini_set('display_errors', 0);
if (ob_get_length()) ob_end_clean(); // Mencegah file excel corrupt

require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$site = $_GET['site'] ?? '';

if (empty($tgl_awal) || empty($tgl_akhir)) die("Error: Pilih Range Tanggal terlebih dahulu!");

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
    ->setCellValue('J1', "Bonus (B)")
    ->setCellValue('K1', "Rapel (C)")
    ->setCellValue('L1', "Potongan (D)")
    ->setCellValue('M1', "Pinjaman (E)")
    ->setCellValue('N1', 'GAJI BERSIH (A+B+C-D-E)');

$sheet->getStyle('A1:N1')->getFont()->setBold(true);

$where = "k.status_karyawan = 'Aktif'";
if (!empty($site)) {
    $where .= " AND k.id_site = '" . $koneksi->real_escape_string($site) . "'";
}

// MENGGUNAKAN SUBQUERY
$sql = "SELECT k.*, s.nama_site,
        (SELECT IFNULL(SUM(nominal), 0) FROM tb_bonus_internal WHERE id_karyawan_internal = k.id_karyawan_internal AND tgl_bonus BETWEEN '$tgl_awal' AND '$tgl_akhir') AS akumulasi_bonus,
        (SELECT IFNULL(SUM(nominal), 0) FROM tb_rapel_internal WHERE id_karyawan_internal = k.id_karyawan_internal AND tgl_rapel BETWEEN '$tgl_awal' AND '$tgl_akhir') AS akumulasi_rapel,
        (SELECT IFNULL(SUM(nominal), 0) FROM tb_potongan_internal WHERE id_karyawan_internal = k.id_karyawan_internal AND tgl_potongan BETWEEN '$tgl_awal' AND '$tgl_akhir') AS akumulasi_potongan,
        (SELECT IFNULL(SUM(nominal_pinjaman), 0) FROM tb_pinjaman_internal WHERE id_karyawan_internal = k.id_karyawan_internal AND tgl_bayar_pinjaman BETWEEN '$tgl_awal' AND '$tgl_akhir') AS akumulasi_pinjaman
        FROM tb_karyawan_internal k
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        WHERE $where
        ORDER BY k.nik_karyawan_internal ASC";
$result = $koneksi->query($sql);

$rowNum = 2;
$no = 1;
while ($d = $result->fetch_assoc()) {
    $gapok = floatval($d['gaji_pokok']);
    $bonus = floatval($d['akumulasi_bonus']);
    $rapel = floatval($d['akumulasi_rapel']);
    $potongan = floatval($d['akumulasi_potongan']);
    $pinjaman = floatval($d['akumulasi_pinjaman']);
    
    $gaji_bersih = ($gapok + $bonus + $rapel) - ($potongan + $pinjaman);

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
    $sheet->setCellValue('K' . $rowNum, $rapel);
    $sheet->setCellValue('L' . $rowNum, $potongan);
    $sheet->setCellValue('M' . $rowNum, $pinjaman);
    $sheet->setCellValue('N' . $rowNum, $gaji_bersih);

    $sheet->getStyle('I' . $rowNum . ':N' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $rowNum++;
}

foreach (range('A', 'N') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$nama_site_file = "Semua_Site";
if (!empty($site)) {
    $q_site = $koneksi->query("SELECT nama_site FROM tb_site WHERE id_site = '" . $koneksi->real_escape_string($site) . "'");
    if ($q_site && $q_site->num_rows > 0) {
        $nama_site_file = str_replace(' ', '_', $q_site->fetch_assoc()['nama_site']);
    }
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
$nama_file = "Payroll_Internal_{$nama_site_file}_{$tgl_awal}_sd_{$tgl_akhir}.xlsx";
header('Content-Disposition: attachment;filename="' . $nama_file . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;