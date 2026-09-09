<?php
// app/controller/act-kelola-transaksi.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? '';
$target_dir = __DIR__ . '/../uploads/bukti_transaksi/'; // Path folder upload

if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// =====================================================================
// AKSI SIMPAN DATA (AUTO DOUBLE-ENTRY)
// =====================================================================
if ($action == 'add') {
    $tanggal_transaksi = mysqli_real_escape_string($koneksi, $_POST['tanggal_transaksi']);
    $id_kategori       = mysqli_real_escape_string($koneksi, $_POST['id_kategori']); // Akun Utama (Misal: Beban / Pendapatan)
    $id_lawan          = mysqli_real_escape_string($koneksi, $_POST['id_lawan']); // Akun Lawan (Kas / Bank)
    $jenis_transaksi   = mysqli_real_escape_string($koneksi, $_POST['jenis_transaksi']); // Pemasukan / Pengeluaran
    $nominal = isset($_POST['nominal']) ? floatval(str_replace('.', '', $_POST['nominal'])) : 0;
    $keterangan        = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    if (empty($tanggal_transaksi) || empty($id_kategori) || empty($id_lawan) || empty($nominal)) {
        echo json_encode(['status' => 'error', 'msg' => 'Data formulir tidak lengkap!']);
        exit;
    }

    // Proses Upload Bukti (Dibagikan ke 2 transaksi)
    $bukti_name = '';
    if (isset($_FILES['bukti_transaksi']['name']) && $_FILES['bukti_transaksi']['name'] != '') {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 2 * 1024 * 1024; // 2 MB

        $file_name = $_FILES['bukti_transaksi']['name'];
        $file_size = $_FILES['bukti_transaksi']['size'];
        $file_tmp  = $_FILES['bukti_transaksi']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_extensions)) {
            echo json_encode(['status' => 'error', 'msg' => 'Format file ditolak! Hanya izinkan JPG, PNG, atau PDF.']);
            exit;
        }
        if ($file_size > $max_size) {
            echo json_encode(['status' => 'error', 'msg' => 'Ukuran file terlalu besar! Maksimal 2MB.']);
            exit;
        }

        $bukti_name = 'TRX-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
        if (!move_uploaded_file($file_tmp, $target_dir . $bukti_name)) {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal mengupload file ke server.']);
            exit;
        }
    }

    // --- LOGIKA AKUNTANSI PEMETAAN DEBIT & KREDIT ---
    if ($jenis_transaksi == 'Pemasukan') {
        // Jika Pemasukan Uang: 
        // 1. Akun Kas/Bank bertambah (DEBIT)
        $neraca_lawan = 'debit';
        $arus_lawan   = 'Masuk';
        // 2. Akun Utama/Sumber Uang bertambah (KREDIT)
        $neraca_utama = 'kredit';
        $arus_utama   = 'Masuk';
    } else {
        // Jika Pengeluaran Uang:
        // 1. Akun Utama/Tujuan Pengeluaran bertambah (DEBIT)
        $neraca_utama = 'debit';
        $arus_utama   = 'Keluar';
        // 2. Akun Kas/Bank berkurang (KREDIT)
        $neraca_lawan = 'kredit';
        $arus_lawan   = 'Keluar';
    }

    // GENERATE KODE REFERENSI UNTUK MENGIKAT 2 BARIS TRANSAKSI
    $kode_referensi = 'REF-' . time() . '-' . rand(100, 999);

    // Matikan auto-commit agar tersimpan bersamaan atau batal dua-duanya (Transaction Safety)
    mysqli_autocommit($koneksi, false);
    $status_ok = true;

    // 1. QUERY INSERT UNTUK AKUN UTAMA
    $query_utama = "INSERT INTO tb_transaksi_keuangan 
                    (kode_referensi, tanggal_transaksi, id_kategori, nominal, tipe_arus, keterangan, bukti_transaksi, tipe_neraca) 
                    VALUES 
                    ('$kode_referensi', '$tanggal_transaksi', '$id_kategori', '$nominal', '$arus_utama', '$keterangan', '$bukti_name', '$neraca_utama')";

    if (!mysqli_query($koneksi, $query_utama)) $status_ok = false;

    // 2. QUERY INSERT UNTUK AKUN LAWAN (KAS/BANK)
    // Berikan flag [Auto] agar di tabel terlihat ini adalah baris pasangan
    $ket_lawan = "[Auto] " . $keterangan;
    $query_lawan = "INSERT INTO tb_transaksi_keuangan 
                    (kode_referensi, tanggal_transaksi, id_kategori, nominal, tipe_arus, keterangan, bukti_transaksi, tipe_neraca) 
                    VALUES 
                    ('$kode_referensi', '$tanggal_transaksi', '$id_lawan', '$nominal', '$arus_lawan', '$ket_lawan', '$bukti_name', '$neraca_lawan')";

    if (!mysqli_query($koneksi, $query_lawan)) $status_ok = false;

    // Evaluasi Kesuksesan (Commit atau Rollback)
    if ($status_ok) {
        mysqli_commit($koneksi); // Permanenkan ke database
        echo json_encode(['status' => 'success', 'msg' => 'Jurnal Berpasangan berhasil disimpan!']);
    } else {
        mysqli_rollback($koneksi); // Batalkan semua jika ada yg gagal
        echo json_encode(['status' => 'error', 'msg' => 'Gagal menyimpan transaksi (Rollback).']);
    }

    mysqli_autocommit($koneksi, true); // Kembalikan default MySQL
    exit;
}

