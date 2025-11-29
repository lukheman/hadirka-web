<?php
// app/controller/get-data/get-slip/new-slip.php

header('Content-Type: application/json; charset=utf-8');

// Ambil JSON POST
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid JSON input']));
}

$nik = $input['nik'] ?? null;
$startDate = $input['startDate'] ?? null;
$endDate = $input['endDate'] ?? null;

if (!$nik || !$startDate || !$endDate) {
    http_response_code(400);
    die(json_encode(['error' => 'nik, startDate, endDate wajib']));
}

// ================= DB Connection mysqli =================
$koneksi = mysqli_connect('localhost', 'root', '', 'db_outsorsing');
if (mysqli_connect_errno()) {
    http_response_code(500);
    die(json_encode(['error' => 'Gagal koneksi DB']));
}
mysqli_set_charset($koneksi, "utf8");

// Helper overlap
function daysOverlap($a_start, $a_end, $b_start, $b_end)
{
    $s = max(strtotime($a_start), strtotime($b_start));
    $e = min(strtotime($a_end), strtotime($b_end));
    if ($e < $s) return 0;
    return floor(($e - $s) / 86400) + 1;
}

// ================= 1) Get Karyawan =================
$sql = "SELECT * FROM tb_karyawan WHERE nik_karyawan='$nik' LIMIT 1";
$q = mysqli_query($koneksi, $sql);
$k = mysqli_fetch_assoc($q);
if (!$k) die(json_encode(['error' => 'Karyawan tidak ditemukan']));
$id_karyawan = $k['id_karyawan'];

// ================= 2) Get Karier aktif =================
$sqlKarier = "SELECT * FROM tb_karier kar
JOIN tb_jabatan jab ON kar.id_jabatan = jab.id_jabatan
WHERE kar.id_karyawan = '$id_karyawan'
AND kar.tgl_mulai <= '$endDate'
AND (kar.tgl_selesai IS NULL OR kar.tgl_selesai >= '$startDate')
ORDER BY kar.tgl_mulai DESC LIMIT 1
";
$qk = mysqli_query($koneksi, $sqlKarier);
$karier = mysqli_fetch_assoc($qk);
if (!$karier) die(json_encode(['error' => 'Karier aktif tidak ditemukan']));

// ================= 2a) Ambil Tunjangan =================
$tunjanganRows = [];
$potonganRows = [];
$total_tunjangan = 0;
$total_potongan_tunjangan = 0;

$sqlTunj = "SELECT nama_tunjangan, jumlah_tunjangan 
            FROM tb_tunjangan 
            WHERE id_karier = '" . $karier['id_karier'] . "'";
$qt = mysqli_query($koneksi, $sqlTunj);
while ($tr = mysqli_fetch_assoc($qt)) {
    $nama = strtolower($tr['nama_tunjangan']);
    $row = [
        'nama' => $tr['nama_tunjangan'],
        'jumlah' => floatval($tr['jumlah_tunjangan'])
    ];
    if (strpos($nama, 'bpjs') !== false || strpos($nama, 'jht') !== false || strpos($nama, 'pph') !== false) {
        $total_potongan_tunjangan += floatval($tr['jumlah_tunjangan']);
        $potonganRows[] = $row;
    } else {
        $total_tunjangan += floatval($tr['jumlah_tunjangan']);
        $tunjanganRows[] = $row;
    }
}

// ================= 3) Get Pinjaman =================
$total_potongan_pinjaman = 0;
$pinjamanRows = [];
$sqlPinjaman = "SELECT nominal_pinjaman, ket_pinjaman FROM tb_pinjaman
    WHERE id_karyawan = '$id_karyawan'
    AND tgl_pinjaman BETWEEN '$startDate' AND '$endDate'
";
$qpin = mysqli_query($koneksi, $sqlPinjaman);
while ($pin = mysqli_fetch_assoc($qpin)) {
    $nominal = floatval($pin['nominal_pinjaman']);
    $total_potongan_pinjaman += $nominal;
    $pinjamanRows[] = [
        'nama' => $pin['ket_pinjaman'],
        'jumlah' => $nominal
    ];
}

// ================= 4) Get Absen =================
$absenRows = [];
$sqlAbs = "
SELECT id_absen, tgl_absen, jam_kerja, ket_absen
FROM tb_absen
WHERE id_karyawan='$id_karyawan'
AND tgl_absen BETWEEN '$startDate' AND '$endDate'
ORDER BY tgl_absen ASC
";
$qabs = mysqli_query($koneksi, $sqlAbs);
while ($r = mysqli_fetch_assoc($qabs)) $absenRows[] = $r;

