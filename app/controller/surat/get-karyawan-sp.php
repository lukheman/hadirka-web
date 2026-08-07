<?php
require_once __DIR__ . '/../../config/koneksi.php';

// Ambil Karyawan yang masih Aktif beserta info Karier
$q = $koneksi->query("SELECT kr.id_karyawan, kr.nik_karyawan, kr.nama_lengkap, j.nama_jabatan, s.nama_site 
    FROM tb_karyawan kr
    LEFT JOIN tb_karier k ON k.id_karyawan = kr.id_karyawan
    LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
    LEFT JOIN tb_site s ON k.id_site = s.id_site
    WHERE kr.status_karyawan = 'Aktif'
    GROUP BY kr.id_karyawan
    ORDER BY kr.nama_lengkap ASC");

while ($r = $q->fetch_assoc()) {
    echo "<tr>
        <td class='text-center'><input type='checkbox' class='chk-sp' value='{$r['id_karyawan']}'></td>
        <td>{$r['nik_karyawan']}</td>
        <td>{$r['nama_lengkap']}</td>
        <td>" . ($r['nama_jabatan'] ?? '-') . "</td>
        <td>" . ($r['nama_site'] ?? '-') . "</td>
    </tr>";
}