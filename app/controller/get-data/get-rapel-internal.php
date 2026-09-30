<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

$tgl_awal = $_POST['tgl_awal'] ?? '';
$tgl_akhir = $_POST['tgl_akhir'] ?? '';
$where = "1=1";

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where .= " AND r.tgl_rapel BETWEEN '" . $koneksi->real_escape_string($tgl_awal) . "' AND '" . $koneksi->real_escape_string($tgl_akhir) . "'";
}

$sql = "SELECT r.*, k.nik_karyawan_internal, k.nama_karyawan 
        FROM tb_rapel_internal r
        INNER JOIN tb_karyawan_internal k ON r.id_karyawan_internal = k.id_karyawan_internal
        WHERE $where
        ORDER BY r.tgl_rapel DESC";
$q = $koneksi->query($sql);
$data = [];
$no = 1;

while ($r = $q->fetch_assoc()) {
    $btn = "
        <button class='btn btn-sm btn-warning btnEditRapel' data-id='{$r['id_rapel']}' data-karyawan='{$r['id_karyawan_internal']}' data-tgl='" . date('Y-m-d', strtotime($r['tgl_rapel'])) . "' data-nominal='{$r['nominal']}' data-ket='{$r['ket_rapel']}'><i class='fa fa-edit'></i></button>
        <button class='btn btn-sm btn-danger btnDeleteRapel' data-id='{$r['id_rapel']}'><i class='fa fa-trash'></i></button>
    ";

    $data[] = [
        "no" => $no++,
        "nik" => "<code>{$r['nik_karyawan_internal']}</code>",
        "nama" => "<strong>{$r['nama_karyawan']}</strong>",
        "tgl" => "<span class='badge bg-label-info'>" . date('d/m/Y', strtotime($r['tgl_rapel'])) . "</span>",
        "nominal" => "Rp " . number_format($r['nominal'], 0, ',', '.'),
        "ket" => $r['ket_rapel'] ?: '-',
        "aksi" => "<div class='btn-group'>$btn</div>"
    ];
}
echo json_encode(["data" => $data]);