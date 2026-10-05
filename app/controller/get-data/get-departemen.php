<?php
error_reporting(0);
ini_set('display_errors', 0);

require '../../config/koneksi.php';

$q = $koneksi->query("SELECT * FROM tb_departemen ORDER BY id_departemen DESC");
$no = 1;

while ($r = $q->fetch()) {
    echo "
    <tr>
        <td class='text-center'>{$no}</td>
        <td>{$r['nama_departemen']}</td>
        <td class='text-center'>
            <div class='d-flex justify-content-center gap-1'>
                <button type='button' class='btn btn-sm btn-warning btnEditDepart'
                    data-id='{$r['id_departemen']}'
                    data-nama='{$r['nama_departemen']}'
                    title='Edit departemen'>
                    <i class='bx bx-edit me-1'></i>Edit
                </button>

                <button type='button' class='btn btn-sm btn-danger btnDeleteDepart'
                    data-id='{$r['id_departemen']}'
                    title='Hapus departemen'>
                    <i class='bx bx-trash me-1'></i>Hapus
                </button>
            </div>
        </td>
    </tr>";
    $no++;
}
