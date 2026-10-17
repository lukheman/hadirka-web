<?php
error_reporting(0);
header('Content-Type: application/json');
include "../config/koneksi.php";

$action = $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Gagal memproses permintaan'];

if ($action == 'get-single') {
    $id = $_GET['id'];
    $res = $koneksi->prepare("SELECT * FROM tb_priode WHERE id_priode = ?");
    $res->execute([$id]);
    $row = $res->fetch();
    if ($row !== false && isset($row['tgl_mulai'])) {
        $row['bulan_periode'] = substr((string)$row['tgl_mulai'], 0, 7);
    }
    echo json_encode($row === false ? null : $row);
    exit;
}

if ($action == 'save') {
    $id      = $_POST['id_priode'] ?? '';
    $type    = $_POST['type_priode'] ?? '';
    $bulan   = trim((string)($_POST['bulan_periode'] ?? ''));

    // Kompatibilitas: bila klien lama masih mengirim tgl_mulai, turunkan bulan dari sana.
    if ($bulan === '' && !empty($_POST['tgl_mulai']) && preg_match('/^(\d{4})-(0[1-9]|1[0-2])-\d{2}$/', (string)$_POST['tgl_mulai'], $m)) {
        $bulan = $m[1] . '-' . $m[2];
    }

    try {
        if (!in_array($type, ['absen', 'slip gaji'], true)) {
            throw new Exception('Tipe periode tidak valid.');
        }
        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $bulan, $mm) !== 1) {
            throw new Exception('Bulan periode tidak valid (format YYYY-MM).');
        }
        // Otomatis: tanggal 1 s/d akhir bulan.
        $mulai   = sprintf('%04d-%02d-01', (int)$mm[1], (int)$mm[2]);
        $selesai = sprintf('%04d-%02d-%02d', (int)$mm[1], (int)$mm[2], (int)date('t', strtotime($mulai)));

        // Cegah duplikat: satu tipe hanya boleh punya satu periode per bulan.
        $cek = $koneksi->prepare("SELECT id_priode FROM tb_priode WHERE type_priode = ? AND tgl_mulai = ? LIMIT 1");
        $cek->execute([$type, $mulai]);
        $duplikat = $cek->fetch();
        if ($duplikat && (empty($id) || (int)$duplikat['id_priode'] !== (int)$id)) {
            throw new Exception("Periode $bulan untuk tipe \"$type\" sudah ada.");
        }

        if (empty($id)) {
            $st = $koneksi->prepare("INSERT INTO tb_priode (type_priode, tgl_mulai, tgl_selesai, status_priode)
                    VALUES (?, ?, ?, 'Open')");
            $st->execute([$type, $mulai, $selesai]);
        } else {
            $st = $koneksi->prepare("UPDATE tb_priode SET type_priode=?, tgl_mulai=?, tgl_selesai=? WHERE id_priode=?");
            $st->execute([$type, $mulai, $selesai, $id]);
        }
        $response = ['status' => 'success'];
    } catch (Exception $e) {
        error_log($e->getMessage());
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
}

if ($action == 'toggle-status') {
    $input  = json_decode(file_get_contents('php://input'), true);
    $id     = $input['id'];
    $status = $input['status'];

    // Jika meng-Open, tutup yang lain dengan tipe yang sama
    // (logika lama, tetap dinonaktifkan)
    // if ($status === 'Open') { ... }

    try {
        $st = $koneksi->prepare("UPDATE tb_priode SET status_priode = ? WHERE id_priode = ?");
        $st->execute([$status, $id]);
        $response = ['status' => 'success'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $response = ['status' => 'error', 'message' => 'Gagal memperbarui status.'];
    }
}

if ($action == 'delete') {
    $id = $_GET['id'];
    try {
        $st = $koneksi->prepare("DELETE FROM tb_priode WHERE id_priode=?");
        $st->execute([$id]);
        $response = ['status' => 'success'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $response = ['status' => 'error', 'message' => 'Gagal menghapus data.'];
    }
}

echo json_encode($response);
