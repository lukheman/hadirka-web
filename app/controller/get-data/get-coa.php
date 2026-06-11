<?php
require __DIR__.'/../../config/koneksi.php';
$q = $koneksi->query("SELECT * FROM tb_kategori_keuangan ORDER BY id_kategori DESC");
$no = 1;
while ($r = $q->fetch_assoc()) {
    echo "<tr>
        <td class='text-center'>{$no}</td>
        <td>{$r['kode_akun']}</td>
        <td>{$r['nama_akun']}</td>
        <td>{$r['kelompok']}</td>
        <td class='text-center'>
            <button class='btn btn-primary btn-sm btnEditCOA' 
                data-id='{$r['id_kategori']}' data-kode='{$r['kode_akun']}' 
                data-nama='{$r['nama_akun']}' data-kelompok='{$r['kelompok']}'>Edit</button>
            <button class='btn btn-danger btn-sm btnDeleteCOA' data-id='{$r['id_kategori']}'>Hapus</button>
        </td>
    </tr>";
    $no++;
}