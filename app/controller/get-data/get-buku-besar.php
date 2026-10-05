<?php
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/koneksi.php';

$id_kategori = isset($_GET['id_kategori']) ? $_GET['id_kategori'] : '';
$raw_priode  = isset($_GET['id_priode']) ? (string)$_GET['id_priode'] : '';
$id_priode   = ($raw_priode !== 'semua' && preg_match('/^\d{4}-\d{2}$/', $raw_priode) === 1) ? $raw_priode : '';

if (empty($id_kategori)) {
    echo json_encode(['status' => 'error', 'msg' => 'ID Akun wajib diisi']);
    exit;
}

// Cari metadata kelompok akun terlebih dahulu
$stCOA = $koneksi->prepare("SELECT kelompok FROM tb_kategori_keuangan WHERE id_kategori = ?");
$stCOA->execute([$id_kategori]);
$kelompok = $stCOA->fetch()['kelompok'] ?? 'Aset';

$where = "WHERE t.id_kategori = ?";
$params = [$id_kategori];
if (!empty($id_priode)) {
    $startDate = $id_priode . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));

    // Sesuaikan prefix tabel (t.)
    $where .= " AND t.tanggal_transaksi BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

$sql = "SELECT * FROM tb_transaksi_keuangan t $where ORDER BY t.tanggal_transaksi ASC, t.id_transaksi ASC";
$stBuku = $koneksi->prepare($sql);
$stBuku->execute($params);
$query = $stBuku;
$data = [];

while ($row = $query->fetch()) {
    $data[] = [
        'tanggal'     => date('d/m/Y', strtotime($row['tanggal_transaksi'])),
        'keterangan'  => htmlspecialchars($row['keterangan']),
        'tipe_neraca' => $row['tipe_neraca'],
        'nominal_raw' => (int)$row['nominal']
    ];
}

echo json_encode(['status' => 'success', 'kelompok' => $kelompok, 'data' => $data]);
exit;