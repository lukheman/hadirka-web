<?php
require '../../config/koneksi.php';

$query = "SELECT p.id_pinjaman, k.id_karyawan, k.nik_karyawan, k.nama_lengkap, p.nominal_pinjaman, p.tgl_pinjaman, p.tgl_bayar_pinjaman, p.ket_pinjaman
          FROM tb_pinjaman p
          JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
          ORDER BY p.tgl_pinjaman DESC";
$result = mysqli_query($koneksi, $query);
$no = 1;

while($row = mysqli_fetch_assoc($result)){
    echo "<tr>";
    echo "<td class='text-center'>{$no}</td>";
    echo "<td>{$row['nik_karyawan']}</td>";
    echo "<td>{$row['nama_lengkap']}</td>";
    echo "<td>{$row['tgl_pinjaman']}</td>";
    echo "<td>{$row['tgl_bayar_pinjaman']}</td>";
    echo "<td>".number_format($row['nominal_pinjaman'],0,",",".")."</td>";
    echo "<td>{$row['ket_pinjaman']}</td>";
    echo "<td class='text-center'>
            <button class='btn btn-primary btn-sm btnEdit'
                data-id='{$row['id_pinjaman']}'
                data-idkaryawan='{$row['id_karyawan']}'
                data-tglpinjaman='{$row['tgl_pinjaman']}'
                data-tglbayar='{$row['tgl_bayar_pinjaman']}'
                data-pinjaman='{$row['nominal_pinjaman']}'
                data-ket='{$row['ket_pinjaman']}'>
                <i class='fa fa-edit'></i>
            </button>
            <button class='btn btn-danger btn-sm btnDelete' data-id='{$row['id_pinjaman']}'>
                <i class='fa fa-trash'></i>
            </button>
          </td>";
    echo "</tr>";
    $no++;
}
