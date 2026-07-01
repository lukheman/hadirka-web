<?php
// app/controller/act-posting-payroll.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';
require_once __DIR__ . '/../config/function-slip.php';

$startDate = $_POST['startDate'] ?? '';
$endDate   = $_POST['endDate'] ?? '';

if (empty($startDate) || empty($endDate)) {
    echo json_encode(['status' => 'error', 'message' => 'Periode tidak valid!']);
    exit;
}

$tgl_posting = date('Y-m-01', strtotime($startDate));

try {
    // 1. Ambil ID Akun Keuangan (4001, 1301, 3004, 2003)
    $qBebanGaji = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '4001' LIMIT 1");
    $id_beban_gaji = mysqli_fetch_assoc($qBebanGaji)['id_kategori'] ?? null;

    $qPiutang = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '1301' LIMIT 1");
    $id_piutang = mysqli_fetch_assoc($qPiutang)['id_kategori'] ?? null;

    $qPendapatanLain = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '3004' LIMIT 1");
    $id_pendapatan_lain = mysqli_fetch_assoc($qPendapatanLain)['id_kategori'] ?? null;

    $qTitipan = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '2003' LIMIT 1");
    $id_titipan = mysqli_fetch_assoc($qTitipan)['id_kategori'] ?? null;

    if (!$id_beban_gaji || !$id_piutang || !$id_pendapatan_lain || !$id_titipan) {
        throw new Exception("Setup Akun (4001, 1301, 3004, 2003) belum lengkap di tb_kategori_keuangan.");
    }

    $total_gaji_kotor = 0;
    $total_potong_kasbon = 0;
    $total_potong_denda = 0;
    $total_potong_titipan = 0;
    $total_gaji_bersih_rekap = 0;

    // FIX BUG: Tambahkan DISTINCT agar NIK tidak berulang jika ada double karier di bulan yang sama
    $sqlKaryawan = "SELECT DISTINCT k.nik_karyawan FROM tb_karyawan k
                    LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
                    WHERE kr.tgl_mulai <= '$endDate' 
                    AND (kr.tgl_selesai IS NULL OR kr.tgl_selesai >= '$startDate')";
    $q_karyawan = mysqli_query($koneksi, $sqlKaryawan);

    while ($karyawan = mysqli_fetch_assoc($q_karyawan)) {
        $slip = generateSlipGaji($karyawan['nik_karyawan'], $startDate, $endDate);

        if (!isset($slip['error']) && !isset($slip['skip'])) {

            // [PERBAIKAN 1]: Jika gaji bersih minus, abaikan! Sama seperti di UI data-payrol.php
            $gaji_bersih_karyawan = round($slip['gaji_bersih'] ?? 0);
            if ($gaji_bersih_karyawan < 0) {
                continue;
            }

            // TANGKAP REKAP GAJI BERSIH UNTUK POP UP
            $total_gaji_bersih_rekap += $gaji_bersih_karyawan;

            $total_gaji_kotor += round($slip['total_pendapatan'] ?? 0);
            $total_potong_kasbon += round($slip['total_potongan_pinjaman'] ?? 0);

            // Pisahkan Denda vs Titipan/Sumbangan dari Potongan Tidak Tetap
            if (isset($slip['potongan_lain']) && is_array($slip['potongan_lain'])) {
                foreach ($slip['potongan_lain'] as $pl) {
                    $jenis = strtolower($pl['nama']);

                    if (strpos($jenis, 'sumbangan') !== false || strpos($jenis, 'titipan') !== false || strpos($jenis, 'bpjs') !== false) {
                        $total_potong_titipan += round($pl['jumlah']);   // Ke Akun 2003 (Kewajiban Titipan)
                    } else {
                        $total_potong_denda += round($pl['jumlah']);   // Ke Akun 3004 (Pendapatan Lain-lain)
                    }
                }
            }

            // Ekstrak Potongan Tetap / Jabatan (BPJS, JHT, PPh21)
            if (isset($slip['potongan_tunjangan']) && is_array($slip['potongan_tunjangan'])) {
                foreach ($slip['potongan_tunjangan'] as $pt) {
                    $jenis = strtolower($pt['nama']);

                    if (strpos($jenis, 'bpjs') !== false || strpos($jenis, 'jht') !== false || strpos($jenis, 'pph') !== false) {
                        $total_potong_titipan += round($pt['jumlah']); // Masuk Kewajiban (2003)
                    } else {
                        $total_potong_denda += round($pt['jumlah']);
                    }
                }
            }

            // Potongan Sistem (Mangkir/Izin) otomatis menambah pundi Pendapatan Lain-lain (3004)
            // [PERBAIKAN]: Gunakan round() pada perhitungan bersistem desimal
            $total_potong_denda += round($slip['pot_izin'] ?? 0);
            $total_potong_denda += round($slip['pot_mangkir'] ?? 0);
            $total_potong_denda += round($slip['pot_pkwt'] ?? 0);
            $total_potong_denda += round($slip['potongan_perjam'] ?? 0);
        }
    }

    if ($total_gaji_kotor <= 0) throw new Exception("Total gaji kotor Rp 0. Tidak ada yang diposting.");
    $ket_posting = "Auto-Posting Payroll: " . date('d/m/y', strtotime($startDate)) . " - " . date('d/m/y', strtotime($endDate));

    // Bersihkan duplikat postingan jika admin reposting
    mysqli_query($koneksi, "DELETE FROM tb_transaksi_keuangan WHERE keterangan LIKE 'Auto-Posting Payroll:%' AND tanggal_transaksi = '$tgl_posting'");

    mysqli_begin_transaction($koneksi);

    // [1] Beban Gaji Kotor (Uang Keluar) -> Beban bertambah = debit
    mysqli_query($koneksi, "INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) 
                            VALUES ('$tgl_posting', '$id_beban_gaji', '$total_gaji_kotor', 'Keluar', 'debit', '$ket_posting - Beban Gaji')");

    // [2] Pelunasan Kasbon (Uang Masuk) -> Piutang (Aset) berkurang = kredit
    if ($total_potong_kasbon > 0) {
        mysqli_query($koneksi, "INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) 
                                VALUES ('$tgl_posting', '$id_piutang', '$total_potong_kasbon', 'Masuk', 'kredit', '$ket_posting - Pelunasan Pinjaman')");
    }

    // [3] Potongan Denda / Sanksi (Uang Masuk ke Akun Pendapatan 3004) -> Pendapatan bertambah = kredit
    if ($total_potong_denda > 0) {
        mysqli_query($koneksi, "INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) 
                                VALUES ('$tgl_posting', '$id_pendapatan_lain', '$total_potong_denda', 'Masuk', 'kredit', '$ket_posting - Denda/Sanksi')");
    }

    // [4] Potongan Titipan / Sumbangan (Uang Masuk Sementara ke Kewajiban 2003) -> Kewajiban bertambah = kredit
    if ($total_potong_titipan > 0) {
        mysqli_query($koneksi, "INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) 
                                VALUES ('$tgl_posting', '$id_titipan', '$total_potong_titipan', 'Masuk', 'kredit', '$ket_posting - Sumbangan/Titipan')");
    }

    mysqli_commit($koneksi);

    echo json_encode([
        'status' => 'success',
        'message' => 'Berhasil Posting ke Laporan Keuangan! Uang Keluar Bersih Kas Bank: Rp ' . number_format($total_gaji_bersih_rekap, 0, ',', '.')
    ]);
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;
