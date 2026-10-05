<?php
header("Content-Type: application/json");
require '../../config/koneksi.php';

// 1. Tangkap parameter periode
$periode = isset($_GET['periode']) ? trim($_GET['periode']) : '';

// 2. Setup WHERE clause & Label Teks
$whereClause = "";
$teksPeriode = "Total Keseluruhan"; // Default jika "Semua Tahun" dipilih

$paramsStats = [];
if (!empty($periode)) {
    $whereClause = " WHERE periode = ?";
    $paramsStats[] = intval($periode);
    $teksPeriode = "Total Tahun " . intval($periode); // Menjadi "Total Tahun 2026"
}

// 3. Hitung Total Karyawan (Sesuai filter)
$sqlTotal = "SELECT COUNT(*) as total FROM tb_thr" . $whereClause;
$stTotal = $koneksi->prepare($sqlTotal);
$stTotal->execute($paramsStats);
$total = $stTotal->fetch()['total'];

// 4. Hitung Total Nominal (Sesuai filter)
$sqlNominal = "SELECT SUM(CAST(nominal_thr AS DECIMAL(15,2))) as total_nominal FROM tb_thr" . $whereClause;
$stNominal = $koneksi->prepare($sqlNominal);
$stNominal->execute($paramsStats);
$totalNominal = $stNominal->fetch()['total_nominal'] ?: 0;

// 5. Kirim respon JSON
echo json_encode([
    'total' => $total,
    'nominal' => number_format($totalNominal, 0, ',', '.'),
    'teks_periode' => $teksPeriode
]);
?>