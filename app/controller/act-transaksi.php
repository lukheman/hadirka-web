<?php
session_start();
require_once 'config/koneksi.php'; 

// AKSI SIMPAN DATA (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal_transaksi = mysqli_real_escape_string($conn, $_POST['tanggal_transaksi']);
    $id_kategori       = mysqli_real_escape_string($conn, $_POST['id_kategori']);
    $tipe_arus         = mysqli_real_escape_string($conn, $_POST['tipe_arus']);
    $nominal           = mysqli_real_escape_string($conn, $_POST['nominal']);
    $keterangan        = mysqli_real_escape_string($conn, $_POST['keterangan']);

    if(empty($tanggal_transaksi) || empty($id_kategori) || empty($nominal)) {
        echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
        exit;
    }

    $query = "INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, keterangan) 
              VALUES ('$tanggal_transaksi', '$id_kategori', '$nominal', '$tipe_arus', '$keterangan')";

    if (mysqli_query($conn, $query)) {
        // Redirect ke halaman tabel transaksi setelah berhasil
        echo "<script>window.location.href='manajemen-transaksi.php';</script>";
    } else {
        echo "<script>alert('Gagal!'); window.history.back();</script>";
    }
}

// AKSI HAPUS DATA (GET)
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $query = "DELETE FROM tb_transaksi_keuangan WHERE id_transaksi = '$id'";
    if (mysqli_query($conn, $query)) {
        echo "<script>window.location.href='manajemen-transaksi.php';</script>";
    }
}
?>