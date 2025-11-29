<?php
// $koneksi = mysqli_connect('localhost', 'root', '', 'db_kpp');
$koneksi = mysqli_connect('localhost', 'trie8884_trie8884', 'Triputrajayasultra1505', 'trie8884_tjs_outsoorsing');
if (mysqli_connect_errno()) {
    echo 'Koneksi database gagal : ' . mysqli_connect_error();
}

function url() {
    // return 'http://localhost/payroll/';
    // return 'http://192.168.1.35/tjs-p-outsoorsing/';
    return 'http://hris.triputrajayasultra.com/';
}
?>