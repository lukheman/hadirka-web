<?php
// app/controller/act-kelola-transaksi.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/config/koneksi.php'; // Sesuaikan path

$action = $_POST['action'] ?? '';
$target_dir = __DIR__ . '/../../app/uploads/bukti_transaksi/'; // Path folder upload

// Buat folder jika belum ada
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// AKSI SIMPAN DATA
if ($action == 'add') {
    $tanggal_transaksi = mysqli_real_escape_string($koneksi, $_POST['tanggal_transaksi']);
    $id_kategori       = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $tipe_arus         = mysqli_real_escape_string($koneksi, $_POST['tipe_arus']);
    $nominal           = mysqli_real_escape_string($koneksi, $_POST['nominal']);
    $keterangan        = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    if (empty($tanggal_transaksi) || empty($id_kategori) || empty($nominal)) {
        echo json_encode(['status' => 'error', 'msg' => 'Data formulir tidak lengkap!']);
        exit;
    }

    // Proses Upload dengan Validasi
    $bukti_name = '';
    if (isset($_FILES['bukti_transaksi']['name']) && $_FILES['bukti_transaksi']['name'] != '') {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 2 * 1024 * 1024; // Maksimal 2 MB

        $file_name = $_FILES['bukti_transaksi']['name'];
        $file_size = $_FILES['bukti_transaksi']['size'];
        $file_tmp  = $_FILES['bukti_transaksi']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi Ekstensi
        if (!in_array($ext, $allowed_extensions)) {
            echo json_encode(['status' => 'error', 'msg' => 'Format file ditolak! Hanya izinkan JPG, PNG, atau PDF.']);
            exit;
        }

        // Validasi Ukuran File
        if ($file_size > $max_size) {
            echo json_encode(['status' => 'error', 'msg' => 'Ukuran file terlalu besar! Maksimal 2MB.']);
            exit;
        }

        // Eksekusi Upload
        $bukti_name = 'TRX-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
        if (!move_uploaded_file($file_tmp, $target_dir . $bukti_name)) {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal mengupload file ke server.']);
            exit;
        }
    }

    $query = "INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, keterangan, bukti_transaksi) 
              VALUES ('$tanggal_transaksi', '$id_kategori', '$nominal', '$tipe_arus', '$keterangan', '$bukti_name')";

    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Gagal menyimpan ke database']);
    }
    exit;
}

// AKSI EDIT DATA
if ($action == 'edit') {
    $id_transaksi      = mysqli_real_escape_string($koneksi, $_POST['id_transaksi']);
    $tanggal_transaksi = mysqli_real_escape_string($koneksi, $_POST['tanggal_transaksi']);
    $id_kategori       = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $tipe_arus         = mysqli_real_escape_string($koneksi, $_POST['tipe_arus']);
    $nominal           = mysqli_real_escape_string($koneksi, $_POST['nominal']);
    $keterangan        = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    if (empty($id_transaksi) || empty($tanggal_transaksi) || empty($id_kategori) || empty($nominal)) {
        echo json_encode(['status' => 'error', 'msg' => 'Data formulir tidak lengkap!']);
        exit;
    }

    // Ambil data file lama
    $q_old = mysqli_query($koneksi, "SELECT bukti_transaksi FROM tb_transaksi_keuangan WHERE id_transaksi = '$id_transaksi'");
    $r_old = mysqli_fetch_assoc($q_old);
    $bukti_lama = $r_old['bukti_transaksi'];

    $update_bukti = "";
    if (isset($_FILES['bukti_transaksi']['name']) && $_FILES['bukti_transaksi']['name'] != '') {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 1048576; // Maksimal 1 MB (1024 * 1024)

        $file_name = $_FILES['bukti_transaksi']['name'];
        $file_size = $_FILES['bukti_transaksi']['size'];
        $file_tmp  = $_FILES['bukti_transaksi']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi Ekstensi
        if (!in_array($ext, $allowed_extensions)) {
            echo json_encode(['status' => 'error', 'msg' => 'Format file ditolak! Hanya izinkan JPG, PNG, atau PDF.']);
            exit;
        }

        // Validasi Ukuran File (Backend)
        if ($file_size > $max_size) {
            echo json_encode(['status' => 'error', 'msg' => 'Ukuran file terlalu besar! Maksimal 1MB.']);
            exit;
        }

        // Eksekusi Upload
        $bukti_name = 'TRX-' . time() . '-' . rand(1000, 9999) . '.' . $ext;

        if (move_uploaded_file($file_tmp, $target_dir . $bukti_name)) {
            // Hapus file lama jika ada
            if (!empty($bukti_lama) && file_exists($target_dir . $bukti_lama)) {
                @unlink($target_dir . $bukti_lama);
            }
            $update_bukti = ", bukti_transaksi = '$bukti_name'";
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal mengupload file ke server.']);
            exit;
        }
    }

    $query = "UPDATE tb_transaksi_keuangan 
              SET tanggal_transaksi = '$tanggal_transaksi', 
                  id_kategori = '$id_kategori', 
                  nominal = '$nominal', 
                  tipe_arus = '$tipe_arus', 
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

// AKSI HAPUS DATA
if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);

    // Hapus file fisik terlebih dahulu
    $q_old = mysqli_query($koneksi, "SELECT bukti_transaksi FROM tb_transaksi_keuangan WHERE id_transaksi = '$id'");
    if ($r_old = mysqli_fetch_assoc($q_old)) {
        if (!empty($r_old['bukti_transaksi']) && file_exists($target_dir . $r_old['bukti_transaksi'])) {
            @unlink($target_dir . $r_old['bukti_transaksi']);
        }
    }

    $query = "DELETE FROM tb_transaksi_keuangan WHERE id_transaksi = '$id'";
    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Gagal menghapus data']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'msg' => 'Action tidak dikenali']);
exit;
