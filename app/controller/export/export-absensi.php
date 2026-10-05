<?php
// Mematikan limit waktu dan memory limit untuk data besar
set_time_limit(0);
ini_set('memory_limit', '-1');

// Sesuaikan path berdasarkan struktur direktori
require_once __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Terima parameter via GET
$tgl_a   = $_GET['tgl_a']   ?? '';
$tgl_b   = $_GET['tgl_b']   ?? '';
$site    = $_GET['site']    ?? '';
$jabatan = $_GET['jabatan'] ?? '';

if (!$tgl_a || !$tgl_b || !$site) {
    die("Parameter filter tidak lengkap.");
}

// -------------------------
// Helper: buat rentang tanggal
// -------------------------
function dateRangeArray($start, $end)
{
    $period = new DatePeriod(
        new DateTime($start),
        new DateInterval('P1D'),
        (new DateTime($end))->modify('+1 day')
    );
    $dates = [];
    foreach ($period as $d) {
        $dates[] = $d->format('Y-m-d');
    }
    return $dates;
}

$allDates = dateRangeArray($tgl_a, $tgl_b);

// -------------------------
// Ambil semua absen di rentang sekaligus
// -------------------------
$absenAll = [];
$sqlAbs = "SELECT id_karyawan, tgl_absen, jam_kerja, ket_absen 
           FROM tb_absen
           WHERE tgl_absen BETWEEN ? AND ?";
$stAbs = $koneksi->prepare($sqlAbs);
$stAbs->execute([$tgl_a, $tgl_b]);
while ($r = $stAbs->fetch()) {
    $id = $r['id_karyawan'];
    $tgl = $r['tgl_absen'];
    $absenAll[$id][$tgl] = $r;
}

// -------------------------
// Ambil Karyawan (Sesuai Site & Jabatan)
// -------------------------
$paramsKar = [$site];
$whereJab = "";
if ($jabatan !== '') { $whereJab = " AND kar.id_jabatan = ? "; $paramsKar[] = $jabatan; }
$sqlKar = "
SELECT k.id_karyawan, k.nik_karyawan, k.nama_lengkap,
       COALESCE(j.nama_jabatan, '-') AS nama_jabatan,
       COALESCE(s.nama_site, '-') AS nama_site
FROM tb_karyawan k
LEFT JOIN tb_karier kar ON kar.id_karyawan = k.id_karyawan
LEFT JOIN tb_jabatan j ON j.id_jabatan = kar.id_jabatan
LEFT JOIN tb_site s ON s.id_site = kar.id_site
WHERE kar.id_site = ?
$whereJab
GROUP BY k.id_karyawan
ORDER BY k.nama_lengkap ASC
";

$qKar = $koneksi->prepare($sqlKar);
$qKar->execute($paramsKar);

// -------------------------
// Inisialisasi PhpSpreadsheet
// -------------------------
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Format Import Absensi');

// -------------------------
// SETUP HEADER (Sama seperti file Import)
// -------------------------
$sheet->setCellValue('A1', 'NO');
$sheet->setCellValue('B1', 'NAMA LENGKAP');
$sheet->setCellValue('C1', 'NIK KARYAWAN');

// Styling Header Dasar (Biru/Info)
$headerStyleDasar = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF17A2B8'], 
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
    ],
];
$sheet->getStyle('A1:C1')->applyFromArray($headerStyleDasar);

// Buat Header Kolom Tanggal Dinamis
$colIndex = 4; // Kolom ke-4 adalah 'D'
foreach ($allDates as $tgl) {
    $colLetter = Coordinate::stringFromColumnIndex($colIndex);
    
    // Tulis Header Tanggal
    $sheet->setCellValue($colLetter . '1', $tgl);
    
    // Warnai kuning jika hari tersebut adalah MINGGU (Hari OFF)
    $isMinggu = (date('N', strtotime($tgl)) == 7);
    
    $headerStyleTgl = [
        'font' => ['bold' => true, 'color' => ['argb' => ($isMinggu ? 'FF000000' : 'FFFFFFFF')]],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => ($isMinggu ? 'FFFFFF00' : 'FF17A2B8')], // Kuning jika Minggu, sisanya Biru
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
        ],
    ];
    $sheet->getStyle($colLetter . '1')->applyFromArray($headerStyleTgl);
    
    $colIndex++;
}

