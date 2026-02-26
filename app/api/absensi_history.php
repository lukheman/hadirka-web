<?php
// File: app/api/absensi_history_final.php
// Menggunakan 100% Logic Absensi dari fungsi generateSlipGaji asli.

// Panggil file koneksi database Anda (Sesuaikan nama file koneksi Anda)
include 'koneksi-api.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function sendResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    exit;
}

global $koneksi;
if (!isset($koneksi) || $koneksi === false) {
    sendResponse(['error' => 'Koneksi database gagal.'], 500);
}

// =========================================================
// 1. AMBIL INPUT & VALIDASI
// =========================================================
$input = json_decode(file_get_contents('php://input'), true);
// Asumsi API ini menerima ID Karyawan atau NIK. Saya pakai id_karyawan sesuai permintaan Flutter sebelumnya.
// Namun, jika Anda ingin pakai NIK, ganti baris ini:
// $nik = $input['nik'] ?? null;
// $id_karyawan = null; // Dicari di langkah 2
$id_karyawan = $input['id_karyawan'] ?? null;
$startDate = $input['startDate'] ?? null;
$endDate = $input['endDate'] ?? null;

if (!$id_karyawan || !$startDate || !$endDate) {
    sendResponse(['error' => 'id_karyawan, startDate, dan endDate wajib di body POST.'], 400);
}

$id_karyawan_safe = mysqli_real_escape_string($koneksi, $id_karyawan);
$startDate_safe = mysqli_real_escape_string($koneksi, $startDate);
$endDate_safe = mysqli_real_escape_string($koneksi, $endDate);

// =========================================================
// 2. GET KARYAWAN & KARIER (Untuk Tgl Mulai Karier & OT Rate)
// =========================================================
// Mendapatkan NIK dan Nama (untuk output)
$sqlKaryawan = "SELECT nik_karyawan, nama_lengkap FROM tb_karyawan WHERE id_karyawan='$id_karyawan_safe' LIMIT 1";
$qk = mysqli_query($koneksi, $sqlKaryawan);
$karyawan = mysqli_fetch_assoc($qk);
if (!$karyawan) {
    sendResponse(['error' => 'Karyawan tidak ditemukan.'], 404);
}
$nik = $karyawan['nik_karyawan'];

// Mendapatkan Karier Aktif (Untuk ot_pertama, ot_kedua, dan tgl_mulai)
$sqlKarier = "SELECT kar.tgl_mulai, kar.tgl_selesai, kar.ot_pertama, kar.ot_kedua, jab.nama_jabatan 
            FROM tb_karier kar
            LEFT JOIN tb_jabatan jab ON kar.id_jabatan = jab.id_jabatan
            WHERE kar.id_karyawan = '$id_karyawan_safe'
            AND kar.tgl_mulai <= '$endDate_safe'
            AND (kar.tgl_selesai IS NULL OR kar.tgl_selesai >= '$startDate_safe')
            ORDER BY kar.tgl_mulai DESC LIMIT 1";
$qkarier = mysqli_query($koneksi, $sqlKarier);
$karier = mysqli_fetch_assoc($qkarier);

// Jika karier tidak ditemukan
if (!$karier) {
    sendResponse(['error' => 'Karier aktif tidak ditemukan.'], 404);
}
$tgl_mulai_karier = $karier['tgl_mulai'];
$tgl_selesai_karier = $karier['tgl_selesai'];
$ot_pertama = floatval($karier['ot_pertama'] ?? 0);
$ot_kedua = floatval($karier['ot_kedua'] ?? 0);


// =========================================================
// 3. AMBIL DATA ABSENSI MENTAH (tb_absen) & HITUNG IZIN/SAKIT/CUTI
// =========================================================
$absenRows = [];
$izin_days = 0;
$sakit_days = 0;
$cuti_days = 0;

$sqlAbs = "SELECT tgl_absen, jam_kerja, ket_absen
           FROM tb_absen
           WHERE id_karyawan='$id_karyawan_safe'
           AND tgl_absen BETWEEN '$startDate_safe' AND '$endDate_safe'
           ORDER BY tgl_absen ASC";

$qabs = mysqli_query($koneksi, $sqlAbs);

if ($qabs) {
    while ($r = mysqli_fetch_assoc($qabs)) {
        $absenRows[$r['tgl_absen']] = $r;

        // LOGIC ASLI: Hitung counts berdasarkan jam_kerja (bukan ket_absen)
        $ketRawUpper = strtoupper(trim((string)($r['ket_absen'] ?? '')));
        if ($ketRawUpper === 'IZIN')  $izin_days++;
        if ($ketRawUpper === 'SAKIT') $sakit_days++;
        if ($ketRawUpper === 'CUTI')  $cuti_days++;
    }
}


// =========================================================
// 4. ITERASI DAN PROSES DATA PER HARI (MENGGUNAKAN LOGIKA ASLI)
// =========================================================
$absenForUI = [];
$hari_kerja_count = 0;
$mangkir_count = 0;
$new_count = 0;
$resign_count = 0;
$total_jam_wl = 0;
$total_jam_ot = 0;
$total_jam_spl = 0; // Gabungan SPL 1-7, 8, 9+

// Konstanta Jam Kerja Normal
$STANDARD_HOUR = 7;
$JUMAT_HOUR = 5;

$period = new DatePeriod(
    new DateTime($startDate_safe),
    new DateInterval('P1D'),
    (new DateTime($endDate_safe))->modify('+1 day')
);