// ================= 4a) Helper overlap days =================
function getOverlapDays($conn, $table, $id, $start, $end, $awal, $akhir)
{
    $days = 0;
    $sql = "SELECT $awal, $akhir FROM $table
            WHERE id_karyawan = '$id'
            AND (( $awal BETWEEN '$start' AND '$end')
            OR ( $akhir BETWEEN '$start' AND '$end')
            OR ($awal <= '$start' AND $akhir >= '$end'))";
    $q = mysqli_query($conn, $sql);
    while ($z = mysqli_fetch_assoc($q)) {
        $days += daysOverlap($z[$awal], $z[$akhir], $start, $end);
    }
    return $days;
}
$izin_days  = getOverlapDays($koneksi, "tb_izin",  $id_karyawan, $startDate, $endDate, "tgl_awal_izin", "tgl_akhir_izin");
$sakit_days = getOverlapDays($koneksi, "tb_sakit", $id_karyawan, $startDate, $endDate, "tgl_awal_sakit", "tgl_akhir_sakit");
$cuti_days  = getOverlapDays($koneksi, "tb_cuti",  $id_karyawan, $startDate, $endDate, "tgl_awal_cuti", "tgl_akhir_cuti");

// ================= 5) Perhitungan Absen =================
$absenForUI = [];
$hari_kerja_count = 0;
$mangkir_count = 0;
$total_lembur = 0;
$total_overtime = 0;
$total_spl_1_7 = 0;
$total_spl_8 = 0;
$total_spl_9_up = 0;

$period = new DatePeriod(
    new DateTime($startDate),
    new DateInterval('P1D'),
    (new DateTime($endDate))->modify('+1 day') // tambahkan 1 hari supaya inklusif
);

foreach ($period as $date) {
    $tgl = $date->format('Y-m-d');
    $found = false;

    // 1) Cek tb_absen
    foreach ($absenRows as $a) {
        if ($a['tgl_absen'] == $tgl) {
            $found = true;
            $jam = floatval($a['jam_kerja']);
            $ket = strtoupper($a['ket_absen']);
            $hariNama = date('l', strtotime($tgl));

            $isSpl = ($ket === 'OFF' && $jam > 0);
            if ($isSpl) $ket = 'SPL';

            if (!$isSpl && $ket !== 'OFF' && $jam > 0) $hari_kerja_count++;

            if ($isSpl) {
                $h1_7 = min(7, $jam);
                $h8 = $jam > 7 ? min(1, $jam - 7) : 0;
                $h9up = max(0, $jam - 8);

                $total_spl_1_7 += $h1_7 * floatval($karier['spl']);
                $total_spl_8 += $h8 * floatval($karier['ot_pertama']);
                $total_spl_9_up += $h9up * floatval($karier['ot_kedua']);
            } else {
                if (strtolower($hariNama) === 'friday') {
                    $wl = ($jam > 5 ? min(1, $jam - 5) : 0);
                    $ot = ($jam > 6 ? $jam - 6 : 0);
                } else {
                    $wl = ($jam > 7 ? min(1, $jam - 7) : 0);
                    $ot = ($jam > 8 ? $jam - 8 : 0);
                }
                $total_lembur += $wl * floatval($karier['ot_pertama']);
                $total_overtime += $ot * floatval($karier['ot_kedua']);
            }

            $absenForUI[] = [
                'tanggal' => $tgl,
                'jam' => $jam,
                'wl' => $wl ?? 0,
                'ot' => $ot ?? 0,
                'ket' => $ket
            ];
            break;
        }
    }

    if ($found) continue;

    // 2) Cek tb_izin, tb_sakit, tb_cuti
    $types = [
        ['table' => 'tb_izin',  'start' => 'tgl_awal_izin', 'end' => 'tgl_akhir_izin', 'ket' => 'IZIN'],
        ['table' => 'tb_sakit', 'start' => 'tgl_awal_sakit', 'end' => 'tgl_akhir_sakit', 'ket' => 'SAKIT'],
        ['table' => 'tb_cuti',  'start' => 'tgl_awal_cuti', 'end' => 'tgl_akhir_cuti', 'ket' => 'CUTI'],
    ];

    foreach ($types as $t) {
        $sql = "SELECT 1 FROM {$t['table']} WHERE id_karyawan='$id_karyawan' AND '$tgl' BETWEEN {$t['start']} AND {$t['end']} LIMIT 1";
        $q = mysqli_query($koneksi, $sql);
        if (mysqli_num_rows($q) > 0) {
            $found = true;
            $absenForUI[] = [
                'tanggal' => $tgl,
                'jam' => 0,
                'wl' => 0,
                'ot' => 0,
                'ket' => $t['ket']
            ];
            break;
        }
    }

    // 3) Jika tetap tidak ada, mangkir
    if (!$found) {
        $mangkir_count++;
        $absenForUI[] = [
            'tanggal' => $tgl,
            'jam' => 0,
            'wl' => 0,
            'ot' => 0,
            'ket' => 'MANGKIR'
        ];
    }
}


