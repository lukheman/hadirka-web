<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

// Menangkap parameter action dari POST atau GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action == 'proses') {
    $id_pengajuan = $_POST['id_pengajuan'] ?? '';
    $status = $_POST['status_pinjaman'] ?? '';
    $keterangan_admin = $_POST['keterangan_admin'] ?? '';
    $tgl_bayar = !empty($_POST['tgl_bayar_pinjaman']) ? $_POST['tgl_bayar_pinjaman'] : null;

    if (!$id_pengajuan || !$status) {
        echo json_encode(['status' => 'error', 'msg' => 'Data tidak lengkap']);
        exit;
    }

    // 1. Ambil data asli dari tabel pengajuan
    $stmt = $koneksi->prepare("SELECT * FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = ?");
    $stmt->bind_param("i", $id_pengajuan);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo json_encode(['status' => 'error', 'msg' => 'Data pengajuan tidak ditemukan']);
        exit;
    }

    // Mulai Transaksi Database
    $koneksi->begin_transaction();
    try {
        // 2. Update status dan pesan di tb_pengajuan_pinjaman
        $stmtUpdate = $koneksi->prepare("UPDATE tb_pengajuan_pinjaman SET status_pinjaman = ?, keterangan_admin = ? WHERE id_pengajuan_pinjaman = ?");
        $stmtUpdate->bind_param("ssi", $status, $keterangan_admin, $id_pengajuan);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // Variabel pencocokan untuk Master Data Pinjaman
        $id_karyawan = $data['id_karyawan'];
        $tgl_pinjaman = date('Y-m-d', strtotime($data['tgl_pengajuan']));
        $nominal = $data['nominal_pinjaman'];
        $alasan = "Peminjaman Dana";

        // 3. Logika Master Data Pinjaman (INSERT atau DELETE)
        if ($status == 'disetujui') {
            // Jika ACC, pastikan tidak ada duplikat lalu Insert ke tb_pinjaman
            // Hapus dulu barangkali sebelumnya sudah pernah di-ACC agar tidak dobel
            $stmtDel = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = ?");
            $stmtDel->bind_param("i", $id_pengajuan);
            $stmtDel->execute();
            $stmtDel->close();

            // Insert dengan menyertakan id_pengajuan_pinjaman
            $stmtInsert = $koneksi->prepare("INSERT INTO tb_pinjaman (id_pengajuan_pinjaman, id_karyawan, tgl_pinjaman, tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsert->bind_param("iissds", $id_pengajuan, $id_karyawan, $tgl_pinjaman, $tgl_bayar, $nominal, $alasan);
            $stmtInsert->execute();
            $stmtInsert->close();
        } else if ($status == 'ditolak' || $status == 'pending') {
            // Hapus dari Master Data jika status ditolak atau dikembalikan ke pending
            $stmtDel = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = ?");
            $stmtDel->bind_param("i", $id_pengajuan);
            $stmtDel->execute();
            $stmtDel->close();
        }

        $koneksi->commit();
        echo json_encode(['status' => 'success', 'msg' => 'Pengajuan berhasil diproses.']);
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(['status' => 'error', 'msg' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
    }

    exit;
}

// --- TAMBAHAN: Update Status Cepat ---
elseif ($action == 'update-status') {
    $id = $_GET['id'] ?? '';
    $status = $_GET['status'] ?? '';

    // 1. Ambil data pengajuan untuk acuan menghapus di master data
    $stmtGet = $koneksi->prepare("SELECT id_karyawan, tgl_pengajuan, nominal_pinjaman FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = ?");
    $stmtGet->bind_param("i", $id);
    $stmtGet->execute();
    $res = $stmtGet->get_result();
    $data_pengajuan = $res->fetch_assoc();
    $stmtGet->close();

    // 2. PERBAIKAN: Jika status diubah menjadi pending atau ditolak, hapus otomatis di tb_pinjaman
    if ($data_pengajuan && ($status == 'pending' || $status == 'ditolak')) {
        $id_karyawan = $data_pengajuan['id_karyawan'];
        $tgl_pinjaman = date('Y-m-d', strtotime($data_pengajuan['tgl_pengajuan']));
        $nominal = $data_pengajuan['nominal_pinjaman'];
        $alasan = "Peminjaman Dana"; // Acuan string persis sama seperti saat insert

        $stmtDel = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = ?");
        $stmtDel->bind_param("i", $id); // $id adalah id_pengajuan_pinjaman yang sedang diubah/dihapus
        $stmtDel->execute();
        $stmtDel->close();
    }

    // 3. Update status dan reset keterangan admin di tabel pengajuan
    $stmt = $koneksi->prepare("UPDATE tb_pengajuan_pinjaman SET status_pinjaman = ?, keterangan_admin = '' WHERE id_pengajuan_pinjaman = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'msg' => 'Status berhasil diubah']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

// --- TAMBAHAN: Hapus Pengajuan ---
elseif ($action == 'delete') {
    $id = $_GET['id'] ?? '';

    // Ambil data untuk hapus foto dan hapus dari master data pinjaman jika pernah di-ACC
    $query_cari = mysqli_query($koneksi, "SELECT id_karyawan, tgl_pengajuan, nominal_pinjaman, foto_wajah, foto_id_card FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = '$id'");
    $data = mysqli_fetch_assoc($query_cari);

    if ($data) {
        // PERBAIKAN: Hapus juga dari Master Data Pinjaman sebelum data pengajuan dihapus permanen
        $id_karyawan = $data['id_karyawan'];
        $tgl_pinjaman = date('Y-m-d', strtotime($data['tgl_pengajuan']));
        $nominal = $data['nominal_pinjaman'];
        $alasan = "Peminjaman Dana";

        $stmtDel = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = ?");
        $stmtDel->bind_param("i", $id); // $id adalah id_pengajuan_pinjaman yang sedang diubah/dihapus
        $stmtDel->execute();
        $stmtDel->close();

        // Hapus file foto
        $path_wajah = "../../" . $data['foto_wajah'];
        $path_id = "../../" . $data['foto_id_card'];

        if (!empty($data['foto_wajah']) && file_exists($path_wajah)) unlink($path_wajah);
        if (!empty($data['foto_id_card']) && file_exists($path_id)) unlink($path_id);
    }

    // Eksekusi hapus data pengajuan
    $stmt = $koneksi->prepare("DELETE FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

// --- JIKA TIDAK ADA ACTION YANG COCOK ---
else {
    echo json_encode(['status' => 'error', 'msg' => 'Action tidak valid']);
    exit;
}
