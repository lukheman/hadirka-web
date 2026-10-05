<?php
// app/controller/get-data/get-laporan-keuangan.php
error_reporting(0); 
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/koneksi.php';

// Cek apakah ada filter id_priode yang dikirim
$raw_priode = isset($_GET['id_priode']) ? (string)$_GET['id_priode'] : '';
$id_priode = (preg_match('/^\d{4}-\d{2}$/', $raw_priode) === 1) ? $raw_priode : '';
$bulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

if (!empty($id_priode) && $id_priode !== 'semua') {
    $startDate = $id_priode . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));
    
    $bulanIdx = (int)date('m', strtotime($startDate)) - 1;
    $tahun = date('Y', strtotime($startDate));
    $teks_periode = $bulanIndo[$bulanIdx] . " " . $tahun;
} else {
    $bulan_ini = date('m'); 
    $tahun_ini = date('Y');
    $startDate = date('Y-m-01', strtotime("$tahun_ini-$bulan_ini-01"));
    $endDate   = date('Y-m-t', strtotime("$tahun_ini-$bulan_ini-01"));
    
    $teks_periode = $bulanIndo[(int)$bulan_ini - 1] . " " . $tahun_ini;
}

// =========================================================================================
// 1. AMBIL SEMUA DATA LANGSUNG DARI TABEL TRANSAKSI (LABA RUGI)
// =========================================================================================

// Total Pendapatan
$q_pendapatan = $koneksi->query("SELECT SUM(t.nominal) as total FROM tb_transaksi_keuangan t JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori WHERE k.kelompok = 'Pendapatan' AND t.tanggal_transaksi BETWEEN '$startDate' AND '$endDate'");
$pendapatan = (int)($q_pendapatan->fetch()['total'] ?? 0);

// Total Beban Operasional Lainnya (Kecuali Gaji - 4001)
$q_operasional = $koneksi->query("SELECT SUM(t.nominal) as total FROM tb_transaksi_keuangan t JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori WHERE k.kelompok = 'Beban' AND k.kode_akun != '4001' AND t.tanggal_transaksi BETWEEN '$startDate' AND '$endDate'");
$operasional = (int)($q_operasional->fetch()['total'] ?? 0);

// Total Beban Gaji Bersih (Langsung dari hasil Auto-Posting Payroll akun 4001)
$q_gaji = $koneksi->query("SELECT SUM(t.nominal) as total FROM tb_transaksi_keuangan t JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori WHERE k.kode_akun = '4001' AND t.tanggal_transaksi BETWEEN '$startDate' AND '$endDate'");
$gaji_hr_bersih = (int)($q_gaji->fetch()['total'] ?? 0);

// =========================================================================================
// 2. KASBON & POTONGAN (Diambil dari tabel pinjaman, tidak masuk mutasi Bank)
// =========================================================================================

// Tabel tb_pinjaman dihapus (penyederhanaan gaji Okt 2026): kasbon/piutang = 0.
$pinjaman_keluar = 0;

// Cicilan Masuk & Pemotongan Gaji
$pelunasan_masuk = 0;

// Piutang Berjalan (Belum Lunas & Sudah Dicairkan)
$piutang_berjalan = 0;

// =========================================================================================
// 3. REKAPITULASI AKHIR (LABA RUGI, ARUS KAS & SALDO BANK SPESIFIK)
// =========================================================================================

// Laba Rugi
$total_beban = $operasional + $gaji_hr_bersih;
$laba_bersih = $pendapatan - $total_beban;

// Definisikan kode akun khusus Kas & Bank agar tidak double hitung dengan akun Beban/Pendapatan
$akun_kas_bank = "('1001', '1002', '1003', '1004', '1005')";

