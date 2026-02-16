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
    $ket = $koneksi->real_escape_string($ket);

    if ($id_karyawan <= 0) json_out(['status' => 'error', 'msg' => 'Karyawan harus dipilih']);
    if (!$tgl) json_out(['status' => 'error', 'msg' => 'Tanggal wajib diisi']);
    if ($jam === null) $jam = '';
    if ($ket === null) $ket = '';

    // Insert
    $ins = $koneksi->query("
        INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen)
        VALUES ($id_karyawan, '$tgl', '$jam', '$ket')
    ");

    if ($ins) json_out(['status' => 'success', 'msg' => 'Data absensi berhasil ditambahkan']);
    json_out(['status' => 'error', 'msg' => 'Gagal menambah absensi: ' . $koneksi->error]);
}

/* EDIT */
elseif ($action === 'edit') {
    $id = intval($_POST['id_absen'] ?? 0);
    $id_karyawan = intval($_POST['id_karyawan'] ?? 0);
    $tgl = $_POST['tgl_absen'] ?? '';
    $jam = $_POST['jam_kerja'] ?? '';
    $ket = $_POST['ket_absen'] ?? '';
    $ket = $koneksi->real_escape_string($ket);

    if ($id <= 0) json_out(['status' => 'error', 'msg' => 'ID absensi tidak valid']);
    if ($id_karyawan <= 0) json_out(['status' => 'error', 'msg' => 'Karyawan harus dipilih']);
    if (!$tgl) json_out(['status' => 'error', 'msg' => 'Tanggal wajib diisi']);

    $upd = $koneksi->query("
        UPDATE tb_absen 
        SET id_karyawan = $id_karyawan,
            tgl_absen   = '$tgl',
            jam_kerja   = '$jam',
            ket_absen   = '$ket'
        WHERE id_absen = $id
    ");

    if ($upd) json_out(['status' => 'success', 'msg' => 'Data absensi berhasil diupdate']);
    json_out(['status' => 'error', 'msg' => 'Gagal update absensi: ' . $koneksi->error]);
}

/* DELETE */
elseif ($action === 'delete') {
    $id = intval($_POST['id_absen'] ?? 0);
    if ($id <= 0) json_out(['status' => 'error', 'msg' => 'ID absensi tidak valid']);

    $del = $koneksi->query("DELETE FROM tb_absen WHERE id_absen = $id");
    if ($del) json_out(['status' => 'success', 'msg' => 'Data absensi berhasil dihapus']);
    json_out(['status' => 'error', 'msg' => 'Gagal menghapus absensi: ' . $koneksi->error]);
}

/* ELSE */
else {
    json_out(['status' => 'error', 'msg' => 'Action tidak dikenali']);
}
