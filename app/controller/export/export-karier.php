<?php
// 1. NYALAKAN ERROR UNTUK DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. TAMBAHKAN MEMORY LIMIT (Sangat penting untuk PhpSpreadsheet)
ini_set('memory_limit', '512M'); 
ini_set('max_execution_time', 300);

// 3. BERSIHKAN BUFFER DENGAN AMAN
if (ob_get_length()) {
    ob_end_clean();
}

require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php'; // Pastikan jumlah ../ sudah pas menuju folder root vendor

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// Tangkap parameter filter dari GET
$site    = $_GET['site'] ?? '';
$jabatan = $_GET['jabatan'] ?? '';
$status  = $_GET['status'] ?? 'Aktif'; 
$bulan   = $_GET['bulan'] ?? '';
$ids     = $_GET['ids'] ?? ''; // Tambahan: Tangkap parameter IDs

// Build WHERE Clause
$where = ["1=1"];
$params = [];

if (!empty($ids)) {
    // JIKA ADA DATA YANG DICENTANG: 
    // Pecah string ID menjadi array, pastikan semuanya integer, lalu gabungkan lagi (untuk keamanan)
    $id_array = explode(',', $ids);
    $id_array = array_map('intval', $id_array);
    $id_string = implode(',', $id_array);
    
    // Override filter lain, fokus ambil berdasarkan ID yang dicentang
    $where[] = "k.id_karier IN ($id_string)";
} else {
    // JIKA TIDAK ADA YANG DICENTANG: Gunakan logika filter biasa
    if (!empty($site)) {
        $where[] = "k.id_site = ?";
        $params[] = $site;
    }
    if (!empty($jabatan)) {
        $where[] = "k.id_jabatan = ?";
        $params[] = $jabatan;
    }
    if (!empty($status)) {
        $where[] = "kr.status_karyawan = ?";
        $params[] = $status;
    }
    if (!empty($bulan)) {
        $where[] = "MONTH(k.tgl_selesai) = ?";
        $params[] = $bulan;
    }
}

$where_sql = implode(" AND ", $where);

// Eksekusi Query Utama
$query = "SELECT k.*, j.nama_jabatan, d.nama_departemen, s.nama_site, kr.nik_karyawan, kr.nama_lengkap 
    FROM tb_karier k
    INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan
    INNER JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
    INNER JOIN tb_departemen d ON k.id_departemen = d.id_departemen
    INNER JOIN tb_site s ON k.id_site = s.id_site
    WHERE $where_sql
    ORDER BY kr.nama_lengkap ASC";

$stmt = $koneksi->prepare($query);
$stmt->execute($params);
$rowsKarier = $stmt->fetchAll();

// Inisialisasi PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Tulis Header Kolom
$headers = [
    'A1' => 'NO',
    'B1' => 'NIK KARYAWAN',
    'C1' => 'NAMA LENGKAP', 
    'D1' => 'SITE',
    'E1' => 'DEPARTEMEN', 
    'F1' => 'JABATAN', 
    'G1' => 'GAJI BASIC (HARIAN)',
    'T1' => 'TGL MASUK SITE', 
    'U1' => 'TGL SELESAI KONTRAK',
    'V1' => 'NO SURAT'
];

// Set nilai header dan buat tulisan jadi bold
foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

$rowNum = 2; // Mulai isi data dari baris ke-2
$no = 1;

if (count($rowsKarier) > 0) {
    foreach ($rowsKarier as $row) {
        // Tulis data ke masing-masing cell (hanya gaji pokok; kolom H-S dihapus)
        $sheet->setCellValue('A' . $rowNum, $no++);
        // Khusus NIK kita set eksplisit sebagai String agar awalan '0' tidak hilang di Excel
        $sheet->setCellValueExplicit('B' . $rowNum, $row['nik_karyawan'], DataType::TYPE_STRING); 
        $sheet->setCellValue('C' . $rowNum, $row['nama_lengkap']);
        $sheet->setCellValue('D' . $rowNum, $row['nama_site']);
        $sheet->setCellValue('E' . $rowNum, $row['nama_departemen']);
        $sheet->setCellValue('F' . $rowNum, $row['nama_jabatan']);
        $sheet->setCellValue('G' . $rowNum, $row['gaji_pokok']);
        $sheet->setCellValue('T' . $rowNum, $row['tgl_mulai']);
        $sheet->setCellValue('U' . $rowNum, $row['tgl_selesai']);
        $sheet->setCellValue('V' . $rowNum, $row['no_surat'] ?? '');

        $rowNum++;
    }
}

// Auto-size kolom agar rapi (Opsional tapi bikin Excelnya lebih cantik)
foreach (range('A', 'Y') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// ==== LOGIKA NAMA SITE UNTUK NAMA FILE ====
$nama_site_file = "SEMUA_SITE"; // Default jika tidak ada site yang difilter

if (!empty($site)) {
    $stSite = $koneksi->prepare("SELECT nama_site FROM tb_site WHERE id_site = ?");
    $stSite->execute([$site]);
    $site_data = $stSite->fetch();
    if ($site_data) {
        // Bersihkan nama site dari spasi (ganti dengan underscore) agar rapi jadi nama file
        $nama_site_file = str_replace(' ', '_', strtoupper(trim($site_data['nama_site'])));
    }
}

// Set Header untuk force download sebagai file XLSX
$filename = "Data_Karier_" . $nama_site_file . "_" . date('Ymd_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Tulis ke output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>