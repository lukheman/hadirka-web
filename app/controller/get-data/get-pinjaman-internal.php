<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

$tgl_awal = $_POST['tgl_awal'] ?? '';
$tgl_akhir = $_POST['tgl_akhir'] ?? '';
$where = "1=1";

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    // Karena ini pinjaman/potongan otomatis di slip, kita filter berdasarkan tgl_bayar (kapan uangnya ditarik dari gaji)
    $where .= " AND p.tgl_bayar_pinjaman BETWEEN '" . $koneksi->real_escape_string($tgl_awal) . "' AND '" . $koneksi->real_escape_string($tgl_akhir) . "'";
}

$sql = "SELECT p.*, k.nik_karyawan_internal, k.nama_karyawan 
        FROM tb_pinjaman_internal p
        INNER JOIN tb_karyawan_internal k ON p.id_karyawan_internal = k.id_karyawan_internal
        WHERE $where
        ORDER BY p.tgl_bayar_pinjaman DESC, p.tgl_pinjaman DESC";
$q = $koneksi->query($sql);
$data = [];
$no = 1;

while ($r = $q->fetch_assoc()) {
    $btn = "
        <button class='btn btn-sm btn-warning btnEditPinjaman' 
            data-id='{$r['id_pinjaman']}' 
            data-karyawan='{$r['id_karyawan_internal']}' 
            data-tglcair='" . date('Y-m-d', strtotime($r['tgl_pinjaman'])) . "' 
            data-tglbayar='" . date('Y-m-d', strtotime($r['tgl_bayar_pinjaman'])) . "' 
            data-nominal='{$r['nominal_pinjaman']}' 
            data-ket='{$r['ket_pinjaman']}'>
            <i class='fa fa-edit'></i>
        </button>
        <button class='btn btn-sm btn-danger btnDeletePinjaman' data-id='{$r['id_pinjaman']}'>
            <i class='fa fa-trash'></i>
        </button>
    ";

    $data[] = [
        "no" => $no++,
        "nik" => "<code>{$r['nik_karyawan_internal']}</code>",
        "nama" => "<strong>{$r['nama_karyawan']}</strong>",
        "tgl_cair" => date('d/m/Y', strtotime($r['tgl_pinjaman'])),
        "tgl_bayar" => "<span class='badge bg-label-warning text-dark'>" . date('d/m/Y', strtotime($r['tgl_bayar_pinjaman'])) . "</span>",
        "nominal" => "Rp " . number_format($r['nominal_pinjaman'], 0, ',', '.'),
        "ket" => $r['ket_pinjaman'] ?: '-',
        "aksi" => "<div class='btn-group'>$btn</div>"
    ];
}
echo json_encode(["data" => $data]);