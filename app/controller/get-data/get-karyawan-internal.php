<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

$site = $_POST['site'] ?? '';
$status = $_POST['status'] ?? 'Aktif';

$where = ["1=1"];
if (!empty($site)) $where[] = "k.id_site = '" . $koneksi->real_escape_string($site) . "'";
if ($status !== 'Semua') $where[] = "k.status_karyawan = '" . $koneksi->real_escape_string($status) . "'";

$where_sql = implode(" AND ", $where);
$sql = "SELECT k.*, s.nama_site 
        FROM tb_karyawan_internal k 
        LEFT JOIN tb_site s ON k.id_site = s.id_site 
        WHERE $where_sql ORDER BY k.nik_karyawan_internal ASC";

$q = $koneksi->query($sql);
$data = [];
$no = 1;

while ($r = $q->fetch_assoc()) {
    $st = $r['status_karyawan'];
    $badge = ($st == 'Aktif') ? "<span class='badge bg-success'>$st</span>" : "<span class='badge bg-danger'>$st</span>";

    if ($st == 'Aktif') {
        $nama_aman = htmlspecialchars($r['nama_karyawan'], ENT_QUOTES); // Tambahkan ini
        
        $btn = "
            <button class='btn btn-sm btn-primary btnEdit' data-id='{$r['id_karyawan_internal']}'><i class='fa fa-edit'></i></button>
            <button class='btn btn-sm btn-warning btnEliminasi' data-id='{$r['id_karyawan_internal']}' data-nama='{$r['nama_karyawan']}'><i class='fa fa-user-slash'></i></button>
            <button class='btn btn-sm btn-danger btnDelete' data-id='{$r['id_karyawan_internal']}'><i class='fa fa-trash'></i></button>
        ";
    } else {
        $btn = "
            <button class='btn btn-sm btn-primary btnEdit' data-id='{$r['id_karyawan_internal']}'><i class='fa fa-edit'></i></button>
            <button class='btn btn-sm btn-danger btnDelete' data-id='{$r['id_karyawan_internal']}'><i class='fa fa-trash'></i></button>
        ";
    }

    $data[] = [
        "no" => $no++,
        "nik" => "<code>{$r['nik_karyawan_internal']}</code>",
        "nama" => "<strong>{$r['nama_karyawan']}</strong>",
        "jk" => $r['jenis_kelamin'] ?: '-',
        "site" => $r['nama_site'] ?? '-',
        "jabatan" => $r['jabatan'],
        "gaji" => "Rp " . number_format($r['gaji_pokok'], 0, ',', '.'),
        "status" => $badge,
        "aksi" => "<div class='btn-group'>$btn</div>"
    ];
}

echo json_encode(["data" => $data]);
