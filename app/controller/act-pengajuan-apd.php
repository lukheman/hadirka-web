<?php
header('Content-Type: application/json');
include __DIR__ . "/../config/koneksi.php";
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'update-status') {
    // Tangkap dari POST, fallback ke GET jika tidak ada
    $id = $_POST['id'] ?? $_GET['id'];
    $new_status = $_POST['status'] ?? $_GET['status'];
    $ket_admin = $_POST['ket_admin'] ?? '';

    try {
        // 1. Ambil data pengajuan saat ini
        $cek_query = $koneksi->prepare("SELECT * FROM tb_pengajuan_apd WHERE id_pengajuan_apd = ?");
        $cek_query->execute([$id]);
        $data_pengajuan = $cek_query->fetch();
        if (!$data_pengajuan) {
            echo json_encode(['status' => 'error', 'message' => 'Data pengajuan tidak ditemukan']);
            exit;
        }
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
            $cek_stok = $koneksi->prepare("SELECT stok FROM master_apd WHERE id_apd = ?");
            $cek_stok->execute([$id_apd_req]);
            $row_stok = $cek_stok->fetch();

            if (($row_stok['stok'] ?? 0) < $jumlah_req) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal! Stok APD tidak mencukupi. Sisa stok: ' . ($row_stok['stok'] ?? 0)]);
                exit;
            }
        }

        // 4. Update Status di Database
        $st = $koneksi->prepare("UPDATE tb_pengajuan_apd SET status_pengajuan = ?, ket_admin = ? WHERE id_pengajuan_apd = ?");
        $st->execute([$new_status, $ket_admin, $id]);

        // --- SKENARIO A: BERUBAH JADI "DISETUJUI" ---
        if ($new_status == 'disetujui') {
            $id_kar = $data_pengajuan['id_karyawan'];
            $tgl_sekarang = date('Y-m-d H:i:s');

            // Insert Log Transaksi
            $ins = $koneksi->prepare("INSERT INTO pengambilan_apd (id_karyawan, id_pengajuan_apd, id_apd, jumlah, tanggal_pengambilan)
                           VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$id_kar, $id, $id_apd_req, $jumlah_req, $tgl_sekarang]);

            // Potong Stok
            $pot = $koneksi->prepare("UPDATE master_apd SET stok = stok - ? WHERE id_apd = ?");
            $pot->execute([$jumlah_req, $id_apd_req]);
        }

        // --- SKENARIO B: PEMBATALAN (Dari Disetujui -> Ditolak/Pending) ---
        if ($old_status == 'disetujui' && ($new_status == 'ditolak' || $new_status == 'pending')) {
            // Kembalikan Stok
            $ret = $koneksi->prepare("UPDATE master_apd SET stok = stok + ? WHERE id_apd = ?");
            $ret->execute([$jumlah_req, $id_apd_req]);

            // Hapus Log Transaksi (Karena statusnya ditarik ulang)
            $del = $koneksi->prepare("DELETE FROM pengambilan_apd WHERE id_pengajuan_apd = ?");
            $del->execute([$id]);
        }

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status.']);
    }
    exit;
}

if ($action == 'delete') {
    $id = $_GET['id'];

    try {
        $query_cari = $koneksi->prepare("SELECT id_apd, jumlah, status_pengajuan, bukti_rusak FROM tb_pengajuan_apd WHERE id_pengajuan_apd = ?");
        $query_cari->execute([$id]);
        $data_apd = $query_cari->fetch();

        if ($data_apd) {
            // 👇 PERUBAHAN: Kembalikan stok jika yang dihapus adalah pengajuan yang sudah disetujui 👇
            if ($data_apd['status_pengajuan'] == 'disetujui') {
                $id_apd_return = $data_apd['id_apd'];
                $qty_return = (int)$data_apd['jumlah'];
                $ret = $koneksi->prepare("UPDATE master_apd SET stok = stok + ? WHERE id_apd = ?");
                $ret->execute([$qty_return, $id_apd_return]);
            }

            // Hapus file foto bukti fisik jika ada
            if (!empty($data_apd['bukti_rusak']) && $data_apd['bukti_rusak'] !== 'NULL') {
                $path_bersih = trim($data_apd['bukti_rusak'], "'");
                $path_file = "../../" . $path_bersih;
                if (file_exists($path_file)) unlink($path_file);
            }
        }

        // Hapus pengajuan (Data di tabel pengambilan_apd akan otomatis hilang karena efek CASCADE dari database)
        $st = $koneksi->prepare("DELETE FROM tb_pengajuan_apd WHERE id_pengajuan_apd = ?");
        $st->execute([$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit;
}
