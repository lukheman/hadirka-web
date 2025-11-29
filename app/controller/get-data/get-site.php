<?php
require_once __DIR__ . '/../../config/koneksi.php';


$q = mysqli_query($koneksi, "SELECT * FROM tb_site ORDER BY id_site DESC");
$no = 1;

while ($r = mysqli_fetch_assoc($q)) {
    echo "
    <tr>
        <td class='text-center'>{$no}</td>
        <td>{$r['nama_site']}</td>
        <td>{$r['lokasi']}</td>
        <td class='text-center'>
            <button class='btn btn-sm btn-primary btnEdit'
                data-id='{$r['id_site']}'
                data-nama='{$r['nama_site']}'
                data-lokasi='{$r['lokasi']}'>Edit</button>

            <button class='btn btn-sm btn-danger btnDelete'
                data-id='{$r['id_site']}'>Delete</button>
        </td>
    </tr>";
    $no++;
}
