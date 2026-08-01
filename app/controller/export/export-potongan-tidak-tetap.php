<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

require_once __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';
$site = isset($_GET['site']) ? trim($_GET['site']) : '';
$jabatan = isset($_GET['jabatan']) ? trim($_GET['jabatan']) : '';

$where = ["1=1"];
$judulFile = "Data_Potongan_Tidak_Tetap";

if (!empty($bulan)) {
    $bulan_safe = $koneksi->real_escape_string($bulan);
    $where[] = "p.tgl_potongan LIKE '$bulan_safe%'";
    $judulFile .= "_" . $bulan_safe;
} else {
    $judulFile .= "_SemuaBulan";
}

if (!empty($site)) $where[] = "kr.id_site = '" . $koneksi->real_escape_string($site) . "'";
if (!empty($jabatan)) $where[] = "kr.id_jabatan = '" . $koneksi->real_escape_string($jabatan) . "'";

$whereClause = " WHERE " . implode(" AND ", $where);

$sql = "SELECT 
            p.id_potongan_tidak_tetap, p.tgl_potongan, p.jenis_potongan, p.nominal_potongan, p.ket_potongan,
            k.nik_karyawan, k.nama_lengkap,
            COALESCE(d.nama_departemen, '-') AS nama_departemen,
            COALESCE(j.nama_jabatan, '-') AS nama_jabatan,
            COALESCE(s.nama_site, '-') AS nama_site
        FROM tb_potongan_tidak_tetap p
        JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_departemen d ON d.id_departemen = kr.id_departemen
        LEFT JOIN tb_jabatan j ON j.id_jabatan = kr.id_jabatan
        LEFT JOIN tb_site s ON s.id_site = kr.id_site
        $whereClause
        GROUP BY p.id_potongan_tidak_tetap
        ORDER BY p.tgl_potongan DESC, k.nama_lengkap ASC";

$result = $koneksi->query($sql);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Potongan Tidak Tetap');

$headers = ['A1'=>'NO', 'B1'=>'NIK', 'C1'=>'NAMA KARYAWAN', 'D1'=>'DEPARTEMEN', 'E1'=>'JABATAN', 'F1'=>'SITE', 'G1'=>'TANGGAL', 'H1'=>'JENIS POTONGAN', 'I1'=>'NOMINAL (Rp)', 'J1'=>'KETERANGAN'];
foreach ($headers as $cell => $val) { $sheet->setCellValue($cell, $val); }

$sheet->getStyle('A1:J1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF198754']]
]);

$rowEx = 2; $no = 1;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A'.$rowEx, $no);
    $sheet->setCellValue('B'.$rowEx, $row['nik_karyawan']);
    $sheet->setCellValue('C'.$rowEx, $row['nama_lengkap']);
    $sheet->setCellValue('D'.$rowEx, $row['nama_departemen']);
    $sheet->setCellValue('E'.$rowEx, $row['nama_jabatan']);
    $sheet->setCellValue('F'.$rowEx, $row['nama_site']);
    $sheet->setCellValue('G'.$rowEx, date('Y-m-d', strtotime($row['tgl_potongan'])));
    $sheet->setCellValue('H'.$rowEx, $row['jenis_potongan']);
    $sheet->setCellValue('I'.$rowEx, $row['nominal_potongan']);
    $sheet->getStyle('I'.$rowEx)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->setCellValue('J'.$rowEx, $row['ket_potongan']);
    $rowEx++; $no++;
}

if ($rowEx > 2) {
    $sheet->getStyle('A2:J'.($rowEx-1))->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
}
foreach (range('A', 'J') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$judulFile.'.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;