<?php

// Menggunakan variabel $koneksi sesuai permintaan
// $koneksi = mysqli_connect('localhost', 'root', '', 'database_outsoorsing');
$koneksi = mysqli_connect('localhost', 'trie8884_userrian', 'Raufjum1505', 'trie8884_tjs_db');

// Cek Koneksi
// if (mysqli_connect_errno()) {
//     // Jika koneksi gagal, kembalikan response JSON yang terstruktur
//     http_response_code(503); // Service Unavailable
//     header('Content-Type: application/json');
//     echo json_encode([
//         'status' => 'error', 
//         'message' => 'Koneksi database gagal: ' . mysqli_connect_error()
//     ]);
//     exit; // Hentikan eksekusi script
// }
date_default_timezone_set('Asia/Makassar'); 
?>