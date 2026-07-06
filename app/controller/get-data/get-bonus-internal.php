<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

// JOIN ganda dengan tb_site untuk memberikan informasi lokasi lengkap di log bonus
$sql = "SELECT b.*, k.nik_karyawan_internal, k.nama_karyawan, k.jabatan, s.nama_site 
        FROM tb_bonus_internal b
        INNER JOIN tb_karyawan_internal k ON b.id_karyawan_internal = k.id_karyawan_internal
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        ORDER BY b.tgl_bonus DESC, b.id_bonus DESC";

$q = $koneksi->query($sql);
$no = 1;

while ($r = $q->fetch_assoc()) { 
    $nominalFormat = number_format($r['nominal'], 0, ',', '.');
    $tglDisplay = date('d/m/Y', strtotime($r['tgl_bonus']));
    $tglEdit = date('Y-m-d', strtotime($r['tgl_bonus']));
    $siteName = !empty($r['nama_site']) ? htmlspecialchars($r['nama_site']) : '-';
?>
    <tr class="text-center">
        <td><?= $no++ ?></td>
        <td><code><?= htmlspecialchars($r['nik_karyawan_internal']) ?></code></td>
        <td class="text-start">
            <strong><?= htmlspecialchars($r['nama_karyawan']) ?></strong><br>
            <small class="text-muted"><?= htmlspecialchars($r['jabatan']) ?> (<?= $siteName; ?>)</small>
        </td>
        <td><span class="badge bg-label-success"><?= $tglDisplay ?></span></td>
        <td class="text-end fw-bold text-primary">Rp <?= $nominalFormat ?></td>
        <td class="text-start"><small><?= htmlspecialchars($r['ket_bonus'] ?: '-') ?></small></td>
        <td>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary btnEditBonus"
                    data-id="<?= $r['id_bonus'] ?>"
                    data-karyawan="<?= $r['id_karyawan_internal'] ?>"
                    data-tgl="<?= $tglEdit ?>"
                    data-nominal="<?= $r['nominal'] ?>"
                    data-ket="<?= htmlspecialchars($r['ket_bonus'] ?? '') ?>">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn btn-outline-danger btnDeleteBonus" data-id="<?= $r['id_bonus'] ?>">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
<?php } ?>