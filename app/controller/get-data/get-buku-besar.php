<?php
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/koneksi.php';

$id_kategori = isset($_GET['id_kategori']) ? mysqli_real_escape_string($koneksi, $_GET['id_kategori']) : '';
$id_priode   = isset($_GET['id_priode']) && $_GET['id_priode'] !== 'semua' ? mysqli_real_escape_string($koneksi, $_GET['id_priode']) : '';

if (empty($id_kategori)) {
    echo json_encode(['status' => 'error', 'msg' => 'ID Akun wajib diisi']);
    exit;
}

// Cari metadata kelompok akun terlebih dahulu
$qCOA = mysqli_query($koneksi, "SELECT kelompok FROM tb_kategori_keuangan WHERE id_kategori = '$id_kategori'");
$kelompok = mysqli_fetch_assoc($qCOA)['kelompok'] ?? 'Aset';

$where = "WHERE t.id_kategori = '$id_kategori'";
if (!empty($id_priode)) {
    $qPriode = mysqli_query($koneksi, "SELECT tgl_mulai, tgl_selesai FROM tb_priode WHERE id_priode = '$id_priode'");
    if ($dP = mysqli_fetch_assoc($qPriode)) {
        $where .= " AND t.tanggal_transaksi BETWEEN '{$dP['tgl_mulai']}' AND '{$dP['tgl_selesai']}'";
    }
}

$sql = "SELECT * FROM tb_transaksi_keuangan t $where ORDER BY t.tanggal_transaksi ASC, t.id_transaksi ASC";
$query = mysqli_query($koneksi, $sql);
$data = [];

while ($row = mysqli_fetch_assoc($query)) {
    $data[] = [
        'tanggal'     => date('d/m/Y', strtotime($row['tanggal_transaksi'])),
        'keterangan'  => htmlspecialchars($row['keterangan']),
        'tipe_neraca' => $row['tipe_neraca'],
        'nominal_raw' => (int)$row['nominal']
    ];
}

echo json_encode(['status' => 'success', 'kelompok' => $kelompok, 'data' => $data]);
exit;