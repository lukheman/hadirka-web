<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/koneksi-api.php';

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

// 👇 TAMBAHKAN BLOK INI UNTUK MENGAMBIL OPSI APD 👇
if ($action == 'get-options') {
    $query = mysqli_query($koneksi, "SELECT id_apd, nama_apd FROM master_apd WHERE status_aktif = 'aktif' ORDER BY nama_apd ASC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

if ($action == 'ajukan') {
    $id_karyawan   = mysqli_real_escape_string($koneksi, $_POST['id_karyawan']);
    $id_apd        = mysqli_real_escape_string($koneksi, $_POST['id_apd']);
    $jumlah        = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $tgl_pengajuan = mysqli_real_escape_string($koneksi, $_POST['tgl_pengajuan']);
    $alasan        = mysqli_real_escape_string($koneksi, $_POST['alasan']);

    if (empty($id_karyawan) || empty($id_apd) || empty($jumlah)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $bukti_val = "NULL";
    if (isset($_FILES['bukti_rusak']) && $_FILES['bukti_rusak']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['bukti_rusak']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['bukti_rusak']['name'], PATHINFO_EXTENSION));
        $file_name = 'apd_rusak_' . $id_karyawan . '_' . time() . '.' . $file_ext;
        
        $upload_dir = dirname(__DIR__) . '/uploads/apd/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
        
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $bukti_val = "'uploads/apd/" . $file_name . "'";
        }
    }

    $sql = "INSERT INTO tb_pengajuan_apd (id_karyawan, id_apd, jumlah, tgl_pengajuan, alasan, bukti_rusak, status_pengajuan) 
            VALUES ('$id_karyawan', '$id_apd', '$jumlah', '$tgl_pengajuan', '$alasan', $bukti_val, 'pending')";

    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Pengajuan berhasil dikirim.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . mysqli_error($koneksi)]);
    }

// 👇 TAMBAHAN BLOK UNTUK MEMBATALKAN PENGAJUAN 👇
} elseif ($action == 'batalkan') {
    $id_pengajuan = mysqli_real_escape_string($koneksi, $_POST['id_pengajuan'] ?? '');
    
    if (empty($id_pengajuan)) {
        echo json_encode(['status' => 'error', 'message' => 'ID Pengajuan tidak ditemukan']);
        exit;
    }

    $cek_sql = "SELECT status_pengajuan, bukti_rusak FROM tb_pengajuan_apd WHERE id_pengajuan_apd = '$id_pengajuan'";
    $cek_result = mysqli_query($koneksi, $cek_sql);

    if ($row = mysqli_fetch_assoc($cek_result)) {
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

        if (mysqli_query($koneksi, "DELETE FROM tb_pengajuan_apd WHERE id_pengajuan_apd = '$id_pengajuan'")) {
            echo json_encode(['status' => 'success', 'message' => 'Pengajuan APD berhasil dibatalkan.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data pengajuan tidak ditemukan.']);
    }
// 👆 BATAS TAMBAHAN 👆

} else {
    echo json_encode(['status' => 'error', 'message' => 'Action tidak valid.']);
}
?>