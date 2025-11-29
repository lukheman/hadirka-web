<?php
require '../../config/koneksi.php';

$q = $koneksi->query("SELECT * FROM tb_jabatan ORDER BY id_jabatan DESC");
$no = 1;

while ($r = $q->fetch_assoc()) {
    echo "
    <tr>
        <td class='text-center'>{$no}</td>
        <td>{$r['nama_jabatan']}</td>
        <td class='text-center'>
            <button class='btn btn-primary btn-sm btnEditJabatan'
                data-id='{$r['id_jabatan']}'
                data-nama='{$r['nama_jabatan']}'>
                Edit
            </button>

            <button class='btn btn-danger btn-sm btnDeleteJabatan'
                data-id='{$r['id_jabatan']}'>
                Hapus
            </button>
        </td>
    </tr>";
    $no++;
}
