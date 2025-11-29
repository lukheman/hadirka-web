<?php
// $koneksi = mysqli_connect('localhost', 'root', '', 'db_tjs');
$koneksi = mysqli_connect('localhost', 'trie8884_RajumTJSControl1512', 'Raufjum1505','trie8884_tjs_outsoorsing');
if (mysqli_connect_errno()) {
    echo 'Koneksi database gagal : ' . mysqli_connect_error();
}

function url() {
    // return 'http://localhost/tjs/tjs-outsoorsing/';
    // return 'http://192.168.1.35/tjs-p-outsoorsing/';
    return 'https://hris.triputrajayasultra.com/';
}
?>