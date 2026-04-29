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

require '../../config/koneksi.php';
require '../../../vendor/autoload.php'; // Pastikan jumlah ../ sudah pas menuju folder root vendor

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

if (!empty($site)) {
    $where[] = "k.id_site = '" . $koneksi->real_escape_string($site) . "'";
}
if (!empty($jabatan)) {
    $where[] = "k.id_jabatan = '" . $koneksi->real_escape_string($jabatan) . "'";
}
if (!empty($status)) {
    $where[] = "kr.status_karyawan = '" . $koneksi->real_escape_string($status) . "'";
}
if (!empty($bulan)) {
    $where[] = "MONTH(k.tgl_selesai) = '" . $koneksi->real_escape_string($bulan) . "'";
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

$result = $koneksi->query($query);

// Inisialisasi PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Tulis Header Kolom
$headers = [
    'A1' => 'NO',
    'B1' => 'NIK KARYAWAN',
    'C1' => 'NAMA LENGKAP (Info)', 
    'D1' => 'SITE',
    'E1' => 'DEPARTEMEN', 
    'F1' => 'JABATAN', 
    'G1' => 'GAJI BASIC', 
    'H1' => 'TUNJANGAN DAERAH', 
    'I1' => 'TUNJANGAN HIDUP', 
    'J1' => 'TUNJANGAN MAKAN', 
    'K1' => 'TUNJANGAN SKILL',
    'L1' => 'PERFORMANCE BONUS', 
    'M1' => 'TUNJANGAN POSISI', 
    'N1' => 'OT PERTAMA', 
    'O1' => 'OT KEDUA', 
    'P1' => 'SPL', 
    'Q1' => 'OT1 SPL', 
    'R1' => 'OT2 SPL', 
    'S1' => 'POTONGAN BPJS KES', 
    'T1' => 'POTONGAN JHT', 
    'U1' => 'PPH21', 
    'V1' => 'POTONGAN IZIN', 
    'W1' => 'POTONGAN MANGKIR', 
    'X1' => 'TGL MASUK SITE', 
    'Y1' => 'TGL SELESAI KONTRAK'
];

// Set nilai header dan buat tulisan jadi bold
foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

$rowNum = 2; // Mulai isi data dari baris ke-2
$no = 1;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_karier = $row['id_karier'];

        // Siapkan variabel default untuk Tunjangan
        $t_daerah = 0; $t_hidup = 0; $t_makan = 0; $t_skill = 0; $p_bonus = 0; $t_posisi = 0;
        
        $q_tunjangan = $koneksi->query("SELECT nama_tunjangan, jumlah_tunjangan FROM tb_tunjangan WHERE id_karier='$id_karier'");
        while ($t = $q_tunjangan->fetch_assoc()) {
            $nama_t = strtoupper(trim($t['nama_tunjangan']));
            if ($nama_t === 'TUNJANGAN DAERAH') $t_daerah = $t['jumlah_tunjangan'];
            if ($nama_t === 'TUNJANGAN HIDUP') $t_hidup = $t['jumlah_tunjangan'];
            if ($nama_t === 'TUNJANGAN MAKAN') $t_makan = $t['jumlah_tunjangan'];
            if ($nama_t === 'TUNJANGAN SKILL') $t_skill = $t['jumlah_tunjangan'];
            if ($nama_t === 'PERFORMANCE BONUS') $p_bonus = $t['jumlah_tunjangan'];
            if ($nama_t === 'TUNJANGAN POSISI') $t_posisi = $t['jumlah_tunjangan'];
        }

        // Siapkan variabel default untuk Potongan
        $pot_bpjs = 0; $pot_jht = 0; $pph21 = 0;

        $q_potongan = $koneksi->query("SELECT jenis_potongan, jumlah_potongan FROM tb_potongan WHERE id_karier='$id_karier'");
        while ($p = $q_potongan->fetch_assoc()) {
            $jenis_p = strtoupper(trim($p['jenis_potongan']));
            if (strpos($jenis_p, 'BPJS KESEHATAN') !== false) $pot_bpjs = $p['jumlah_potongan'];
            if (strpos($jenis_p, 'BPJS KETENAGAKERJAAN') !== false) $pot_jht = $p['jumlah_potongan'];
            if ($jenis_p === 'PPH21') $pph21 = $p['jumlah_potongan'];
        }

        // Tulis data ke masing-masing cell
        $sheet->setCellValue('A' . $rowNum, $no++);
        // Khusus NIK kita set eksplisit sebagai String agar awalan '0' tidak hilang di Excel
        $sheet->setCellValueExplicit('B' . $rowNum, $row['nik_karyawan'], DataType::TYPE_STRING); 
        $sheet->setCellValue('C' . $rowNum, $row['nama_lengkap']);
        $sheet->setCellValue('D' . $rowNum, $row['nama_site']);
        $sheet->setCellValue('E' . $rowNum, $row['nama_departemen']);
        $sheet->setCellValue('F' . $rowNum, $row['nama_jabatan']);
        $sheet->setCellValue('G' . $rowNum, $row['gaji_pokok']);
        $sheet->setCellValue('H' . $rowNum, $t_daerah);
        $sheet->setCellValue('I' . $rowNum, $t_hidup);
        $sheet->setCellValue('J' . $rowNum, $t_makan);
        $sheet->setCellValue('K' . $rowNum, $t_skill);
        $sheet->setCellValue('L' . $rowNum, $p_bonus);
        $sheet->setCellValue('M' . $rowNum, $t_posisi);
        $sheet->setCellValue('N' . $rowNum, $row['ot_pertama']);
        $sheet->setCellValue('O' . $rowNum, $row['ot_kedua']);
        $sheet->setCellValue('P' . $rowNum, $row['spl']);
        $sheet->setCellValue('Q' . $rowNum, $row['ot1_spl']);
        $sheet->setCellValue('R' . $rowNum, $row['ot2_spl']);
        $sheet->setCellValue('S' . $rowNum, $pot_bpjs);
        $sheet->setCellValue('T' . $rowNum, $pot_jht);
        $sheet->setCellValue('U' . $rowNum, $pph21);
        $sheet->setCellValue('V' . $rowNum, $row['pot_izin']);
        $sheet->setCellValue('W' . $rowNum, $row['pot_mangkir']);
        $sheet->setCellValue('X' . $rowNum, $row['tgl_mulai']);
        $sheet->setCellValue('Y' . $rowNum, $row['tgl_selesai']);

        $rowNum++;
    }
}

// Auto-size kolom agar rapi (Opsional tapi bikin Excelnya lebih cantik)
foreach (range('A', 'Y') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set Header untuk force download sebagai file XLSX
$filename = "Export_Data_Karier_" . date('Ymd_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Tulis ke output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>