// =====================================================================
// AKSI EDIT DATA (RAW MODE / BARIS PER BARIS)
// =====================================================================
if ($action == 'edit') {
    $id_transaksi      = mysqli_real_escape_string($koneksi, $_POST['id_transaksi']);
    $tanggal_transaksi = mysqli_real_escape_string($koneksi, $_POST['tanggal_transaksi']);
    $id_kategori       = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $tipe_arus         = mysqli_real_escape_string($koneksi, $_POST['tipe_arus']);
    $tipe_neraca       = mysqli_real_escape_string($koneksi, $_POST['tipe_neraca']);
    $nominal = isset($_POST['nominal']) ? floatval(str_replace('.', '', $_POST['nominal'])) : 0;
    $keterangan        = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    if (empty($id_transaksi) || empty($tanggal_transaksi) || empty($id_kategori) || empty($nominal) || empty($tipe_neraca)) {
        echo json_encode(['status' => 'error', 'msg' => 'Data formulir tidak lengkap!']);
        exit;
    }

    $q_old = mysqli_query($koneksi, "SELECT bukti_transaksi FROM tb_transaksi_keuangan WHERE id_transaksi = '$id_transaksi'");
    $r_old = mysqli_fetch_assoc($q_old);
    $bukti_lama = $r_old['bukti_transaksi'];

    $update_bukti = "";
    if (isset($_FILES['bukti_transaksi']['name']) && $_FILES['bukti_transaksi']['name'] != '') {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 1048576; // 1 MB

        $file_name = $_FILES['bukti_transaksi']['name'];
        $file_size = $_FILES['bukti_transaksi']['size'];
        $file_tmp  = $_FILES['bukti_transaksi']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_extensions)) {
            echo json_encode(['status' => 'error', 'msg' => 'Format file ditolak!']);
            exit;
        }

        if ($file_size > $max_size) {
            echo json_encode(['status' => 'error', 'msg' => 'Ukuran file terlalu besar! Maksimal 1MB.']);
            exit;
        }

        $bukti_name = 'TRX-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
        if (move_uploaded_file($file_tmp, $target_dir . $bukti_name)) {
            if (!empty($bukti_lama) && file_exists($target_dir . $bukti_lama)) {
                @unlink($target_dir . $bukti_lama);
            }
            $update_bukti = ", bukti_transaksi = '$bukti_name'";
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal mengupload file.']);
            exit;
        }
    }

    $query = "UPDATE tb_transaksi_keuangan 
              SET tanggal_transaksi = '$tanggal_transaksi', 
                  id_kategori = '$id_kategori', 
                  nominal = '$nominal', 
                  tipe_arus = '$tipe_arus', 
                  tipe_neraca = '$tipe_neraca',
                  keterangan = '$keterangan' 
                  $update_bukti 
              WHERE id_transaksi = '$id_transaksi'";

    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil diperbarui']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Gagal memperbarui database']);
    }
    exit;
}

// =====================================================================
// AKSI HAPUS DATA (AUTO-DELETE PASANGAN)
// =====================================================================
if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);

    // Cek data transaksi beserta kode referensinya
    $q_cek = mysqli_query($koneksi, "SELECT kode_referensi, bukti_transaksi FROM tb_transaksi_keuangan WHERE id_transaksi = '$id'");
    
    if ($row = mysqli_fetch_assoc($q_cek)) {
        $kode_ref = $row['kode_referensi'];

        // Hapus file fisik jika ada
        if (!empty($row['bukti_transaksi']) && file_exists($target_dir . $row['bukti_transaksi'])) {
            @unlink($target_dir . $row['bukti_transaksi']);
        }

        // Jika transaksi ini punya kode_referensi, hapus keduanya (utama & pasangan). 
        // Jika null (misal: data lama sebelum sistem ini diterapkan), hapus by id saja.
        if (!empty($kode_ref)) {
            $query = "DELETE FROM tb_transaksi_keuangan WHERE kode_referensi = '$kode_ref'";
        } else {
            $query = "DELETE FROM tb_transaksi_keuangan WHERE id_transaksi = '$id'";
        }

        if (mysqli_query($koneksi, $query)) {
            echo json_encode(['status' => 'success', 'msg' => 'Transaksi beserta pasangannya berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal menghapus data di database']);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Data tidak ditemukan']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'msg' => 'Action tidak dikenali']);
exit;
