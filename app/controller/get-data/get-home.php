<?php
error_reporting(0); // Matikan error saat produksi agar JSON tidak rusak
header('Content-Type: application/json');

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../config/function.php";
include __DIR__ . "/../../config/function-slip.php";

$id_priode = isset($_GET['id_priode']) ? mysqli_real_escape_string($koneksi, $_GET['id_priode']) : '';

// 1. CARI PERIODE SAAT INI (YANG DIPILIH ATAU YANG TERBARU)
if (!empty($id_priode)) {
    $qCur = mysqli_query($koneksi, "SELECT * FROM tb_priode WHERE id_priode = '$id_priode' AND type_priode = 'slip gaji'");
} else {
    // Jika tidak ada ID, otomatis ambil periode slip gaji terakhir
    $qCur = mysqli_query($koneksi, "SELECT * FROM tb_priode WHERE type_priode = 'slip gaji' ORDER BY tgl_mulai DESC LIMIT 1");
}

$pCur = mysqli_fetch_assoc($qCur);

if (!$pCur) {
    echo json_encode(["status" => "error", "message" => "Tidak ada periode slip gaji ditemukan"]);
    exit;
}

$mulai_cur = $pCur['tgl_mulai'];
$selesai_cur = $pCur['tgl_selesai'];
$periode_text = $mulai_cur . " - " . $selesai_cur;

// 2. CARI PERIODE SEBELUMNYA UNTUK PERBANDINGAN (Berdasarkan tanggal mulai)
$qPrev = mysqli_query($koneksi, "SELECT * FROM tb_priode WHERE type_priode = 'slip gaji' AND tgl_mulai < '$mulai_cur' ORDER BY tgl_mulai DESC LIMIT 1");
$pPrev = mysqli_fetch_assoc($qPrev);

// ==============================================================
// FUNGSI UNTUK MENGHITUNG TOTAL GAJI & PINJAMAN BERDASARKAN TANGGAL
// ==============================================================
function hitungTotalPeriode($koneksi, $mulai, $selesai) {
    $t_gaji = 0;
    $t_pinjaman = 0;

    // Hitung Gaji (Looping seluruh karyawan aktif di rentang tanggal tersebut)
    $sqlKaryawan = "SELECT k.nik_karyawan 
                    FROM tb_karyawan k
                    LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
                    WHERE kr.tgl_mulai <= '$selesai' 
                      AND (kr.tgl_selesai IS NULL OR kr.tgl_selesai >= '$mulai')";
    
    $qKaryawan = mysqli_query($koneksi, $sqlKaryawan);
    if ($qKaryawan) {
        while ($r = mysqli_fetch_assoc($qKaryawan)) {
            if (function_exists('generateSlipGaji')) {
                $slip = generateSlipGaji($r['nik_karyawan'], $mulai, $selesai);
                if (!isset($slip['error'])) {
                    $t_gaji += (int)($slip['gaji_bersih'] ?? 0);
                }
            }
        }
    }

    // Hitung Pinjaman
    $qPinjaman = mysqli_query($koneksi,"SELECT COALESCE(SUM(nominal_pinjaman),0) AS tot FROM tb_pinjaman WHERE tgl_pinjaman BETWEEN '$mulai' AND '$selesai'");
    if ($qPinjaman) {
        $t_pinjaman = (int)mysqli_fetch_assoc($qPinjaman)['tot'];
    }

    return ['gaji' => $t_gaji, 'pinjaman' => $t_pinjaman];
}

// ==============================================================
// FUNGSI UNTUK MENGHITUNG PERSENTASE NAIK / TURUN
// ==============================================================
function hitungPersentase($sekarang, $sebelumnya) {
    if ($sebelumnya == 0) {
        // Jika periode lalu Rp 0, dan sekarang ada nominal, maka kenaikannya 100%
        return $sekarang > 0 ? 100 : 0;
    }
    // Rumus: ((Sekarang - Sebelumnya) / Sebelumnya) * 100
    $hasil = (($sekarang - $sebelumnya) / $sebelumnya) * 100;
    return round($hasil, 2); // Bulatkan 2 angka di belakang koma
}


// 3. EKSEKUSI PERHITUNGAN
$data_cur = hitungTotalPeriode($koneksi, $mulai_cur, $selesai_cur);

$data_prev = ['gaji' => 0, 'pinjaman' => 0];
if ($pPrev) {
    // Jika ada periode sebelumnya, hitung!
    $data_prev = hitungTotalPeriode($koneksi, $pPrev['tgl_mulai'], $pPrev['tgl_selesai']);
}

// 4. HITUNG PERSENTASENYA
$perc_gaji = hitungPersentase($data_cur['gaji'], $data_prev['gaji']);
$perc_pinjaman = hitungPersentase($data_cur['pinjaman'], $data_prev['pinjaman']);

$total_keseluruhan_cur = $data_cur['gaji'] + $data_cur['pinjaman'];
$total_keseluruhan_prev = $data_prev['gaji'] + $data_prev['pinjaman'];
$perc_total = hitungPersentase($total_keseluruhan_cur, $total_keseluruhan_prev);

// 5. OUTPUT KE JSON
echo json_encode([
    "status" => "success",
    "periode" => $periode_text,
    "total_gaji" => $data_cur['gaji'],
    "total_pinjaman" => $data_cur['pinjaman'],
    "perc_gaji" => $perc_gaji,
    "perc_pinjaman" => $perc_pinjaman,
    "perc_total" => $perc_total
]);
exit;