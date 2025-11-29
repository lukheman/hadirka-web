<?php
header('Content-Type: application/json');
require '../../config/koneksi.php';

$total = $lunas = 0;

$sql = $koneksi->query("SELECT nominal_pinjaman, tgl_bayar_pinjaman FROM pinjaman");
while($row = $sql->fetch_assoc()){
    $total += $row['nominal_pinjaman'];
    if(!empty($row['tgl_bayar_pinjaman'])){
        $lunas += $row['nominal_pinjaman'];
    }
}

echo json_encode([
    'total' => $total,
    'lunas' => $lunas
]);
