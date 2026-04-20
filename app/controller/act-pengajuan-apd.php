<?php
header('Content-Type: application/json');
include "../config/koneksi.php";
$action = $_GET['action'] ?? '';

if ($action == 'update-status') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $status = mysqli_real_escape_string($koneksi, $_GET['status']);
    
    // 1. Ambil data pengajuan saat ini
    $cek_query = mysqli_query($koneksi, "SELECT * FROM tb_pengajuan_apd WHERE id_pengajuan_apd = '$id'");
    $data_pengajuan = mysqli_fetch_assoc($cek_query);

    // 2. Cegah Double Approve
    if ($data_pengajuan['status_pengajuan'] == 'disetujui' && $status == 'disetujui') {
        echo json_encode(['status' => 'error', 'message' => 'Pengajuan ini sudah disetujui sebelumnya.']);
        exit;
    }

    // CEK STOK SEBELUM APPROVE
    if ($status == 'disetujui') {
        $id_apd_req = $data_pengajuan['id_apd'];
        $jumlah_req = (int)$data_pengajuan['jumlah'];
        
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM master_apd WHERE id_apd = '$id_apd_req'");
        $row_stok = mysqli_fetch_assoc($cek_stok);
        
        if ($row_stok['stok'] < $jumlah_req) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal! Stok APD tidak mencukupi. Sisa stok: ' . $row_stok['stok']]);
            exit; 
        }
    }
    
    // 3. Update Status
    $sql = "UPDATE tb_pengajuan_apd SET status_pengajuan = '$status' WHERE id_pengajuan_apd = '$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        
        // 4. LOGIKA AUTO-INSERT JIKA DISETUJUI
        if ($status == 'disetujui') {
            $id_kar = $data_pengajuan['id_karyawan'];
            $id_apd = $data_pengajuan['id_apd'];
            $jumlah = $data_pengajuan['jumlah'];
            $tgl_sekarang = date('Y-m-d H:i:s');
            
            // 👇 PERUBAHAN: Memasukkan id_pengajuan_apd ke tabel transaksi 👇
            $insert_log = "INSERT INTO pengambilan_apd 
                           (id_karyawan, id_pengajuan_apd, id_apd, jumlah, tanggal_pengambilan) 
                           VALUES 
                           ('$id_kar', '$id', '$id_apd', '$jumlah', '$tgl_sekarang')";
            
            mysqli_query($koneksi, $insert_log);
            
            // 👇 POTONG STOK OTOMATIS 👇
            mysqli_query($koneksi, "UPDATE master_apd SET stok = stok - $jumlah WHERE id_apd = '$id_apd'");
        }

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}

if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    $query_cari = mysqli_query($koneksi, "SELECT id_apd, jumlah, status_pengajuan, bukti_rusak FROM tb_pengajuan_apd WHERE id_pengajuan_apd = '$id'");
    $data_apd = mysqli_fetch_assoc($query_cari);

    if ($data_apd) {
        // 👇 PERUBAHAN: Kembalikan stok jika yang dihapus adalah pengajuan yang sudah disetujui 👇
        if ($data_apd['status_pengajuan'] == 'disetujui') {
            $id_apd_return = $data_apd['id_apd'];
            $qty_return = $data_apd['jumlah'];
            mysqli_query($koneksi, "UPDATE master_apd SET stok = stok + $qty_return WHERE id_apd = '$id_apd_return'");
        }

        // Hapus file foto bukti fisik jika ada
        if (!empty($data_apd['bukti_rusak']) && $data_apd['bukti_rusak'] !== 'NULL') {
            $path_bersih = trim($data_apd['bukti_rusak'], "'");
            $path_file = "../../" . $path_bersih;
            if (file_exists($path_file)) unlink($path_file);
        }
    }

    // Hapus pengajuan (Data di tabel pengambilan_apd akan otomatis hilang karena efek CASCADE dari database)
    $sql = "DELETE FROM tb_pengajuan_apd WHERE id_pengajuan_apd = '$id'";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}
?>