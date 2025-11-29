<?php
error_reporting(0);
ini_set('display_errors', 0);

require '../../config/koneksi.php';

$q = $koneksi->query("SELECT * FROM tb_departemen ORDER BY id_departemen DESC");
$no = 1;

while ($r = $q->fetch_assoc()) {
    echo "
    <tr>
        <td class='text-center'>{$no}</td>
        <td>{$r['nama_departemen']}</td>
        <td class='text-center'>
            <button class='btn btn-primary btn-sm btnEditDepart'
                data-id='{$r['id_departemen']}'
                data-nama='{$r['nama_departemen']}'>
                Edit
            </button>

            <button class='btn btn-danger btn-sm btnDeleteDepart'
                data-id='{$r['id_departemen']}'>
                Hapus
            </button>
        </td>
    </tr>";
    $no++;
}
