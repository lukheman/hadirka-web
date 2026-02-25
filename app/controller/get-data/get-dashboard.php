<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Biarkan 1 dulu untuk memastikan

header('Content-Type: application/json');

// Ambil lokasi root otomatis dari XAMPP (D:\xampp\htdocs\tjs-outsoorsing)
$root = $_SERVER['DOCUMENT_ROOT'] . "/tjs-outsoorsing/app";

// Panggil file menggunakan path absolut
include $root . "/config/koneksi.php";
include $root . "/config/function.php";
include $root . "/config/function-slip.php";

$mulai = "";
$selesai = "";

// 1. Cek apakah ada request filter tanggal dari Date Picker frontend
if (isset($_GET['start']) && isset($_GET['end']) && !empty($_GET['start']) && !empty($_GET['end'])) {
    $mulai = mysqli_real_escape_string($koneksi, $_GET['start']);
    $selesai = mysqli_real_escape_string($koneksi, $_GET['end']);
    $periode_text = $mulai . " - " . $selesai;
} else {
    // 2. Jika tidak ada filter, ambil periode aktif dari tb_priode
    $periode = mysqli_query($koneksi, "
        SELECT * FROM tb_priode 
        WHERE status_priode = 'Open' 
        LIMIT 1
    ");

    if(!$periode){
        echo json_encode(["status"=>"error","message"=>mysqli_error($koneksi)]);
        exit;
    }

    if(mysqli_num_rows($periode)==0){
        echo json_encode(["status"=>"error","message"=>"Tidak ada periode aktif"]);
        exit;
    }

    $p = mysqli_fetch_assoc($periode);
    $mulai   = $p['tgl_mulai'];
    $selesai = $p['tgl_selesai'];
    $periode_text = $mulai . " - " . $selesai;
}

// 3. HITUNG TOTAL GAJI SECARA DINAMIS MENGGUNAKAN FUNCTION
$total_gaji = 0;

// Ambil karyawan yang aktif di periode ini (Logika sama dengan get-payrol.php)
$sqlKaryawan = "SELECT k.nik_karyawan 
                FROM tb_karyawan k
                LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
                WHERE kr.tgl_mulai <= '$selesai' 
                  AND (kr.tgl_selesai IS NULL OR kr.tgl_selesai >= '$mulai')";

$qKaryawan = mysqli_query($koneksi, $sqlKaryawan);

if ($qKaryawan) {
    while ($r = mysqli_fetch_assoc($qKaryawan)) {
        if (function_exists('generateSlipGaji')) {
            // Hitung gaji per karyawan
            $slip = generateSlipGaji($r['nik_karyawan'], $mulai, $selesai);
            
            // Jika tidak ada error, tambahkan ke total
            if (!isset($slip['error'])) {
                $total_gaji += (int)($slip['gaji_bersih'] ?? 0);
            }
        }
    }
}

// 4. TOTAL PINJAMAN (Asumsi tabel tb_pinjaman ini ADA di database)
$qPinjaman = mysqli_query($koneksi,"
    SELECT COALESCE(SUM(nominal_pinjaman),0) AS total_pinjaman
    FROM tb_pinjaman
    WHERE tgl_pinjaman BETWEEN '$mulai' AND '$selesai'
");

// PERBAIKAN DI SINI: Pastikan nilai tidak null jika tidak ada data
$data_pinjaman = $qPinjaman ? mysqli_fetch_assoc($qPinjaman) : null;
$total_pinjaman_fix = $data_pinjaman['total_pinjaman'] ?? 0;

echo json_encode([
    "status" => "success",
    "periode" => $periode_text,
    "total_gaji" => $total_gaji,
    "total_pinjaman" => $total_pinjaman_fix // Gunakan variabel yang sudah di-fix
]);
exit;