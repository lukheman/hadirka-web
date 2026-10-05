<?php
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/koneksi.php';

$raw_priode = isset($_GET['id_priode']) ? (string)$_GET['id_priode'] : '';
$id_priode = ($raw_priode !== 'semua' && preg_match('/^\d{4}-\d{2}$/', $raw_priode) === 1) ? $raw_priode : '';

$whereClause = "";
$params = [];

if (!empty($id_priode)) {
    $startDate = $id_priode . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));

    // Sesuaikan prefix tabel (t.)
    $whereClause = "AND t.tanggal_transaksi <= ?";
    $params[] = $endDate;
}

// Group & Sum mutasi Debit/Kredit per Akun menggunakan Conditional Aggregation
$sql = "
    SELECT 
        k.kode_akun, 
        k.nama_akun,
        k.kelompok,
        SUM(CASE WHEN t.tipe_neraca = 'debit' THEN t.nominal ELSE 0 END) as total_debit,
        SUM(CASE WHEN t.tipe_neraca = 'kredit' THEN t.nominal ELSE 0 END) as total_kredit
    FROM tb_kategori_keuangan k
    LEFT JOIN tb_transaksi_keuangan t ON k.id_kategori = t.id_kategori $whereClause
    GROUP BY k.id_kategori, k.kode_akun, k.nama_akun, k.kelompok
    ORDER BY k.kode_akun ASC
";

$stNeraca = $koneksi->prepare($sql);
$stNeraca->execute($params);
$query = $stNeraca;
$data = [];

while ($row = $query->fetch()) {
    $deb = (int)$row['total_debit'];
    $kre = (int)$row['total_kredit'];
    $finalDebit = 0;
    $finalKredit = 0;

    // Kalkulasi Bersih Sesuai Aturan Saldo Normal
    if ($row['kelompok'] == 'Aset' || $row['kelompok'] == 'Beban') {
        $saldo = $deb - $kre;
        if ($saldo >= 0) $finalDebit = $saldo;
        else $finalKredit = abs($saldo);
    } else {
        // Kewajiban, Ekuitas, Pendapatan
        $saldo = $kre - $deb;
        if ($saldo >= 0) $finalKredit = $saldo;
        else $finalDebit = abs($saldo);
    }

    $data[] = [
        'kode_akun' => $row['kode_akun'],
        'nama_akun' => $row['nama_akun'],
        'debit'     => $finalDebit,
        'kredit'    => $finalKredit
    ];
}

echo json_encode(['status' => 'success', 'data' => $data]);
exit;