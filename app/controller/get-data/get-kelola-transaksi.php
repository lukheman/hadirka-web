<?php
// app/controller/get-data/get-kelola-transaksi.php
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/koneksi.php';

// Cek apakah ada parameter filter periode
$id_priode = isset($_GET['id_priode']) && $_GET['id_priode'] !== 'semua' ? mysqli_real_escape_string($koneksi, $_GET['id_priode']) : '';

$whereClause = "";
if (!empty($id_priode)) {
    // Cari tanggal mulai dan selesai dari tabel tb_priode
    $qPriode = mysqli_query($koneksi, "SELECT tgl_mulai, tgl_selesai FROM tb_priode WHERE id_priode = '$id_priode'");
    if (mysqli_num_rows($qPriode) > 0) {
        $dataPriode = mysqli_fetch_assoc($qPriode);
        $startDate = $dataPriode['tgl_mulai'];
        $endDate = $dataPriode['tgl_selesai'];
        $whereClause = "WHERE t.tanggal_transaksi BETWEEN '$startDate' AND '$endDate'";
    }
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

$query = mysqli_query($koneksi, $sql);

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $kelompok = $row['kelompok'];

        if (isset($data[$kelompok])) {
            $data[$kelompok][] = [
                'id_transaksi'    => $row['id_transaksi'],
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
