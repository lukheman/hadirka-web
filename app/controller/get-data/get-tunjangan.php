<?php
require __DIR__ . '/../../config/koneksi.php';
require '../../config/function.php';

$id_karier = decrypt($_GET['id']);

$stTunj = $koneksi->prepare("SELECT * FROM tb_tunjangan WHERE id_karier = ? ORDER BY id_tunjangan DESC");
$stTunj->execute([$id_karier]);
$q = $stTunj;

$no = 1;
while ($r = $q->fetch()) {
    $nama_esc = htmlspecialchars($r['nama_tunjangan'], ENT_QUOTES);
    $jenis = $r['jenis_tunjangan'] ?? 'tetap';
    $badge = $jenis === 'tidak_tetap'
        ? "<span class='badge bg-warning text-dark'>Tidak Tetap</span>"
        : "<span class='badge bg-success'>Tetap</span>";
    $tampil = (string)($r['tampil_template'] ?? '1') === '0' ? '0' : '1';
    $badge_tampil = $tampil === '0'
        ? "<span class='badge bg-secondary'>Tidak</span>"
        : "<span class='badge bg-info text-dark'>Ya</span>";
    $k_makan = strtolower(trim((string)$r['nama_tunjangan']));
    $suffix_hari = in_array($k_makan, ['tunjangan makan', 'tunjangan uang makan'], true) ? '/hari' : '';
    echo "
    <tr>
        <td class='text-center'>$no</td>
        <td>{$nama_esc}</td>
        <td class='text-center'>{$badge}</td>
        <td class='text-end'>".number_format($r['jumlah_tunjangan']).$suffix_hari."</td>
        <td class='text-center'>{$badge_tampil}</td>
        <td class='text-center'>
            <button class='btn btn-warning btn-sm btnEditTunjangan'
                data-id='{$r['id_tunjangan']}'
                data-nama='{$nama_esc}'
                data-jenis='{$jenis}'
                data-tampil='{$tampil}'
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
