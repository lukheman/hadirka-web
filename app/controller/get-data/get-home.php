<?php
error_reporting(0);
header('Content-Type: application/json');
// FIX: Tambahkan ini agar perhitungan massal tidak timeout
header('X-LiteSpeed-No-Timeout: 1');
set_time_limit(0);

include __DIR__ . "/../../config/koneksi.php";
include __DIR__ . "/../../config/function.php";
include __DIR__ . "/../../config/function-slip.php";

$id_priode = isset($_GET['id_priode']) ? $_GET['id_priode'] : '';
$id_site = isset($_GET['id_site']) ? $_GET['id_site'] : '';
$only_karyawan = isset($_GET['only_karyawan']) ? $_GET['only_karyawan'] : '0';

// ==============================================================
// 1. MENGAMBIL JUMLAH KARYAWAN AKTIF PER JABATAN (DIATASKAN)
// ==============================================================
$karyawan_per_jabatan = [];
$whereJabatan = "kar.status_karyawan = 'Aktif'";
$paramsJabatan = [];

// Terapkan filter site jika ada
if (!empty($id_site)) {
    $whereJabatan .= " AND k.id_site = ?";
    $paramsJabatan[] = $id_site;
}

$sqlJabatan = "SELECT j.nama_jabatan, COUNT(kar.nik_karyawan) as jumlah
               FROM tb_karyawan kar
               JOIN tb_karier k ON kar.id_karyawan = k.id_karyawan
               JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
               WHERE $whereJabatan
               GROUP BY j.id_jabatan, j.nama_jabatan
               ORDER BY jumlah DESC";

$stJabatan = $koneksi->prepare($sqlJabatan);
$stJabatan->execute($paramsJabatan);
$qJabatan = $stJabatan;
if ($qJabatan) {
    while ($rowJ = $qJabatan->fetch()) {
        $karyawan_per_jabatan[] = [
            'jabatan' => $rowJ['nama_jabatan'] ? $rowJ['nama_jabatan'] : 'Belum Ditugaskan',
            'jumlah' => (int)$rowJ['jumlah']
        ];
    }
}

// JIKA HANYA REQUEST DATA KARYAWAN (Bypass perhitungan gaji agar server tidak berat)
if ($only_karyawan === '1') {
    echo json_encode([
        "status" => "success",
        "karyawan_per_jabatan" => $karyawan_per_jabatan
    ]);
    exit;
}


// ==============================================================
// 2. CARI PERIODE SAAT INI (YANG DIPILIH ATAU YANG TERBARU)
// ==============================================================
if (!empty($id_priode)) {
    $stCur = $koneksi->prepare("SELECT * FROM tb_priode WHERE id_priode = ? AND type_priode = 'slip gaji'");
    $stCur->execute([$id_priode]);
    $qCur = $stCur;
} else {
    $qCur = $koneksi->query("SELECT * FROM tb_priode WHERE type_priode = 'slip gaji' ORDER BY tgl_mulai DESC LIMIT 1");
}

$pCur = $qCur->fetch();
if (!$pCur) {
    echo json_encode(["status" => "error", "message" => "Tidak ada periode slip gaji ditemukan"]);
    exit;
}

$mulai_cur = $pCur['tgl_mulai'];
$selesai_cur = $pCur['tgl_selesai'];
$periode_text = $mulai_cur . " - " . $selesai_cur;

// 3. CARI PERIODE SEBELUMNYA UNTUK PERBANDINGAN
$stPrev = $koneksi->prepare("SELECT * FROM tb_priode WHERE type_priode = 'slip gaji' AND tgl_mulai < ? ORDER BY tgl_mulai DESC LIMIT 1");
$stPrev->execute([$mulai_cur]);
$qPrev = $stPrev;
$pPrev = $qPrev->fetch();

function hitungTotalPeriode($koneksi, $mulai, $selesai)
{
    $t_gaji = 0;
    $t_pinjaman = 0;

    $sqlKaryawan = "SELECT k.nik_karyawan
                    FROM tb_karyawan k
                    LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
                    WHERE kr.tgl_mulai <= ?
                      AND (kr.tgl_selesai IS NULL OR kr.tgl_selesai >= ?)";

    $stKaryawan = $koneksi->prepare($sqlKaryawan);
    $stKaryawan->execute([$selesai, $mulai]);
    $qKaryawan = $stKaryawan;
    if ($qKaryawan) {
        while ($r = $qKaryawan->fetch()) {
            if (function_exists('generateSlipGaji')) {
                $slip = generateSlipGaji($r['nik_karyawan'], $mulai, $selesai);
                if (!isset($slip['error']) && !isset($slip['skip'])) {
                    // UBAH DARI: $gaji_net = (int)($slip['gaji_bersih'] ?? 0);
                    // MENJADI:
                    $gaji_net = (int)round($slip['gaji_bersih'] ?? 0);
                    // Samakan dengan Payroll: Abaikan jika gajinya minus
                    if ($gaji_net > 0) {
                        $t_gaji += $gaji_net;
                    }
                }
            }
        }
    }

    // Tabel tb_pinjaman dihapus (penyederhanaan gaji Okt 2026): pinjaman = 0.
    $t_pinjaman = 0;

    return ['gaji' => $t_gaji, 'pinjaman' => $t_pinjaman];
}

function hitungPersentase($sekarang, $sebelumnya)
{
    if ($sebelumnya == 0) {
        return $sekarang > 0 ? 100 : 0;
    }
    $hasil = (($sekarang - $sebelumnya) / $sebelumnya) * 100;
    return round($hasil, 2);
}

// 4. EKSEKUSI PERHITUNGAN
$data_cur = hitungTotalPeriode($koneksi, $mulai_cur, $selesai_cur);
$data_prev = ['gaji' => 0, 'pinjaman' => 0];
if ($pPrev) {
    $data_prev = hitungTotalPeriode($koneksi, $pPrev['tgl_mulai'], $pPrev['tgl_selesai']);
}

$perc_gaji = hitungPersentase($data_cur['gaji'], $data_prev['gaji']);
$perc_pinjaman = hitungPersentase($data_cur['pinjaman'], $data_prev['pinjaman']);

$total_keseluruhan_cur = $data_cur['gaji'] + $data_cur['pinjaman'];
$total_keseluruhan_prev = $data_prev['gaji'] + $data_prev['pinjaman'];
$perc_total = hitungPersentase($total_keseluruhan_cur, $total_keseluruhan_prev);

// 5. OUTPUT KE JSON KESELURUHAN
ob_clean(); // FIX: Pastikan tidak ada spasi kosong yang merusak JSON
echo json_encode([
    "status" => "success",
    "periode" => $periode_text,
    "total_gaji" => $data_cur['gaji'],
    "total_pinjaman" => $data_cur['pinjaman'],
    "perc_gaji" => $perc_gaji,
    "perc_pinjaman" => $perc_pinjaman,
    "perc_total" => $perc_total,
    "karyawan_per_jabatan" => $karyawan_per_jabatan
]);
exit;
