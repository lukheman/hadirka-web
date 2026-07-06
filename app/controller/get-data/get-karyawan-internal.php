<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

// SQL JOIN dengan tb_site untuk mendapatkan nama_site secara dinamis
$sql = "SELECT k.*, s.nama_site 
        FROM tb_karyawan_internal k 
        LEFT JOIN tb_site s ON k.id_site = s.id_site 
        ORDER BY k.nik_karyawan_internal ASC";
$q = $koneksi->query($sql);
$no = 1;

while ($r = $q->fetch_assoc()) {
    $gajiFormat = number_format($r['gaji_pokok'], 0, ',', '.');
    $siteDisplay = !empty($r['nama_site']) ? htmlspecialchars($r['nama_site']) : '<span class="badge bg-label-secondary">No Site</span>';
    $infoBank = !empty($r['nama_bank']) ? "<strong>{$r['nama_bank']}</strong> - <code>{$r['no_rek']}</code><br><small class='text-muted'>An. {$r['atas_nama_rek']}</small>" : '<span class="badge bg-label-warning">Belum Ada Rekening</span>';
?>
    <tr class="text-center">
        <td><?= $no++ ?></td>
        <td><code><?= htmlspecialchars($r['nik_karyawan_internal']) ?></code></td>
        <td class="text-start"><strong><?= htmlspecialchars($r['nama_karyawan']) ?></strong></td>
        <td><span class="badge bg-label-info"><?= $siteDisplay ?></span></td>
        <td><?= htmlspecialchars($r['jabatan']) ?></td>
        <td class="text-end fw-bold text-success">Rp <?= $gajiFormat ?></td>
        <td class="text-start"><?= $infoBank ?></td>
        <td>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-primary btnEditKaryawan"
                    data-id="<?= $r['id_karyawan_internal'] ?>"
                    data-nik="<?= $r['nik_karyawan_internal'] ?>"
                    data-site="<?= $r['id_site'] ?>"
                    data-nama="<?= $r['nama_karyawan'] ?>"
                    data-jabatan="<?= $r['jabatan'] ?>"
                    data-gaji="<?= $r['gaji_pokok'] ?>"
                    data-bank="<?= $r['nama_bank'] ?>"
                    data-rek="<?= $r['no_rek'] ?>"
                    data-atasnama="<?= $r['atas_nama_rek'] ?>">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn btn-danger btnDeleteKaryawan" data-id="<?= $r['id_karyawan_internal'] ?>">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
<?php } ?>