<?php
require '../../config/koneksi.php';
$site = $_POST['site'] ?? '';

$data = [];
if ($site) {
    $stmt = $koneksi->prepare("
        SELECT DISTINCT j.id_jabatan, j.nama_jabatan
        FROM tb_karier kar
        JOIN tb_jabatan j ON kar.id_jabatan = j.id_jabatan
        WHERE kar.id_site = ?
          AND (kar.tgl_selesai IS NULL OR kar.tgl_selesai >= CURDATE())
    ");
    $stmt->execute([$site]);
    $res = $stmt;
    while ($row = $res->fetch()) {
        $data[] = $row;
    }
}
echo json_encode($data);
