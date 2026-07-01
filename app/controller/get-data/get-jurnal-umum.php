<?php
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/koneksi.php';

$id_priode = isset($_GET['id_priode']) && $_GET['id_priode'] !== 'semua' ? mysqli_real_escape_string($koneksi, $_GET['id_priode']) : '';
$where = "";

if (!empty($id_priode)) {
    $startDate = $id_priode . '-01'; 
    $endDate = date('Y-m-t', strtotime($startDate)); 
    
    // Sesuaikan prefix tabel (t.) sesuai file masing-masing
    $where = "WHERE t.tanggal_transaksi BETWEEN '$startDate' AND '$endDate'";
}

$sql = "SELECT t.*, k.nama_akun, k.kode_akun 
        FROM tb_transaksi_keuangan t 
        JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori 
        $where 
        ORDER BY t.tanggal_transaksi ASC, t.id_transaksi ASC";

$query = mysqli_query($koneksi, $sql);
$data = [];

while ($row = mysqli_fetch_assoc($query)) {
    $isDebit = ($row['tipe_neraca'] === 'debit');
    $data[] = [
        'tanggal' => date('d/m/Y', strtotime($row['tanggal_transaksi'])),
        'nama_akun' => $row['nama_akun'],
        'kode_akun' => $row['kode_akun'],
        'keterangan' => htmlspecialchars($row['keterangan']),
        'tipe_neraca' => $row['tipe_neraca'],
        'debit' => $isDebit ? 'Rp ' . number_format($row['nominal'], 0, ',', '.') : '-',
        'kredit' => !$isDebit ? 'Rp ' . number_format($row['nominal'], 0, ',', '.') : '-',
        'debit_raw' => $isDebit ? (int)$row['nominal'] : 0,
        'kredit_raw' => !$isDebit ? (int)$row['nominal'] : 0,
    ];
}

echo json_encode(['status' => 'success', 'data' => $data]);
exit;