<?php
require '../../config/koneksi.php';
require '../../config/function.php';

// Pastikan ID didekripsi dengan benar
$id_karier = decrypt($_GET['id']);

// Gunakan ORDER BY agar data terbaru muncul di atas
$q = $koneksi->query("SELECT * FROM tb_potongan WHERE id_karier='$id_karier' ORDER BY id_potongan DESC");

$no = 1;
if ($q->num_rows > 0) {
    while ($r = $q->fetch_assoc()) {
        echo "
        <tr>
            <td class='text-center'>$no</td>
            <td>{$r['jenis_potongan']}</td>
            <td class='text-end'>".number_format($r['jumlah_potongan'], 0, ',', '.')."</td>
            <td class='text-center'>
                <button class='btn btn-warning btn-sm btnEditPotongan'
                    data-id='{$r['id_potongan']}'
                    data-nama='{$r['jenis_potongan']}'
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
    echo "<tr><td colspan='4' class='text-center'>Tidak ada data potongan.</td></tr>";
}
?>