<?php
// File: app/api/periods_list.php

// Panggil file koneksi database Anda (Asumsi $koneksi adalah objek/resource mysqli)
include 'koneksi-api.php'; 

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); 

// Fungsi untuk mengirim response JSON dan menghentikan eksekusi
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    exit;
}

global $koneksi;
if (!isset($koneksi) || $koneksi === false) {
    sendResponse(['error' => 'Koneksi database gagal.'], 500);
}

try {
    // Ambil data periode yang type_priode-nya 'absen'
    $sql = "SELECT 
            id_priode, 
            CONCAT(DATE_FORMAT(tgl_mulai, '%M %Y')) AS tb_priode,
            tgl_mulai, 
            tgl_selesai, 
            status_priode, 
            type_priode 
        FROM 
            tb_priode 
        WHERE 
            type_priode = 'absen' 
            and 
            status_priode = 'open'
        ORDER BY 
            tgl_mulai DESC
    ";
    
    $q = mysqli_query($koneksi, $sql);

    if (!$q) {
        sendResponse(['error' => 'Query database gagal: ' . mysqli_error($koneksi)], 500);
    }
    
    $periods = [];
    while ($r = mysqli_fetch_assoc($q)) {
        $periods[] = $r;
    }

    // Mengirimkan hasil sebagai array JSON
    sendResponse($periods);

} catch (Exception $e) {
    sendResponse(['error' => 'General Error: ' . $e->getMessage()], 500);
}

?>