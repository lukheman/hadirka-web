<?php
// app/controller/get-data/get-kelola-transaksi.php
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/koneksi.php';

// Cek apakah ada parameter filter periode
$raw_priode = isset($_GET['id_priode']) ? (string)$_GET['id_priode'] : '';
$id_priode = ($raw_priode !== 'semua' && preg_match('/^\d{4}-\d{2}$/', $raw_priode) === 1) ? $raw_priode : '';

$whereClause = "";
$params = [];
if (!empty($id_priode)) {
    $startDate = $id_priode . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));

    // Sesuaikan prefix tabel (t.)
    $whereClause = "WHERE t.tanggal_transaksi BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

// Siapkan wadah per kelompok
$data = [
    'Aset' => [],
    'Kewajiban' => [],
    'Ekuitas' => [],
    'Pendapatan' => [],
    'Beban' => []
];

$sql = "
    SELECT t.*, k.nama_akun, k.kode_akun, k.kelompok 
    FROM tb_transaksi_keuangan t 
    JOIN tb_kategori_keuangan k ON t.id_kategori = k.id_kategori 
    $whereClause
    ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC
";

$stKelola = $koneksi->prepare($sql);
$stKelola->execute($params);
$query = $stKelola;

if ($query) {
    while ($row = $query->fetch()) {
        $kelompok = $row['kelompok'];

        if (isset($data[$kelompok])) {
            $data[$kelompok][] = [
                'id_transaksi'    => $row['id_transaksi'],
                'kode_referensi'  => $row['kode_referensi'] ? $row['kode_referensi'] : '-',
                'id_kategori'     => $row['id_kategori'],
                'tanggal'         => date('d M Y', strtotime($row['tanggal_transaksi'])),
                'tanggal_raw'     => $row['tanggal_transaksi'],
                'kode_akun'       => $row['kode_akun'],
                'nama_akun'       => $row['nama_akun'],
                'keterangan'      => htmlspecialchars($row['keterangan']),
                'tipe_arus'       => $row['tipe_arus'],
                'tipe_neraca'     => $row['tipe_neraca'], // <-- Tambahkan baris ini
                'nominal'         => number_format($row['nominal'], 0, ',', '.'),
                'nominal_raw'     => (int)$row['nominal'],
                'bukti_transaksi' => $row['bukti_transaksi']
            ];
        }
    }
}

echo json_encode(['status' => 'success', 'data' => $data]);
exit;
