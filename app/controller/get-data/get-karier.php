<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

$id = !empty($_GET['id']) ? decrypt($_GET['id']) : null;
$no = 1;

if ($id) {
    // Tampilan detail dari profil spesifik (data-karier.php)
    // Site/Departemen/Jabatan pakai LEFT JOIN: hasil import hanya mengisi jabatan
    // (site/departemen bisa NULL) — baris tetap tampil dengan tanda '-'.
    $stmt = $koneksi->prepare("SELECT k.*, j.nama_jabatan, d.nama_departemen, s.nama_site, kr.nik_karyawan, kr.nama_lengkap
        FROM tb_karyawan kr
        INNER JOIN tb_karier k ON k.id_karyawan = kr.id_karyawan
        LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        LEFT JOIN tb_departemen d ON k.id_departemen = d.id_departemen
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        WHERE k.id_karyawan=? 
        ORDER BY k.tgl_mulai DESC");
    $stmt->execute([$id]);
    $q = $stmt;
} else {
    // Tampilan global dengan filter (import-karier.php)
    $site    = $_GET['site'] ?? '';
    $jabatan = $_GET['jabatan'] ?? '';
    $status  = $_GET['status'] ?? 'Aktif'; // Default tampilkan yang Aktif
    $bulan   = $_GET['bulan'] ?? '';

    $where = ["1=1"];
    $params = [];

    if (!empty($site)) {
        $where[] = "k.id_site = ?";
        $params[] = $site;
    }

    if (!empty($jabatan)) {
        $where[] = "k.id_jabatan = ?";
        $params[] = $jabatan;
    }

    if (!empty($status)) {
        $where[] = "kr.status_karyawan = ?";
        $params[] = $status;
    }

    if (!empty($bulan)) {
        $where[] = "MONTH(k.tgl_selesai) = ?";
        $params[] = $bulan;
        // Jika ingin spesifik mencari habis kontrak HANYA di TAHUN INI, hilangkan komentar baris di bawah:
        $where[] = "YEAR(k.tgl_selesai) = YEAR(CURDATE())"; 
    }

    $where_sql = implode(" AND ", $where);

    $stKarier = $koneksi->prepare("SELECT k.*, j.nama_jabatan, d.nama_departemen, s.nama_site, kr.nik_karyawan, kr.nama_lengkap
        FROM tb_karier k
        INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan
        LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        LEFT JOIN tb_departemen d ON k.id_departemen = d.id_departemen
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        WHERE $where_sql
        ORDER BY kr.nik_karyawan ASC");
    $stKarier->execute($params);
    $q = $stKarier;
}

while ($r = $q->fetch()) { ?>
    <tr class="text-center">
        <?php if ($id) { ?>
        <td><?= $no ?></td>
    <?php } else { ?>
        <td><input type="checkbox" class="chk-extend" value="<?= $r['id_karier'] ?>"></td>
        <td><?= $no ?></td>
    <?php } ?>
        <?php if (!$id) { ?>
        <td><?= $r['nik_karyawan'] ?></td>
        <td><?= $r['nama_lengkap'] ?></td>
        <?php } ?>
        <td><?= trim((string)($r['nama_site'] ?? '')) !== '' ? htmlspecialchars($r['nama_site']) : '-' ?></td>
        <td><?= trim((string)($r['nama_departemen'] ?? '')) !== '' ? htmlspecialchars($r['nama_departemen']) : '-' ?></td>
        <td><?= trim((string)($r['nama_jabatan'] ?? '')) !== '' ? htmlspecialchars($r['nama_jabatan']) : '-' ?></td>

        <!-- Gaji Pokok (harian) -->
        <td><?= rupiah($r['gaji_pokok']) ?></td>

        <!-- Tanggal -->
        <td><?= !empty($r['tgl_mulai']) ? $r['tgl_mulai'] : '-' ?></td>
        <td><?= !empty($r['tgl_selesai']) ? $r['tgl_selesai'] : '-' ?></td>

        <!-- No Surat Kontrak -->
        <td><?= htmlspecialchars($r['no_surat'] ?? '', ENT_QUOTES) ?></td>

        <td class="text-center">
            <?php $fileKontrak = $r['file_kontrak'] ?? ''; ?>
            <?php if (!empty($fileKontrak)) { ?>
            <a href="app/uploads/kontrak/<?= rawurlencode($fileKontrak) ?>" target="_blank" class="btn btn-sm btn-success" title="Lihat File Kontrak"><i class="fa fa-file-pdf"></i></a>
            <?php } ?>
            <!-- Tombol generate surat kontrak (Preview & unduh Word) dihapus atas permintaan; fitur ini tidak dipakai lagi. -->
            <button type="button" class="btn btn-sm btn-secondary btnUploadKontrak" data-id="<?= $r['id_karier'] ?>" data-nama="<?= htmlspecialchars($r['nama_lengkap'], ENT_QUOTES) ?>" data-file="<?= htmlspecialchars($fileKontrak, ENT_QUOTES) ?>" title="<?= !empty($fileKontrak) ? 'Ganti File Kontrak' : 'Upload File Kontrak' ?>"><i class="fa fa-upload"></i></button>

            <?php if (!$id) { ?>
            <button class="btn btn-warning btn-sm btnExtendKarier"
                data-id="<?= $r['id_karier'] ?>"
                data-nama="<?= $r['nama_lengkap'] ?>"
                title="Perpanjang 6 bulan">
                <i class="fa fa-calendar-plus"></i>
            </button>
            <?php } ?>
            
            <button class="btn btn-warning btn-sm btnEditKarier"
                data-id="<?= $r['id_karier'] ?>"
                data-id-karier-enc="<?= htmlspecialchars(encrypt($r['id_karier']), ENT_QUOTES) ?>"
                data-nama="<?= htmlspecialchars($r['nama_lengkap'], ENT_QUOTES) ?>"
                data-id_karyawan="<?= $r['id_karyawan'] ?>"
                data-id_site="<?= $r['id_site'] ?>"
                data-id_departemen="<?= $r['id_departemen'] ?>"
                data-id_jabatan="<?= $r['id_jabatan'] ?>"

                data-gaji_pokok="<?= $r['gaji_pokok'] ?>"

                data-tgl_mulai="<?= $r['tgl_mulai'] ?>"
                data-tgl_selesai="<?= $r['tgl_selesai'] ?>"
                data-no_surat="<?= htmlspecialchars($r['no_surat'] ?? '', ENT_QUOTES) ?>"
                title="Edit karier">
                <i class="fa fa-pen"></i>
            </button>

            <button class="btn btn-danger btn-sm btnDeleteKarier"
                data-id="<?= $r['id_karier'] ?>"
                title="Hapus karier">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>
<?php
    $no++;
}
