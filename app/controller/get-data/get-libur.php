<?php
// get-libur.php
require '../../config/koneksi.php'; // sesuaikan path jika perlu

$q = $koneksi->query("SELECT * FROM tb_libur ORDER BY tgl_awal_libur ASC");
$no = 1;
$html = "";

while ($r = $q->fetch_assoc()) {
    $id = $r['id_libur'];
    $awal = $r['tgl_awal_libur'];
    $akhir = $r['tgl_akhir_libur'];
    $ket = htmlspecialchars($r['ket_libur'], ENT_QUOTES);

    $html .= "<tr>";
    $html .= "<td class='text-center'>{$no}</td>";
    $html .= "<td>{$awal}</td>";
    $html .= "<td>{$akhir}</td>";
    $html .= "<td>{$ket}</td>";
    $html .= "<td class='text-center'>";
    $html .= "<button class='btn btn-sm btn-warning btnEditLibur me-1' data-id='{$id}' data-awal='{$awal}' data-akhir='{$akhir}' data-ket='{$ket}'>Edit</button>";
    $html .= "<button class='btn btn-sm btn-danger btnDeleteLibur' data-id='{$id}'>Hapus</button>";
    $html .= "</td>";
    $html .= "</tr>";

    $no++;
}

echo $html;
