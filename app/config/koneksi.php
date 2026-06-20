<?php
date_default_timezone_set('Asia/Makassar');

// saya meng-uncentang intelephense undefined variables di vs code biar variabel $koneksi di file lain tidak merah
// $koneksi = mysqli_connect('localhost', 'root', '', 'db_tjs_os');
$koneksi = mysqli_connect('localhost', 'trie8884_fendi', 'TJSFendi2026', 'trie8884_tjs_db');
if (mysqli_connect_errno()) {
    echo 'Koneksi database gagal : ' . mysqli_connect_error();
}

function url() {
    // return 'http://localhost/tjs-outsoorsing/';
    return 'https://hris.triputrajayasultra.com/';
}
?>