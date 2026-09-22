<?php
require __DIR__ . '/../../config/koneksi.php';
require '../../config/function.php';

$id_karier = decrypt($_GET['id']);

$q = $koneksi->query("SELECT * FROM tb_tunjangan WHERE id_karier='$id_karier' ORDER BY id_tunjangan DESC");

$no = 1;
while ($r = $q->fetch_assoc()) {
    $nama_esc = htmlspecialchars($r['nama_tunjangan'], ENT_QUOTES);
    $jenis = $r['jenis_tunjangan'] ?? 'tetap';
    $badge = $jenis === 'tidak_tetap'
        ? "<span class='badge bg-warning text-dark'>Tidak Tetap</span>"
        : "<span class='badge bg-success'>Tetap</span>";
    echo "
    <tr>
        <td class='text-center'>$no</td>
        <td>{$nama_esc}</td>
        <td class='text-center'>{$badge}</td>
        <td class='text-end'>".number_format($r['jumlah_tunjangan'])."</td>
        <td class='text-center'>
            <button class='btn btn-warning btn-sm btnEditTunjangan'
                data-id='{$r['id_tunjangan']}'
                data-nama='{$nama_esc}'
                data-jenis='{$jenis}'
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
