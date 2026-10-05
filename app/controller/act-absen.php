<?php
// act-absen.php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

function json_out($arr)
{
    header('Content-Type: application/json');
    echo json_encode($arr);
    exit;
}

$action = $_POST['action'] ?? '';

/* ADD */
if ($action === 'add') {
    $id_karyawan = intval($_POST['id_karyawan'] ?? 0);
    $tgl = $_POST['tgl_absen'] ?? '';
    $jam = $_POST['jam_kerja'] ?? '';
    $ket = $_POST['ket_absen'] ?? '';

    if ($id_karyawan <= 0) json_out(['status' => 'error', 'msg' => 'Karyawan harus dipilih']);
    if (!$tgl) json_out(['status' => 'error', 'msg' => 'Tanggal wajib diisi']);
    if ($jam === null) $jam = '';
    if ($ket === null) $ket = '';

    // Insert
    try {
        $st = $koneksi->prepare("INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen) VALUES (?, ?, ?, ?)");
        $st->execute([$id_karyawan, $tgl, $jam, $ket]);
        json_out(['status' => 'success', 'msg' => 'Data absensi berhasil ditambahkan']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        json_out(['status' => 'error', 'msg' => 'Gagal menambah absensi.']);
    }
}

/* EDIT */
elseif ($action === 'edit') {
    $id = intval($_POST['id_absen'] ?? 0);
    $id_karyawan = intval($_POST['id_karyawan'] ?? 0);
    $tgl = $_POST['tgl_absen'] ?? '';
    $jam = $_POST['jam_kerja'] ?? '';
    $ket = $_POST['ket_absen'] ?? '';

    if ($id <= 0) json_out(['status' => 'error', 'msg' => 'ID absensi tidak valid']);
    if ($id_karyawan <= 0) json_out(['status' => 'error', 'msg' => 'Karyawan harus dipilih']);
    if (!$tgl) json_out(['status' => 'error', 'msg' => 'Tanggal wajib diisi']);

    try {
        $st = $koneksi->prepare("UPDATE tb_absen
            SET id_karyawan = ?,
                tgl_absen   = ?,
                jam_kerja   = ?,
                ket_absen   = ?
            WHERE id_absen = ?");
        $st->execute([$id_karyawan, $tgl, $jam, $ket, $id]);
        json_out(['status' => 'success', 'msg' => 'Data absensi berhasil diupdate']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        json_out(['status' => 'error', 'msg' => 'Gagal update absensi.']);
    }
}

/* DELETE */
elseif ($action === 'delete') {
    $id = intval($_POST['id_absen'] ?? 0);
    if ($id <= 0) json_out(['status' => 'error', 'msg' => 'ID absensi tidak valid']);

    try {
        $st = $koneksi->prepare("DELETE FROM tb_absen WHERE id_absen = ?");
        $st->execute([$id]);
        json_out(['status' => 'success', 'msg' => 'Data absensi berhasil dihapus']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        json_out(['status' => 'error', 'msg' => 'Gagal menghapus absensi.']);
    }
}

/* ELSE */
else {
    json_out(['status' => 'error', 'msg' => 'Action tidak dikenali']);
}
