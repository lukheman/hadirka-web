<?php
require __DIR__.'/../../config/koneksi.php';

$q = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY id_jabatan DESC");
$no = 1;

while ($r = $q->fetch_assoc()) {
    $kode = htmlspecialchars($r['kode_jabatan'] ?? '', ENT_QUOTES);
    $nama = htmlspecialchars($r['nama_jabatan'] ?? '', ENT_QUOTES);
    echo "
    <tr>
        <td class='text-center'>{$no}</td>
        <td>{$kode}</td>
        <td>{$nama}</td>
        <td class='text-center'>
            <button class='btn btn-primary btn-sm btnEditJabatan'
                data-id='{$r['id_jabatan']}'
                data-kode='{$kode}'
                data-nama='{$nama}'
                title='Edit jabatan'>
                <i class='fa fa-pen'></i>
            </button>

            <button class='btn btn-danger btn-sm btnDeleteJabatan'
                data-id='{$r['id_jabatan']}'
                title='Hapus jabatan'>
                <i class='fa fa-trash'></i>
            </button>
        </td>
    </tr>";
    $no++;
}
