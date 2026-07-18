<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- GET STATUS AKSES PENGAJUAN ---
if ($action == 'get-status-akses') {
    $q = mysqli_query($koneksi, "SELECT nilai FROM tb_pengaturan WHERE nama_pengaturan = 'buka_pinjaman'");
    $data = mysqli_fetch_assoc($q);
    $nilai = $data ? $data['nilai'] : '1';
    echo json_encode(['status' => 'success', 'nilai' => $nilai]);
    exit;
}

// --- TOGGLE BUKA / TUTUP AKSES ---
elseif ($action == 'toggle-akses') {
    $nilai = $_POST['nilai'] ?? '0';
    $stmt = $koneksi->prepare("UPDATE tb_pengaturan SET nilai = ? WHERE nama_pengaturan = 'buka_pinjaman'");
    $stmt->bind_param("s", $nilai);
    
    if ($stmt->execute()) {
        $msg = $nilai == '1' ? 'Akses pengajuan pinjaman berhasil DIBUKA.' : 'Akses pengajuan pinjaman berhasil DITUTUP.';
        echo json_encode(['status' => 'success', 'msg' => $msg]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Gagal mengubah status akses.']);
    }
    $stmt->close();
    exit;
}

// --- PROSES SATUAN (ACC / TOLAK DARI MODAL) ---
if ($action == 'proses') {
    $id_pengajuan = (int)($_POST['id_pengajuan'] ?? 0); // Keamanan: Paksa jadi integer
    $status = $_POST['status_pinjaman'] ?? '';
    $keterangan_admin = $_POST['keterangan_admin'] ?? '';
    
    $tgl_bayar = !empty($_POST['tgl_bayar_pinjaman']) ? $_POST['tgl_bayar_pinjaman'] : date('Y-m-d');

    if ($status == 'disetujui') {
        $pesan_otomatis = "Pinjaman akan diproses pada hari sabtu.";
        if (empty(trim($keterangan_admin))) {
            $keterangan_admin = $pesan_otomatis;
        } else {
            $keterangan_admin = trim($keterangan_admin) . "\n\nCatatan Sistem: " . $pesan_otomatis;
        }
    }

    if (!$id_pengajuan || !$status) {
        echo json_encode(['status' => 'error', 'msg' => 'Data tidak lengkap']);
        exit;
    }

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

    $koneksi->begin_transaction();
    try {
        $stmtUpdate = $koneksi->prepare("UPDATE tb_pengajuan_pinjaman SET status_pinjaman = ?, keterangan_admin = ? WHERE id_pengajuan_pinjaman = ?");
        $stmtUpdate->bind_param("ssi", $status, $keterangan_admin, $id_pengajuan);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $id_karyawan = $data['id_karyawan'];
        $tgl_pinjaman = date('Y-m-d', strtotime($data['tgl_pengajuan']));
        $nominal = $data['nominal_pinjaman'];
        $alasan = "Peminjaman Dana";

        if ($status == 'disetujui') {
            $stmtDel = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = ?");
            $stmtDel->bind_param("i", $id_pengajuan);
            $stmtDel->execute();
            $stmtDel->close();

            $stmtInsert = $koneksi->prepare("INSERT INTO tb_pinjaman (id_pengajuan_pinjaman, id_karyawan, tgl_pinjaman, tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsert->bind_param("iissds", $id_pengajuan, $id_karyawan, $tgl_pinjaman, $tgl_bayar, $nominal, $alasan);
            $stmtInsert->execute();
            $stmtInsert->close();
            
            // Pesan khusus untuk ACC
            $pesan_sukses = "Pengajuan berhasil disetujui.";
            
        } else if ($status == 'ditolak' || $status == 'pending') {
            $stmtDel = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = ?");
            $stmtDel->bind_param("i", $id_pengajuan);
            $stmtDel->execute();
            $stmtDel->close();
            
            // Pesan khusus untuk Penolakan
            $pesan_sukses = "Pengajuan berhasil ditolak.";
        }

        $koneksi->commit();
        echo json_encode(['status' => 'success', 'msg' => $pesan_sukses]);
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(['status' => 'error', 'msg' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
    }
    exit;
}

// --- UPDATE STATUS CEPAT (BATAL / SET PENDING) ---
elseif ($action == 'update-status') {
    $id = (int)($_GET['id'] ?? 0); // Keamanan: Paksa jadi integer mencegah SQL Injection
    $status = $_GET['status'] ?? '';

    $stmtGet = $koneksi->prepare("SELECT id_karyawan, tgl_pengajuan, nominal_pinjaman FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = ?");
    $stmtGet->bind_param("i", $id);
    $stmtGet->execute();
    $res = $stmtGet->get_result();
    $data_pengajuan = $res->fetch_assoc();
    $stmtGet->close();

    if ($data_pengajuan && ($status == 'pending' || $status == 'ditolak')) {
        $stmtDel = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();
        $stmtDel->close();
    }

    $stmt = $koneksi->prepare("UPDATE tb_pengajuan_pinjaman SET status_pinjaman = ?, keterangan_admin = '' WHERE id_pengajuan_pinjaman = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'msg' => 'Status berhasil diubah ke Pending']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

// --- PROSES ACC BATCH / MASSAL ---
elseif ($action == 'acc_batch') {
    $ids = $_POST['id_pengajuan'] ?? [];
    
    $tgl_bayar = !empty($_POST['tgl_bayar_pinjaman']) ? $_POST['tgl_bayar_pinjaman'] : date('Y-m-d');
    
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['status' => 'error', 'msg' => 'Tidak ada pengajuan yang dipilih']);
        exit;
    }

    $ids = array_map('intval', $ids);
    $ids_str = implode(',', $ids);
    $pesan_otomatis = "Pinjaman telah disetujui (ACC Massal). Pinjaman akan diproses pada hari sabtu.";

    $koneksi->begin_transaction();
    try {
        // PERBAIKAN LOGIKA BATCH: Hanya ambil yang benar-benar berstatus "pending". 
        // Mengamankan dari aksi usil user yang mengirimkan ID yang sudah ditolak.
        $sqlData = "SELECT * FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman IN ($ids_str) AND status_pinjaman = 'pending'";
        $res = $koneksi->query($sqlData);
        
        while ($data = $res->fetch_assoc()) {
            $id_pengajuan = $data['id_pengajuan_pinjaman'];
            $id_karyawan = $data['id_karyawan'];
            $tgl_pinjaman = date('Y-m-d', strtotime($data['tgl_pengajuan']));
            $nominal = $data['nominal_pinjaman'];
            $alasan = "Peminjaman Dana";

            $stmtDel = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = ?");
            $stmtDel->bind_param("i", $id_pengajuan);
            $stmtDel->execute();
            $stmtDel->close();

            $stmtInsert = $koneksi->prepare("INSERT INTO tb_pinjaman (id_pengajuan_pinjaman, id_karyawan, tgl_pinjaman, tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsert->bind_param("iissds", $id_pengajuan, $id_karyawan, $tgl_pinjaman, $tgl_bayar, $nominal, $alasan);
            $stmtInsert->execute();
            $stmtInsert->close();
        }

        $sqlUpdate = "UPDATE tb_pengajuan_pinjaman 
                      SET status_pinjaman = 'disetujui', keterangan_admin = '$pesan_otomatis' 
                      WHERE id_pengajuan_pinjaman IN ($ids_str) AND status_pinjaman = 'pending'";
        $koneksi->query($sqlUpdate);
        
        $affected = $koneksi->affected_rows;
        $koneksi->commit();
        
        if ($affected > 0) {
            echo json_encode(['status' => 'success', 'msg' => "$affected pengajuan berhasil di-ACC massal."]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Tidak ada data valid yang diproses. Data mungkin sudah bukan berstatus Pending.']);
        }
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(['status' => 'error', 'msg' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
    }
    exit;
}

// --- HAPUS PENGAJUAN PERMANEN ---
elseif ($action == 'delete') {
    $id = (int)($_GET['id'] ?? 0); // Keamanan: Paksa jadi integer mencegah SQL Injection

    // Query tanpa prepared statement aman jika $id dipastikan integer
    $query_cari = mysqli_query($koneksi, "SELECT id_karyawan, tgl_pengajuan, nominal_pinjaman, foto_wajah, foto_id_card FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = '$id'");
    $data = mysqli_fetch_assoc($query_cari);

    if ($data) {
        $stmtDel = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();
        $stmtDel->close();

        $path_wajah = "../../" . $data['foto_wajah'];
        $path_id = "../../" . $data['foto_id_card'];
        if (!empty($data['foto_wajah']) && file_exists($path_wajah)) unlink($path_wajah);
        if (!empty($data['foto_id_card']) && file_exists($path_id)) unlink($path_id);
    }

    $stmt = $koneksi->prepare("DELETE FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'msg' => 'Data beserta bukti file berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $stmt->error]);
    }
    $stmt->close();
    exit;
}

else {
    echo json_encode(['status' => 'error', 'msg' => 'Action tidak valid']);
    exit;
}