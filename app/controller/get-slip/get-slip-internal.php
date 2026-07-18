<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/koneksi.php';

$nik = $koneksi->real_escape_string($_POST['nik'] ?? '');
$tgl_awal = $koneksi->real_escape_string($_POST['tgl_awal'] ?? '');
$tgl_akhir = $koneksi->real_escape_string($_POST['tgl_akhir'] ?? '');

if(empty($nik) || empty($tgl_awal) || empty($tgl_akhir)) { 
    echo json_encode(['status'=>'error', 'msg'=>'Parameter tanggal atau karyawan tidak lengkap']); 
    exit; 
}

$q = $koneksi->query("SELECT id_karyawan_internal, nik_karyawan_internal, nama_karyawan, jabatan, gaji_pokok FROM tb_karyawan_internal WHERE nik_karyawan_internal='$nik'");
if($q->num_rows == 0) { echo json_encode(['status'=>'error', 'msg'=>'Karyawan tidak ditemukan']); exit; }

$karyawan = $q->fetch_assoc();
$gapok = floatval($karyawan['gaji_pokok']);
$id_kar = $karyawan['id_karyawan_internal'];

// 1. Get Bonus
$bonusList = []; $totBonus = 0;
$qb = $koneksi->query("SELECT nominal, ket_bonus as ket, DATE_FORMAT(tgl_bonus, '%d-%m-%Y') as tgl FROM tb_bonus_internal WHERE id_karyawan_internal=$id_kar AND tgl_bonus BETWEEN '$tgl_awal' AND '$tgl_akhir'");
while($b = $qb->fetch_assoc()) {
    $bonusList[] = $b;
    $totBonus += floatval($b['nominal']);
}

// 2. Get Rapel
$rapelList = []; $totRapel = 0;
$qr = $koneksi->query("SELECT nominal, ket_rapel as ket, DATE_FORMAT(tgl_rapel, '%d-%m-%Y') as tgl FROM tb_rapel_internal WHERE id_karyawan_internal=$id_kar AND tgl_rapel BETWEEN '$tgl_awal' AND '$tgl_akhir'");
while($r = $qr->fetch_assoc()) {
    $rapelList[] = $r;
    $totRapel += floatval($r['nominal']);
}

// 3. Get Potongan Internal
$potonganList = []; $totPotongan = 0;
$qp = $koneksi->query("SELECT nominal, ket_potongan as ket, DATE_FORMAT(tgl_potongan, '%d-%m-%Y') as tgl FROM tb_potongan_internal WHERE id_karyawan_internal=$id_kar AND tgl_potongan BETWEEN '$tgl_awal' AND '$tgl_akhir'");
while($p = $qp->fetch_assoc()) {
    $potonganList[] = $p;
    $totPotongan += floatval($p['nominal']);
}

// 4. Get Pinjaman Internal
$pinjamanList = []; $totPinjaman = 0;
$qpj = $koneksi->query("SELECT nominal_pinjaman as nominal, ket_pinjaman as ket, DATE_FORMAT(tgl_bayar_pinjaman, '%d-%m-%Y') as tgl FROM tb_pinjaman_internal WHERE id_karyawan_internal=$id_kar AND tgl_bayar_pinjaman BETWEEN '$tgl_awal' AND '$tgl_akhir'");
while($pj = $qpj->fetch_assoc()) {
    $pinjamanList[] = $pj;
    $totPinjaman += floatval($pj['nominal']);
}

// Kalkulasi
$total_pendapatan = $gapok + $totBonus + $totRapel;
$total_potongan = $totPotongan + $totPinjaman;
$gaji_bersih = $total_pendapatan - $total_potongan;

echo json_encode([
    'status' => 'success',
    'data' => [
        'nik' => $karyawan['nik_karyawan_internal'],
        'nama' => $karyawan['nama_karyawan'],
        'jabatan' => $karyawan['jabatan'],
        'gapok' => $gapok,
        'bonus' => $bonusList,
        'rapel' => $rapelList,
        'potongan' => $potonganList,
        'pinjaman' => $pinjamanList,
        'total_pendapatan' => $total_pendapatan,
        'total_potongan_semua' => $total_potongan,
        'gaji_bersih' => $gaji_bersih
    ]
]);