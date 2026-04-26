<?php
// get-laporan-keuangan.php
require_once __DIR__ . '/../../config/koneksi.php';

$bulan_ini = isset($_GET['bulan']) ? $_GET['bulan'] : date('m'); 
$tahun_ini = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// 1. PENDAPATAN MANUAL (Dari tb_transaksi_keuangan)
$q_pendapatan = mysqli_query($koneksi, "SELECT SUM(t.nominal) as total FROM tb_transaksi_keuangan t JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori WHERE k.kelompok = 'Pendapatan' AND MONTH(t.tanggal_transaksi) = '$bulan_ini' AND YEAR(t.tanggal_transaksi) = '$tahun_ini'");
$pendapatan = mysqli_fetch_assoc($q_pendapatan)['total'] ?? 0;

// 2. BEBAN OPERASIONAL MANUAL
$q_operasional = mysqli_query($koneksi, "SELECT SUM(t.nominal) as total FROM tb_transaksi_keuangan t JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori WHERE k.kelompok = 'Beban' AND MONTH(t.tanggal_transaksi) = '$bulan_ini' AND YEAR(t.tanggal_transaksi) = '$tahun_ini'");
$operasional = mysqli_fetch_assoc($q_operasional)['total'] ?? 0;

// 3. BEBAN GAJI (Otomatis dari HR)
$q_gaji = mysqli_query($koneksi, "SELECT SUM(total_gaji) as total FROM tb_gaji WHERE MONTH(tanggal_gaji) = '$bulan_ini' AND YEAR(tanggal_gaji) = '$tahun_ini'");
$gaji_hr = mysqli_fetch_assoc($q_gaji)['total'] ?? 0;

// 4. KASBON KARYAWAN (Otomatis dari HR)
// Uang Keluar (Pencairan Kasbon Baru)
$q_pinjaman = mysqli_query($koneksi, "SELECT SUM(nominal_pinjaman) as total FROM tb_pinjaman WHERE MONTH(tgl_pinjaman) = '$bulan_ini' AND YEAR(tgl_pinjaman) = '$tahun_ini'");
$pinjaman_keluar = mysqli_fetch_assoc($q_pinjaman)['total'] ?? 0;

// Uang Masuk (Cicilan Masuk / Potong Gaji sesuai tgl bayar)
$q_pelunasan = mysqli_query($koneksi, "SELECT SUM(nominal_pinjaman) as total FROM tb_pinjaman WHERE MONTH(tgl_bayar_pinjaman) = '$bulan_ini' AND YEAR(tgl_bayar_pinjaman) = '$tahun_ini'");
$pelunasan_masuk = mysqli_fetch_assoc($q_pelunasan)['total'] ?? 0; 

// Total Piutang Berjalan (Belum Lunas)
$q_piutang = mysqli_query($koneksi, "SELECT SUM(nominal_pinjaman) as total FROM tb_pinjaman WHERE tgl_bayar_pinjaman IS NULL OR tgl_bayar_pinjaman > LAST_DAY(CONCAT('$tahun_ini', '-', '$bulan_ini', '-01'))");
$piutang_berjalan = mysqli_fetch_assoc($q_piutang)['total'] ?? 0;

// 5. POSISI KAS & BANK MANUAL
$q_masuk = mysqli_query($koneksi, "SELECT SUM(nominal) as total FROM tb_transaksi_keuangan WHERE tipe_arus = 'Masuk' AND MONTH(tanggal_transaksi) = '$bulan_ini' AND YEAR(tanggal_transaksi) = '$tahun_ini'");
$kas_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;

$q_keluar = mysqli_query($koneksi, "SELECT SUM(nominal) as total FROM tb_transaksi_keuangan WHERE tipe_arus = 'Keluar' AND MONTH(tanggal_transaksi) = '$bulan_ini' AND YEAR(tanggal_transaksi) = '$tahun_ini'");
$kas_keluar = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;

// 6. KALKULASI FINAL
$total_beban = $operasional + $gaji_hr;
$laba_bersih = $pendapatan - $total_beban;

$saldo_awal_bulan = 100000000; // Sebaiknya ambil dari record bulan sebelumnya
$total_uang_masuk_bulan_ini = $kas_masuk + $pelunasan_masuk;
$total_uang_keluar_bulan_ini = $kas_keluar + $pinjaman_keluar + $gaji_hr;
$saldo_bank = $saldo_awal_bulan + $total_uang_masuk_bulan_ini - $total_uang_keluar_bulan_ini;
?>