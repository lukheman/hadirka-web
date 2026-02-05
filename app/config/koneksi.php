<?php
// $koneksi = mysqli_connect('localhost', 'root', '', 'db_tjs-os');
$koneksi = mysqli_connect('localhost', 'trie8884_userrian', 'Raufjum1505', 'trie8884_tjs_db');
if (mysqli_connect_errno()) {
    echo 'Koneksi database gagal : ' . mysqli_connect_error();
}

function url() {
    // return 'http://localhost/tjs-outsoorsing/';
    return 'https://hris.triputrajayasultra.com/';
}
?>