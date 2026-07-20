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
    $id_pengajuan = (int)($_POST['id_pengajuan'] ?? 0);
    $status = $_POST['status_pinjaman'] ?? '';
    $keterangan_admin = $_POST['keterangan_admin'] ?? '';

    if ($status == 'disetujui') {
        $pesan_otomatis = "Pengajuan disetujui dan masuk daftar antrean transfer/pencairan.";
        $keterangan_admin = empty(trim($keterangan_admin)) ? $pesan_otomatis : trim($keterangan_admin) . "\n\nCatatan Sistem: " . $pesan_otomatis;
    }

    if (!$id_pengajuan || !$status) {
        echo json_encode(['status' => 'error', 'msg' => 'Data tidak lengkap']);
        exit;
    }

    $stmtUpdate = $koneksi->prepare("UPDATE tb_pengajuan_pinjaman SET status_pinjaman = ?, keterangan_admin = ? WHERE id_pengajuan_pinjaman = ?");
    $stmtUpdate->bind_param("ssi", $status, $keterangan_admin, $id_pengajuan);

    if ($stmtUpdate->execute()) {
        // Jika ditolak/pending, pastikan terhapus dari tb_pinjaman jika sebelumnya nyangkut
        if ($status == 'ditolak' || $status == 'pending') {
            $koneksi->query("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = $id_pengajuan");
        }
        $pesan = $status == 'disetujui' ? "Pengajuan berhasil disetujui (Menunggu TF)." : "Pengajuan berhasil ditolak.";
        echo json_encode(['status' => 'success', 'msg' => $pesan]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Gagal mengubah status.']);
    }
    $stmtUpdate->close();
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
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['status' => 'error', 'msg' => 'Tidak ada pengajuan yang dipilih']);
        exit;
    }

    $ids = array_map('intval', $ids);
    $ids_str = implode(',', $ids);
    $pesan_otomatis = "Pinjaman telah disetujui (ACC Massal). Menunggu proses pencairan.";

    $sqlUpdate = "UPDATE tb_pengajuan_pinjaman 
                  SET status_pinjaman = 'disetujui', keterangan_admin = '$pesan_otomatis' 
                  WHERE id_pengajuan_pinjaman IN ($ids_str) AND status_pinjaman = 'pending'";
    $koneksi->query($sqlUpdate);
    $affected = $koneksi->affected_rows;

    if ($affected > 0) {
        echo json_encode(['status' => 'success', 'msg' => "$affected pengajuan berhasil di-ACC massal."]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Tidak ada data valid yang diproses.']);
    }
    exit;
}

