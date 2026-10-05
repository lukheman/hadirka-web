<?php
require '../../config/koneksi.php'; // $koneksi adalah PDO

header('Content-Type: application/json');

$site = $_POST['site'] ?? '';
$jabatan = $_POST['jabatan'] ?? '';

$data = [];

if ($site && $jabatan) {
    // PERBAIKAN: Tambahkan kr.nik_karyawan pada baris SELECT di bawah ini
    $stmt = $koneksi->prepare("SELECT kr.id_karyawan, kr.nik_karyawan, kr.nama_lengkap
        FROM tb_karier c
        JOIN tb_karyawan kr ON c.id_karyawan = kr.id_karyawan
        WHERE c.id_site = ?
          AND c.id_jabatan = ?
          AND (c.tgl_selesai IS NULL OR c.tgl_selesai >= CURDATE())
        ORDER BY kr.nama_lengkap ASC");
        
    if ($stmt) {
        $stmt->execute([$site, $jabatan]);
        $res = $stmt;
        while ($row = $res->fetch()) {
            $data[] = $row;
        }
    }
}

echo json_encode($data);