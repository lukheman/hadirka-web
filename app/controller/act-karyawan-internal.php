<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? '';
$id     = intval($_POST['id'] ?? $_POST['id_karyawan_internal'] ?? 0);

try {
    if ($action === 'get') {
        $q = $koneksi->query("SELECT * FROM tb_karyawan_internal WHERE id_karyawan_internal=$id");
        if ($q->num_rows > 0) echo json_encode(['status' => 'success', 'data' => $q->fetch_assoc()]);
        else echo json_encode(['status' => 'error', 'msg' => 'Data tidak ditemukan']);
        exit;
    }

    if ($action === 'delete') {
        $koneksi->query("DELETE FROM tb_karyawan_internal WHERE id_karyawan_internal=$id");
        echo json_encode(['status' => 'success', 'msg' => 'Dihapus permanen']);
        exit;
    }

    if ($action === 'eliminasi') {
        $status = $_POST['status_karyawan'];
        $tgl_keluar = $_POST['tgl_keluar'];
        $ket = trim($_POST['ket_keluar']);

        $stmt = $koneksi->prepare("UPDATE tb_karyawan_internal SET status_karyawan=?, tgl_keluar=?, ket_keluar=? WHERE id_karyawan_internal=?");
        $stmt->bind_param("sssi", $status, $tgl_keluar, $ket, $id);
        if ($stmt->execute()) echo json_encode(['status' => 'success', 'msg' => 'Karyawan dieliminasi']);
        else echo json_encode(['status' => 'error', 'msg' => $stmt->error]);
        exit;
    }

    // ADD / EDIT
    // Ambil input dan bersihkan awalan 'INT-' jika user kebetulan mengetiknya manual
    $nik_input = trim($_POST['nik_karyawan_internal']);

    // Hapus awalan 'INT', 'INT-', 'INT ', 'int', 'int-' (case-insensitive) di awal string
    // Regex /^INT[\s-]?/i akan mendeteksi kata INT yang diikuti spasi/strip (opsional) di awal text
    $nik_input = preg_replace('/^INT[\s-]?/i', '', $nik_input);

    // Ubah sisa angkanya/hurufnya menjadi uppercase dan gabungkan dengan INT-
    $nik = 'INT-' . strtoupper(trim($nik_input));

    $id_site = intval($_POST['id_site']);
    $nama = trim($_POST['nama_karyawan']);
    $jk = trim($_POST['jenis_kelamin'] ?? '');
    $jabatan = trim($_POST['jabatan']);
    $gaji = preg_replace('/\D/', '', $_POST['gaji_pokok']);
    $tgl_masuk = $_POST['tgl_masuk'];
    $bank = strtoupper(trim($_POST['nama_bank'] ?? ''));
    $rek = trim($_POST['no_rek'] ?? '');
    $an = strtoupper(trim($_POST['atas_nama_rek'] ?? ''));

    if ($action === 'add') {
        $cek = $koneksi->query("SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE nik_karyawan_internal='$nik'");
        if ($cek->num_rows > 0) throw new Exception("NIK sudah ada!");

        $stmt = $koneksi->prepare("INSERT INTO tb_karyawan_internal (nik_karyawan_internal, id_site, nama_karyawan, jenis_kelamin, jabatan, gaji_pokok, tgl_masuk, nama_bank, no_rek, atas_nama_rek) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sissssssss", $nik, $id_site, $nama, $jk, $jabatan, $gaji, $tgl_masuk, $bank, $rek, $an);
        if ($stmt->execute()) echo json_encode(['status' => 'success', 'msg' => 'Berhasil ditambahkan']);
    } elseif ($action === 'edit') {
        $stmt = $koneksi->prepare("UPDATE tb_karyawan_internal SET nik_karyawan_internal=?, id_site=?, nama_karyawan=?, jenis_kelamin=?, jabatan=?, gaji_pokok=?, tgl_masuk=?, nama_bank=?, no_rek=?, atas_nama_rek=? WHERE id_karyawan_internal=?");
        $stmt->bind_param("sissssssssi", $nik, $id_site, $nama, $jk, $jabatan, $gaji, $tgl_masuk, $bank, $rek, $an, $id);
        if ($stmt->execute()) echo json_encode(['status' => 'success', 'msg' => 'Berhasil diupdate']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}
