<?php
error_reporting(0);
ini_set('display_errors', 0);
if (ob_get_length()) ob_end_clean(); // Mencegah file excel corrupt

require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// 1. TANGKAP FILTER
$site = $_GET['site'] ?? '';
$status = $_GET['status'] ?? 'Semua';

// 2. BUILD WHERE CLAUSE
$where = ["1=1"];
if (!empty($site)) $where[] = "k.id_site = '" . $koneksi->real_escape_string($site) . "'";
if ($status !== 'Semua') $where[] = "k.status_karyawan = '" . $koneksi->real_escape_string($status) . "'";
$where_sql = implode(" AND ", $where);

// 3. EKSEKUSI QUERY
$sql = "SELECT k.*, s.nama_site 
        FROM tb_karyawan_internal k 
        LEFT JOIN tb_site s ON k.id_site = s.id_site 
        WHERE $where_sql 
        ORDER BY k.nik_karyawan_internal ASC";
$result = $koneksi->query($sql);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Master Karyawan Internal');

$headers = ['A1' => 'No', 'B1' => 'NIK', 'C1' => 'Nama Karyawan', 'D1' => 'L/P', 'E1' => 'Site', 'F1' => 'Jabatan', 'G1' => 'Gaji Pokok', 'H1' => 'Bank', 'I1' => 'No Rekening', 'J1' => 'Atas Nama', 'K1' => 'Status', 'L1' => 'Tgl Masuk', 'M1' => 'Tgl Keluar', 'N1' => 'Ket Keluar'];
foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

$rowNum = 2;
$no = 1;
while ($d = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValueExplicit('B' . $rowNum, $d['nik_karyawan_internal'], DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $d['nama_karyawan']);
    $sheet->setCellValue('D' . $rowNum, $d['jenis_kelamin']);
    $sheet->setCellValue('E' . $rowNum, $d['nama_site'] ?? '-');
    $sheet->setCellValue('F' . $rowNum, $d['jabatan']);
    $sheet->setCellValue('G' . $rowNum, floatval($d['gaji_pokok']));
    $sheet->setCellValue('H' . $rowNum, $d['nama_bank']);
    $sheet->setCellValueExplicit('I' . $rowNum, $d['no_rek'], DataType::TYPE_STRING);
    $sheet->setCellValue('J' . $rowNum, $d['atas_nama_rek']);
    $sheet->setCellValue('K' . $rowNum, $d['status_karyawan']);
    $sheet->setCellValue('L' . $rowNum, $d['tgl_masuk']);
    $sheet->setCellValue('M' . $rowNum, $d['tgl_keluar']);
    $sheet->setCellValue('N' . $rowNum, $d['ket_keluar']);
    $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $rowNum++;
}

foreach (range('A', 'N') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 4. LOGIKA NAMA FILE DINAMIS
$nama_site_file = "SEMUA_SITE";
if (!empty($site)) {
    $q_site = $koneksi->query("SELECT nama_site FROM tb_site WHERE id_site = '" . $koneksi->real_escape_string($site) . "'");
    if ($q_site && $q_site->num_rows > 0) {
        $site_data = $q_site->fetch_assoc();
        $nama_site_file = str_replace(' ', '_', strtoupper(trim($site_data['nama_site'])));
    }
}

$status_file = str_replace(' ', '_', strtoupper($status));
$filename = "Export_Karyawan_Internal_" . $nama_site_file . "_" . $status_file . "_" . date('Ymd_His') . ".xlsx";

// 5. DOWNLOAD FILE
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
