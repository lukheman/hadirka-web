<?php
require '../../config/koneksi.php';
header('Content-Type: text/html; charset=utf-8');
$alamat_foto = url() . 'assets/img/user-pic/1764730880_a0e8fed89f33.JPG';

$q = $koneksi->query("SELECT * FROM tb_user ORDER BY nama_user ASC");
$no = 1;
$html = '';
while ($row = $q->fetch_assoc()) {
    $qrSq = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM tb_site WHERE id_site ='{$row['site']}'"));

    $id = (int)$row['id_user'];
    $username = htmlspecialchars($row['username']);
    $nama = htmlspecialchars($row['nama_user']);
    $jabatan = htmlspecialchars($row['jabatan']);
    $site = (htmlspecialchars($row['site']) == 0) ? 0 : $qrSq['nama_site'];
    $level = (htmlspecialchars($row['level']) == 1 ? 'Admin' : 'Karyawan');
    $foto = $row['foto'];
    $foto_url = ($foto && file_exists($alamat_foto . $foto)) ? $alamat_foto . $foto : 'assets/img/logo/tjs-nobg.png';
    $foto_url_esc = htmlspecialchars($foto_url);

    // data attributes untuk edit
    $data_attrs = "data-id=\"{$id}\" data-username=\"{$username}\" data-nama=\"{$nama}\" data-jabatan=\"{$jabatan}\" data-site=\"{$row['site']}\" data-level=\"{$row['level']}\" data-foto-url=\"{$foto_url_esc}\"";

    $html .= "<tr>";
    $html .= "<td class=\"text-center\">{$no}</td>";
    $html .= "<td><img src=\"{$foto_url_esc}\" width=\"40\" class=\"rounded-circle\"></td>";
    $html .= "<td>{$username}</td>";
    $html .= "<td>{$nama}</td>";
    $html .= "<td>{$jabatan}</td>";
    $html .= "<td>{$site}</td>";
    $html .= "<td>{$level}</td>";
    $html .= "<td class=\"text-center\">";
    $html .= "<button class=\"btn btn-warning btn-sm btnEditUser\" {$data_attrs}>Edit</button> ";
    $html .= "<button class=\"btn btn-danger btn-sm btnDeleteUser\" data-id=\"{$id}\">Hapus</button>";
    $html .= "</td>";
    $html .= "</tr>";

    $no++;
}

echo $html;
