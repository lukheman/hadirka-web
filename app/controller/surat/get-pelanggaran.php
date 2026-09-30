<?php
require_once __DIR__ . '/../../config/koneksi.php';

$q = $koneksi->query("SELECT * FROM m_jenis_pelanggaran ORDER BY id_pelanggaran DESC");
$no = 1;
while ($r = $q->fetch_assoc()) {
    $nama = htmlspecialchars($r['nama_pelanggaran']);
    
    // Potong untuk tampilan tabel, biarkan full untuk passing ke modal edit
    $ket_singkat = htmlspecialchars(substr($r['keterangan_default'], 0, 50));
    $ket_full = htmlspecialchars($r['keterangan_default']);
    
    echo "<tr>
        <td class='text-center'>{$no}</td>
        <td>{$nama}</td>
        <td><small>{$ket_singkat}...</small></td>
        <td class='text-center'>
            <button class='btn btn-warning btn-sm btnEditPel' 
                data-id='{$r['id_pelanggaran']}'
                data-nama='{$nama}'
                data-ket='{$ket_full}' title='Edit'><i class='fa fa-edit'></i></button>
            <button class='btn btn-danger btn-sm btnDeletePel' data-id='{$r['id_pelanggaran']}' title='Hapus'><i class='fa fa-trash'></i></button>
        </td>
    </tr>";
    $no++;
}