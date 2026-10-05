<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

// 1. Tangkap parameter bulan (Format: YYYY-MM)
$bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';
$site = isset($_GET['site']) ? trim($_GET['site']) : '';
$jabatan = isset($_GET['jabatan']) ? trim($_GET['jabatan']) : '';

// 2. Setup WHERE clause & Label Teks
$where = ["1=1"];
$teksBulan = "Total Keseluruhan"; // Default jika "Semua Bulan" dipilih

$paramsStats = [];
if (!empty($bulan)) {
    // PERBAIKAN: Masukkan ke array $where, bukan ditimpa
    $where[] = "DATE_FORMAT(p.tgl_potongan, '%Y-%m') = ?";
    $paramsStats[] = $bulan;
    
    $parts = explode('-', $bulan);
    $namaBulanArr = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    $teksBulan = "Total Bulan " . $namaBulanArr[$parts[1]] . " " . $parts[0];
}

if (!empty($site)) { $where[] = "kr.id_site = ?"; $paramsStats[] = $site; }
if (!empty($jabatan)) { $where[] = "kr.id_jabatan = ?"; $paramsStats[] = $jabatan; }

$whereClause = " WHERE " . implode(" AND ", $where);

// 3. Hitung Total Pinjaman (Sesuai filter)
$sqlTotal = "SELECT COUNT(DISTINCT p.id_potongan_tidak_tetap) as total 
             FROM tb_potongan_tidak_tetap p
             LEFT JOIN tb_karier kr ON p.id_karyawan = kr.id_karyawan
             $whereClause";
$stTotal = $koneksi->prepare($sqlTotal);
$stTotal->execute($paramsStats);
$total = $stTotal->fetch()['total'];

// 4. Hitung Total Nominal (Sesuai filter)
$sqlNominal = "SELECT SUM(nominal_potongan) as total_nominal FROM (
                   SELECT p.id_potongan_tidak_tetap, p.nominal_potongan 
                   FROM tb_potongan_tidak_tetap p
                   LEFT JOIN tb_karier kr ON p.id_karyawan = kr.id_karyawan
                   $whereClause
                   GROUP BY p.id_potongan_tidak_tetap
               ) as sub";
$stNominal = $koneksi->prepare($sqlNominal);
$stNominal->execute($paramsStats);
$totalNominal = $stNominal->fetch()['total_nominal'] ?: 0;

// 5. Kirim respon JSON
echo json_encode([
    'total' => $total,
    'nominal' => number_format($totalNominal, 0, ',', '.'),
    'teks_bulan' => $teksBulan
]);
?>