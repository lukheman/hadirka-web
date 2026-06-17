<?php
header('Content-Type: application/json');
include __DIR__ . "/../config/koneksi.php";
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'update-status') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $new_status = mysqli_real_escape_string($koneksi, $_GET['status']);
    $ket_admin = mysqli_real_escape_string($koneksi, $_POST['ket_admin'] ?? '');

    // 1. Ambil data pengajuan saat ini
    $cek_query = mysqli_query($koneksi, "SELECT * FROM tb_pengajuan_apd WHERE id_pengajuan_apd = '$id'");
    $data_pengajuan = mysqli_fetch_assoc($cek_query);
    $old_status = $data_pengajuan['status_pengajuan'];

    // 2. Cegah proses jika statusnya sama
    if ($old_status == $new_status) {
        echo json_encode(['status' => 'error', 'message' => 'Status pengajuan sudah ' . $new_status]);
        exit;
    }

    $id_apd_req = $data_pengajuan['id_apd'];
    $jumlah_req = (int)$data_pengajuan['jumlah'];

    // 3. CEK STOK SEBELUM APPROVE (Hanya jika mengubah JADI disetujui)
    if ($new_status == 'disetujui') {
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM master_apd WHERE id_apd = '$id_apd_req'");
        $row_stok = mysqli_fetch_assoc($cek_stok);

        if ($row_stok['stok'] < $jumlah_req) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal! Stok APD tidak mencukupi. Sisa stok: ' . $row_stok['stok']]);
            exit;
        }
    }

    // 4. Update Status di Database
    $sql = "UPDATE tb_pengajuan_apd SET status_pengajuan = '$new_status', ket_admin = '$ket_admin' WHERE id_pengajuan_apd = '$id'";

    if (mysqli_query($koneksi, $sql)) {

        // --- SKENARIO A: BERUBAH JADI "DISETUJUI" ---
        if ($new_status == 'disetujui') {
            $id_kar = $data_pengajuan['id_karyawan'];
            $tgl_sekarang = date('Y-m-d H:i:s');

            // Insert Log Transaksi
            $insert_log = "INSERT INTO pengambilan_apd (id_karyawan, id_pengajuan_apd, id_apd, jumlah, tanggal_pengambilan) 
                           VALUES ('$id_kar', '$id', '$id_apd_req', '$jumlah_req', '$tgl_sekarang')";
            mysqli_query($koneksi, $insert_log);

            // Potong Stok
            mysqli_query($koneksi, "UPDATE master_apd SET stok = stok - $jumlah_req WHERE id_apd = '$id_apd_req'");
        }

        // --- SKENARIO B: PEMBATALAN (Dari Disetujui -> Ditolak/Pending) ---
        if ($old_status == 'disetujui' && ($new_status == 'ditolak' || $new_status == 'pending')) {
            // Kembalikan Stok
            mysqli_query($koneksi, "UPDATE master_apd SET stok = stok + $jumlah_req WHERE id_apd = '$id_apd_req'");

            // Hapus Log Transaksi (Karena statusnya ditarik ulang)
            mysqli_query($koneksi, "DELETE FROM pengambilan_apd WHERE id_pengajuan_apd = '$id'");
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
