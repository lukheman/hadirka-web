<?php
header('Content-Type: application/json');
include "../config/koneksi.php"; // Pastikan path koneksi benar

$action = $_GET['action'] ?? '';

// 1a. Ambil opsi APD untuk dropdown
if ($action == 'get-options-apd') {
    // Hanya tampilkan yang stoknya > 0 dan tambahkan info sisa stok di namanya
    $query = $koneksi->query("SELECT id_apd, CONCAT(nama_apd, ' (Stok: ', stok, ')') AS nama_apd FROM master_apd WHERE status_aktif = 'aktif' AND stok > 0 ORDER BY nama_apd ASC");
    $data = [];
    while ($row = $query->fetch()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// 1b. Ambil semua karyawan untuk dropdown Select2
if ($action == 'get-all-karyawan') {
    $query = $koneksi->query("SELECT id_karyawan, nik_karyawan, nama_lengkap FROM tb_karyawan ORDER BY nama_lengkap ASC");
    $data = [];
    while ($row = $query->fetch()) {
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
                WHERE c.id_karyawan = ? AND (c.tgl_selesai IS NULL OR c.tgl_selesai >= CURDATE())
                ORDER BY c.id_karier DESC LIMIT 1";

        $query = $koneksi->prepare($sql);
        $query->execute([$id]);
        if ($row = $query->fetch()) {
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
    $id_k   = $_POST['id_karyawan'];
    $tgl    = $_POST['tanggal_pengambilan'];

    // Tangkap array dari form
    $id_apd_array = $_POST['id_apd'];
    $jumlah_array = $_POST['jumlah'];

    if (empty($id_k) || empty($id_apd_array)) {
        echo json_encode(['status' => 'error', 'message' => 'Karyawan dan APD harus dipilih']);
        exit;
    }

    try {
        // 1. CEK SEMUA STOK DULU (Mencegah partial save jika ada 1 barang yg stoknya kurang)
        for ($i = 0; $i < count($id_apd_array); $i++) {
            $id_a = $id_apd_array[$i];
            $qty  = (int)$jumlah_array[$i];
            if (!empty($id_a) && $qty > 0) {
                $cek = $koneksi->prepare("SELECT nama_apd, stok FROM master_apd WHERE id_apd = ?");
                $cek->execute([$id_a]);
                $data_stok = $cek->fetch();
                if (($data_stok['stok'] ?? 0) < $qty) {
                    echo json_encode(['status' => 'error', 'message' => 'Stok ' . ($data_stok['nama_apd'] ?? '') . ' tidak cukup! Sisa: ' . ($data_stok['stok'] ?? 0)]);
                    exit;
                }
            }
        }

        // 2. JIKA AMAN, BARU EKSEKUSI INSERT DAN POTONG STOK
        $all_success = true;
        $errors = [];

        // Loop sebanyak item APD yang diinputkan
        for ($i = 0; $i < count($id_apd_array); $i++) {
            $id_a = $id_apd_array[$i];
            $qty  = (int)$jumlah_array[$i];

            // Pastikan APD tidak kosong di baris tersebut
            if (!empty($id_a) && $qty > 0) {
                try {
                    $ins = $koneksi->prepare("INSERT INTO pengambilan_apd (id_karyawan, id_apd, tanggal_pengambilan, jumlah)
                            VALUES (?, ?, ?, ?)");
                    $ins->execute([$id_k, $id_a, $tgl, $qty]);
                    // Potong Stok
                    $pot = $koneksi->prepare("UPDATE master_apd SET stok = stok - ? WHERE id_apd = ?");
                    $pot->execute([$qty, $id_a]);
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $all_success = false;
                    $errors[] = 'Gagal menyimpan item APD.';
                }
            }
        }

        if ($all_success) {
            echo json_encode(['status' => 'success']);
        } else {
            // Menggabungkan pesan error jika ada yang gagal insert
            echo json_encode(['status' => 'error', 'message' => implode(" | ", $errors)]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data.']);
    }
    exit;
}

// 3. Ambil data spesifik untuk form Edit
if ($action == 'get-single') {
    $id = $_GET['id'];
    $query = $koneksi->prepare("SELECT * FROM pengambilan_apd WHERE id_pengambilan = ?");
    $query->execute([$id]);

    if ($row = $query->fetch()) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    }
    exit;
}

// 4. Update Transaksi (Satu data)
if ($action == 'update') {
    $id_p   = $_POST['id_pengambilan'];
    $id_k   = $_POST['id_karyawan'];
    $id_a   = $_POST['id_apd'];
    $tgl    = $_POST['tanggal_pengambilan'];
    $qty    = $_POST['jumlah'];

    if (empty($id_p) || empty($id_k) || empty($id_a)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit;
    }

    try {
        $st = $koneksi->prepare("UPDATE pengambilan_apd SET
                id_karyawan = ?,
                id_apd = ?,
                tanggal_pengambilan = ?,
                jumlah = ?
                WHERE id_pengambilan = ?");
        $st->execute([$id_k, $id_a, $tgl, $qty, $id_p]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data.']);
    }
    exit;
}

if ($action == 'delete') {
    $id = $_GET['id'];

    try {
        // 1. Ambil data transaksi sebelum dihapus
        $q_cek = $koneksi->prepare("SELECT id_apd, jumlah, id_pengajuan_apd FROM pengambilan_apd WHERE id_pengambilan = ?");
        $q_cek->execute([$id]);
        $data_trx = $q_cek->fetch();

        if ($data_trx) {
            $id_apd_return = $data_trx['id_apd'];
            $qty_return = (int)$data_trx['jumlah'];
            $id_pengajuan = $data_trx['id_pengajuan_apd'];

            // 2. Kembalikan stok ke master_apd
            $ret = $koneksi->prepare("UPDATE master_apd SET stok = stok + ? WHERE id_apd = ?");
            $ret->execute([$qty_return, $id_apd_return]);

            // 3. Jika transaksi ini berasal dari pengajuan Mobile, ubah status pengajuan jadi 'pending'
            if (!empty($id_pengajuan)) {
                $upd = $koneksi->prepare("UPDATE tb_pengajuan_apd SET status_pengajuan = 'pending' WHERE id_pengajuan_apd = ?");
                $upd->execute([$id_pengajuan]);
            }
        }

        // 4. Hapus data transaksi
        $st = $koneksi->prepare("DELETE FROM pengambilan_apd WHERE id_pengambilan = ?");
        $st->execute([$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit;
}