// Urutkan absen dan beri nomor
usort($absenForUI, fn($a, $b) => strtotime($a['tanggal']) - strtotime($b['tanggal']));
$rowNo = 1;
foreach ($absenForUI as &$r) $r['no'] = str_pad($rowNo++, 2, '0', STR_PAD_LEFT);
unset($r);


// ================= Nilai dari karier =================
$basic = floatval($karier['gaji_pokok']);
$t_hidup = floatval($karier['t_hidup']);
$t_daerah = floatval($karier['t_daerah']);
$t_makan = floatval($karier['t_makan']);
$t_skil = floatval($karier['t_skil']);

$bpjs_kesehatan = floatval($karier['bpjs_kesehatan'] ?? 0);
$jht = floatval($karier['jht'] ?? 0);
$pph21 = floatval($karier['pph21'] ?? 0);
$pot_sio = floatval($karier['pot_sio'] ?? 0);
$pot_pkwt = floatval($karier['pot_pkwt'] ?? 0);

$total_pot_izin = floatval($karier['pot_izin']) * $izin_days;
$total_pot_mangkir = floatval($karier['pot_mangkir']) * $mangkir_count;
$total_pot_perjam = floatval($karier['pot_perjam']) * $perjam_days;

$total_pendapatan =
    $basic + $t_hidup + $t_daerah + $t_makan + $t_skil +
    $total_lembur + $total_overtime +
    $total_spl_1_7 + $total_spl_8 + $total_spl_9_up +
    $total_tunjangan;

$total_potongan =
    $total_potongan_tunjangan +
    $total_potongan_pinjaman +
    $total_pot_izin +
    $total_pot_perjam +
    $total_pot_mangkir;

$gaji_bersih = $total_pendapatan - $total_potongan;

// ================= Response JSON =================
echo json_encode([
    'nik' => $nik,
    'nama' => $k['nama_lengkap'],
    'jabatan' => $karier['nama_jabatan'],
    'period' => "$startDate - $endDate",
    'basic' => $basic,
    't_hidup' => $t_hidup,
    't_daerah' => $t_daerah,
    't_makan' => $t_makan,
    't_skil' => $t_skil,
    'total_lembur' => $total_lembur,
    'total_overtime' => $total_overtime,
    'total_spl_1_7' => $total_spl_1_7,
    'total_spl_8' => $total_spl_8,
    'total_spl_9_up' => $total_spl_9_up,
    'bpjs_kesehatan' => $bpjs_kesehatan,
    'jht' => $jht,
    'pph21' => $pph21,
    'pot_sio' => $pot_sio,
    'pot_pkwt' => $pot_pkwt,
    'pot_izin' => $total_pot_izin,
    'pot_perjam' => $total_pot_perjam,
    'pot_mangkir' => $total_pot_mangkir,
    'total_pendapatan' => $total_pendapatan,
    'total_potongan' => $total_potongan,
    'gaji_bersih' => $gaji_bersih,
    'hari_kerja' => $hari_kerja_count,
    'izin' => $izin_days,
    'perjam' => $perjam_days,
    'sakit' => $sakit_days,
    'cuti' => $cuti_days,
    'tunjangan_pendapatan' => $tunjanganRows,
    'total_tunjangan_pendapatan' => $total_tunjangan,
    'potongan_tunjangan' => $potonganRows,
    'total_potongan_tunjangan' => $total_potongan_tunjangan,
    'pinjaman' => $pinjamanRows,
    'total_potongan_pinjaman' => $total_potongan_pinjaman,
    'mangkir' => $mangkir_count,
    'absen' => $absenForUI
], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
