<?php
header('Content-Type: application/json');
include "../config/koneksi.php"; // Pastikan path koneksi benar

$action = $_GET['action'] ?? '';

// 1a. Ambil opsi APD untuk dropdown
if ($action == 'get-options-apd') {
    $query = mysqli_query($koneksi, "SELECT id_apd, nama_apd FROM master_apd WHERE status_aktif = 'aktif' ORDER BY nama_apd ASC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// 1b. Ambil semua karyawan untuk dropdown Select2
if ($action == 'get-all-karyawan') {
    $query = mysqli_query($koneksi, "SELECT id_karyawan, nik_karyawan, nama_lengkap FROM tb_karyawan ORDER BY nama_lengkap ASC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// 1c. Ambil detail Site, Departemen, dan Jabatan Karyawan
if ($action == 'get-karyawan-detail') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        // Gabungkan tabel karier dengan master site, departemen, dan jabatan
        $sql = "SELECT s.nama_site, d.nama_departemen, j.nama_jabatan
                FROM tb_karier c
                LEFT JOIN tb_site s ON c.id_site = s.id_site
                LEFT JOIN tb_departemen d ON c.id_departemen = d.id_departemen
                LEFT JOIN tb_jabatan j ON c.id_jabatan = j.id_jabatan
                WHERE c.id_karyawan = '$id' AND (c.tgl_selesai IS NULL OR c.tgl_selesai >= CURDATE())
                ORDER BY c.id_karier DESC LIMIT 1";
                
        $query = mysqli_query($koneksi, $sql);
        if ($row = mysqli_fetch_assoc($query)) {
            echo json_encode([
                'status' => 'success',
                'site' => $row['nama_site'] ?? '-',
                'departemen' => $row['nama_departemen'] ?? '-',
                'jabatan' => $row['nama_jabatan'] ?? '-'
            ]);
            exit;
        }
    }
    // Jika tidak ditemukan/error
    echo json_encode(['status' => 'error', 'site' => '-', 'departemen' => '-', 'jabatan' => '-']);
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