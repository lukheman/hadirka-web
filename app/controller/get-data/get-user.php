<?php
require '../../config/koneksi.php';
header('Content-Type: text/html; charset=utf-8');

// Variabel Base URL dari fungsi Anda (Pastikan url() mengembalikan contoh: http://localhost/tjs-outsoorsing/)
$base_url = url();

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

    $lvl_angka = (int)$row['level'];
    if ($lvl_angka === 0) { $level = 'Admin'; }
    elseif ($lvl_angka === 1) { $level = 'Direktur'; }
    elseif ($lvl_angka === 2) { $level = 'HRD'; }
    elseif ($lvl_angka === 3) { $level = 'Supervisor'; }
    elseif ($lvl_angka === 4) { $level = 'Safety'; }
    else { $level = 'User Biasa'; }

    $foto = $row['foto'];
    
    // LOGIKA FOTO YANG LEBIH AMAN:
    // Jika $foto ada nilainya di database, susun URL lengkapnya.
    // Jika kosong, langsung pakai URL logo default.
    if (!empty($foto)) {
        $foto_url = $base_url . 'assets/img/user-pic/' . $foto;
    } else {
        $foto_url = $base_url . 'assets/img/logo/tjs-nobg.png';
    }
    
    $foto_url_esc = htmlspecialchars($foto_url);
    $default_logo_esc = htmlspecialchars($base_url . 'assets/img/logo/tjs-nobg.png');

    // data attributes untuk modal edit (Agar saat edit, preview fotonya juga muncul)
    $data_attrs = "data-id=\"{$id}\" data-username=\"{$username}\" data-nama=\"{$nama}\" data-jabatan=\"{$jabatan}\" data-site=\"{$row['site']}\" data-level=\"{$row['level']}\" data-foto-url=\"{$foto_url_esc}\"";

    $html .= "<tr>";
    $html .= "<td class=\"text-center\">{$no}</td>";
    
    // Trik onerror: Jika foto asli hilang di folder (tapi namanya ada di database), otomatis ganti ke logo TJS
    $html .= "<td><img src=\"{$foto_url_esc}\" onerror=\"this.onerror=null;this.src='{$default_logo_esc}';\" width=\"40\" class=\"rounded-circle\" style=\"object-fit: cover; aspect-ratio: 1/1;\"></td>";

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
?>