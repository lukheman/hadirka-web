<?php
require '../../config/koneksi.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$site = $data['id_site'] ?? '';

if ($site == '') {
    echo json_encode([]);
    exit;
}

$q = mysqli_query($koneksi,
    "SELECT tb_karyawan.id_karyawan, nik_karyawan, nama_lengkap, nama_jabatan 
     FROM tb_karier
     JOIN tb_karyawan ON tb_karyawan.id_karyawan = tb_karier.id_karyawan
     LEFT JOIN tb_jabatan ON tb_jabatan.id_jabatan = tb_karier.id_jabatan
     WHERE tb_karier.id_site = '$site'"
);

$list = [];
while ($r = mysqli_fetch_assoc($q)) {
    $list[] = [
        "value" => $r['nik_karyawan'] . "|" . $r['nama_lengkap'] . "|" . ($r['nama_jabatan'] ?? ''),
        "text"  => $r['nik_karyawan'] . " - " . $r['nama_lengkap']
    ];
}

echo json_encode($list);
