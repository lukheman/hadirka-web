<?php
// Fungsi untuk mendapatkan data jabatan dari database
function data_jabatan() {
    // Koneksi ke database
    global $koneksi;
    // Query untuk mendapatkan data jabatan
    $sql = "SELECT id_jabatan, nama_jabatan FROM tb_jabatan order by nama_jabatan ASC";
    $result = $koneksi->query($sql);
    return $result;
}

function data_devisi() {
    // Koneksi ke database
    global $koneksi;
    // Query untuk mendapatkan data devisi
    $sql = "SELECT id_devisi, nama_devisi FROM tb_devisi order by nama_devisi ASC";
    $result = $koneksi->query($sql);
    return $result;
}

function data_departement() {
    // Koneksi ke database
    global $koneksi;
    // Query untuk mendapatkan data departement
    $sql = "SELECT id_departemen, nama_departemen FROM tb_departemen order by nama_departemen ASC";
    $result = $koneksi->query($sql);
    return $result;
}

function data_site() {
    // Koneksi ke database
    global $koneksi;
    // Query untuk mendapatkan data site
    $sql = "SELECT id_site, nama_site FROM tb_site";
    $result = $koneksi->query($sql);
    return $result;
}

function data_karyawan($order) {
    global $koneksi;
    // $order hanya diisi nama kolom internal (nama_lengkap), bukan input user.
    $allowed = ['nama_lengkap', 'nik_karyawan', 'id_karyawan'];
    if (!in_array($order, $allowed, true)) $order = 'nama_lengkap';
    $sql = "SELECT id_karyawan, nama_lengkap, nik_karyawan FROM tb_karyawan ORDER BY $order ASC";
    $result = $koneksi->query($sql);
    return $result;
}
?>
