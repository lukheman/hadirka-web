<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

$tgl_awal = $koneksi->real_escape_string($_POST['tgl_awal'] ?? '');
$tgl_akhir = $koneksi->real_escape_string($_POST['tgl_akhir'] ?? '');
$site = $_POST['site'] ?? '';

$where = "k.status_karyawan = 'Aktif'";
if (!empty($site)) $where .= " AND k.id_site = '" . $koneksi->real_escape_string($site) . "'";

// MENGGUNAKAN SUBQUERY UNTUK MENCEGAH DUPLIKASI DATA
$sql = "SELECT k.nik_karyawan_internal, k.nama_karyawan, s.nama_site, k.gaji_pokok,
        (SELECT IFNULL(SUM(nominal), 0) FROM tb_bonus_internal WHERE id_karyawan_internal = k.id_karyawan_internal AND tgl_bonus BETWEEN '$tgl_awal' AND '$tgl_akhir') AS total_bonus,
        (SELECT IFNULL(SUM(nominal), 0) FROM tb_rapel_internal WHERE id_karyawan_internal = k.id_karyawan_internal AND tgl_rapel BETWEEN '$tgl_awal' AND '$tgl_akhir') AS total_rapel,
        (SELECT IFNULL(SUM(nominal), 0) FROM tb_potongan_internal WHERE id_karyawan_internal = k.id_karyawan_internal AND tgl_potongan BETWEEN '$tgl_awal' AND '$tgl_akhir') AS total_potongan,
        (SELECT IFNULL(SUM(nominal_pinjaman), 0) FROM tb_pinjaman_internal WHERE id_karyawan_internal = k.id_karyawan_internal AND tgl_bayar_pinjaman BETWEEN '$tgl_awal' AND '$tgl_akhir') AS total_pinjaman
        FROM tb_karyawan_internal k
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        WHERE $where
        ORDER BY k.nama_karyawan ASC";

$q = $koneksi->query($sql);
$data = [];
$no = 1;

while ($r = $q->fetch_assoc()) {
    $gapok = floatval($r['gaji_pokok']);
    $bonus = floatval($r['total_bonus']);
    $rapel = floatval($r['total_rapel']);
    $potongan = floatval($r['total_potongan']);
    $pinjaman = floatval($r['total_pinjaman']);
    
    // RUMUS GAJI BERSIH (Pendapatan - Potongan)
    $bersih = ($gapok + $bonus + $rapel) - ($potongan + $pinjaman);

    $data[] = [
        "no" => $no++,
        "nik" => "<code>{$r['nik_karyawan_internal']}</code>",
        "nama" => "<strong>{$r['nama_karyawan']}</strong>",
        "site" => $r['nama_site'] ?? '-',
        "gapok" => "Rp " . number_format($gapok, 0, ',', '.'),
        "bonus" => "Rp " . number_format($bonus, 0, ',', '.'),
        "rapel" => "Rp " . number_format($rapel, 0, ',', '.'),
        "potongan" => "Rp " . number_format($potongan, 0, ',', '.'),
        "pinjaman" => "Rp " . number_format($pinjaman, 0, ',', '.'),
        "gaji_bersih" => "Rp " . number_format($bersih, 0, ',', '.')
    ];
}
echo json_encode(["data" => $data]);