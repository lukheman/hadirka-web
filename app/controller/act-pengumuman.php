<?php
header('Content-Type: application/json');
include "../config/koneksi.php";

$action = $_GET['action'] ?? '';

if ($action == 'get-single') {
    $id = $_GET['id'];
    $res = $koneksi->prepare("SELECT * FROM tb_pengumuman WHERE id = ?");
    $res->execute([$id]);
    $row = $res->fetch();
    echo json_encode($row === false ? null : $row);
    exit;
}

if ($action == 'save') {
    $id      = $_POST['id'];
    $judul   = $_POST['judul'];

    // Mengambil input dari detail_konten
    $detail = $_POST['detail_konten'];
    $sub    = mb_strimwidth($detail, 0, 100, "...");

    $ikon    = $_POST['ikon_nama'];
    $warna   = $_POST['warna_kode'];

    try {
        if (empty($id)) {
            // Tambahkan detail_konten ke Query INSERT
            $st = $koneksi->prepare("INSERT INTO tb_pengumuman (judul, sub_judul, detail_konten, ikon_nama, warna_kode, waktu_buat)
                    VALUES (?, ?, ?, ?, ?, NOW())");
            $st->execute([$judul, $sub, $detail, $ikon, $warna]);
        } else {
            // Tambahkan detail_konten ke Query UPDATE
            $st = $koneksi->prepare("UPDATE tb_pengumuman SET
                    judul=?,
                    sub_judul=?,
                    detail_konten=?,
                    ikon_nama=?,
                    warna_kode=?
                    WHERE id=?");
            $st->execute([$judul, $sub, $detail, $ikon, $warna, $id]);
        }
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data.']);
    }
    exit;
}

if ($action == 'delete') {
    $id = $_GET['id'];

    try {
        $st = $koneksi->prepare("DELETE FROM tb_pengumuman WHERE id = ?");
        $st->execute([$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit;
}
