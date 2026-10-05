<?php
require '../../config/koneksi.php';
require '../../config/function.php';

// Pastikan ID didekripsi dengan benar
$id_karier = decrypt($_GET['id']);

// Gunakan ORDER BY agar data terbaru muncul di atas
$stPot = $koneksi->prepare("SELECT * FROM tb_potongan WHERE id_karier = ? ORDER BY id_potongan DESC");
$stPot->execute([$id_karier]);
$rowsPot = $stPot->fetchAll();

$no = 1;
if (count($rowsPot) > 0) {
    foreach ($rowsPot as $r) {
        $nama_esc = htmlspecialchars($r['jenis_potongan'], ENT_QUOTES);
        echo "
        <tr>
            <td class='text-center'>$no</td>
            <td>{$nama_esc}</td>
            <td class='text-end'>" . number_format($r['jumlah_potongan'], 0, ',', '.') . "</td>
            <td class='text-center'>
                <button class='btn btn-warning btn-sm btnEditPotongan'
                    data-id='{$r['id_potongan']}'
                    data-nama='{$nama_esc}'
                    data-jumlah='{$r['jumlah_potongan']}'>
                    <i class='fa fa-edit'></i>
                </button>
                <button class='btn btn-danger btn-sm btnDeletePotongan'
                    data-id='{$r['id_potongan']}'>
                    <i class='fa fa-trash'></i>
                </button>
            </td>
        </tr>
        ";
        $no++;
    }
} else {
    echo "
    <tr>
        <td class='text-center'>-</td>
        <td class='text-center'>Tidak ada data</td>
        <td class='text-center'>-</td>
        <td class='text-center'>-</td>
    </tr>
    ";
}
