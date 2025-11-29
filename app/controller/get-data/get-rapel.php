<?php
require '../../config/koneksi.php';
require '../../config/function.php';

$query = "SELECT r.id_rapel, r.id_karyawan, k.nama_lengkap, r.tgl_rapel, r.nominal_rapel, r.ket_rapel
          FROM tb_rapel r
          JOIN tb_karyawan k ON r.id_karyawan = k.id_karyawan
          ORDER BY r.tgl_rapel DESC";

$result = mysqli_query($koneksi, $query);
$no = 1;

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td class='text-center'>{$no}</td>";
    echo "<td>{$row['nama_lengkap']}</td>";
    echo "<td>{$row['tgl_rapel']}</td>";
    echo "<td>" . number_format($row['nominal_rapel'], 0, ",", ".") . "</td>";
    echo "<td>{$row['ket_rapel']}</td>";
    echo "<td class='text-center'>
    <button class='btn btn-primary btn-sm btnEdit'
                data-id='{$row['id_rapel']}'
                data-idkaryawan='{$row['id_karyawan']}'
                data-tgl='{$row['tgl_rapel']}'
                data-nominal='{$row['nominal_rapel']}'
                data-ket='{$row['ket_rapel']}'>
                <i class='fa fa-edit'></i>
            </button>
            <button class='btn btn-danger btn-sm btnDelete' data-id='{$row['id_rapel']}'>
                <i class='fa fa-trash'></i>
            </button>
          </td>";
    echo "</tr>";
    $no++;
}
