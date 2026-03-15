<?php
header("Content-Type: application/json");
require '../../config/koneksi.php';

// 1. Tangkap parameter periode
$periode = isset($_GET['periode']) ? trim($_GET['periode']) : '';

// 2. Setup WHERE clause & Label Teks
$whereClause = "";
$teksPeriode = "Total Keseluruhan"; // Default jika "Semua Tahun" dipilih

if (!empty($periode)) {
    $whereClause = " WHERE periode = '" . intval($periode) . "'";
    $teksPeriode = "Total Tahun " . intval($periode); // Menjadi "Total Tahun 2026"
}

// 3. Hitung Total Karyawan (Sesuai filter)
$sqlTotal = "SELECT COUNT(*) as total FROM tb_thr" . $whereClause;
$resultTotal = $koneksi->query($sqlTotal);
$total = $resultTotal->fetch_assoc()['total'];

// 4. Hitung Total Nominal (Sesuai filter)
$sqlNominal = "SELECT SUM(CAST(nominal_thr AS DECIMAL(15,2))) as total_nominal FROM tb_thr" . $whereClause;
$resultNominal = $koneksi->query($sqlNominal);
$totalNominal = $resultNominal->fetch_assoc()['total_nominal'] ?: 0;

// 5. Kirim respon JSON
echo json_encode([
    'total' => $total,
    'nominal' => number_format($totalNominal, 0, ',', '.'),
    'teks_periode' => $teksPeriode
]);
?>