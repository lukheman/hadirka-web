<?php
require '../../config/koneksi.php';

$q = $koneksi->query("SELECT d.id_divisi, d.nama_divisi, dp.nama_departemen, dp.id_departemen
    FROM tb_devisi d
    JOIN tb_departement dp ON dp.id_departemen = d.id_departemen
    ORDER BY d.id_divisi DESC
");

$no = 1;

while ($r = $q->fetch_assoc()) {
    echo "
    <tr>
        <td class='text-center'>{$no}</td>
        <td>{$r['nama_divisi']}</td>
        <td>{$r['nama_departemen']}</td>
        <td class='text-center'>
            <button class='btn btn-primary btn-sm btnEditDivisi'
                data-id='{$r['id_divisi']}'
                data-nama='{$r['nama_divisi']}'
                data-depart='{$r['id_departemen']}'>
                Edit
            </button>

            <button class='btn btn-danger btn-sm btnDeleteDivisi'
                data-id='{$r['id_divisi']}'>
                Hapus
            </button>
        </td>
    </tr>";
    $no++;
}
