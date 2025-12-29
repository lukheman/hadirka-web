<?php
header("Content-Type: application/json");
require '../../config/koneksi.php';

// Total Rapel
$sqlTotal = "SELECT COUNT(*) as total FROM tb_rapel";
$resultTotal = $koneksi->query($sqlTotal);
$total = $resultTotal->fetch_assoc()['total'];

// Total Nominal
$sqlNominal = "SELECT SUM(nominal_rapel) as total_nominal FROM tb_rapel";
$resultNominal = $koneksi->query($sqlNominal);
$totalNominal = $resultNominal->fetch_assoc()['total_nominal'] ?: 0;

// Rapel Bulan Ini
$bulanIni = date('Y-m');
$sqlBulanIni = "SELECT COUNT(*) as bulan_ini 
                FROM tb_rapel 
                WHERE DATE_FORMAT(tgl_rapel, '%Y-%m') = '$bulanIni'";
$resultBulanIni = $koneksi->query($sqlBulanIni);
$bulanIniCount = $resultBulanIni->fetch_assoc()['bulan_ini'];

echo json_encode([
    'total' => $total,
    'nominal' => number_format($totalNominal, 0, ',', '.'),
    'bulan_ini' => $bulanIniCount
]);
?>