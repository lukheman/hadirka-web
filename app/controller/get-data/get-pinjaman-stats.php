<?php
header("Content-Type: application/json");
require '../../config/koneksi.php';

// 1. Total Transaksi Pinjaman
$sqlTotal = "SELECT COUNT(*) as total FROM tb_pinjaman";
$resultTotal = $koneksi->query($sqlTotal);
$total = $resultTotal->fetch_assoc()['total'];

// 2. Total Nominal Pinjaman
$sqlNominal = "SELECT SUM(nominal_pinjaman) as total_nominal FROM tb_pinjaman";
$resultNominal = $koneksi->query($sqlNominal);
$totalNominal = $resultNominal->fetch_assoc()['total_nominal'] ?: 0;

// 3. Total Pinjaman Bulan Ini (Berdasarkan tgl_pinjaman)
$bulanIni = date('Y-m');
$sqlBulanIni = "SELECT COUNT(*) as bulan_ini 
                FROM tb_pinjaman 
                WHERE DATE_FORMAT(tgl_pinjaman, '%Y-%m') = '$bulanIni'";
$resultBulanIni = $koneksi->query($sqlBulanIni);
$bulanIniCount = $resultBulanIni->fetch_assoc()['bulan_ini'];

// Return dalam bentuk JSON
echo json_encode([
    'total' => $total,
    // Format nominal ke ribuan langsung bisa dilakukan di frontend atau backend,
    // di sini kita format sesuai dengan logic yang ada di rapel-stats
    'nominal' => number_format($totalNominal, 0, ',', '.'),
    'bulan_ini' => $bulanIniCount
]);
