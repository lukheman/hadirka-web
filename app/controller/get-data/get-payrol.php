<?php
include '../../config/koneksi.php';
include '../../config/function.php';
include '../../config/function-slip.php';

// --- Periode Dinamis Dari AJAX ---
$startDate = $_POST['startDate'] ?? date("Y-m-01");
$endDate   = $_POST['endDate']   ?? date("Y-m-t");

// Ambil semua karyawan
$sql = "SELECT id_karyawan, nik_karyawan, nama_lengkap 
        FROM tb_karyawan 
        ORDER BY nama_lengkap ASC";

$q = mysqli_query($koneksi, $sql);

// JSON response array
$data = [];
$no = 1;

while ($r = mysqli_fetch_assoc($q)) {

    $slip = generateSlipGaji($r['nik_karyawan'], $startDate, $endDate);

    if (isset($slip['error'])) {
        $jabatan = "-";
        $gaji_bersih = 0;
    } else {
        $jabatan = $slip['jabatan'];
        $gaji_bersih = (int)$slip['gaji_bersih'];
    }

    $data[] = [
        "no" => $no++,
        "nama" => $r['nama_lengkap'],
        "jabatan" => $jabatan,
        "gaji_bersih" => number_format($gaji_bersih, 0, ',', '.')
    ];
}

// Output JSON
header("Content-Type: application/json");
echo json_encode([
    "status" => "success",
    "data" => $data
]);
exit;
