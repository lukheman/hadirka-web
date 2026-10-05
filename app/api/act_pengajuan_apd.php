<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/koneksi-api.php';

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

// 👇 TAMBAHKAN BLOK INI UNTUK MENGAMBIL OPSI APD 👇
if ($action == 'get-options') {
    // Tetap gunakan stok > 0 sebagai filter, tapi kembalikan nama_apd nya saja agar UI rapi
    try {
        $query = $koneksi->query("SELECT id_apd, nama_apd 
                                         FROM master_apd 
                                         WHERE status_aktif = 'aktif' AND stok > 0 
                                         ORDER BY nama_apd ASC");
        $data = [];
        while ($row = $query->fetch()) {
            $data[] = $row;
        }
        echo json_encode($data);
    } catch (PDOException $e) {
        error_log('act_pengajuan_apd get-options gagal: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database.']);
    }
    exit;
}

if ($action == 'ajukan') {
    $id_karyawan   = $_POST['id_karyawan'] ?? '';
    $id_apd        = $_POST['id_apd'] ?? '';
    $jumlah        = $_POST['jumlah'] ?? '';
    $tgl_pengajuan = $_POST['tgl_pengajuan'] ?? '';
    $alasan        = $_POST['alasan'] ?? '';

    if (empty($id_karyawan) || empty($id_apd) || empty($jumlah)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    // 👇 1. QUERY UNTUK AMBIL NIK & NAMA KARYAWAN 👇
    $nik_karyawan = 'UNKNOWN';
    $nama_karyawan = 'UNKNOWN';

    // Pastikan pencarian/pengambilan data menggunakan NIK jika sistem Anda mengaturnya demikian, 
    // namun karena dari mobile yang dikirim adalah id_karyawan, kita gunakan id_karyawan sebagai acuan.
    try {
        $st_kar = $koneksi->prepare("SELECT nik_karyawan, nama_lengkap FROM tb_karyawan WHERE id_karyawan = ?");
        $st_kar->execute([$id_karyawan]);
        $row_kar = $st_kar->fetch();
        if ($row_kar) {
            $nik_karyawan = $row_kar['nik_karyawan'];
            // Hapus spasi pada nama agar link gambar tidak terputus di browser
            $nama_karyawan = str_replace(' ', '', $row_kar['nama_lengkap']); 
        }
    } catch (PDOException $e) {
        error_log('act_pengajuan_apd ajukan cek karyawan gagal: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database.']);
        exit;
    }
    
    // Format tanggal menjadi string yang aman untuk file (Contoh: 20260420_133845)
    $tgl_format_file = date('Ymd_His', strtotime($tgl_pengajuan));
    // 👆 ======================================== 👆

    $bukti_path = null;
    if (isset($_FILES['bukti_rusak']) && $_FILES['bukti_rusak']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['bukti_rusak']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['bukti_rusak']['name'], PATHINFO_EXTENSION));
        
        // 👇 2. FORMAT PENAMAAN FILE BARU 👇
        // Format: apd_nik_nama_tanggal.ext
        $file_name = 'apd_' . $nik_karyawan . '_' . $nama_karyawan . '_' . $tgl_format_file . '.' . $file_ext;
        // 👆 ============================= 👆

        $upload_dir = dirname(__DIR__) . '/uploads/apd/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
        
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $bukti_path = 'uploads/apd/' . $file_name;
        }
    }

    try {
        $st = $koneksi->prepare("INSERT INTO tb_pengajuan_apd (id_karyawan, id_apd, jumlah, tgl_pengajuan, alasan, bukti_rusak, status_pengajuan) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $st->execute([$id_karyawan, $id_apd, $jumlah, $tgl_pengajuan, $alasan, $bukti_path]);
        echo json_encode(['status' => 'success', 'message' => 'Pengajuan berhasil dikirim.']);
    } catch (PDOException $e) {
        error_log('act_pengajuan_apd ajukan insert gagal: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data. Terjadi kesalahan database.']);
    }

    // 👇 TAMBAHAN BLOK UNTUK MEMBATALKAN PENGAJUAN 👇
} elseif ($action == 'batalkan') {
    $id_pengajuan = $_POST['id_pengajuan'] ?? '';

    if (empty($id_pengajuan)) {
        echo json_encode(['status' => 'error', 'message' => 'ID Pengajuan tidak ditemukan']);
        exit;
    }

    try {
        $st_cek = $koneksi->prepare("SELECT status_pengajuan, bukti_rusak FROM tb_pengajuan_apd WHERE id_pengajuan_apd = ?");
        $st_cek->execute([$id_pengajuan]);
        $row = $st_cek->fetch();
    } catch (PDOException $e) {
        error_log('act_pengajuan_apd batalkan cek gagal: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database.']);
        exit;
    }

    if ($row) {
        if ($row['status_pengajuan'] !== 'pending') {
            echo json_encode(['status' => 'error', 'message' => 'Gagal dibatalkan. Pengajuan sudah diproses oleh Admin.']);
            exit;
        }

        // Hapus file bukti fisik jika ada
        if (!empty($row['bukti_rusak'])) {
            $base_dir = dirname(__DIR__) . '/';
            $file_path = $base_dir . trim($row['bukti_rusak'], "'");
            if (file_exists($file_path)) unlink($file_path);
        }

        try {
            $st_del = $koneksi->prepare("DELETE FROM tb_pengajuan_apd WHERE id_pengajuan_apd = ?");
            $st_del->execute([$id_pengajuan]);
            echo json_encode(['status' => 'success', 'message' => 'Pengajuan APD berhasil dibatalkan.']);
        } catch (PDOException $e) {
            error_log('act_pengajuan_apd batalkan hapus gagal: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data pengajuan tidak ditemukan.']);
    }
    // 👆 BATAS TAMBAHAN 👆

} else {
    echo json_encode(['status' => 'error', 'message' => 'Action tidak valid.']);
}
