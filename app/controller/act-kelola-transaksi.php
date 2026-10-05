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
    $tanggal_transaksi = $_POST['tanggal_transaksi'];
    $id_kategori       = $_POST['id_kategori']; // Akun Utama (Misal: Beban / Pendapatan)
    $id_lawan          = $_POST['id_lawan']; // Akun Lawan (Kas / Bank)
    $jenis_transaksi   = $_POST['jenis_transaksi']; // Pemasukan / Pengeluaran
    $nominal = isset($_POST['nominal']) ? floatval(str_replace('.', '', $_POST['nominal'])) : 0;
    $keterangan        = $_POST['keterangan'];

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

    // Simpan bersamaan atau batal dua-duanya (Transaction Safety)
    $koneksi->beginTransaction();
    try {
        // 1. QUERY INSERT UNTUK AKUN UTAMA
        $st1 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan
                        (kode_referensi, tanggal_transaksi, id_kategori, nominal, tipe_arus, keterangan, bukti_transaksi, tipe_neraca)
                        VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?)");
        $st1->execute([$kode_referensi, $tanggal_transaksi, $id_kategori, $nominal, $arus_utama, $keterangan, $bukti_name, $neraca_utama]);

        // 2. QUERY INSERT UNTUK AKUN LAWAN (KAS/BANK)
        // Berikan flag [Auto] agar di tabel terlihat ini adalah baris pasangan
        $ket_lawan = "[Auto] " . $keterangan;
        $st2 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan
                        (kode_referensi, tanggal_transaksi, id_kategori, nominal, tipe_arus, keterangan, bukti_transaksi, tipe_neraca)
                        VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?)");
        $st2->execute([$kode_referensi, $tanggal_transaksi, $id_lawan, $nominal, $arus_lawan, $ket_lawan, $bukti_name, $neraca_lawan]);

        $koneksi->commit(); // Permanenkan ke database
        echo json_encode(['status' => 'success', 'msg' => 'Jurnal Berpasangan berhasil disimpan!']);
    } catch (PDOException $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack(); // Batalkan semua jika ada yg gagal
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'msg' => 'Gagal menyimpan transaksi.']);
    }
    exit;
}

// =====================================================================
// AKSI EDIT DATA (RAW MODE / BARIS PER BARIS)
// =====================================================================
if ($action == 'edit') {
    $id_transaksi      = $_POST['id_transaksi'];
    $tanggal_transaksi = $_POST['tanggal_transaksi'];
    $id_kategori       = $_POST['id_kategori'];
    $tipe_arus         = $_POST['tipe_arus'];
    $tipe_neraca       = $_POST['tipe_neraca'];
    $nominal = isset($_POST['nominal']) ? floatval(str_replace('.', '', $_POST['nominal'])) : 0;
    $keterangan        = $_POST['keterangan'];

    if (empty($id_transaksi) || empty($tanggal_transaksi) || empty($id_kategori) || empty($nominal) || empty($tipe_neraca)) {
        echo json_encode(['status' => 'error', 'msg' => 'Data formulir tidak lengkap!']);
        exit;
    }

    $q_old = $koneksi->prepare("SELECT bukti_transaksi FROM tb_transaksi_keuangan WHERE id_transaksi = ?");
    $q_old->execute([$id_transaksi]);
    $r_old = $q_old->fetch();
    $bukti_lama = $r_old['bukti_transaksi'] ?? '';

    $update_bukti_nama = null;
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
            $update_bukti_nama = $bukti_name;
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal mengupload file.']);
            exit;
        }
    }

    try {
        if ($update_bukti_nama !== null) {
            $st = $koneksi->prepare("UPDATE tb_transaksi_keuangan
                      SET tanggal_transaksi = ?,
                          id_kategori = ?,
                          nominal = ?,
                          tipe_arus = ?,
                          tipe_neraca = ?,
                          keterangan = ?,
                          bukti_transaksi = ?
                      WHERE id_transaksi = ?");
            $st->execute([$tanggal_transaksi, $id_kategori, $nominal, $tipe_arus, $tipe_neraca, $keterangan, $update_bukti_nama, $id_transaksi]);
        } else {
            $st = $koneksi->prepare("UPDATE tb_transaksi_keuangan
                      SET tanggal_transaksi = ?,
                          id_kategori = ?,
                          nominal = ?,
                          tipe_arus = ?,
                          tipe_neraca = ?,
                          keterangan = ?
                      WHERE id_transaksi = ?");
            $st->execute([$tanggal_transaksi, $id_kategori, $nominal, $tipe_arus, $tipe_neraca, $keterangan, $id_transaksi]);
        }
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil diperbarui']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'msg' => 'Gagal memperbarui database']);
    }
    exit;
}

// =====================================================================
// AKSI HAPUS DATA (AUTO-DELETE PASANGAN)
// =====================================================================
if ($action == 'delete') {
    $id = $_POST['id'];

    try {
        // Cek data transaksi beserta kode referensinya
        $q_cek = $koneksi->prepare("SELECT kode_referensi, bukti_transaksi FROM tb_transaksi_keuangan WHERE id_transaksi = ?");
        $q_cek->execute([$id]);
        $row = $q_cek->fetch();

        if ($row) {
            $kode_ref = $row['kode_referensi'];

            // Hapus file fisik jika ada
            if (!empty($row['bukti_transaksi']) && file_exists($target_dir . $row['bukti_transaksi'])) {
                @unlink($target_dir . $row['bukti_transaksi']);
            }

            // Jika transaksi ini punya kode_referensi, hapus keduanya (utama & pasangan).
            // Jika null (misal: data lama sebelum sistem ini diterapkan), hapus by id saja.
            if (!empty($kode_ref)) {
                $st = $koneksi->prepare("DELETE FROM tb_transaksi_keuangan WHERE kode_referensi = ?");
                $st->execute([$kode_ref]);
            } else {
                $st = $koneksi->prepare("DELETE FROM tb_transaksi_keuangan WHERE id_transaksi = ?");
                $st->execute([$id]);
            }

            echo json_encode(['status' => 'success', 'msg' => 'Transaksi beserta pasangannya berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Data tidak ditemukan']);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'msg' => 'Gagal menghapus data di database']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'msg' => 'Action tidak dikenali']);
exit;
