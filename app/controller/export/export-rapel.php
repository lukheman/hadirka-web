<?php
/**
 * ============================================
 * EXPORT DATA RAPEL KE EXCEL
 * ============================================
 * File: export-rapel.php
 */

// Mematikan limit waktu dan memory limit untuk data besar
set_time_limit(0);
ini_set('memory_limit', '-1');

// Sesuaikan path konfigurasi database dan library
require_once __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// 1. Tangkap Parameter GET
$bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';
$site = isset($_GET['site']) ? trim($_GET['site']) : '';
$jabatan = isset($_GET['jabatan']) ? trim($_GET['jabatan']) : '';

// 2. Susun Kondisi Filter (WHERE Clause)
$where = ["1=1"];
$judulFile = "Data_Rapel";

if (!empty($bulan)) {
    $bulan_safe = $koneksi->real_escape_string($bulan);
    $where[] = "DATE_FORMAT(r.tgl_rapel, '%Y-%m') = '$bulan_safe'";
    $judulFile .= "_" . $bulan_safe;
} else {
    $judulFile .= "_SemuaBulan";
}

if (!empty($site)) {
    $where[] = "kr.id_site = '" . $koneksi->real_escape_string($site) . "'";
}
if (!empty($jabatan)) {
    $where[] = "kr.id_jabatan = '" . $koneksi->real_escape_string($jabatan) . "'";
}

$whereClause = " WHERE " . implode(" AND ", $where);

// 3. Eksekusi Kueri dengan LEFT JOIN dan GROUP BY 
// (Menghindari data hilang jika karyawan sudah resign/tidak aktif)
$sql = "SELECT 
            r.id_rapel,
            r.tgl_rapel,
            r.nominal_rapel,
            r.ket_rapel,
            k.nik_karyawan,
            k.nama_lengkap,
            COALESCE(d.nama_departemen, '-') AS nama_departemen,
            COALESCE(j.nama_jabatan, '-') AS nama_jabatan,
            COALESCE(s.nama_site, '-') AS nama_site
        FROM tb_rapel r
        JOIN tb_karyawan k ON r.id_karyawan = k.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_departemen d ON d.id_departemen = kr.id_departemen
        LEFT JOIN tb_jabatan j ON j.id_jabatan = kr.id_jabatan
        LEFT JOIN tb_site s ON s.id_site = kr.id_site
        $whereClause
        GROUP BY r.id_rapel
        ORDER BY r.tgl_rapel DESC, k.nama_lengkap ASC";

$result = $koneksi->query($sql);

if (!$result) {
    die("Kueri error: " . $koneksi->error);
}

// 4. Inisialisasi Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rekap Rapel');

// 5. Konfigurasi Header Kolom
$headers = [
    'A1' => 'NO',
    'B1' => 'NIK',
    'C1' => 'NAMA KARYAWAN',
    'D1' => 'DEPARTEMEN',
    'E1' => 'JABATAN',
    'F1' => 'SITE',
    'G1' => 'TANGGAL RAPEL',
    'H1' => 'NOMINAL (Rp)',
    'I1' => 'KETERANGAN'
];

foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
}

// Styling Header
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF198754'], // Warna Hijau Success (seperti UI Anda)
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
    ],
];
$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

// 6. Looping Data
$rowExcel = 2;
$no = 1;

while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowExcel, $no);
    $sheet->setCellValue('B' . $rowExcel, $row['nik_karyawan']);
    $sheet->setCellValue('C' . $rowExcel, $row['nama_lengkap']);
    $sheet->setCellValue('D' . $rowExcel, $row['nama_departemen']);
    $sheet->setCellValue('E' . $rowExcel, $row['nama_jabatan']);
    $sheet->setCellValue('F' . $rowExcel, $row['nama_site']);
    
    // Format Tanggal Excel
    $sheet->setCellValue('G' . $rowExcel, date('Y-m-d', strtotime($row['tgl_rapel'])));
    $sheet->getStyle('G' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Format Nominal Excel
    $sheet->setCellValue('H' . $rowExcel, $row['nominal_rapel']);
    $sheet->getStyle('H' . $rowExcel)->getNumberFormat()->setFormatCode('#,##0');

    $sheet->setCellValue('I' . $rowExcel, $row['ket_rapel']);

    $rowExcel++;
    $no++;
}

// 7. Styling Border Seluruh Data
if ($rowExcel > 2) {
    $dataStyle = [
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ]
    ];
    $sheet->getStyle('A2:I' . ($rowExcel - 1))->applyFromArray($dataStyle);
}

// 8. Auto-size Kolom
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 9. Output dan Download File Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $judulFile . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;