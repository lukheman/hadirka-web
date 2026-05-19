<?php
// app/controller/act-transaksi.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? '';

// AKSI SIMPAN DATA
if ($action == 'add') {
    $tanggal_transaksi = mysqli_real_escape_string($koneksi, $_POST['tanggal_transaksi']);
    $id_kategori       = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $tipe_arus         = mysqli_real_escape_string($koneksi, $_POST['tipe_arus']);
    $nominal           = mysqli_real_escape_string($koneksi, $_POST['nominal']);
    $keterangan        = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    if(empty($tanggal_transaksi) || empty($id_kategori) || empty($nominal)) {
        echo json_encode(['status' => 'error', 'msg' => 'Data formulir tidak lengkap!']);
        exit;
    }

    $query = "INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, keterangan) 
              VALUES ('$tanggal_transaksi', '$id_kategori', '$nominal', '$tipe_arus', '$keterangan')";

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

    if(empty($id_transaksi) || empty($tanggal_transaksi) || empty($id_kategori) || empty($nominal)) {
        echo json_encode(['status' => 'error', 'msg' => 'Data formulir tidak lengkap!']);
        exit;
    }

    $query = "UPDATE tb_transaksi_keuangan 
              SET tanggal_transaksi = '$tanggal_transaksi', 
                  id_kategori = '$id_kategori', 
                  nominal = '$nominal', 
                  tipe_arus = '$tipe_arus', 
                  keterangan = '$keterangan' 
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
    
    $query = "DELETE FROM tb_transaksi_keuangan WHERE id_transaksi = '$id'";
    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Gagal menghapus data']);
    }
    exit;
}

// Jika action tidak dikenali
echo json_encode(['status' => 'error', 'msg' => 'Action tidak dikenali']);
exit;
?>