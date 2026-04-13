<?php
header('Content-Type: application/json');
require_once '../config/koneksi.php';

$action = $_POST['action'] ?? '';

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

    // Mulai Transaksi Database (agar jika salah satu gagal, semuanya batal)
    $koneksi->begin_transaction();
    try {
        // 2. Update status dan pesan di tb_pengajuan_pinjaman
        $stmtUpdate = $koneksi->prepare("UPDATE tb_pengajuan_pinjaman SET status_pinjaman = ?, keterangan_admin = ? WHERE id_pengajuan_pinjaman = ?");
        $stmtUpdate->bind_param("ssi", $status, $keterangan_admin, $id_pengajuan);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // 3. Jika ACC, otomatis Insert ke tb_pinjaman (Master Data)
        if ($status == 'disetujui') {
            $id_karyawan = $data['id_karyawan'];
            // Ambil tanggal pengajuan sebagai tanggal pinjaman
            $tgl_pinjaman = date('Y-m-d', strtotime($data['tgl_pengajuan'])); 
            $nominal = $data['nominal_pinjaman'];
            $alasan = "Peminjaman Dana";

            $stmtInsert = $koneksi->prepare("INSERT INTO tb_pinjaman (id_karyawan, tgl_pinjaman, tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman) VALUES (?, ?, ?, ?, ?)");
            $stmtInsert->bind_param("issds", $id_karyawan, $tgl_pinjaman, $tgl_bayar, $nominal, $alasan);
            $stmtInsert->execute();
            $stmtInsert->close();
        }

        $koneksi->commit();
        echo json_encode(['status' => 'success', 'msg' => 'Pengajuan berhasil diproses. Karyawan dapat melihat status di aplikasi.']);
        
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(['status' => 'error', 'msg' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Action tidak valid']);
}
?>