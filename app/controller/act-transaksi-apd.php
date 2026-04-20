<?php
header('Content-Type: application/json');
include "../config/koneksi.php"; // Pastikan path koneksi benar

$action = $_GET['action'] ?? '';

// 1a. Ambil opsi APD untuk dropdown
if ($action == 'get-options-apd') {
    // Hanya tampilkan yang stoknya > 0 dan tambahkan info sisa stok di namanya
    $query = mysqli_query($koneksi, "SELECT id_apd, CONCAT(nama_apd, ' (Stok: ', stok, ')') AS nama_apd FROM master_apd WHERE status_aktif = 'aktif' AND stok > 0 ORDER BY nama_apd ASC");
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

// 2. Simpan Transaksi (Multiple APD)
if ($action == 'save') {
    $id_k   = mysqli_real_escape_string($koneksi, $_POST['id_karyawan']);
    $tgl    = mysqli_real_escape_string($koneksi, $_POST['tanggal_pengambilan']);

    // Tangkap array dari form
    $id_apd_array = $_POST['id_apd'];
    $jumlah_array = $_POST['jumlah'];

    if (empty($id_k) || empty($id_apd_array)) {
        echo json_encode(['status' => 'error', 'message' => 'Karyawan dan APD harus dipilih']);
        exit;
    }

    // 1. CEK SEMUA STOK DULU (Mencegah partial save jika ada 1 barang yg stoknya kurang)
    for ($i = 0; $i < count($id_apd_array); $i++) {
        $id_a = mysqli_real_escape_string($koneksi, $id_apd_array[$i]);
        $qty  = (int)$jumlah_array[$i];
        if (!empty($id_a) && $qty > 0) {
            $cek = mysqli_query($koneksi, "SELECT nama_apd, stok FROM master_apd WHERE id_apd = '$id_a'");
            $data_stok = mysqli_fetch_assoc($cek);
            if ($data_stok['stok'] < $qty) {
                echo json_encode(['status' => 'error', 'message' => 'Stok ' . $data_stok['nama_apd'] . ' tidak cukup! Sisa: ' . $data_stok['stok']]);
                exit;
            }
        }
    }

    // 2. JIKA AMAN, BARU EKSEKUSI INSERT DAN POTONG STOK
    $all_success = true;
    $errors = [];

    // Loop sebanyak item APD yang diinputkan
    for ($i = 0; $i < count($id_apd_array); $i++) {
        $id_a = mysqli_real_escape_string($koneksi, $id_apd_array[$i]);
        $qty  = (int)$jumlah_array[$i];

        // Pastikan APD tidak kosong di baris tersebut
        if (!empty($id_a) && $qty > 0) {
            $sql = "INSERT INTO pengambilan_apd (id_karyawan, id_apd, tanggal_pengambilan, jumlah) 
                    VALUES ('$id_k', '$id_a', '$tgl', '$qty')";
            if (mysqli_query($koneksi, $sql)) {
                // Potong Stok
                mysqli_query($koneksi, "UPDATE master_apd SET stok = stok - $qty WHERE id_apd = '$id_a'");
            } else {
                $all_success = false;
                $errors[] = mysqli_error($koneksi);
            }
        }
    }

    if ($all_success) {
        echo json_encode(['status' => 'success']);
    } else {
        // Menggabungkan pesan error jika ada yang gagal insert
        echo json_encode(['status' => 'error', 'message' => implode(" | ", $errors)]);
    }
    exit;
}

// 3. Ambil data spesifik untuk form Edit
if ($action == 'get-single') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $sql = "SELECT * FROM pengambilan_apd WHERE id_pengambilan = '$id'";
    $query = mysqli_query($koneksi, $sql);
    
    if ($row = mysqli_fetch_assoc($query)) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    }
    exit;
}

// 4. Update Transaksi (Satu data)
if ($action == 'update') {
    $id_p   = mysqli_real_escape_string($koneksi, $_POST['id_pengambilan']);
    $id_k   = mysqli_real_escape_string($koneksi, $_POST['id_karyawan']);
    $id_a   = mysqli_real_escape_string($koneksi, $_POST['id_apd']);
    $tgl    = mysqli_real_escape_string($koneksi, $_POST['tanggal_pengambilan']);
    $qty    = mysqli_real_escape_string($koneksi, $_POST['jumlah']);

    if (empty($id_p) || empty($id_k) || empty($id_a)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit;
    }

    $sql = "UPDATE pengambilan_apd SET 
            id_karyawan = '$id_k', 
            id_apd = '$id_a', 
            tanggal_pengambilan = '$tgl', 
            jumlah = '$qty' 
            WHERE id_pengambilan = '$id_p'";

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
