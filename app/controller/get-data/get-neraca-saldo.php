<?php
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/koneksi.php';

$id_priode = isset($_GET['id_priode']) && $_GET['id_priode'] !== 'semua' ? mysqli_real_escape_string($koneksi, $_GET['id_priode']) : '';
$whereClause = "";

if (!empty($id_priode)) {
    $qPriode = mysqli_query($koneksi, "SELECT tgl_selesai FROM tb_priode WHERE id_priode = '$id_priode'");
    if ($dP = mysqli_fetch_assoc($qPriode)) {
        // Ambil data kumulatif dari awal waktu sampai batas akhir tanggal periode terpilih
        $whereClause = "AND t.tanggal_transaksi <= '{$dP['tgl_selesai']}'";
    }
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

$query = mysqli_query($koneksi, $sql);
$data = [];

while ($row = mysqli_fetch_assoc($query)) {
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