// -- MENGHITUNG SALDO AWAL DINAMIS (HANYA DARI KAS/BANK) --
$q_awal_masuk = $koneksi->query("
    SELECT SUM(t.nominal) as total 
    FROM tb_transaksi_keuangan t 
    JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori 
    WHERE t.tipe_arus = 'Masuk' 
    AND k.kode_akun IN $akun_kas_bank 
    AND t.tanggal_transaksi < '$startDate'
");
$awal_masuk = (int)($q_awal_masuk->fetch()['total'] ?? 0);

$q_awal_keluar = $koneksi->query("
    SELECT SUM(t.nominal) as total 
    FROM tb_transaksi_keuangan t 
    JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori 
    WHERE t.tipe_arus = 'Keluar' 
    AND k.kode_akun IN $akun_kas_bank 
    AND t.tanggal_transaksi < '$startDate'
");
$awal_keluar = (int)($q_awal_keluar->fetch()['total'] ?? 0);
$saldo_awal_bulan = $awal_masuk - $awal_keluar;

// -- MENGHITUNG PERGERAKAN BULAN INI SAJA (HANYA DARI KAS/BANK) --
$q_masuk = $koneksi->query("
    SELECT SUM(t.nominal) as total 
    FROM tb_transaksi_keuangan t 
    JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori 
    WHERE t.tipe_arus = 'Masuk' 
    AND k.kode_akun IN $akun_kas_bank 
    AND t.tanggal_transaksi BETWEEN '$startDate' AND '$endDate'
");
$total_uang_masuk_bulan_ini = (int)($q_masuk->fetch()['total'] ?? 0);

$q_keluar = $koneksi->query("
    SELECT SUM(t.nominal) as total 
    FROM tb_transaksi_keuangan t 
    JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori 
    WHERE t.tipe_arus = 'Keluar' 
    AND k.kode_akun IN $akun_kas_bank 
    AND t.tanggal_transaksi BETWEEN '$startDate' AND '$endDate'
");
$total_uang_keluar_bulan_ini = (int)($q_keluar->fetch()['total'] ?? 0);

// Saldo Bank Global Keseluruhan
$saldo_bank = $saldo_awal_bulan + $total_uang_masuk_bulan_ini - $total_uang_keluar_bulan_ini;

// -- MENGAMBIL SALDO SPESIFIK AKUN KAS DAN BANK (1001, 1002, 1003, 1004, 1005) --
$q_bank_spesifik = $koneksi->query("
    SELECT 
        k.kode_akun, 
        k.nama_akun,
        SUM(CASE WHEN t.tipe_arus = 'Masuk' THEN t.nominal ELSE 0 END) as total_masuk,
        SUM(CASE WHEN t.tipe_arus = 'Keluar' THEN t.nominal ELSE 0 END) as total_keluar
    FROM tb_kategori_keuangan k
    LEFT JOIN tb_transaksi_keuangan t ON k.id_kategori = t.id_kategori AND t.tanggal_transaksi <= '$endDate'
    WHERE k.kode_akun IN ('1001', '1002', '1003', '1004', '1005')
    GROUP BY k.id_kategori, k.kode_akun, k.nama_akun
    ORDER BY k.kode_akun ASC
");

$rincian_bank = [];
while($b = $q_bank_spesifik->fetch()) {
    $saldo_akun = (int)$b['total_masuk'] - (int)$b['total_keluar'];
    $rincian_bank[] = [
        'kode_akun' => $b['kode_akun'],
        'nama_akun' => $b['nama_akun'],
        'saldo'     => $saldo_akun
    ];
}


// =========================================================================================
// 4. MENGAMBIL DATA UNTUK GRAFIK (CHART.JS)
// =========================================================================================

// Data Grafik Pendapatan
$q_grafik_pendapatan = $koneksi->query("
    SELECT k.nama_akun, SUM(t.nominal) as total 
    FROM tb_transaksi_keuangan t 
    JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori 
    WHERE k.kelompok = 'Pendapatan' AND t.tanggal_transaksi BETWEEN '$startDate' AND '$endDate'
    GROUP BY k.id_kategori, k.nama_akun 
    ORDER BY total DESC
");
$chart_pendapatan = ['labels' => [], 'data' => []];
while($r = $q_grafik_pendapatan->fetch()) {
    $chart_pendapatan['labels'][] = $r['nama_akun'];
    $chart_pendapatan['data'][] = (int)$r['total'];
}

// Data Grafik Beban (Pengeluaran)
$q_grafik_beban = $koneksi->query("
    SELECT k.nama_akun, SUM(t.nominal) as total 
    FROM tb_transaksi_keuangan t 
    JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori 
    WHERE k.kelompok = 'Beban' AND t.tanggal_transaksi BETWEEN '$startDate' AND '$endDate'
    GROUP BY k.id_kategori, k.nama_akun 
    ORDER BY total DESC
");
$chart_beban = ['labels' => [], 'data' => []];
while($r = $q_grafik_beban->fetch()) {
    $chart_beban['labels'][] = $r['nama_akun'];
    $chart_beban['data'][] = (int)$r['total'];
}


// Kirim Output JSON
echo json_encode([
    'status' => 'success',
    'teks_periode' => $teks_periode,
    'pendapatan' => $pendapatan,
    'operasional' => $operasional,
    'gaji_hr' => $gaji_hr_bersih,
    'total_beban' => $total_beban,
    'laba_bersih' => $laba_bersih,
    'saldo_awal_bulan' => $saldo_awal_bulan,
    'total_uang_masuk' => $total_uang_masuk_bulan_ini, 
    'total_uang_keluar' => $total_uang_keluar_bulan_ini, 
    'saldo_bank' => $saldo_bank,
    'rincian_bank' => $rincian_bank,
    'pinjaman_keluar' => $pinjaman_keluar,
    'pelunasan_masuk' => $pelunasan_masuk, 
    'piutang_berjalan' => $piutang_berjalan,
    'chart_pendapatan' => $chart_pendapatan,   // <-- Data untuk Grafik Pie Pendapatan
    'chart_beban' => $chart_beban              // <-- Data untuk Grafik Bar Pengeluaran
]);
exit;
?>