// -------------------------
// LOOPING PENGISIAN DATA KARYAWAN & NILAI ABSEN
// -------------------------
$rowExcel = 2;
$no = 1;

// Siapkan gaya untuk sel background kuning (Hari OFF / MINGGU)
$yellowStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFFFF00'], 
    ]
];

while ($k = $qKar->fetch()) {
    $idk = $k['id_karyawan'];

    // Info Karyawan
    $sheet->setCellValue('A' . $rowExcel, $no);
    $sheet->setCellValue('B' . $rowExcel, $k['nama_lengkap']);
    $sheet->setCellValue('C' . $rowExcel, $k['nik_karyawan']);

    $absForK = $absenAll[$idk] ?? [];

    $colIndexTgl = 4; 
    foreach ($allDates as $tgl) {
        $colLetter = Coordinate::stringFromColumnIndex($colIndexTgl);
        $isMinggu = (date('N', strtotime($tgl)) == 7);

        // Jika hari ini MINGGU, warnai sel dengan warna kuning
        if ($isMinggu) {
            $sheet->getStyle($colLetter . $rowExcel)->applyFromArray($yellowStyle);
        }

        // Tentukan nilai yang akan diisi
        $nilai_cell = '';
        
        if (isset($absForK[$tgl])) {
            $rowAbs = $absForK[$tgl];
            $jam_kerja = trim((string)$rowAbs['jam_kerja']);
            $ket = strtoupper(trim((string)$rowAbs['ket_absen']));

            if (in_array($ket, ['IZIN', 'SAKIT', 'CUTI', 'RESIGN', 'NEW'])) {
                // Tulis keterangannya langsung
                $nilai_cell = $ket;
            } elseif ($ket === 'OFF') {
                if (floatval($jam_kerja) > 0) {
                    // Kerja lembur di hari libur (SPL)
                    $nilai_cell = floatval($jam_kerja);
                } else {
                    $nilai_cell = 'OFF';
                }
            } elseif ($ket === 'MANGKIR') {
                $nilai_cell = 'M';
            } else {
                // Asumsi hadir (masuk normal)
                if (is_numeric($jam_kerja) && floatval($jam_kerja) > 0) {
                    $nilai_cell = floatval($jam_kerja);
                } else {
                    $nilai_cell = 'M'; // Tercatat tapi 0 jam dianggap Mangkir
                }
            }
        } else {
            // Jika sama sekali tidak ada data di DB, kita biarkan KOSONG.
            // Sesuai sistem CSV Anda (Cell Kosong = Alpha). 
            // Namun, jika Anda lebih suka ditulis 'M', ubah $nilai_cell = 'M';
            $nilai_cell = ''; 
            
            if ($isMinggu) {
                 $nilai_cell = 'OFF'; // Otomatis OFF jika kosong di hari Minggu
            }
        }

        // Tulis ke sel
        $sheet->setCellValue($colLetter . $rowExcel, $nilai_cell);
        
        // Atur posisi tulisan di tengah untuk sel nilai
        $sheet->getStyle($colLetter . $rowExcel)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $colIndexTgl++;
    }

    $rowExcel++;
    $no++;
}

// -------------------------
// STYLING AKHIR (Border & Auto-size)
// -------------------------
$lastColLetter = Coordinate::stringFromColumnIndex($colIndex - 1);

// Tambahkan Border ke seluruh data
if ($rowExcel > 2) {
    $dataStyle = [
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
        ],
    ];
    $sheet->getStyle('A2:' . $lastColLetter . ($rowExcel - 1))->applyFromArray($dataStyle);
}

// Auto-size Columns
for ($i = 1; $i < $colIndex; $i++) {
    $colL = Coordinate::stringFromColumnIndex($i);
    $sheet->getColumnDimension($colL)->setAutoSize(true);
}

// -------------------------
// Output / Download File Excel
// -------------------------
$namaFile = "Export_Rekapitulasi_Absensi_{$tgl_a}_sd_{$tgl_b}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $namaFile . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;