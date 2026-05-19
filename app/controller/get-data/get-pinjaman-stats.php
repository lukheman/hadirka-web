<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

// 1. Tangkap parameter bulan (Format: YYYY-MM)
$bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';

// 2. Setup WHERE clause & Label Teks
$whereClause = "";
$teksBulan = "Total Keseluruhan"; // Default jika "Semua Bulan" dipilih

if (!empty($bulan)) {
    // Escape string untuk keamanan
    $bulan_safe = $koneksi->real_escape_string($bulan);
    $whereClause = " WHERE DATE_FORMAT(tgl_pinjaman, '%Y-%m') = '$bulan_safe'";
    
    // Ubah "2026-03" jadi "Maret 2026" untuk teks di tampilan
    $parts = explode('-', $bulan);
    $namaBulanArr = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    $teksBulan = "Total Bulan " . $namaBulanArr[$parts[1]] . " " . $parts[0];
}

// 3. Hitung Total Pinjaman (Sesuai filter)
$sqlTotal = "SELECT COUNT(*) as total FROM tb_pinjaman" . $whereClause;
$resultTotal = $koneksi->query($sqlTotal);
$total = $resultTotal->fetch_assoc()['total'];

// 4. Hitung Total Nominal (Sesuai filter)
$sqlNominal = "SELECT SUM(nominal_pinjaman) as total_nominal FROM tb_pinjaman" . $whereClause;
$resultNominal = $koneksi->query($sqlNominal);
$totalNominal = $resultNominal->fetch_assoc()['total_nominal'] ?: 0;

// 5. Kirim respon JSON
echo json_encode([
    'total' => $total,
    'nominal' => number_format($totalNominal, 0, ',', '.'),
    'teks_bulan' => $teksBulan
]);
?>