foreach ($period as $date) {

    $tgl = $date->format('Y-m-d');
    $hariNama = strtolower(date('l', strtotime($tgl)));
    $normal_hours = ($hariNama === 'friday') ? $JUMAT_HOUR : $STANDARD_HOUR;

    $jam = 0.0;
    $wl = 0.0;
    $ot = 0.0;
    $ket = '';
    $status_chip = '';

    // ================= NEW (MASA KERJA) =================
    // if ($tgl < $tgl_mulai_karier) {
    //     $ket = 'NEW';
    //     $status_chip = 'NEW';
    //     $new_count++; 

    //     $absenForUI[] = [
    //         'tanggal' => $tgl,
    //         'jam' => 0, 'wl' => 0, 'ot' => 0,
    //         'ket' => 'NEW', 'status_chip' => 'NEW' 
    //     ];
    //     continue;
    // }

    // ================= ADA DATA ABSEN =================
    if (isset($absenRows[$tgl])) {

        $data     = $absenRows[$tgl];
        $ketAbsen = strtoupper(trim((string)$data['ket_absen']));
        $jamRaw   = trim((string)$data['jam_kerja']);
        $jam      = is_numeric($jamRaw) ? floatval($jamRaw) : 0;

        // ===== DATA NEW DARI DATABASE =====
        if ($ketAbsen === 'NEW') {
            $new_count++;
            $absenForUI[] = [
                'tanggal' => $tgl,
                'jam' => 0,
                'wl' => 0,
                'ot' => 0,
                'ket' => 'NEW',
                'status_chip' => 'NEW'
            ];
            continue;
        }

        // ===== DATA RESIGN =====
        if ($ketAbsen === 'RESIGN' || $ketAbsen === 'R') {
            $resign_count++;
            $absenForUI[] = [
                'tanggal' => $tgl,
                'jam' => 0,
                'wl' => 0,
                'ot' => 0,
                'ket' => 'RESIGN',
                'status_chip' => 'RESIGN'
            ];
            continue;
        }

        // ===== NON-HADIR (IZIN / SAKIT / CUTI) =====
        else if (in_array($ketAbsen, ['IZIN', 'SAKIT', 'CUTI'])) {
            $ket = $ketAbsen;
            $status_chip = $ketAbsen;
            // jam, wl, ot tetap 0
        }

        // ===== OFF MURNI (OFF & Jam = 0) =====
        else if ($ketAbsen === 'OFF' && $jam == 0) {
            $ket = 'OFF';
            $status_chip = 'OFF';
        }

        // ===== SPL (ket_absen = OFF & Jam > 0) =====
        // Note: Logika SPL di sini hanya dipicu jika ket_absen adalah OFF (Hari Libur)
        else if ($ketAbsen === 'OFF' && $jam > 0) {
            $ket = 'SPL';
            $status_chip = 'SPL';
            $total_jam_spl += $jam;
            $hari_kerja_count++;
            // Perhitungan lembur $total_spl_x tidak perlu, tapi total jamnya perlu
        }

        // ===== HADIR (Jam > 0 & Bukan SPL/OFF) =====
        else if ($jam > 0) {
            $ket = 'HADIR';
            $status_chip = 'HADIR';

            // Hitung WL dan OT (Normal Days)
            $wl = min(1.0, max(0.0, $jam - $normal_hours));
            $ot = max(0.0, $jam - $normal_hours - $wl);

            $total_jam_wl += $wl;
            $total_jam_ot += $ot;
            $hari_kerja_count++;

            // Note: Perhitungan jam kurang ($total_jam_kurang) diabaikan karena fokus ke absensi UI.
        }

        // ===== DATA ADA TAPI TIDAK VALID/Terlewat (Dianggap MANGKIR di Logika Asli) =====
        else {
            $ket = 'MANGKIR';
            $status_chip = 'MANGKIR';
            $mangkir_count++;
        }
    }

    // ================= TIDAK ADA DATA ABSEN (MANGKIR) =================
    else {
        // Cek dulu apakah sudah Resign berdasarkan tb_karier
        if (!empty($tgl_selesai_karier) && $tgl > $tgl_selesai_karier) {
            $ket = 'RESIGN';
            $status_chip = 'RESIGN';
            $resign_count++;
        } else {
            $ket = 'MANGKIR';
            $status_chip = 'MANGKIR';
            $mangkir_count++;
        }
    }

    $absenForUI[] = [
        'tanggal' => $tgl,
        'jam' => $jam,
        'wl' => $wl,
        'ot' => $ot,
        'ket' => $ket,
        'status_chip' => $status_chip
    ];
}

// =========================================================
// 5. KIRIM RESPONSE
// =========================================================
$response = [
    'nik' => $nik,
    'nama' => $karyawan['nama_lengkap'] ?? 'N/A',
    'hari_kerja' => $hari_kerja_count, // Hari yang dicatat (HADIR + SPL)
    'izin' => $izin_days,
    'sakit' => $sakit_days,
    'cuti' => $cuti_days,
    'mangkir' => $mangkir_count,
    'new' => $new_count,
    'resign' => $resign_count,

    // Total Jam Gabungan (untuk summary Flutter)
    // Total Jam Hadir = Jam Hadir Normal (Termasuk WL/OT) + Jam SPL
    // Untuk summary di Flutter, total jam adalah sum dari semua item['jam'] > 0
    'total_jam_hadir_sum' => array_sum(array_column($absenForUI, 'jam')),

    'absen' => $absenForUI
];

sendResponse($response);
