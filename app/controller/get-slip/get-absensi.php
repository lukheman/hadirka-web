<?php
header('Content-Type: application/json');
require '../../config/koneksi.php';

$id_karyawan = $_GET['id_karyawan'] ?? '';
$tgl_a = $_GET['tgl_a'] ?? date('Y-m-01');
$tgl_b = $_GET['tgl_b'] ?? date('Y-m-d');

if (!$id_karyawan) {
    echo json_encode(['status' => 'error', 'message' => 'id_karyawan kosong']);
    exit;
}

// Contoh query: ambil absensi antara tgl_a dan tgl_b
// Pastikan tabel dan kolom sesuai. Contoh asumsi: tb_absensi (id_karyawan, tanggal, jam_kerja, ket)
$sql = "SELECT tanggal, jam_kerja, ket FROM tb_absensi
        WHERE id_karyawan = '" . mysqli_real_escape_string($conn, $id_karyawan) . "'
        AND tanggal BETWEEN '" . mysqli_real_escape_string($conn, $tgl_a) . "' AND '" . mysqli_real_escape_string($conn, $tgl_b) . "'
        ORDER BY tanggal ASC";

$res = mysqli_query($conn, $sql);
if (!$res) {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    // buat juga nama hari/format tanggal sesuai kebutuhan
    $hari = strftime('%A', strtotime($row['tanggal']));
    $data[] = [
        'tanggal' => date('d/m/Y', strtotime($row['tanggal'])),
        'hari' => strtoupper($hari),
        'jam_kerja' => $row['jam_kerja'] ?? null,
        'ket' => $row['ket'] ?? null
    ];
}

if (count($data) == 0) {
    echo json_encode(['status' => 'empty', 'message' => 'tidak ada absensi']);
    exit;
}

echo json_encode(['status' => 'success', 'data' => $data]);
