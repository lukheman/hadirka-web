<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? '';
$id     = intval($_POST['id_karyawan_internal'] ?? 0);
$response = ['status' => 'error', 'msg' => 'Aksi tidak valid'];

try {
    if ($action === 'delete') {
        if ($id <= 0) throw new Exception("ID tidak valid");
        $stmt = $koneksi->prepare("DELETE FROM tb_karyawan_internal WHERE id_karyawan_internal = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) $response = ['status' => 'success', 'msg' => 'Karyawan berhasil dihapus'];
        else throw new Exception($stmt->error);
        echo json_encode($response);
        exit;
    }

    // Tangkap input & sanitasi
    $nik     = strtoupper(trim($_POST['nik_karyawan_internal'] ?? ''));
    $id_site = intval($_POST['id_site'] ?? 0);
    $nama    = trim($_POST['nama_karyawan'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $gaji    = preg_replace('/\D/', '', $_POST['gaji_pokok'] ?? '0');
    $bank    = !empty($_POST['nama_bank']) ? strtoupper(trim($_POST['nama_bank'])) : null;
    $rek     = !empty($_POST['no_rek']) ? trim($_POST['no_rek']) : null;
    $an_rek  = !empty($_POST['atas_nama_rek']) ? strtoupper(trim($_POST['atas_nama_rek'])) : null;

    if (empty($nik) || empty($nama) || empty($jabatan) || $id_site <= 0) {
        throw new Exception("Kolom NIK, Site, Nama, dan Jabatan wajib diisi!");
    }

    if ($action === 'add') {
        $cek = $koneksi->query("SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE nik_karyawan_internal='$nik'");
        if ($cek->num_rows > 0) throw new Exception("Gagal: NIK '$nik' sudah digunakan karyawan lain!");

        $stmt = $koneksi->prepare("INSERT INTO tb_karyawan_internal (nik_karyawan_internal, id_site, nama_karyawan, jabatan, gaji_pokok, nama_bank, no_rek, atas_nama_rek) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sissssss", $nik, $id_site, $nama, $jabatan, $gaji, $bank, $rek, $an_rek);
        $msg = "Karyawan baru berhasil ditambahkan";
    } elseif ($action === 'edit') {
        $cek = $koneksi->query("SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE nik_karyawan_internal='$nik' AND id_karyawan_internal != $id");
        if ($cek->num_rows > 0) throw new Exception("Gagal: NIK '$nik' sudah dimiliki oleh karyawan lain!");

        $stmt = $koneksi->prepare("UPDATE tb_karyawan_internal SET nik_karyawan_internal=?, id_site=?, nama_karyawan=?, jabatan=?, gaji_pokok=?, nama_bank=?, no_rek=?, atas_nama_rek=? WHERE id_karyawan_internal=?");
        $stmt->bind_param("sissssssi", $nik, $id_site, $nama, $jabatan, $gaji, $bank, $rek, $an_rek, $id);
        $msg = "Data karyawan berhasil diupdate";
    }

    if ($stmt->execute()) $response = ['status' => 'success', 'msg' => $msg];
    else throw new Exception($stmt->error);
} catch (Exception $e) {
    $response = ['status' => 'error', 'msg' => $e->getMessage()];
}

echo json_encode($response);
exit;
