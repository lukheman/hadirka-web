<?php
// Mematikan limit waktu dan memory limit untuk data besar
set_time_limit(0);
ini_set('memory_limit', '-1');

// Sesuaikan path berdasarkan struktur direktori (sama seperti csv-absen.php)
require_once __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Terima parameter via GET
$tgl_a   = $_GET['tgl_a']   ?? '';
$tgl_b   = $_GET['tgl_b']   ?? '';
$site    = $_GET['site']    ?? '';
$jabatan = $_GET['jabatan'] ?? '';

if (!$tgl_a || (!$tgl_b) || (!$site)) {
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
    foreach ($period as $d) $dates[] = $d->format('Y-m-d');
    return $dates;
}

$allDates = dateRangeArray($tgl_a, $tgl_b);

// -------------------------
// Ambil semua absen di rentang sekaligus
// -------------------------
$absenAll = [];
$sqlAbs = "SELECT id_karyawan, tgl_absen, jam_kerja, ket_absen 
           FROM tb_absen
           WHERE tgl_absen BETWEEN '$tgl_a' AND '$tgl_b'";
$resAbs = mysqli_query($koneksi, $sqlAbs);
while ($r = mysqli_fetch_assoc($resAbs)) {
    $id = $r['id_karyawan'];
    $tgl = $r['tgl_absen'];
    $r['jam_kerja'] = trim((string)$r['jam_kerja']);
    $r['ket_absen']  = isset($r['ket_absen']) ? trim((string)$r['ket_absen']) : '';
    $absenAll[$id][$tgl] = $r;
}

// -------------------------
// Ambil Karyawan
// -------------------------
$whereJab = $jabatan ? " AND kar.id_jabatan = '$jabatan' " : "";
$sqlKar = "
SELECT k.id_karyawan, k.nama_lengkap,
       COALESCE(d.nama_departemen, '') AS nama_departemen,
       COALESCE(j.nama_jabatan, '') AS nama_jabatan,
       COALESCE(s.nama_site, '') AS nama_site
FROM tb_karyawan k
LEFT JOIN tb_karier kar ON kar.id_karyawan = k.id_karyawan
LEFT JOIN tb_jabatan j ON j.id_jabatan = kar.id_jabatan
LEFT JOIN tb_departemen d ON d.id_departemen = kar.id_departemen
LEFT JOIN tb_site s ON s.id_site = kar.id_site
WHERE kar.id_site = '$site'
$whereJab
GROUP BY k.id_karyawan
ORDER BY k.nama_lengkap ASC
";

$qKar = mysqli_query($koneksi, $sqlKar);

// -------------------------
// Inisialisasi PhpSpreadsheet
// -------------------------
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rekap Absensi');

// Set Header Excel
$headers = [
    'A1' => 'No',
    'B1' => 'Nama Lengkap',
    'C1' => 'Departemen',
    'D1' => 'Jabatan',
    'E1' => 'Site',
    'F1' => 'Hadir',
    'G1' => 'Overtime 1',
    'H1' => 'Overtime 2',
    'I1' => 'SPL',
    'J1' => 'Overtime 1 SPL',
    'K1' => 'Overtime 2 SPL',
    'L1' => 'Mangkir',
    'M1' => 'Izin',
    'N1' => 'Sakit',
    'O1' => 'Cuti'
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
        'startColor' => ['argb' => 'FF17A2B8'], // Info color
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
    ],
];
$sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

$rowExcel = 2;
$no = 1;

while ($k = mysqli_fetch_assoc($qKar)) {
    $idk = $k['id_karyawan'];

    // Inisialisasi counters
    $hadir = 0;
    $ot1 = 0;
    $ot2 = 0;
    $spl = 0;
    $ot1_spl = 0;
    $ot2_spl = 0;
    $mangkir = 0;
    $izin = 0;
    $sakit = 0;
    $cuti = 0;

    $absForK = $absenAll[$idk] ?? [];

    foreach ($allDates as $tgl) {
        if (isset($absForK[$tgl])) {
            $row = $absForK[$tgl];
            $jamRaw = trim((string)$row['jam_kerja']);
            $ket = strtoupper(trim((string)$row['ket_absen']));

            if (in_array($ket, ['RESIGN', 'R', 'NEW'])) continue;

            if (in_array($ket, ['IZIN', 'SAKIT', 'CUTI'])) {
                if ($ket === 'IZIN')  $izin++;
                if ($ket === 'SAKIT') $sakit++;
                if ($ket === 'CUTI')  $cuti++;
                continue;
            }

            if ($ket === 'OFF') {
                if (floatval($jamRaw) > 0) {
                    $jk = floatval($jamRaw);
                    $spl += min($jk, 7);
                    $ot1_spl += ($jk >= 8) ? 1 : 0;
                    $ot2_spl += ($jk >= 9) ? ($jk - 8) : 0;
                }
                continue;
            }

            if (is_numeric($jamRaw)) {
                $jk = floatval($jamRaw);
                if ($jk == 0) {
                    $mangkir++;
                    continue;
                }

                $hariNama = strtolower(date('l', strtotime($tgl)));
                $normal_hours = ($hariNama === 'friday') ? 5 : 7;
                $hadir += min($jk, $normal_hours);

                $wl = 0;
                $ot = 0;
                if ($hariNama === 'friday') {
                    $wl = min(1, max(0, $jk - 5));
                    $ot = max(0, $jk - 5 - $wl);
                } else {
                    $wl = min(1, max(0, $jk - 7));
                    $ot = max(0, $jk - 7 - $wl);
                }
                $ot1 += $wl;
                $ot2 += $ot;
                continue;
            }

            if ($ket === 'MANGKIR') {
                $mangkir++;
                continue;
            }
            $mangkir++;
            continue;
        } else {
            $mangkir++;
            continue;
        }
    }

    // Tulis ke Excel
    $sheet->setCellValue('A' . $rowExcel, $no);
    $sheet->setCellValue('B' . $rowExcel, $k['nama_lengkap']);
    $sheet->setCellValue('C' . $rowExcel, $k['nama_departemen']);
    $sheet->setCellValue('D' . $rowExcel, $k['nama_jabatan']);
    $sheet->setCellValue('E' . $rowExcel, $k['nama_site']);
    $sheet->setCellValue('F' . $rowExcel, $hadir);
    $sheet->setCellValue('G' . $rowExcel, $ot1);
    $sheet->setCellValue('H' . $rowExcel, $ot2);
    $sheet->setCellValue('I' . $rowExcel, $spl);
    $sheet->setCellValue('J' . $rowExcel, $ot1_spl);
    $sheet->setCellValue('K' . $rowExcel, $ot2_spl);
    $sheet->setCellValue('L' . $rowExcel, $mangkir);
    $sheet->setCellValue('M' . $rowExcel, $izin);
    $sheet->setCellValue('N' . $rowExcel, $sakit);
    $sheet->setCellValue('O' . $rowExcel, $cuti);

    $rowExcel++;
    $no++;
}

// Tambahkan Border ke seluruh data
if ($rowExcel > 2) {
    $dataStyle = [
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
        ],
    ];
    $sheet->getStyle('A2:O' . ($rowExcel - 1))->applyFromArray($dataStyle);
}

// Auto-size Columns
foreach (range('A', 'O') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// -------------------------
// Output / Download File Excel
// -------------------------
$namaFile = "Rekap_Absensi_{$tgl_a}_sd_{$tgl_b}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $namaFile . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
