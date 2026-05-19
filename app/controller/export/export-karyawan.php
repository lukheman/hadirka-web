<?php
// Matikan error reporting agar file excel tidak corrupt
error_reporting(0);
ini_set('display_errors', 0);
ini_set('memory_limit', '512M'); 
ini_set('max_execution_time', 300);

if (ob_get_length()) {
    ob_end_clean();
}

require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// Tangkap parameter filter dari GET
$site    = $_GET['site'] ?? '';
$jabatan = $_GET['jabatan'] ?? '';
$status  = $_GET['status'] ?? 'Aktif'; 
$bulan   = $_GET['bulan'] ?? '';

// Build WHERE Clause
$where = ["1=1"];

if (!empty($status)) {
    if ($status !== 'Semua') {
        $where[] = "kar.status_karyawan = '" . $koneksi->real_escape_string($status) . "'";
    }
} else {
    $where[] = "kar.status_karyawan = 'Aktif'";
}

if (!empty($site)) {
    $where[] = "k.id_site = '" . $koneksi->real_escape_string($site) . "'";
}

if (!empty($jabatan)) {
    $where[] = "k.id_jabatan = '" . $koneksi->real_escape_string($jabatan) . "'";
}

if (!empty($bulan)) {
    $where[] = "MONTH(k.tgl_selesai) = '" . $koneksi->real_escape_string($bulan) . "'";
}

$where_sql = implode(" AND ", $where);

// Eksekusi Query Utama (Sama seperti get-karyawan.php)
$query = "SELECT 
        kar.*, i.*, k.*,
        s.nama_site, d.nama_departemen, j.nama_jabatan,
        te.tgl_keluar, te.alasan_keluar,
        kon1.nama_kontak AS nama_kontak1, kon1.hubungan AS hubungan1, kon1.no_telp_darurat AS no_telp_darurat1,
        kon2.nama_kontak AS nama_kontak2, kon2.hubungan AS hubungan2, kon2.no_telp_darurat AS no_telp_darurat2
    FROM tb_karyawan kar
    LEFT JOIN tb_identitas i ON kar.id_karyawan = i.id_karyawan
    LEFT JOIN tb_karier k ON kar.id_karyawan = k.id_karyawan
    LEFT JOIN tb_site s ON k.id_site = s.id_site
    LEFT JOIN tb_departemen d ON k.id_departemen = d.id_departemen
    LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
    LEFT JOIN tb_tereliminasi te ON kar.id_karyawan = te.id_karyawan
    LEFT JOIN tb_kontak_darurat kon1 ON kar.id_karyawan = kon1.id_karyawan AND kon1.nomor_urut = 1
    LEFT JOIN tb_kontak_darurat kon2 ON kar.id_karyawan = kon2.id_karyawan AND kon2.nomor_urut = 2
    WHERE $where_sql
    ORDER BY kar.nama_lengkap ASC";

$result = $koneksi->query($query);

// Cek jika error query
if (!$result) {
    die("Query Error: " . $koneksi->error);
}

// Inisialisasi PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Tulis Header Kolom Sesuai File Database Karyawan IPIP.xlsx
$headers = [
    'A1' => 'No', 'B1' => 'NIK Karyawan', 'C1' => 'Nama Lengkap', 'D1' => 'Jenis Kelamin (L/P)', 
    'E1' => 'Site', 'F1' => 'Departemen', 'G1' => 'Jabatan', 'H1' => 'Tanggal Masuk Site',
    'I1' => 'Tanggal Selesai Kontrak', 'J1' => 'Tempat Lahir', 'K1' => 'Tanggal Lahir', 'L1' => 'Kewarganegaraan',
    'M1' => 'Agama', 'N1' => 'Jenjang Pendidikan', 'O1' => 'Pendidikan Terakhir', 'P1' => 'Jurusan', 
    'Q1' => 'Atas Nama Rekening', 'R1' => 'Nomor Rekening', 'S1' => 'Nama Bank', 'T1' => 'Nama Ibu Kandung',
    'U1' => 'NIK KTP', 'V1' => 'Alamat KTP', 'W1' => 'RT/RW', 'X1' => 'Desa', 
    'Y1' => 'Kecamatan', 'Z1' => 'Kabupaten', 'AA1' => 'Provinsi', 'AB1' => 'Email Pribadi', 
    'AC1' => 'No Telepon', 'AD1' => 'Nama Kontak Darurat 1', 'AE1' => 'Hubungan 1', 'AF1' => 'No Telepon Darurat 1', 
    'AG1' => 'Nama Kontak Darurat 2', 'AH1' => 'Hubungan 2', 'AI1' => 'No Telepon Darurat 2', 'AJ1' => 'NPWP', 
    'AK1' => 'BPJS Ketenagakerjaan', 'AL1' => 'BPJS Kesehatan', 'AM1' => 'Status Pernikahan', 'AN1' => 'Nomor KK', 
    'AO1' => 'Status Karyawan', 'AP1' => 'Tanggal Keluar', 'AQ1' => 'Keterangan'
];

foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

$rowNum = 2; 
$no = 1;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNum, $no++);
        $sheet->setCellValueExplicit('B' . $rowNum, $row['nik_karyawan'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValue('C' . $rowNum, $row['nama_lengkap']);
        $sheet->setCellValue('D' . $rowNum, $row['jenis_kelamin']);
        $sheet->setCellValue('E' . $rowNum, $row['nama_site'] ?? '-');
        $sheet->setCellValue('F' . $rowNum, $row['nama_departemen'] ?? '-');
        $sheet->setCellValue('G' . $rowNum, $row['nama_jabatan'] ?? '-');
        $sheet->setCellValue('H' . $rowNum, $row['tgl_mulai'] ?? '-');
        $sheet->setCellValue('I' . $rowNum, $row['tgl_selesai'] ?? '-');
        $sheet->setCellValue('J' . $rowNum, $row['tempat_lahir'] ?? '-');
        $sheet->setCellValue('K' . $rowNum, $row['tgl_lahir'] ?? '-');
        $sheet->setCellValue('L' . $rowNum, $row['nationality'] ?? '-');
        $sheet->setCellValue('M' . $rowNum, $row['agama'] ?? '-');
        $sheet->setCellValue('N' . $rowNum, $row['education_level'] ?? '-');
        $sheet->setCellValue('O' . $rowNum, $row['last_education'] ?? '-');
        $sheet->setCellValue('P' . $rowNum, $row['major'] ?? '-');
        $sheet->setCellValue('Q' . $rowNum, $row['atas_nama_rek'] ?? '-');
        $sheet->setCellValueExplicit('R' . $rowNum, $row['no_rek'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValue('S' . $rowNum, $row['nama_bank'] ?? '-');
        $sheet->setCellValue('T' . $rowNum, $row['nama_ibu'] ?? '-');
        $sheet->setCellValueExplicit('U' . $rowNum, $row['nik_ktp'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValue('V' . $rowNum, $row['alamat_ktp'] ?? '-');
        $sheet->setCellValueExplicit('W' . $rowNum, $row['rt_rw'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValue('X' . $rowNum, $row['desa'] ?? '-');
        $sheet->setCellValue('Y' . $rowNum, $row['kecamatan'] ?? '-');
        $sheet->setCellValue('Z' . $rowNum, $row['kabupaten'] ?? '-');
        $sheet->setCellValue('AA' . $rowNum, $row['provinsi'] ?? '-');
        $sheet->setCellValue('AB' . $rowNum, $row['email_pribadi'] ?? '-');
        $sheet->setCellValueExplicit('AC' . $rowNum, $row['no_telp'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValue('AD' . $rowNum, $row['nama_kontak1'] ?? '-');
        $sheet->setCellValue('AE' . $rowNum, $row['hubungan1'] ?? '-');
        $sheet->setCellValueExplicit('AF' . $rowNum, $row['no_telp_darurat1'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValue('AG' . $rowNum, $row['nama_kontak2'] ?? '-');
        $sheet->setCellValue('AH' . $rowNum, $row['hubungan2'] ?? '-');
        $sheet->setCellValueExplicit('AI' . $rowNum, $row['no_telp_darurat2'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('AJ' . $rowNum, $row['npwp'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('AK' . $rowNum, $row['bpjs_tk'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('AL' . $rowNum, $row['bpjs_kes'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValue('AM' . $rowNum, $row['status_pernikahan'] ?? '-');
        $sheet->setCellValueExplicit('AN' . $rowNum, $row['kk_number'] ?? '-', DataType::TYPE_STRING);
        $sheet->setCellValue('AO' . $rowNum, $row['status_karyawan'] ?? '-');
        $sheet->setCellValue('AP' . $rowNum, $row['tgl_keluar'] ?? '-');
        $sheet->setCellValue('AQ' . $rowNum, $row['alasan_keluar'] ?? '-');

        $rowNum++;
    }
}

// Auto-size kolom agar rapi (Sesuai kolom terakhir yaitu AQ)
foreach (range('A', 'Z') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
foreach (range('A', 'Q') as $col) { $sheet->getColumnDimension('A'.$col)->setAutoSize(true); }

// Logika nama site untuk nama file
$nama_site_file = "SEMUA_SITE"; 
if (!empty($site)) {
    $q_site = $koneksi->query("SELECT nama_site FROM tb_site WHERE id_site = '" . $koneksi->real_escape_string($site) . "'");
    if ($q_site && $q_site->num_rows > 0) {
        $site_data = $q_site->fetch_assoc();
        $nama_site_file = str_replace(' ', '_', strtoupper(trim($site_data['nama_site'])));
    }
}

// Cek error tersembunyi
if (headers_sent() || error_get_last()) {
    die("<br><br><b>TIDAK BISA DOWNLOAD KARENA ADA ERROR!</b>");
}

$filename = "Export_Database_Karyawan_" . $nama_site_file . "_" . date('Ymd_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>