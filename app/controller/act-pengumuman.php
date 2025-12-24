<?php
header('Content-Type: application/json');
include "../config/koneksi.php";

$action = $_GET['action'] ?? '';

if ($action == 'get-single') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $res = mysqli_query($koneksi, "SELECT * FROM tb_pengumuman WHERE id = '$id'");
    echo json_encode(mysqli_fetch_assoc($res));
    exit;
}

if ($action == 'save') {
    $id      = $_POST['id'];
    $judul   = mysqli_real_escape_string($koneksi, $_POST['judul']);

    // Mengambil input dari detail_konten
    $detail = mysqli_real_escape_string($koneksi, $_POST['detail_konten']);
    $sub    = mb_strimwidth($detail, 0, 100, "...");

    $ikon    = mysqli_real_escape_string($koneksi, $_POST['ikon_nama']);
    $warna   = mysqli_real_escape_string($koneksi, $_POST['warna_kode']);

    if (empty($id)) {
        // Tambahkan detail_konten ke Query INSERT
        $sql = "INSERT INTO tb_pengumuman (judul, sub_judul, detail_konten, ikon_nama, warna_kode, waktu_buat) 
                VALUES ('$judul', '$sub', '$detail', '$ikon', '$warna', NOW())";
    } else {
        // Tambahkan detail_konten ke Query UPDATE
        $sql = "UPDATE tb_pengumuman SET 
                judul='$judul', 
                sub_judul='$sub', 
                detail_konten='$detail', 
                ikon_nama='$ikon', 
                warna_kode='$warna' 
                WHERE id='$id'";
    }

    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}

if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $sql = "DELETE FROM tb_pengumuman WHERE id = '$id'";

    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}
