<?php
require '../../config/koneksi.php';

$tgl_a = $_GET['tgl_a'] ?? date('Y-m-01');
$tgl_b = $_GET['tgl_b'] ?? date('Y-m-d');

function dateRange($start, $end)
{
    $dates = [];
    $current = strtotime($start);
    $endTime = strtotime($end);

    while ($current <= $endTime) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }

    return $dates;
}

$allDates = dateRange($tgl_a, $tgl_b);

/* -------------------------
    LOAD DATA LIBUR SEKALI
--------------------------*/
$liburArr = [];
$liburQ = $koneksi->query("SELECT tgl_awal_libur, tgl_akhir_libur FROM tb_libur");
while ($l = $liburQ->fetch_assoc()) {
    $liburArr[] = $l;
}

function isLibur($tgl, $liburArr)
{
    foreach ($liburArr as $l) {
        if ($tgl >= $l['tgl_awal_libur'] && $tgl <= $l['tgl_akhir_libur']) {
            return true;
        }
    }
    return false;
}

/* -------------------------
    LOAD SEMUA ABSEN SEKALI
--------------------------*/
$absenAll = [];
$absenQ = $koneksi->query("SELECT id_karyawan, tgl_absen, jam_kerja, ket_absen FROM tb_absen 
                          WHERE tgl_absen BETWEEN '$tgl_a' AND '$tgl_b'");
while ($a = $absenQ->fetch_assoc()) {
    $absenAll[$a['id_karyawan']][$a['tgl_absen']] = $a;
}

/* -------------------------
    LOAD IZIN SAKIT CUTI SEKALI
--------------------------*/
function loadRange($table, $tgl_a, $tgl_b, $koneksi)
{
    $arr = [];
    $q = $koneksi->query("SELECT id_karyawan, tgl_awal_$table AS awal, 
                          tgl_akhir_$table AS akhir FROM tb_$table 
                          WHERE tgl_awal_$table <= '$tgl_b' 
                          AND tgl_akhir_$table >= '$tgl_a'");
    while ($r = $q->fetch_assoc()) {
        $arr[$r['id_karyawan']][] = $r;
    }
    return $arr;
}

$izinAll  = loadRange("izin",  $tgl_a, $tgl_b, $koneksi);
$sakitAll = loadRange("sakit", $tgl_a, $tgl_b, $koneksi);
$cutiAll  = loadRange("cuti",  $tgl_a, $tgl_b, $koneksi);

/* -------------------------
    AMBIL KARYAWAN AKTIF
--------------------------*/
$site = $_GET['site'] ?? "";
$jabatan = $_GET['jabatan'] ?? "";

$where = " WHERE 1=1 ";
if ($site !== "") $where .= " AND kar.id_site='$site'";
if ($jabatan !== "") $where .= " AND kar.id_jabatan='$jabatan'";

$sqlKar = "
SELECT k.id_karyawan, k.nama_lengkap, 
       j.nama_jabatan, d.nama_departemen, s.nama_site
FROM tb_karyawan k
LEFT JOIN tb_karier kar ON kar.id_karyawan = k.id_karyawan 
LEFT JOIN tb_jabatan j ON j.id_jabatan = kar.id_jabatan
LEFT JOIN tb_departemen d ON d.id_departemen = kar.id_departemen
LEFT JOIN tb_site s ON s.id_site = kar.id_site
$where
";
$karyawanQ = $koneksi->query($sqlKar);

$no = 1;

while ($k = $karyawanQ->fetch_assoc()) {

    $hadir = $ot1 = $ot2 = $spl = $ot1_spl = $ot2_spl = 0;
    $mangkir = $izin = $sakit = $cuti = 0;

    foreach ($allDates as $tgl) {

        $absen = $absenAll[$k['id_karyawan']][$tgl] ?? null;

        $isIzin = isset($izinAll[$k['id_karyawan']]) && array_filter($izinAll[$k['id_karyawan']], fn($iz) => $tgl >= $iz['awal'] && $tgl <= $iz['akhir']);
        $isSakit = isset($sakitAll[$k['id_karyawan']]) && array_filter($sakitAll[$k['id_karyawan']], fn($sk) => $tgl >= $sk['awal'] && $tgl <= $sk['akhir']);
        $isCuti = isset($cutiAll[$k['id_karyawan']]) && array_filter($cutiAll[$k['id_karyawan']], fn($ct) => $tgl >= $ct['awal'] && $tgl <= $ct['akhir']);

        if ($absen) {
            $jk = floatval($absen['jam_kerja']);
            $ket = strtoupper($absen['ket_absen'] ?? '');

            if ($ket === 'OFF' && $jk == 0) {
                continue; // tetap OFF
            }

            if ($ket === 'OFF' && $jk > 0) {
                // masuk hari OFF → SPL
                $spl += min($jk, 7);
                $ot1_spl += ($jk >= 8) ? 1 : 0;
                $ot2_spl += ($jk >= 9) ? $jk - 8 : 0;
            } else {
                // hari normal → hadir & OT
                $hadir += min($jk, 7);
                $ot1 += ($jk >= 8) ? 1 : 0;
                $ot2 += ($jk >= 9) ? $jk - 8 : 0;
            }
        } else {
            // Tidak ada absen
            if (!$isIzin && !$isSakit && !$isCuti) {
                $mangkir++;
            }
        }

        if ($isIzin) $izin++;
        if ($isSakit) $sakit++;
        if ($isCuti) $cuti++;
    }


    echo "
    <tr data-id='{$k['id_karyawan']}'>
        <td class='text-center'>{$no}</td>
        <td>{$k['nama_lengkap']}</td>
        <td>{$k['nama_departemen']}</td>
        <td>{$k['nama_jabatan']}</td>
        <td>{$k['nama_site']}</td>
        <td class='text-center'>{$hadir}</td>
        <td class='text-center'>{$ot1}</td>
        <td class='text-center'>{$ot2}</td>
        <td class='text-center'>{$spl}</td>
        <td class='text-center'>{$ot1_spl}</td>
        <td class='text-center'>{$ot2_spl}</td>
        <td class='text-center'>{$mangkir}</td>
        <td class='text-center'>{$izin}</td>
        <td class='text-center'>{$sakit}</td>
        <td class='text-center'>{$cuti}</td>
    </tr>";

    $no++;
}

$koneksi->close();
