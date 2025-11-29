<?php
require '../../config/koneksi.php'; // pastikan $koneksi adalah MySQLi

header('Content-Type: application/json');

$site = $_POST['site'] ?? '';
$jabatan = $_POST['jabatan'] ?? '';

$data = [];

if ($site && $jabatan) {
    $stmt = $koneksi->prepare("SELECT kr.id_karyawan, kr.nama_lengkap
        FROM tb_karier c
        JOIN tb_karyawan kr ON c.id_karyawan = kr.id_karyawan
        WHERE c.id_site = ?
          AND c.id_jabatan = ?
          AND (c.tgl_selesai IS NULL OR c.tgl_selesai >= CURDATE())
    order by nama_lengkap ASC");
    if ($stmt) {
        $stmt->bind_param("ii", $site, $jabatan);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
    }
}

echo json_encode($data);
