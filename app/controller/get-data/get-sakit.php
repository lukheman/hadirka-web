<?php
require '../../config/koneksi.php';
require '../../config/function.php';

$tgl = $_POST['tgl'] ?? date('Y-m-d');
$site = $_POST['site'] ?? '';

if (!$site) {
    echo "<tr><td colspan='7' class='text-center text-danger'>Site harus dipilih!</td></tr>";
    exit;
}

$sql = "SELECT *
        FROM tb_sakit i
        JOIN tb_karyawan k ON i.id_karyawan = k.id_karyawan
        JOIN tb_karier kar ON kar.id_karyawan = k.id_karyawan
        JOIN tb_departemen d ON kar.id_departemen = d.id_departemen
        JOIN tb_jabatan j ON kar.id_jabatan = j.id_jabatan
        JOIN tb_site s ON kar.id_site = s.id_site
        WHERE kar.id_site = '$site'
        AND '$tgl' BETWEEN i.tgl_awal_sakit AND i.tgl_akhir_sakit
        ORDER BY i.tgl_awal_sakit ASC";


$result = mysqli_query($koneksi, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<tr><td colspan='7' class='text-center text-warning'>
            Tidak ada data sakit pada tanggal tersebut
          </td></tr>";
    exit;
}

$no = 1;
while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td class='text-center'><?= $no ?></td>
        <td><?= $row['nama_lengkap'] ?></td>
        <td><?= $row['nama_jabatan'] ?></td>
        <td><?= $row['nama_site'] ?></td>
        <td class='text-center'><?= date('d-m-Y', strtotime($row['tgl_awal_sakit'])) ?> <br> <?= jumlahHari($row['tgl_awal_sakit'], $row['tgl_akhir_sakit']) ?> Hari</td>
        <!-- <td class='text-center'></td> -->
        <td class="text-center"><?= $row['ket_sakit'] ?></td>
        <td class='text-center'>
            <button class='btn btn-sm btn-primary btnEditSakit'
                data-id='<?= $row['id_sakit'] ?>'
                data-site='<?= $row['id_site'] ?>'
                data-jabatan='<?= $row['id_jabatan'] ?>'
                data-karyawan='<?= $row['id_karyawan'] ?>'
                data-awal='<?= $row['tgl_awal_sakit'] ?>'
                data-akhir='<?= $row['tgl_akhir_sakit'] ?>'
                data-ket='<?= $row['ket_sakit'] ?>'>
                Edit
            </button>
            <button class='btn btn-sm btn-danger btnDeleteSakit'
                data-id='<?= $row['id_sakit'] ?>'>
                Hapus
            </button>
        </td>
    </tr>
<?php
    $no++;
}
