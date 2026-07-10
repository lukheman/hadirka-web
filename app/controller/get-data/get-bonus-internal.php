<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

$bulan = $_POST['bulan'] ?? '';
$where = "1=1";

if (!empty($bulan)) {
    // Format $bulan adalah YYYY-MM
    $where .= " AND DATE_FORMAT(b.tgl_bonus, '%Y-%m') = '" . $koneksi->real_escape_string($bulan) . "'";
}

$sql = "SELECT b.*, k.nik_karyawan_internal, k.nama_karyawan 
        FROM tb_bonus_internal b
        INNER JOIN tb_karyawan_internal k ON b.id_karyawan_internal = k.id_karyawan_internal
        WHERE $where
        ORDER BY b.tgl_bonus DESC";
$q = $koneksi->query($sql);
$data = [];
$no = 1;

while ($r = $q->fetch_assoc()) {
    $btn = "
        <button class='btn btn-sm btn-primary btnEdit' data-id='{$r['id_bonus']}' data-karyawan='{$r['id_karyawan_internal']}' data-tgl='" . date('Y-m-d', strtotime($r['tgl_bonus'])) . "' data-nominal='{$r['nominal']}' data-ket='{$r['ket_bonus']}'><i class='fa fa-edit'></i></button>
        <button class='btn btn-sm btn-danger btnDelete' data-id='{$r['id_bonus']}'><i class='fa fa-trash'></i></button>
    ";

    $data[] = [
        "no" => $no++,
        "nik" => "<code>{$r['nik_karyawan_internal']}</code>",
        "nama" => "<strong>{$r['nama_karyawan']}</strong>",
        "tgl" => "<span class='badge bg-label-info'>" . date('d/m/Y', strtotime($r['tgl_bonus'])) . "</span>",
        "nominal" => "Rp " . number_format($r['nominal'], 0, ',', '.'),
        "ket" => $r['ket_bonus'] ?: '-',
        "aksi" => "<div class='btn-group'>$btn</div>"
    ];
}
echo json_encode(["data" => $data]);
