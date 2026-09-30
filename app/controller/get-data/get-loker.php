<?php
require __DIR__.'/../../config/koneksi.php';

$q = $koneksi->query("
    SELECT l.*, j.nama_jabatan 
    FROM tb_loker l 
    JOIN tb_jabatan j ON l.id_jabatan = j.id_jabatan 
    ORDER BY l.id_loker DESC
");
$no = 1;

while ($r = $q->fetch_assoc()) {
    $badge = $r['status_loker'] == 'Aktif' ? 'bg-success' : 'bg-danger';
    echo "
    <tr>
        <td class='text-center'>{$no}</td>
        <td>{$r['nama_jabatan']}</td>
        <td class='text-center'><span class='badge {$badge}'>{$r['status_loker']}</span></td>
        <td class='text-center'>
            <button class='btn btn-warning btn-sm btnEditLoker' 
                data-id='{$r['id_loker']}' 
                data-jabatan='{$r['id_jabatan']}' 
                data-status='{$r['status_loker']}'>
                <i class='fa fa-edit'></i> Edit
            </button>
            <button class='btn btn-danger btn-sm btnDeleteLoker' data-id='{$r['id_loker']}'>
                <i class='fa fa-trash'></i> Hapus
            </button>
        </td>
    </tr>";
    $no++;
}
?>