<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action == "add") {
    $nama_tipe = trim($_POST['nama_tipe'] ?? '');
    $jam_mulai = $_POST['jam_kerja_mulai'] ?? null;
    $jam_selesai = $_POST['jam_kerja_selesai'] ?? null;
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($nama_tipe === '') {
        echo json_encode(["status" => "error", "msg" => "Nama tipe karyawan wajib diisi."]);
        exit;
    }

    // Validasi jam kerja: jika salah satu diisi, keduanya harus diisi
    if (($jam_mulai === '' || $jam_mulai === null) !== ($jam_selesai === '' || $jam_selesai === null)) {
        echo json_encode(["status" => "error", "msg" => "Jam kerja mulai dan selesai harus diisi keduanya."]);
        exit;
    }

    $sql = $koneksi->prepare("INSERT INTO tb_tipe_karyawan (nama_tipe, jam_kerja_mulai, jam_kerja_selesai, keterangan) VALUES (?, ?, ?, ?)");
    try {
        $sql->execute([$nama_tipe, $jam_mulai, $jam_selesai, $keterangan]);
        echo json_encode(["status" => "success", "msg" => "Tipe karyawan berhasil ditambahkan."]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        if (strpos($e->getMessage(), "Duplicate entry") !== false) {
            echo json_encode(["status" => "error", "msg" => "Nama tipe karyawan '" . htmlspecialchars($nama_tipe) . "' sudah ada."]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Gagal menambahkan tipe karyawan."]);
        }
    }
    exit;
}

if ($action == "edit") {
    $id = intval($_POST['id_tipe_karyawan'] ?? 0);
    $nama_tipe = trim($_POST['nama_tipe'] ?? '');
    $jam_mulai = $_POST['jam_kerja_mulai'] ?? null;
    $jam_selesai = $_POST['jam_kerja_selesai'] ?? null;
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($id <= 0) {
        echo json_encode(["status" => "error", "msg" => "ID tipe karyawan tidak valid."]);
        exit;
    }
    if ($nama_tipe === '') {
        echo json_encode(["status" => "error", "msg" => "Nama tipe karyawan wajib diisi."]);
        exit;
    }

    // Validasi jam kerja
    if (($jam_mulai === '' || $jam_mulai === null) !== ($jam_selesai === '' || $jam_selesai === null)) {
        echo json_encode(["status" => "error", "msg" => "Jam kerja mulai dan selesai harus diisi keduanya."]);
        exit;
    }

    $sql = $koneksi->prepare("UPDATE tb_tipe_karyawan SET nama_tipe=?, jam_kerja_mulai=?, jam_kerja_selesai=?, keterangan=? WHERE id_tipe_karyawan=?");
    try {
        $sql->execute([$nama_tipe, $jam_mulai, $jam_selesai, $keterangan, $id]);
        echo json_encode(["status" => "success", "msg" => "Tipe karyawan berhasil diupdate."]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        if (strpos($e->getMessage(), "Duplicate entry") !== false) {
            echo json_encode(["status" => "error", "msg" => "Nama tipe karyawan '" . htmlspecialchars($nama_tipe) . "' sudah ada."]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Gagal mengupdate tipe karyawan."]);
        }
    }
    exit;
}

if ($action == "delete") {
    $id = intval($_POST['id_tipe_karyawan'] ?? 0);

    if ($id <= 0) {
        echo json_encode(["status" => "error", "msg" => "ID tipe karyawan tidak valid."]);
        exit;
    }

    // Cek apakah tipe masih digunakan oleh karyawan
    $cek = $koneksi->prepare("SELECT COUNT(*) AS jml FROM tb_karyawan WHERE id_tipe_karyawan = ?");
    $cek->execute([$id]);
    $used = $cek->fetch()['jml'];

    if ($used > 0) {
        echo json_encode(["status" => "error", "msg" => "Tipe karyawan ini masih digunakan oleh {$used} karyawan. Tidak dapat dihapus."]);
        exit;
    }

    $sql = $koneksi->prepare("DELETE FROM tb_tipe_karyawan WHERE id_tipe_karyawan = ?");
    try {
        $sql->execute([$id]);
        echo json_encode(["status" => "success", "msg" => "Tipe karyawan berhasil dihapus."]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal menghapus tipe karyawan."]);
    }
    exit;
}

echo json_encode(["status" => "error", "msg" => "Aksi tidak dikenali."]);
