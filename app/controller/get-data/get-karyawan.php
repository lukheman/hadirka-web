<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/koneksi.php';
require_once '../../config/function.php';

$filterSite = $_POST['site'] ?? "";
$filterJabatan = $_POST['jabatan'] ?? "";

// BASE QUERY
$sql = "
    SELECT 
        k.*, 
        kr.id_site, 
        kr.id_jabatan, 
        kr.tgl_selesai,
        s.nama_site,
        j.nama_jabatan
    FROM tb_karyawan k
    LEFT JOIN tb_karier kr ON k.id_karyawan = kr.id_karyawan
    LEFT JOIN tb_site s ON kr.id_site = s.id_site
    LEFT JOIN tb_jabatan j ON kr.id_jabatan = j.id_jabatan
";

$where = [];

// FILTER
if ($filterSite == "") {
    // hanya tampilkan karyawan baru atau expired kontrak
    $where[] = "(kr.id_karier IS NULL OR (kr.tgl_selesai < CURDATE()))";
} else {
    $where[] = "kr.id_site = '" . $koneksi->real_escape_string($filterSite) . "'";

    if ($filterJabatan != "") {
        $where[] = "kr.id_jabatan = '" . $koneksi->real_escape_string($filterJabatan) . "'";
    }
}

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY k.id_karyawan DESC";

$q = $koneksi->query($sql);

// TIDAK ADA DATA
if (!$q || $q->num_rows == 0) {
    // echo "<tr><td colspan='11' class='text-center'>Tidak ada data</td></tr>";
    exit;
}

$no = 1;
while ($r = $q->fetch_assoc()) {

    $expired = "";
    if (!empty($r['tgl_selesai']) && $r['tgl_selesai'] < date("Y-m-d")) {
        $expired = "text-danger";
    }
    $jabatan = $r['nama_jabatan'] ?: "None";


    // COLOR DATA 
    $idKR = $r['id_karyawan'];
    $cek_karier = mysqli_query($koneksi, "SELECT * FROM tb_karier WHERE id_karyawan = '$idKR'");
    $row = mysqli_fetch_array($cek_karier);

    $id_karier  = $row['id_karier'];
    $cek_tunjagan = mysqli_query($koneksi, "SELECT * from tb_tunjangan WHERE id_karier='$id_karier' AND nama_tunjangan like '%BPJS%'");
    $rw = mysqli_fetch_array($cek_tunjagan);
    if ($id_karier == $rw['id_karier'] && $rw['jumlah_tunjangan'] == 0) {
        $rowcolor = ' bg-danger';
    } else {
        $rowcolor = '';
    }
?>

    <tr class="<?= $expired ?> text-center <?= $rowcolor ?>">
        <td><?= $no++?></td>
        <td><?= $r['nama_lengkap']; ?></td>
        <td>
            <span class="badge bg-label-info"><?= $r['nik_karyawan']; ?></span>
        </td>
        <td><?= $jabatan; ?></td>
        <td>
            <span class="badge bg-label-primary"><?= jk($r['jenis_kelamin']); ?></span>
        </td>
        <td><?= $r['agama']; ?></td>
        <td><?= $r['tempat_lahir']; ?></td>
        <td>
            <?= date("d-m-Y", strtotime($r['tgl_lahir'])); ?>
        </td>
        <td><?= hitungUmur($r['tgl_lahir']); ?> Tahun</td>
        <td>
            <a target="_blank" href="http://wa.me/<?= no_telp($r['no_telp']) ?>" class="badge bg-label-success">
                <?= ($r['no_telp']) ?>
            </a>
        </td>

        <td>
            <a href="karier/<?= encrypt($r['id_karyawan']) ?>" class="btn btn-sm btn-success">
                Karier
            </a>
            <!-- Modal Identitas -->
        </td>
    </tr>

<?php } ?>