<?php
// File: slip_gaji_api.php (Endpoint Detail Slip Gaji - Mengambil ID dari URL/Query Parameter)

header('Content-Type: application/json; charset=utf-8');

// --- PENTING: Sesuaikan dengan nama file koneksi Anda ---
// Tidak ada session_start() di sini
include 'koneksi-api.php';
// --------------------------------------------------------

// ====================================================================
//          LOGIKA PERHITUNGAN GAJI (generateSlipGaji Function)
// ====================================================================
if (!function_exists('generateSlipGaji')) {
function generateSlipGaji($id_karyawan, $startDate, $endDate)
{
    global $koneksi;

    $id_karyawan = mysqli_real_escape_string($koneksi, $id_karyawan);
    $startDate = mysqli_real_escape_string($koneksi, $startDate);
    $endDate = mysqli_real_escape_string($koneksi, $endDate);

    // ================= KARYAWAN =================
    $sql = "SELECT nik_karyawan AS nik, nama_lengkap, id_karyawan
            FROM tb_karyawan WHERE id_karyawan='$id_karyawan' LIMIT 1";
    $q = mysqli_query($koneksi, $sql);
    $k = mysqli_fetch_assoc($q);
    if (!$k) return ['error' => 'Karyawan tidak ditemukan'];

    // ================= KARIER =================
    $sqlKarier = "SELECT kar.*, jab.nama_jabatan
        FROM tb_karier kar
        LEFT JOIN tb_jabatan jab ON kar.id_jabatan = jab.id_jabatan
        WHERE kar.id_karyawan='$id_karyawan'
        AND kar.tgl_mulai <= '$endDate'
        AND (kar.tgl_selesai IS NULL OR kar.tgl_selesai >= '$startDate')
        ORDER BY kar.tgl_mulai DESC LIMIT 1";
    $qk = mysqli_query($koneksi, $sqlKarier);
    $karier = mysqli_fetch_assoc($qk);
    if (!$karier) return ['error' => 'Karier aktif tidak ditemukan'];

    $tgl_mulai_karier = $karier['tgl_mulai'];

    // ================= TUNJANGAN =================
    $tunjanganRows = [];
    $total_tunjangan = 0;
    $qt = mysqli_query($koneksi,
        "SELECT nama_tunjangan, jumlah_tunjangan FROM tb_tunjangan WHERE id_karier='{$karier['id_karier']}'");
    while ($r = mysqli_fetch_assoc($qt)) {
        $j = floatval($r['jumlah_tunjangan']);
        $tunjanganRows[] = ['nama'=>$r['nama_tunjangan'],'jumlah'=>$j];
        $total_tunjangan += $j;
    }

    // ================= POTONGAN =================
    $potonganRows = [];
    $total_potongan_tunjangan = 0;
    $qp = mysqli_query($koneksi,
        "SELECT jenis_potongan, jumlah_potongan FROM tb_potongan WHERE id_karier='{$karier['id_karier']}'");
    while ($r = mysqli_fetch_assoc($qp)) {
        $j = floatval($r['jumlah_potongan']);
        $potonganRows[] = ['nama'=>$r['jenis_potongan'],'jumlah'=>$j];
        $total_potongan_tunjangan += $j;
    }

    // ================= RAPEL =================
    $total_rapel = 0;
    $rapelRows = [];
    $qr = mysqli_query($koneksi,
        "SELECT nominal_rapel, ket_rapel FROM tb_rapel
         WHERE id_karyawan='$id_karyawan'
         AND tgl_rapel BETWEEN '$startDate' AND '$endDate'");
    while ($r = mysqli_fetch_assoc($qr)) {
        $j = floatval($r['nominal_rapel']);
        $total_rapel += $j;
        $rapelRows[] = ['nama'=>$r['ket_rapel'],'jumlah'=>$j];
    }

    // ================= PINJAMAN =================
    $pinjamanRows = [];
    $total_potongan_pinjaman = 0;
    $qpj = mysqli_query($koneksi,
        "SELECT nominal_pinjaman, ket_pinjaman FROM tb_pinjaman
         WHERE id_karyawan='$id_karyawan'
         AND tgl_bayar_pinjaman BETWEEN '$startDate' AND '$endDate'");
    while ($r = mysqli_fetch_assoc($qpj)) {
        $j = floatval($r['nominal_pinjaman']);
        $pinjamanRows[] = ['nama'=>$r['ket_pinjaman'],'jumlah'=>$j];
        $total_potongan_pinjaman += $j;
    }

    // ================= ABSEN RAW =================
    $absenRows = [];
    $qa = mysqli_query($koneksi,
        "SELECT tgl_absen, jam_kerja, ket_absen FROM tb_absen
         WHERE id_karyawan='$id_karyawan'
         AND tgl_absen BETWEEN '$startDate' AND '$endDate'");
    while ($r = mysqli_fetch_assoc($qa)) {
        $absenRows[$r['tgl_absen']] = $r;
    }

    // ================= LOOP HARI =================
    $absenForUI = [];
    $hari_kerja_count = $izin_days = $sakit_days = $cuti_days = $mangkir_count = 0;
    $total_jam_wl = $total_jam_ot = $total_jam_kurang = 0;
    $total_potongan_perjam = 0;
    $total_lembur = $total_overtime = 0;
    $total_spl_1_7 = $total_spl_8 = $total_spl_9_up = 0;

    $period = new DatePeriod(
        new DateTime($startDate),
        new DateInterval('P1D'),
        (new DateTime($endDate))->modify('+1 day')
    );

    $i = 1;

    foreach ($period as $d) {
        $tgl = $d->format('Y-m-d');
        $hariNama = strtolower(date('l', strtotime($tgl)));
        $normal_hours = ($hariNama === 'friday') ? 5 : 7;

        $ket = 'MANGKIR';
        $jam = 0;

        if (isset($absenRows[$tgl])) {
            $ketAbsen = strtoupper(trim($absenRows[$tgl]['ket_absen']));
            $jam = floatval($absenRows[$tgl]['jam_kerja']);

            if ($ketAbsen === 'IZIN') { $izin_days++; $ket='IZIN'; $jam=0; }
            else if ($ketAbsen === 'SAKIT') { $sakit_days++; $ket='SAKIT'; $jam=0; }
            else if ($ketAbsen === 'CUTI') { $cuti_days++; $ket='CUTI'; $jam=0; }

            // ================= SPL =================
            else if (in_array($ketAbsen,['OFF','OFF DAY','LIBUR','HOLIDAY','OFF_DAY']) && $jam > 0) {
                $r1 = floatval($karier['ot_pertama']);
                $r2 = floatval($karier['ot_kedua']);

                if ($jam <= 7) {
                    $total_spl_1_7 += $jam * $r1;
                } else if ($jam == 8) {
                    $total_spl_1_7 += 7 * $r1;
                    $total_spl_8 += 1 * $r2;
                } else {
                    $total_spl_1_7 += 7 * $r1;
                    $total_spl_8 += 1 * $r2;
                    $total_spl_9_up += ($jam - 8) * $r2;
                }
                $ket = 'SPL';
            }

            // ================= HADIR =================
            else if ($jam > 0) {
                if ($jam > $normal_hours) {
                    $lebih = $jam - $normal_hours;
                    $wl = min(1, $lebih);
                    $ot = max(0, $lebih - $wl);
                    $total_lembur += $wl * floatval($karier['ot_pertama']);
                    $total_overtime += $ot * floatval($karier['ot_kedua']);
                    $total_jam_wl += $wl;
                    $total_jam_ot += $ot;
                }
                if ($jam < $normal_hours) {
                    $kurang = $normal_hours - $jam;
                    $total_jam_kurang += $kurang;
                    $total_potongan_perjam += $kurang * ($karier['gaji_pokok']/25/$normal_hours);
                }
                $hari_kerja_count++;
                $ket = 'HADIR';
            }
        } else {
            $mangkir_count++;
        }

        $absenForUI[] = [
            'no'=>$i++,
            'tanggal'=>date('d M',strtotime($tgl)),
            'hari'=>date('D',strtotime($tgl)),
            'jam'=>$jam,
            'ket'=>$ket
        ];
    }

    // ================= FINAL =================
    $basic = floatval($karier['gaji_pokok']);
    $total_pendapatan =
        $basic + $total_tunjangan + $total_lembur + $total_overtime +
        $total_spl_1_7 + $total_spl_8 + $total_spl_9_up + $total_rapel;

    $total_potongan =
        $total_potongan_tunjangan + $total_potongan_pinjaman + $total_potongan_perjam;

    return [
        'nik'=>$k['nik'],
        'nama'=>$k['nama_lengkap'],
        'jabatan'=>$karier['nama_jabatan'] ?? '',
        'period'=>date('d M Y',strtotime($startDate)).' - '.date('d M Y',strtotime($endDate)),
        'gajiBersih'=>$total_pendapatan - $total_potongan,
        'totalPendapatan'=>$total_pendapatan,
        'totalPotongan'=>$total_potongan,
        'pendapatan'=>array_merge(
            [['nama'=>'Gaji Pokok','jumlah'=>$basic]],
            $tunjanganRows,
            $rapelRows,
            array_filter([
                $total_lembur>0?['nama'=>'Lembur WL','jumlah'=>$total_lembur]:null,
                $total_overtime>0?['nama'=>'Lembur OT','jumlah'=>$total_overtime]:null,
                $total_spl_1_7>0?['nama'=>'SPL Rate 1','jumlah'=>$total_spl_1_7]:null,
                $total_spl_8>0?['nama'=>'SPL Rate 2','jumlah'=>$total_spl_8]:null,
                $total_spl_9_up>0?['nama'=>'SPL Rate 3','jumlah'=>$total_spl_9_up]:null,
            ])
        ),
        'potongan'=>array_merge($potonganRows,$pinjamanRows),
        'hariKerja'=>$hari_kerja_count,
        'izin'=>$izin_days,
        'sakit'=>$sakit_days,
        'cuti'=>$cuti_days,
        'mangkir'=>$mangkir_count,
        'totalJamWL'=>$total_jam_wl,
        'totalJamOT'=>$total_jam_ot,
        'totalJamKurang'=>$total_jam_kurang,
        'absensi'=>$absenForUI
    ];
}}


// ====================================================================
//          API ENDPOINT HANDLER
// ====================================================================

// TANGKAP ID KARYAWAN dari URL parameter 'id_karyawan'
$id_karyawan = $_GET['id_karyawan'] ?? null;
$startDate = $_GET['startDate'] ?? null;
$endDate = $_GET['endDate'] ?? null;

if (!$id_karyawan || !$startDate || !$endDate) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Parameter ID Karyawan, startDate, atau endDate tidak lengkap.']);
    exit;
}

// Sanitasi Input
$id_karyawan = mysqli_real_escape_string($koneksi, $id_karyawan);
$startDate = mysqli_real_escape_string($koneksi, $startDate);
$endDate = mysqli_real_escape_string($koneksi, $endDate);

$response = generateSlipGaji($id_karyawan, $startDate, $endDate);

if (isset($koneksi) && is_object($koneksi)) $koneksi->close();

if (isset($response['error'])) {
    http_response_code(404); // Not Found
    echo json_encode($response);
    exit;
}

echo json_encode($response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
