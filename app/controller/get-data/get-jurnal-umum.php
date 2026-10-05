<?php
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/koneksi.php';

$raw_priode = isset($_GET['id_priode']) ? (string)$_GET['id_priode'] : '';
$id_priode = ($raw_priode !== 'semua' && preg_match('/^\d{4}-\d{2}$/', $raw_priode) === 1) ? $raw_priode : '';
$where = "";
$params = [];

if (!empty($id_priode)) {
    $startDate = $id_priode . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));

    // Sesuaikan prefix tabel (t.) sesuai file masing-masing
    $where = "WHERE t.tanggal_transaksi BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

$sql = "SELECT t.*, k.nama_akun, k.kode_akun 
        FROM tb_transaksi_keuangan t 
        JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori 
        $where 
        ORDER BY t.tanggal_transaksi ASC, t.id_transaksi ASC";

$stJurnal = $koneksi->prepare($sql);
$stJurnal->execute($params);
$query = $stJurnal;
$data = [];

while ($row = $query->fetch()) {
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