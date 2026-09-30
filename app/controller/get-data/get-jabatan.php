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
            <div class='d-flex justify-content-center gap-1'>
                <button type='button' class='btn btn-sm btn-warning btnEditJabatan'
                    data-id='{$r['id_jabatan']}'
                    data-kode='{$kode}'
                    data-nama='{$nama}'
                    title='Edit jabatan'>
                    <i class='bx bx-edit me-1'></i>Edit
                </button>

                <button type='button' class='btn btn-sm btn-danger btnDeleteJabatan'
                    data-id='{$r['id_jabatan']}'
                    title='Hapus jabatan'>
                    <i class='bx bx-trash me-1'></i>Hapus
                </button>
            </div>
        </td>
    </tr>";
    $no++;
}
