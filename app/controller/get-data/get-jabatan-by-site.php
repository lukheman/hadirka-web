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
    $stmt->bind_param("i", $site);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}
echo json_encode($data);
