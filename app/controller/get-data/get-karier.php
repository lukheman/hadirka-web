<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

$id = !empty($_GET['id']) ? decrypt($_GET['id']) : null;
$no = 1;

if ($id) {
    // Tampilan detail dari profil spesifik (data-karier.php)
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
    // Tampilan global dengan filter (import-karier.php)
    $site    = $_GET['site'] ?? '';
    $jabatan = $_GET['jabatan'] ?? '';
    $status  = $_GET['status'] ?? 'Aktif'; // Default tampilkan yang Aktif
    $bulan   = $_GET['bulan'] ?? '';

    $where = ["1=1"];

    if (!empty($site)) {
        $where[] = "k.id_site = '" . $koneksi->real_escape_string($site) . "'";
    }
    
    if (!empty($jabatan)) {
        $where[] = "k.id_jabatan = '" . $koneksi->real_escape_string($jabatan) . "'";
    }
    
    if (!empty($status)) {
        $where[] = "kr.status_karyawan = '" . $koneksi->real_escape_string($status) . "'";
    }

    if (!empty($bulan)) {
        $where[] = "MONTH(k.tgl_selesai) = '" . $koneksi->real_escape_string($bulan) . "'";
        // Jika ingin spesifik mencari habis kontrak HANYA di TAHUN INI, hilangkan komentar baris di bawah:
        $where[] = "YEAR(k.tgl_selesai) = YEAR(CURDATE())"; 
    }

    $where_sql = implode(" AND ", $where);

    $q = $koneksi->query("SELECT k.*, j.nama_jabatan, d.nama_departemen, s.nama_site, kr.nik_karyawan, kr.nama_lengkap 
        FROM tb_karier k
        INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan
        INNER JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        INNER JOIN tb_departemen d ON k.id_departemen = d.id_departemen
        INNER JOIN tb_site s ON k.id_site = s.id_site
        WHERE $where_sql
        ORDER BY kr.nik_karyawan ASC");
}

while ($r = $q->fetch_assoc()) { ?>
    <tr class="text-center">
        <?php if ($id) { ?>
        <td><?= $no ?></td>
    <?php } else { ?>
        <td><input type="checkbox" class="chk-extend" value="<?= $r['id_karier'] ?>"></td>
        <td><?= $no ?></td>
    <?php } ?>
        <td><?= $r['nik_karyawan'] ?></td>
        <?php if (!$id) { ?>
        <td><?= $r['nama_lengkap'] ?></td>
        <?php } ?>
        <td><?= $r['nama_site'] ?></td>
        <td><?= $r['nama_departemen'] ?></td>
        <td><?= $r['nama_jabatan'] ?></td>

        <!-- Gaji Pokok -->
        <td><?= rupiah($r['gaji_pokok']) ?></td>
        <!-- OT -->
        <td><?= rupiah($r['lembur']) ?></td>

        <!-- Potongan -->
        <td><?= rupiah($r['pot_izin']) ?></td>
        <td><?= rupiah($r['pot_mangkir']) ?></td>

        <!-- Tanggal -->
        <td><?= $r['tgl_mulai'] ?></td>
        <td><?= $r['tgl_selesai'] ?></td>

        <td class="text-center">
            <button type="button" class="btn btn-sm btn-info btnTunjangan" data-id-karier-enc="<?= htmlspecialchars(encrypt($r['id_karier']), ENT_QUOTES) ?>" data-nama="<?= htmlspecialchars($r['nama_lengkap'], ENT_QUOTES) ?>">Tunjangan</button>
            <button type="button" class="btn btn-sm btn-info btnPotongan" data-id-karier-enc="<?= htmlspecialchars(encrypt($r['id_karier']), ENT_QUOTES) ?>" data-nama="<?= htmlspecialchars($r['nama_lengkap'], ENT_QUOTES) ?>">Potongan</button>

            <button class="btn btn-warning btn-sm btnExtendKarier"
                data-id="<?= $r['id_karier'] ?>"
                data-nama="<?= $r['nama_lengkap'] ?>">
                <i class="fa fa-calendar-plus"></i> +6 Bln
            </button>
            
            <button class="btn btn-primary btn-sm btnEditKarier"
                data-id="<?= $r['id_karier'] ?>"
                data-id_karyawan="<?= $r['id_karyawan'] ?>"
                data-id_site="<?= $r['id_site'] ?>"
                data-id_departemen="<?= $r['id_departemen'] ?>"
                data-id_jabatan="<?= $r['id_jabatan'] ?>"

                data-gaji_pokok="<?= $r['gaji_pokok'] ?>"

                data-lembur="<?= $r['lembur'] ?>"

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