// --- TANDAI CAIR / TF (SATUAN) ---
elseif ($action == 'cair_single') {
    $id = (int)($_POST['id'] ?? 0);
    $tgl_bayar = $_POST['tgl_bayar_pinjaman'] ?? date('Y-m-d');
    $pesan_tambahan = "Dana pinjaman telah berhasil ditransfer ke rekening Anda.";

    $koneksi->begin_transaction();
    try {
        // 1. Ambil data pengajuan
        $res = $koneksi->query("SELECT * FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = $id AND status_pinjaman = 'disetujui'");
        $data = $res->fetch_assoc();

        if (!$data) throw new Exception("Data tidak ditemukan atau status bukan disetujui.");

        $tgl_pinjaman = date('Y-m-d', strtotime($data['tgl_pengajuan']));
        $nominal = $data['nominal_pinjaman'];
        $alasan = "Peminjaman Dana (App)";

        // 2. Insert ke tb_pinjaman
        $koneksi->query("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = $id"); // Safety clear
        $stmtIns = $koneksi->prepare("INSERT INTO tb_pinjaman (id_pengajuan_pinjaman, id_karyawan, tgl_pinjaman, tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtIns->bind_param("iissds", $id, $data['id_karyawan'], $tgl_pinjaman, $tgl_bayar, $nominal, $alasan);
        $stmtIns->execute();
        $stmtIns->close();

        // 3. Update status ke dicairkan
        $stmtUpd = $koneksi->prepare("UPDATE tb_pengajuan_pinjaman SET status_pinjaman = 'dicairkan', keterangan_admin = CONCAT(IFNULL(keterangan_admin, ''), '\n\nUpdate: ', ?) WHERE id_pengajuan_pinjaman = ?");
        $stmtUpd->bind_param("si", $pesan_tambahan, $id);
        $stmtUpd->execute();
        $stmtUpd->close();

        $koneksi->commit();
        echo json_encode(['status' => 'success', 'msg' => 'Pencairan berhasil. Potongan gaji ditambahkan.']);
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    }
    exit;
}

// --- TANDAI CAIR / TF (BATCH / MASSAL) ---
elseif ($action == 'cair_batch') {
    $ids = $_POST['id_pengajuan'] ?? [];
    $tgl_bayar = $_POST['tgl_bayar_pinjaman'] ?? date('Y-m-d');

    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['status' => 'error', 'msg' => 'Tidak ada pengajuan yang dipilih']);
        exit;
    }

    $ids = array_map('intval', $ids);
    $ids_str = implode(',', $ids);
    $pesan_tambahan = "Dana pinjaman telah berhasil ditransfer ke rekening Anda.";

    $koneksi->begin_transaction();
    try {
        $sqlData = "SELECT * FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman IN ($ids_str) AND status_pinjaman = 'disetujui'";
        $res = $koneksi->query($sqlData);

        $affected = 0;

        // PREPARE STATEMENT DI LUAR LOOP AGAR LEBIH RINGAN
        $stmtInsert = $koneksi->prepare("INSERT INTO tb_pinjaman (id_pengajuan_pinjaman, id_karyawan, tgl_pinjaman, tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman) VALUES (?, ?, ?, ?, ?, ?)");

        // Bind param di luar loop (variabel referensi)
        $id_p = $id_k = 0;
        $tgl_p = $tgl_b = $alasan = "";
        $nom = 0;
        $stmtInsert->bind_param("iissds", $id_p, $id_k, $tgl_p, $tgl_b, $nom, $alasan);

        while ($data = $res->fetch_assoc()) {
            $id_p = $data['id_pengajuan_pinjaman'];
            $id_k = $data['id_karyawan'];
            $tgl_p = date('Y-m-d', strtotime($data['tgl_pengajuan']));
            $nom = $data['nominal_pinjaman'];
            $alasan = "Peminjaman Dana (App)";
            $tgl_b = $tgl_bayar; // Dari input modal

            // Safety delete
            $koneksi->query("DELETE FROM tb_pinjaman WHERE id_pengajuan_pinjaman = $id_p");

            // EXECUTE SAJA DI DALAM LOOP
            $stmtInsert->execute();

            $koneksi->query("UPDATE tb_pengajuan_pinjaman SET status_pinjaman = 'dicairkan', keterangan_admin = CONCAT(IFNULL(keterangan_admin, ''), '\n\nUpdate: $pesan_tambahan') WHERE id_pengajuan_pinjaman = $id_p");
            $affected++;
        }
        $stmtInsert->close();
        $koneksi->commit();

        if ($affected > 0) {
            echo json_encode(['status' => 'success', 'msg' => "$affected data berhasil dicairkan dan masuk ke potongan gaji."]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Tidak ada data valid yang diproses.']);
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

    // Tambahkan pengambilan 'status_pinjaman' pada query
    $query_cari = mysqli_query($koneksi, "SELECT status_pinjaman, id_karyawan, tgl_pengajuan, nominal_pinjaman, foto_wajah, foto_id_card FROM tb_pengajuan_pinjaman WHERE id_pengajuan_pinjaman = '$id'");
    $data = mysqli_fetch_assoc($query_cari);

    if ($data) {
        // --- BACKING PENGAMANAN SERVER ---
        // Jika sudah dicairkan, tolak proses hapus secara paksa!
        if ($data['status_pinjaman'] === 'dicairkan') {
            echo json_encode(['status' => 'error', 'msg' => 'AKSES DITOLAK! Data yang sudah dicairkan tidak dapat dihapus dari menu pengajuan.']);
            exit;
        }

        // Lanjut proses hapus bukti gambar dan relasi di tb_pinjaman...
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
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Action tidak valid']);
    exit;
}
