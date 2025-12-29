<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../config/koneksi.php';
require '../../config/function.php';

$id = isset($_GET['id']) ? decrypt($_GET['id']) : null;
$no = 1;

if ($id) {
    $stmt = $koneksi->prepare("SELECT k.*, j.nama_jabatan, d.nama_departemen, s.nama_site, kr.nik_karyawan, kr.nama_lengkap
        FROM tb_karyawan kr
        INNER JOIN tb_karier k ON k.id_karyawan = kr.id_karyawan
        INNER JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        INNER JOIN tb_departemen d ON k.id_departemen = d.id_departemen
        INNER JOIN tb_site s ON k.id_site = s.id_site
        WHERE k.id_karyawan=? 
        ORDER BY k.tgl_mulai DESC");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $q = $stmt->get_result();
} else {
    $q = $koneksi->query("SELECT k.*, j.nama_jabatan, d.nama_departemen, s.nama_site 
        FROM tb_karier k
        INNER JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        INNER JOIN tb_departemen d ON k.id_departemen = d.id_departemen
        INNER JOIN tb_site s ON k.id_site = s.id_site
        ORDER BY k.tgl_mulai DESC");
}

while ($r = $q->fetch_assoc()) { ?>
    <tr class="text-center">
        <td><?= $no ?></td>
        <td><?= $r['nik_karyawan'] ?></td>
        <td><?= $r['nama_lengkap'] ?></td>
        <td><?= $r['nama_site'] ?></td>
        <td><?= $r['nama_departemen'] ?></td>
        <td><?= $r['nama_jabatan'] ?></td>

        <!-- Gaji Pokok -->
        <td><?= rupiah($r['gaji_pokok']) ?></td>
        <!-- OT -->
        <td><?= rupiah($r['ot_pertama']) ?></td>
        <td><?= rupiah($r['ot_kedua']) ?></td>
        <td><?= rupiah($r['spl']) ?></td>
        <td><?= rupiah($r['ot1_spl']) ?></td>
        <td><?= rupiah($r['ot2_spl']) ?></td>

        <!-- Potongan -->
        <td><?= rupiah($r['pot_izin']) ?></td>
        <td><?= rupiah($r['pot_mangkir']) ?></td>

        <!-- Tanggal -->
        <td><?= $r['tgl_mulai'] ?></td>
        <td><?= $r['tgl_selesai'] ?></td>

        <td class="text-center">
            <a href="data-tunjangan/<?= encrypt($r['id_karier']) ?>" class="btn btn-sm btn-info">Tunjangan</a>
            <a href="data-potongan/<?= encrypt($r['id_karier']) ?>" class="btn btn-sm btn-info">Tanggungan</a>

            <button class="btn btn-primary btn-sm btnEditKarier"
                data-id="<?= $r['id_karier'] ?>"
                data-id_karyawan="<?= $r['id_karyawan'] ?>"
                data-id_site="<?= $r['id_site'] ?>"
                data-id_departemen="<?= $r['id_departemen'] ?>"
                data-id_jabatan="<?= $r['id_jabatan'] ?>"

                data-gaji_pokok="<?= $r['gaji_pokok'] ?>"

                data-ot_pertama="<?= $r['ot_pertama'] ?>"
                data-ot_kedua="<?= $r['ot_kedua'] ?>"
                data-spl="<?= $r['spl'] ?>"
                data-ot1_spl="<?= $r['ot1_spl'] ?>"
                data-ot2_spl="<?= $r['ot2_spl'] ?>"

                data-pot_izin="<?= $r['pot_izin'] ?>"
                data-pot_mangkir="<?= $r['pot_mangkir'] ?>"

                data-tgl_mulai="<?= $r['tgl_mulai'] ?>"
                data-tgl_selesai="<?= $r['tgl_selesai'] ?>">

                Edit
            </button>

            <button class="btn btn-danger btn-sm btnDeleteKarier"
                data-id="<?= $r['id_karier'] ?>">
                <i class="fa fa-trash"></i> Hapus
            </button>
        </td>
    </tr>
<?php
    $no++;
}
