<?php
// $koneksi = mysqli_connect('localhost', 'root', '', 'db_kpp');
$koneksi = mysqli_connect('localhost', 'karc1582_onehr', 'Onehrkpp1505', 'karc1582_onehr');
if (mysqli_connect_errno()) {
    echo 'Koneksi database gagal : ' . mysqli_connect_error();
}

function url() {
    // return 'http://localhost/payroll/';
    // return 'http://192.168.1.35/kpp-outsoorsing/';lllllllll
    return 'https://onehr.karyaputrapomalaa.com/';
}
?>