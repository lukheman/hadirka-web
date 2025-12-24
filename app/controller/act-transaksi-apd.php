<?php
header('Content-Type: application/json');
include "../config/koneksi.php"; // Pastikan path koneksi benar

$action = $_GET['action'] ?? '';

// 1. Ambil opsi APD untuk dropdown
if ($action == 'get-options-apd') {
    $query = mysqli_query($koneksi, "SELECT id_apd, nama_apd FROM master_apd WHERE status_aktif = 'aktif' ORDER BY nama_apd ASC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// 2. Simpan Transaksi (Hanya simpan id_karyawan, id_apd, tgl, dan jumlah)
if ($action == 'save') {
    $id_k   = mysqli_real_escape_string($koneksi, $_POST['id_karyawan']);
    $id_a   = mysqli_real_escape_string($koneksi, $_POST['id_apd']);
    $tgl    = mysqli_real_escape_string($koneksi, $_POST['tanggal_pengambilan']);
    $qty    = mysqli_real_escape_string($koneksi, $_POST['jumlah']);

    if (empty($id_k) || empty($id_a)) {
        echo json_encode(['status' => 'error', 'message' => 'Karyawan dan APD harus dipilih']);
        exit;
    }

    $sql = "INSERT INTO pengambilan_apd (id_karyawan, id_apd, tanggal_pengambilan, jumlah) 
            VALUES ('$id_k', '$id_a', '$tgl', '$qty')";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}

// Tambahkan blok ini di act-transaksi-apd.php
if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $sql = "DELETE FROM pengambilan_apd WHERE id_pengambilan = '$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}
?>