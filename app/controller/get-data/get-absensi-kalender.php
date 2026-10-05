<?php
// JSON untuk kalender rekap bulanan per karyawan.
// GET: id_karyawan, tgl_a, tgl_b
require_once __DIR__ . '/../../config/koneksi.php';

header('Content-Type: application/json');

$id_karyawan = $_GET['id_karyawan'] ?? '';
$tgl_a = $_GET['tgl_a'] ?? '';
$tgl_b = $_GET['tgl_b'] ?? '';

if (!$id_karyawan || !$tgl_a || !$tgl_b) {
    echo json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
    exit;
}

$nama_bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

// Rentang tanggal
$allDates = [];
$cur = strtotime($tgl_a);
$end = strtotime($tgl_b);
while ($cur <= $end) {
    $allDates[] = date('Y-m-d', $cur);
    $cur = strtotime('+1 day', $cur);
}

// Absen karyawan
$absenAll = [];
$stAbsen = $koneksi->prepare("SELECT tgl_absen, jam_kerja, ket_absen
                           FROM tb_absen
                           WHERE id_karyawan = ?
                           AND tgl_absen BETWEEN ? AND ?");
$stAbsen->execute([$id_karyawan, $tgl_a, $tgl_b]);
$absenQ = $stAbsen;
if ($absenQ) {
    while ($a = $absenQ->fetch()) {
        $absenAll[$a['tgl_absen']] = $a;
    }
}

// Izin yang disetujui
$izinAll = [];
$stIzin = $koneksi->prepare("
    SELECT DATE(tgl_izin) as tanggal
    FROM tb_pengajuan_izin
    WHERE id_karyawan = ?
    AND DATE(tgl_izin) <= ?
    AND DATE(tgl_izin) >= ?
    AND status_izin = 'disetujui'
");
$stIzin->execute([$id_karyawan, $tgl_b, $tgl_a]);
$qIzin = $stIzin;
if ($qIzin) {
    while ($r = $qIzin->fetch()) {
        $izinAll[] = $r['tanggal'];
    }
}

// Sakit yang disetujui
$sakitAll = [];
$stSakit = $koneksi->prepare("
    SELECT tgl_awal_sakit AS awal, tgl_akhir_sakit AS akhir
    FROM tb_sakit
    WHERE id_karyawan = ?
    AND tgl_awal_sakit <= ?
    AND tgl_akhir_sakit >= ?
    AND status_sakit = 'disetujui'
");
$stSakit->execute([$id_karyawan, $tgl_b, $tgl_a]);
$qSakit = $stSakit;
if ($qSakit) {
    while ($r = $qSakit->fetch()) {
        $sakitAll[] = $r;
    }
}

// Info karyawan
$nama = '-';
$nik = '-';
$stKaryawan = $koneksi->prepare("SELECT k.nama_lengkap, k.nik_karyawan, COALESCE(j.nama_jabatan, '-') AS nama_jabatan
    FROM tb_karyawan k
    LEFT JOIN tb_karier kar ON kar.id_karyawan = k.id_karyawan
    LEFT JOIN tb_jabatan j ON j.id_jabatan = kar.id_jabatan
    WHERE k.id_karyawan = ? LIMIT 1");
$stKaryawan->execute([$id_karyawan]);
$kar = $stKaryawan->fetch();
if ($kar) {
    $nama = $kar['nama_lengkap'];
    $nik = $kar['nik_karyawan'];
    $jabatan = $kar['nama_jabatan'];
} else {
    $jabatan = '-';
}

$days = [];
$totals = [
    'hadir_hari' => 0, 'hadir_jam' => 0,
    'spl_hari' => 0, 'spl_jam' => 0,
    'ot1' => 0, 'ot2' => 0,
    'mangkir' => 0, 'izin' => 0, 'sakit' => 0, 'cuti' => 0,
];

foreach ($allDates as $tgl) {
    $hariNama = strtolower(date('l', strtotime($tgl)));
    $normal_hours = ($hariNama === 'friday') ? 5 : 7;

    $jam = 0;
    $normal = 0;
    $ot1 = 0;
    $ot2 = 0;
    $ket = 'Mangkir';

    // Cek izin / sakit sistem
    $isIzin = in_array($tgl, $izinAll);
    $isSakit = false;
    foreach ($sakitAll as $r) {
        if ($tgl >= $r['awal'] && $tgl <= $r['akhir']) {
            $isSakit = true;
            break;
        }
    }

    if (isset($absenAll[$tgl])) {
        $row = $absenAll[$tgl];
        $jk = floatval($row['jam_kerja']);
        $k = strtoupper(trim((string)$row['ket_absen']));

        if ($k === 'IZIN') {
            $ket = 'Izin';
        } elseif ($k === 'SAKIT') {
            $ket = 'Sakit';
        } elseif ($k === 'CUTI') {
            $ket = 'Cuti';
        } elseif ($k === 'RESIGN') {
            $ket = 'Resign';
        } elseif ($k === 'NEW' || $k === 'R') {
            $ket = 'New';
        } elseif ($k === 'OFF') {
            if ($jk > 0) {
                $ket = 'SPL';
                $jam = $jk;
                $normal = min($jk, $normal_hours);
                if ($hariNama === 'friday') {
                    $ot1 = min(1, max(0, $jk - 5));
                    $ot2 = max(0, $jk - 5 - $ot1);
                } else {
                    $ot1 = min(1, max(0, $jk - 7));
                    $ot2 = max(0, $jk - 7 - $ot1);
                }
                $totals['spl_hari']++;
                $totals['spl_jam'] += $normal;
            } else {
                $ket = 'OFF';
            }
        } elseif ($k === 'MANGKIR') {
            $ket = 'Mangkir';
        } elseif (is_numeric(trim((string)$row['jam_kerja']))) {
            if ($jk > 0) {
                $ket = 'Masuk';
                $jam = $jk;
                $normal = min($jk, $normal_hours);
                if ($hariNama === 'friday') {
                    $ot1 = min(1, max(0, $jk - 5));
                    $ot2 = max(0, $jk - 5 - $ot1);
                } else {
                    $ot1 = min(1, max(0, $jk - 7));
                    $ot2 = max(0, $jk - 7 - $ot1);
                }
                $totals['hadir_hari']++;
                $totals['hadir_jam'] += $normal;
            } else {
                $ket = 'Mangkir';
            }
        } else {
            $ket = 'Mangkir';
        }
    } else {
        if ($isIzin) {
            $ket = 'Izin (Sistem)';
        } elseif ($isSakit) {
            $ket = 'Sakit (Sistem)';
        } else {
            $ket = 'Mangkir';
        }
    }

    if (in_array($ket, ['Mangkir'])) $totals['mangkir']++;
    if (in_array($ket, ['Izin', 'Izin (Sistem)'])) $totals['izin']++;
    if (in_array($ket, ['Sakit', 'Sakit (Sistem)'])) $totals['sakit']++;
    if ($ket === 'Cuti') $totals['cuti']++;
    $totals['ot1'] += $ot1;
    $totals['ot2'] += $ot2;

    $ts = strtotime($tgl);
    $days[] = [
        'tgl' => $tgl,
        'day' => (int)date('j', $ts),
        'month' => $nama_bulan[(int)date('n', $ts)],
        'month_key' => date('Y-m', $ts),
        'weekday' => (int)date('N', $ts), // 1=Senin .. 7=Minggu
        'ket' => $ket,
        'jam' => $jam,
        'normal' => $normal,
        'ot1' => $ot1,
        'ot2' => $ot2,
    ];
}

// Bulatkan total jam (hindari 6.9999999)
foreach (['hadir_jam', 'spl_jam', 'ot1', 'ot2'] as $k) {
    $totals[$k] = round($totals[$k], 1);
}

echo json_encode([
    'status' => 'success',
    'nama' => $nama,
    'nik' => $nik,
    'jabatan' => $jabatan,
    'periode' => ['dari' => $tgl_a, 'sampai' => $tgl_b],
    'days' => $days,
    'totals' => $totals,
]);
