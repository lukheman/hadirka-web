<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../config/function.php';

$site   = $_POST['site'] ?? '';
$tgl    = $_POST['tgl'] ?? date('Y-m-d');
$status = $_POST['status'] ?? '';
$no     = 1;

// Ambil karyawan berdasar site dan karier terbaru/aktif
$sql = "
SELECT 
    k.id_karyawan,
    k.nama_lengkap,
    j.nama_jabatan,
    j.id_jabatan,
    s.nama_site,
    s.id_site,
    kr.tgl_mulai,
    kr.tgl_selesai
FROM tb_karyawan k
LEFT JOIN tb_karier kr ON kr.id_karier = (
    SELECT kr2.id_karier
    FROM tb_karier kr2
    WHERE kr2.id_karyawan = k.id_karyawan
      AND (kr2.tgl_selesai IS NULL OR kr2.tgl_selesai >= ?)
    ORDER BY kr2.tgl_mulai DESC, kr2.id_karier DESC
    LIMIT 1
)
LEFT JOIN tb_site s ON kr.id_site = s.id_site
LEFT JOIN tb_jabatan j ON kr.id_jabatan = j.id_jabatan
WHERE 1=1
";

$paramsAbsen = [$tgl];
if ($site != "") {
    $sql .= " AND kr.id_site = ?";
    $paramsAbsen[] = $site;
}

$stKaryawan = $koneksi->prepare($sql);
$stKaryawan->execute($paramsAbsen);
$rowsKaryawan = $stKaryawan->fetchAll();

if (count($rowsKaryawan) == 0) {
    exit;
}

foreach ($rowsKaryawan as $k) {
    $idk = $k['id_karyawan'];

    // Ambil data absen
    $stA = $koneksi->prepare("
        SELECT * FROM tb_absen
        WHERE id_karyawan = ? AND tgl_absen = ?
        LIMIT 1
    ");
    $stA->execute([$idk, $tgl]);
    $a = $stA->fetch();

    if ($a) {
        // Ada data absen
        $ket = strtoupper($a['ket_absen']);
        $jam = $a['jam_kerja'];

        if ($status != "" && $status != $ket) continue;
?>
        <tr>
            <td><?= $no ?></td>
            <td class="td-nama" data-karyawan-id="<?= $k['id_karyawan'] ?>"><?= $k['nama_lengkap'] ?></td>
            <td class="td-jabatan" data-jabatan-id="<?= $k['id_jabatan'] ?>"><?= $k['nama_jabatan'] ?></td>
            <td class="td-site" data-site-id="<?= $k['id_site'] ?>"><?= $k['nama_site'] ?></td>
            <td class="td-tanggal" data-tgl="<?= $a['tgl_absen'] ?>"><?= tanggal_indo($a['tgl_absen'], true) ?></td>
            <td class="td-jam" data-jam="<?= $jam ?>"><?= $jam ?></td>
            <!-- <td class="td-ket" data-ket="<?= $a['ket_absen'] ?>"><?= strtoupper($a['ket_absen']) ?></td> -->
            <td class="td-ket" data-ket="<?= $a['ket_absen'] ?>"><?= $a['ket_absen'] ?></td>
            <td class="text-center">
                <button class="btn btn-sm btn-warning btn-edit" data-id="<?= $a['id_absen'] ?>"><i class="fa fa-edit"></i></button>
                <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $a['id_absen'] ?>"><i class="fa fa-trash"></i></button>
            </td>
        </tr>

    <?php
        $no++;
    } else {
        // MANGKIR? cek karier aktif & hari
        $tgl_mulai   = $k['tgl_mulai'] ?? null;
        $tgl_selesai = $k['tgl_selesai'] ?? null;

        if (($tgl_mulai && $tgl < $tgl_mulai) || ($tgl_selesai && $tgl > $tgl_selesai)) continue;

        $hari = date('N', strtotime($tgl)); // 1=Senin .. 7=Minggu
        if ($hari == 7) continue;

        if ($status != "" && $status != "MANGKIR") continue;
    ?>
        <tr class='table-danger'>
            <td><?= $no ?></td>
            <td><?= $k['nama_lengkap'] ?></td>
            <td><?= $k['nama_jabatan'] ?></td>
            <td><?= $k['nama_site'] ?></td>
            <td><?= tanggal_indo($tgl, true) ?></td>
            <td>Tidak Masuk</td>
            <td>MANGKIR</td>
            <td class="text-center">
                -
            </td>
        </tr>
<?php
        $no++;
    }
}
