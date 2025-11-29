<?php
// act-libur.php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

function json_out($arr)
{
    header('Content-Type: application/json');
    echo json_encode($arr);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $awal = $_POST['tgl_awal_libur'] ?? '';
    $akhir = $_POST['tgl_akhir_libur'] ?? '';
    $ket = $koneksi->real_escape_string($_POST['ket_libur'] ?? '');

    if (!$awal || !$akhir) json_out(['status' => 'error', 'msg' => 'Tanggal harus diisi']);
    if ($awal > $akhir) json_out(['status' => 'error', 'msg' => 'Tanggal awal tidak boleh lebih besar dari tanggal akhir']);

    $ins = $koneksi->query("INSERT INTO tb_libur (tgl_awal_libur, tgl_akhir_libur, ket_libur) VALUES ('$awal','$akhir','$ket')");
    if ($ins) json_out(['status' => 'success', 'msg' => 'Data libur berhasil ditambahkan']);
    json_out(['status' => 'error', 'msg' => 'Gagal menambah data']);
} elseif ($action === 'edit') {
    $id = intval($_POST['id_libur'] ?? 0);
    $awal = $_POST['tgl_awal_libur'] ?? '';
    $akhir = $_POST['tgl_akhir_libur'] ?? '';
    $ket = $koneksi->real_escape_string($_POST['ket_libur'] ?? '');

    if ($id <= 0) json_out(['status' => 'error', 'msg' => 'ID tidak valid']);
    if (!$awal || !$akhir) json_out(['status' => 'error', 'msg' => 'Tanggal harus diisi']);
    if ($awal > $akhir) json_out(['status' => 'error', 'msg' => 'Tanggal awal tidak boleh lebih besar dari tanggal akhir']);

    $u = $koneksi->query("UPDATE tb_libur SET tgl_awal_libur='$awal', tgl_akhir_libur='$akhir', ket_libur='$ket' WHERE id_libur=$id");
    if ($u) json_out(['status' => 'success', 'msg' => 'Data berhasil diupdate']);
    json_out(['status' => 'error', 'msg' => 'Gagal update']);
} elseif ($action === 'delete') {
    $id = intval($_POST['id_libur'] ?? 0);
    if ($id <= 0) json_out(['status' => 'error', 'msg' => 'ID tidak valid']);

    $d = $koneksi->query("DELETE FROM tb_libur WHERE id_libur=$id");
    if ($d) json_out(['status' => 'success', 'msg' => 'Data berhasil dihapus']);
    json_out(['status' => 'error', 'msg' => 'Gagal menghapus']);
} else {
    json_out(['status' => 'error', 'msg' => 'Action tidak dikenali']);
}
