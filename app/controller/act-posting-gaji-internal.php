<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

$tgl_awal = $koneksi->real_escape_string($_POST['tgl_awal'] ?? '');
$tgl_akhir = $koneksi->real_escape_string($_POST['tgl_akhir'] ?? '');

if (empty($tgl_awal) || empty($tgl_akhir)) {
    echo json_encode(['status' => 'error', 'msg' => 'Pilih Range Tanggal terlebih dahulu!']);
    exit;
}

$tgl_posting = $tgl_akhir;

try {
    // 1. Ambil Master Akun
    // ---> PERUBAHAN DI SINI: Kode akun diubah menjadi 4000 untuk Beban Gaji Internal
    $qBebanGaji = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '4000' LIMIT 1");
    $id_beban_gaji = mysqli_fetch_assoc($qBebanGaji)['id_kategori'] ?? null;

    $qKas = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '1001' LIMIT 1");
    $id_kas = mysqli_fetch_assoc($qKas)['id_kategori'] ?? null;

    $qPiutang = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '1304' LIMIT 1");
    $id_piutang = mysqli_fetch_assoc($qPiutang)['id_kategori'] ?? null;

    // ---> PERUBAHAN DI SINI: Update pesan error untuk akun 4000
    if (!$id_beban_gaji || !$id_kas || !$id_piutang) {
        throw new Exception("Setup Akun (4000/1001/1304) belum lengkap di tb_kategori_keuangan.");
    }

    // 2. Hitung Semua Komponen Menggunakan Sub-Query
    $sqlHitung = "SELECT 
        SUM(k.gaji_pokok) AS total_gapok,
        (SELECT IFNULL(SUM(nominal), 0) FROM tb_bonus_internal WHERE tgl_bonus BETWEEN '$tgl_awal' AND '$tgl_akhir' AND id_karyawan_internal IN (SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE status_karyawan = 'Aktif')) AS total_bonus,
        (SELECT IFNULL(SUM(nominal), 0) FROM tb_rapel_internal WHERE tgl_rapel BETWEEN '$tgl_awal' AND '$tgl_akhir' AND id_karyawan_internal IN (SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE status_karyawan = 'Aktif')) AS total_rapel,
        (SELECT IFNULL(SUM(nominal), 0) FROM tb_potongan_internal WHERE tgl_potongan BETWEEN '$tgl_awal' AND '$tgl_akhir' AND id_karyawan_internal IN (SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE status_karyawan = 'Aktif')) AS total_potongan,
        (SELECT IFNULL(SUM(nominal_pinjaman), 0) FROM tb_pinjaman_internal WHERE tgl_bayar_pinjaman BETWEEN '$tgl_awal' AND '$tgl_akhir' AND id_karyawan_internal IN (SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE status_karyawan = 'Aktif')) AS total_pinjaman
        FROM tb_karyawan_internal k WHERE k.status_karyawan = 'Aktif'";

    $qHitung = $koneksi->query($sqlHitung);
    $resHitung = $qHitung->fetch_assoc();

    $gapok = floatval($resHitung['total_gapok']);
    $bonus = floatval($resHitung['total_bonus']);
    $rapel = floatval($resHitung['total_rapel']);
    $potongan = floatval($resHitung['total_potongan']);
    $pinjaman = floatval($resHitung['total_pinjaman']);

    // 3. RUMUS AKUNTANSI
    // Pendapatan Kotor dikurangi Potongan internal murni (Beban Gaji sesungguhnya yang dikeluarkan PT)
    $beban_gaji = ($gapok + $bonus + $rapel) - $potongan;
    
    // Gaji Bersih yang benar-benar ditransfer/diberikan tunai ke karyawan
    $kas_keluar = $beban_gaji - $pinjaman;

    if ($kas_keluar <= 0) {
        throw new Exception("Total gaji bersih Rp 0. Tidak ada yang diposting.");
    }

    $ket_posting = "Auto-Posting Gaji Internal: $tgl_awal s/d $tgl_akhir";

    // Hapus data postingan lama jika re-posting
    mysqli_query($koneksi, "DELETE FROM tb_transaksi_keuangan WHERE keterangan = '$ket_posting'");

    mysqli_begin_transaction($koneksi);

    // JURNAL 1: Beban Gaji Karyawan Internal (Debit) -> Kode 4000
    $stmt1 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) VALUES (?, ?, ?, 'Keluar', 'debit', ?)");
    $stmt1->bind_param("sids", $tgl_posting, $id_beban_gaji, $beban_gaji, $ket_posting);
    $stmt1->execute();

    // JURNAL 2: Kas Keluar (Kredit) -> Kode 1001
    $stmt2 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) VALUES (?, ?, ?, 'Keluar', 'kredit', ?)");
    $stmt2->bind_param("sids", $tgl_posting, $id_kas, $kas_keluar, $ket_posting);
    $stmt2->execute();

    // JURNAL 3: Piutang Pinjaman Karyawan (Kredit) -> Kode 1301 (Jika ada potongan pinjaman)
    if ($pinjaman > 0) {
        $stmt3 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) VALUES (?, ?, ?, 'Keluar', 'kredit', ?)");
        $stmt3->bind_param("sids", $tgl_posting, $id_piutang, $pinjaman, $ket_posting);
        $stmt3->execute();
    }

    mysqli_commit($koneksi);

    $tgl_format_indo = date('d-m-Y', strtotime($tgl_posting));
    echo json_encode([
        'status' => 'success',
        'msg' => "Posting Berhasil!\nBeban Gaji Internal: Rp " . number_format($beban_gaji, 0, ',', '.') . "\nKas Keluar: Rp " . number_format($kas_keluar, 0, ',', '.') . "\nPotong Piutang: Rp ". number_format($pinjaman, 0, ',', '.')
    ]);
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}