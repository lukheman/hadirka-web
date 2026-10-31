<?php
// Export Data Karyawan — harus identik dengan format utama yang dibaca
// app/controller/csv-control/csv-karyawan.php:
//     NO. | ID KARYAWAN | NIK KARYAWAN | NAMA | JABATAN | TIPE KARYAWAN |
//     PROYEK | GAJI POKOK | BANK | REKENING | A/N
//
// Tujuan: hasil export bisa langsung diedit di Excel lalu di-import kembali
// lewat fitur Import Data (app/controller/csv-control/csv-karyawan.php).
// Pemetaan nilai = kebalikan dari import:
//   B ID KARYAWAN <- tb_karyawan.nik_karyawan (IPM-xxxxx)
//   C NIK KARYAWAN <- tb_identitas.nik_ktp (NIK kependudukan)
//   D NAMA       <- tb_karyawan.nama_lengkap
//   E JABATAN       <- jabatan karier TERAKHIR
//   F TIPE KARYAWAN <- tb_tipe_karyawan.nama_tipe
//   G PROYEK        <- nama site
//   H GAJI POKOK    <- gaji_pokok karier terakhir
//   I BANK          <- tb_identitas.nama_bank
//   J REKENING      <- tb_identitas.no_rek (TEKS: nol depan aman)
//   K A/N           <- tb_identitas.atas_nama_rek
//
// Filter yang dipakai SAMA dengan halaman Data Karyawan (site, jabatan, tipe, status, bulan),
// jadi data yang diexport = data yang sedang tampil di daftar.
//
// Catatan: halaman Data Karyawan Tereliminasi masih memakai export-karyawan.php (44 kolom).

error_reporting(0);
ini_set('display_errors', 0);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

if (ob_get_length()) {
    ob_end_clean();
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Guard: hanya level yang boleh membuka halaman Data Karyawan (Finance termasuk, read-only).
if (!in_array((int)($_SESSION['level'] ?? 99), [0, 1, 2, 3, 5, 6], true)) {
    http_response_code(403);
    exit('Akses ditolak.');
}

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// Tangkap parameter filter dari GET (sama seperti daftar karyawan)
$site    = $_GET['site'] ?? '';
$jabatan = $_GET['jabatan'] ?? '';
$tipe    = $_GET['tipe_karyawan'] ?? '';
$status  = $_GET['status'] ?? '';
$bulan   = $_GET['bulan'] ?? '';

// Build WHERE clause — mengikuti get-karyawan.php (yang tampil = yang diexport)
$where = ["1=1"];
$params = [];

if (!empty($status)) {
    if ($status !== 'Semua') {
        $where[] = "kar.status_karyawan = ?";
        $params[] = $status;
    }
} else {
    $where[] = "kar.status_karyawan = 'Aktif'";
}

if (!empty($site)) {
    $where[] = "k.id_site = ?";
    $params[] = $site;
}

if (!empty($jabatan)) {
    $where[] = "k.id_jabatan = ?";
    $params[] = $jabatan;
}

if (!empty($tipe) && in_array($tipe, ['Kontrak', 'Harian'], true)) {
    // Samakan filter dengan daftar Data Karyawan: tipe berasal dari master tipe.
    $where[] = "tipe.nama_tipe = ?";
    $params[] = $tipe;
}

if (!empty($bulan)) {
    $where[] = "MONTH(k.tgl_selesai) = ?";
    $params[] = $bulan;
    $where[] = "YEAR(k.tgl_selesai) = YEAR(CURDATE())";
}

$where_sql = implode(" AND ", $where);

// Karier yang dipakai = baris terakhir (id_karier terbesar), konsisten dengan daftar.
    $query = "SELECT
        kar.nik_karyawan,
        kar.nama_lengkap,
        j.nama_jabatan,
        tipe.nama_tipe,
        s.nama_site,
        k.gaji_pokok,
        i.nik_ktp,
        i.nama_bank,
        i.no_rek,
        i.atas_nama_rek
    FROM tb_karyawan kar
    LEFT JOIN tb_karier k ON kar.id_karyawan = k.id_karyawan
        AND k.id_karier = (SELECT MAX(k2.id_karier) FROM tb_karier k2 WHERE k2.id_karyawan = kar.id_karyawan)
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        LEFT JOIN tb_tipe_karyawan tipe ON kar.id_tipe_karyawan = tipe.id_tipe_karyawan
        LEFT JOIN tb_identitas i ON kar.id_karyawan = i.id_karyawan
    WHERE $where_sql
    ORDER BY kar.nik_karyawan ASC";

try {
    $stmt = $koneksi->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('export-karyawan-template: ' . $e->getMessage());
    die("Query Error: gagal mengambil data karyawan.");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Karyawan'); // sama seperti sheet template import

// Header PERSIS seperti format utama yang divalidasi import.
$headers = [
    'A1' => 'NO.',
    'B1' => 'ID KARYAWAN',
    'C1' => 'NIK KARYAWAN',
    'D1' => 'NAMA',
    'E1' => 'JABATAN',
    'F1' => 'TIPE KARYAWAN',
    'G1' => 'PROYEK',
    'H1' => 'GAJI POKOK',
    'I1' => 'BANK',
    'J1' => 'REKENING',
    'K1' => 'A/N',
];
foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

$rowNum = 2;
$no = 1;
foreach ($rows as $row) {
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValueExplicit('B' . $rowNum, (string)($row['nik_karyawan'] ?? ''), DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('C' . $rowNum, (string)($row['nik_ktp'] ?? ''), DataType::TYPE_STRING);
    $sheet->setCellValue('D' . $rowNum, $row['nama_lengkap'] ?? '');
    $sheet->setCellValue('E' . $rowNum, $row['nama_jabatan'] ?? '');
    $sheet->setCellValue('F' . $rowNum, $row['nama_tipe'] ?? 'Kontrak');
    $sheet->setCellValue('G' . $rowNum, $row['nama_site'] ?? '');

    // Angka polos (tanpa pemisah ribuan) agar aman dibaca ulang saat di-import.
    if ($row['gaji_pokok'] !== null && $row['gaji_pokok'] !== '') {
        $sheet->setCellValue('H' . $rowNum, (int)round((float)$row['gaji_pokok']));
    }

    $sheet->setCellValue('I' . $rowNum, $row['nama_bank'] ?? '');
    // Nomor rekening wajib teks agar nol depan tidak hilang saat diedit di Excel.
    $sheet->setCellValueExplicit('J' . $rowNum, (string)($row['no_rek'] ?? ''), DataType::TYPE_STRING);
    $sheet->setCellValue('K' . $rowNum, $row['atas_nama_rek'] ?? '');
    $rowNum++;
}

// Rapikan lebar kolom A-K
foreach (range('A', 'K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Nama file
$nama_site_file = "SEMUA_SITE";
if (!empty($site)) {
    $stSite = $koneksi->prepare("SELECT nama_site FROM tb_site WHERE id_site = ?");
    $stSite->execute([$site]);
    $site_data = $stSite->fetch();
    if ($site_data) {
        $nama_site_file = str_replace(' ', '_', strtoupper(trim($site_data['nama_site'])));
    }
}

// Cek error tersembunyi (mis. ada output liar sebelum header) agar file tidak corrupt
if (headers_sent()) {
    die("<br><br><b>TIDAK BISA DOWNLOAD KARENA ADA ERROR!</b>");
}

$filename = "Export_Karyawan_Format_Import_" . $nama_site_file . "_" . date('Ymd_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
