<?php
require '../../config/koneksi.php';
require '../../config/function.php';

$id_karier = decrypt($_GET['id']);

$q = $koneksi->query("SELECT * FROM tb_tunjangan WHERE id_karier='$id_karier' ORDER BY id_tunjangan DESC");

$no = 1;
while ($r = $q->fetch_assoc()) {
    echo "
    <tr>
        <td class='text-center'>$no</td>
        <td>{$r['nama_tunjangan']}</td>
        <td class='text-end'>".number_format($r['jumlah_tunjangan'])."</td>
        <td class='text-center'>
            <button class='btn btn-warning btn-sm btnEditTunjangan'
                data-id='{$r['id_tunjangan']}'
                data-nama='{$r['nama_tunjangan']}'
                data-jumlah='{$r['jumlah_tunjangan']}'>
                <i class='fa fa-edit'></i>
            </button>
            <button class='btn btn-danger btn-sm btnDeleteTunjangan'
                data-id='{$r['id_tunjangan']}'>
                <i class='fa fa-trash'></i>
            </button>
        </td>
    </tr>
    ";
    $no++;
}
?>
