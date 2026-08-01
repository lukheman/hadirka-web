<?php
/**
 * ============================================
 * EXPORT EXCEL DATA PENGAJUAN PINJAMAN
 * ============================================
 */

// Mematikan limit waktu dan memory limit untuk data besar
set_time_limit(0);
ini_set('memory_limit', '-1');

require_once __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// 1. Tangkap Parameter GET dari Frontend
$startDate = isset($_GET['startDate']) ? $koneksi->real_escape_string($_GET['startDate']) : date("Y-m-01");
$endDate   = isset($_GET['endDate']) ? $koneksi->real_escape_string($_GET['endDate']) : date("Y-m-t");
$site      = isset($_GET['site']) ? $koneksi->real_escape_string($_GET['site']) : '';
$jabatan   = isset($_GET['jabatan']) ? $koneksi->real_escape_string($_GET['jabatan']) : '';
$bank      = isset($_GET['bank']) ? $koneksi->real_escape_string($_GET['bank']) : '';
$status    = isset($_GET['status']) ? $koneksi->real_escape_string($_GET['status']) : '';

// 2. Susun Kondisi Filter (WHERE Clause)
$where = ["DATE(p.tgl_pengajuan) BETWEEN '$startDate' AND '$endDate'"];

if (!empty($site)) $where[] = "kr.id_site = '$site'";
if (!empty($jabatan)) $where[] = "kr.id_jabatan = '$jabatan'";
if (!empty($status)) $where[] = "p.status_pinjaman = '$status'";

// Logika filter Bank (Diadopsi dari NormalizeBankName di JS lama)
if (!empty($bank)) {
    if ($bank === 'BELUM_PUNYA') {
        $where[] = "(i.nama_bank IS NULL OR i.nama_bank = '' OR i.nama_bank = 'BELUM PUNYA REKENING')";
    } elseif (in_array($bank, ['MANDIRI', 'BRI', 'BNI', 'BCA'])) {
        $where[] = "i.nama_bank LIKE '%$bank%'";
    } elseif ($bank === 'LAINNYA') {
        $where[] = "(i.nama_bank NOT LIKE '%MANDIRI%' AND i.nama_bank NOT LIKE '%BRI%' AND i.nama_bank NOT LIKE '%BNI%' AND i.nama_bank NOT LIKE '%BCA%' AND i.nama_bank IS NOT NULL AND i.nama_bank != '' AND i.nama_bank != 'BELUM PUNYA REKENING')";
    }
}

$whereClause = " WHERE " . implode(" AND ", $where);

// 3. Eksekusi Kueri 
$sql = "SELECT p.*, k.nama_lengkap, k.nik_karyawan,
               COALESCE(i.nama_bank, 'BELUM PUNYA REKENING') AS nama_bank, 
               COALESCE(i.no_rek, '-') AS no_rek, 
               COALESCE(i.atas_nama_rek, '-') AS atas_nama_rek,
               COALESCE(j.nama_jabatan, '-') AS nama_jabatan,
               COALESCE(s.nama_site, '-') AS nama_site
        FROM tb_pengajuan_pinjaman p
        JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
        LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_jabatan j ON j.id_jabatan = kr.id_jabatan
        LEFT JOIN tb_site s ON kr.id_site = s.id_site
        $whereClause
        GROUP BY p.id_pengajuan_pinjaman
        ORDER BY p.tgl_pengajuan DESC, k.nama_lengkap ASC";

$result = $koneksi->query($sql);

if (!$result) {
    die("Kueri error: " . $koneksi->error);
}

// 4. Inisialisasi Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Pengajuan Pinjaman');

// 5. Konfigurasi Header Kolom
$headers = [
    'A1' => 'NO',
    'B1' => 'NIK',
    'C1' => 'NAMA LENGKAP',
    'D1' => 'JABATAN',
    'E1' => 'SITE',
    'F1' => 'TANGGAL PENGAJUAN',
    'G1' => 'NOMINAL PINJAMAN (Rp)',
    'H1' => 'ALASAN PINJAMAN',
    'I1' => 'ATAS NAMA REKENING',
    'J1' => 'NOMOR REKENING',
    'K1' => 'NAMA BANK',
    'L1' => 'STATUS'
];

foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
}

// Styling Header (Gradient Biru-Ungu seperti UI Approval Anda)
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF4E73DF'], // Primary color
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
    ],
];
$sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

// 6. Looping Data
$rowExcel = 2;
$no = 1;

while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowExcel, $no);
    $sheet->setCellValue('B' . $rowExcel, $row['nik_karyawan']);
    $sheet->setCellValue('C' . $rowExcel, $row['nama_lengkap']);
    $sheet->setCellValue('D' . $rowExcel, $row['nama_jabatan']);
    $sheet->setCellValue('E' . $rowExcel, $row['nama_site']);
    
    // Format Tanggal Pengajuan
    $sheet->setCellValue('F' . $rowExcel, date('Y-m-d H:i', strtotime($row['tgl_pengajuan'])));
    $sheet->getStyle('F' . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Format Nominal Excel
    $sheet->setCellValue('G' . $rowExcel, $row['nominal_pinjaman']);
    $sheet->getStyle('G' . $rowExcel)->getNumberFormat()->setFormatCode('#,##0');

    $sheet->setCellValue('H' . $rowExcel, $row['alasan_pinjaman'] ?: '-');
    $sheet->setCellValue('I' . $rowExcel, $row['atas_nama_rek']);
    
    // Format Rekening menjadi text utuh agar tidak jadi 1.23E+ format scientific
    $sheet->setCellValueExplicit('J' . $rowExcel, $row['no_rek'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    
    $sheet->setCellValue('K' . $rowExcel, $row['nama_bank']);
    
    // Status
    $statusText = strtoupper($row['status_pinjaman']);
    if($statusText === 'DICAIRKAN') $statusText = 'SUDAH CAIR / TF';
    $sheet->setCellValue('L' . $rowExcel, $statusText);

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
    $sheet->getStyle('A2:L' . ($rowExcel - 1))->applyFromArray($dataStyle);
}

// 8. Auto-size Kolom
foreach (range('A', 'L') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 9. Output dan Download File Excel
$judulFile = "Data_Pengajuan_Pinjaman_{$startDate}_sd_{$endDate}";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $judulFile . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;