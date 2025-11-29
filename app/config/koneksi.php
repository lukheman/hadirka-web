<?php
// $koneksi = mysqli_connect('localhost', 'root', '', 'db_tjs');
$koneksi = mysqli_connect('localhost', 'trie8884_userrian', 'Raufjum1505','trie8884_tjs_db');
if (mysqli_connect_errno()) {
    echo 'Koneksi database gagal : ' . mysqli_connect_error();
}

function url() {
    // return 'http://localhost/tjs/tjs-outsoorsing/';
    // return 'http://192.168.1.35/tjs-p-outsoorsing/';
    return 'https://slip-gaji.triputrajayasultra.com/';
}
?>