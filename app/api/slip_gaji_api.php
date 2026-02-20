<?php
// File: slip_gaji_api.php (Endpoint Detail Slip Gaji)

header('Content-Type: application/json; charset=utf-8');

// --- PENTING: Sesuaikan dengan nama file koneksi Anda ---
include 'koneksi-api.php';
// --------------------------------------------------------

if (!function_exists('generateSlipGaji')) {
    function generateSlipGaji($id_karyawan, $startDate, $endDate)
    {
        global $koneksi;

        // Sanitasi input internal
        $id_karyawan = mysqli_real_escape_string($koneksi, $id_karyawan);
        $startDate = mysqli_real_escape_string($koneksi, $startDate);
        $endDate = mysqli_real_escape_string($koneksi, $endDate);

        // ================= 1) Get Karyawan =================
        $sql = "SELECT nik_karyawan, nama_lengkap, id_karyawan FROM tb_karyawan WHERE id_karyawan='$id_karyawan' LIMIT 1";
        $q = mysqli_query($koneksi, $sql);
        $k = mysqli_fetch_assoc($q);
        if (!$k) return ['error' => 'Karyawan tidak ditemukan'];

        // ================= 2) Get Karier Aktif =================
        $sqlKarier = "SELECT kar.*, jab.nama_jabatan 
            FROM tb_karier kar
            LEFT JOIN tb_jabatan jab ON kar.id_jabatan = jab.id_jabatan
            WHERE kar.id_karyawan = '$id_karyawan'
            AND kar.tgl_mulai <= '$endDate'
            AND (kar.tgl_selesai IS NULL OR kar.tgl_selesai >= '$startDate')
            ORDER BY kar.tgl_mulai DESC LIMIT 1";
        $qk = mysqli_query($koneksi, $sqlKarier);
        $karier = mysqli_fetch_assoc($qk);
        if (!$karier) return ['error' => 'Karier aktif tidak ditemukan'];

        $tgl_mulai_karier = $karier['tgl_mulai'];
        $tgl_selesai_karier = $karier['tgl_selesai']; // bisa NULL

        // ================= 3) Tunjangan & Potongan dari DB =================
        $tunjanganRows = [];
        $total_tunjangan_db = 0;
        $qt = mysqli_query($koneksi, "SELECT nama_tunjangan, jumlah_tunjangan FROM tb_tunjangan WHERE id_karier = '{$karier['id_karier']}'");
        while ($tr = mysqli_fetch_assoc($qt)) {
            $tunjanganRows[] = ['nama' => $tr['nama_tunjangan'], 'jumlah' => floatval($tr['jumlah_tunjangan'])];
            $total_tunjangan_db += floatval($tr['jumlah_tunjangan']);
        }

        $potonganRows = [];
        $total_potongan_db = 0;
        $bpjs_kesehatan = 0;
        $jht = 0;
        $pph21 = 0;
        $qp = mysqli_query($koneksi, "SELECT jenis_potongan, jumlah_potongan FROM tb_potongan WHERE id_karier = '{$karier['id_karier']}'");
        while ($pr = mysqli_fetch_assoc($qp)) {
            $potonganRows[] = ['nama' => $pr['jenis_potongan'], 'jumlah' => floatval($pr['jumlah_potongan'])];
            $total_potongan_db += floatval($pr['jumlah_potongan']);

            // Ekstrak BPJS/JHT/PPH21
            $n = strtolower($pr['jenis_potongan']);
            if (strpos($n, 'bpjs') !== false) {
                $bpjs_kesehatan += floatval($pr['jumlah_potongan']);
            } elseif (strpos($n, 'jht') !== false) {
                $jht += floatval($pr['jumlah_potongan']);
            } elseif (strpos($n, 'pph') !== false) {
                $pph21 += floatval($pr['jumlah_potongan']);
            }
        }

        // ================= 4) Rapel & Pinjaman =================
        $total_rapel = 0;
        $rapelRows = [];
        $qr = mysqli_query($koneksi, "SELECT tgl_rapel, nominal_rapel, ket_rapel FROM tb_rapel WHERE id_karyawan = '$id_karyawan' AND tgl_rapel BETWEEN '$startDate' AND '$endDate'");
        while ($r = mysqli_fetch_assoc($qr)) {
            $total_rapel += floatval($r['nominal_rapel']);
            $rapelRows[] = [
                'tanggal' => $r['tgl_rapel'],
                'nama' => $r['ket_rapel'],
                'jumlah' => floatval($r['nominal_rapel'])
            ];
        }

        $total_potongan_pinjaman = 0;
        $pinjamanRows = [];
        $qpj = mysqli_query($koneksi, "SELECT tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman FROM tb_pinjaman WHERE id_karyawan = '$id_karyawan' AND tgl_bayar_pinjaman BETWEEN '$startDate' AND '$endDate'");
        while ($pj = mysqli_fetch_assoc($qpj)) {
            $total_potongan_pinjaman += floatval($pj['nominal_pinjaman']);
            $pinjamanRows[] = [
                'nama' => $pj['ket_pinjaman'],
                'jumlah' => floatval($pj['nominal_pinjaman']),
                'tgl_jatuh_tempo' => $pj['tgl_bayar_pinjaman']
            ];
        }

        // ================= 5) Looping Absensi =================
        $absenRows = [];
        $qa = mysqli_query($koneksi, "SELECT tgl_absen, jam_kerja, ket_absen FROM tb_absen WHERE id_karyawan='$id_karyawan' AND tgl_absen BETWEEN '$startDate' AND '$endDate'");
        while ($row = mysqli_fetch_assoc($qa)) {
            $absenRows[$row['tgl_absen']] = $row;
        }

        $absenForUI = [];
        $hari_kerja_count = $izin_days = $sakit_days = $cuti_days = $mangkir_count = $new_count = $resign_count = 0;
        $total_lembur = $total_overtime = $total_spl_1_7 = $total_spl_8 = $total_spl_9_up = 0;
        $total_jam_wl = $total_jam_ot = 0;
        $total_jam_spl_1_7 = $total_jam_spl_8 = $total_jam_spl_9_up = 0;
        $total_jam_kurang = $total_potongan_perjam = 0;

        $period = new DatePeriod(new DateTime($startDate), new DateInterval('P1D'), (new DateTime($endDate))->modify('+1 day'));

        $i = 1;
        foreach ($period as $date) {
            $tgl = $date->format('Y-m-d');
            $hariNama = strtolower($date->format('l'));
            $normal_hours = ($hariNama === 'friday') ? 5 : 7;

            // ================= NEW =================
            if ($tgl < $tgl_mulai_karier) {
                $isMinggu = ($hariNama === 'sunday');
                $absenForUI[] = [
                    'no' => $i++,
                    'tanggal' => date('d M', strtotime($tgl)),
                    'hari' => date('D', strtotime($tgl)),
                    'jam' => 0,
                    'ket' => 'NEW'
                ];
                // $new_count++;
                if (!$isMinggu) {
                    $new_count++;
                }
                continue;
            }

            // ================= ADA DATA ABSEN =================
            if (isset($absenRows[$tgl])) {
                $ketAbsen = strtoupper(trim((string)$absenRows[$tgl]['ket_absen']));
                $jamRaw = trim((string)$absenRows[$tgl]['jam_kerja']);
                $jam = is_numeric($jamRaw) ? floatval($jamRaw) : 0;

                // ===== IZIN / SAKIT / CUTI =====
                if (in_array($ketAbsen, ['IZIN', 'SAKIT', 'CUTI'])) {
                    if ($ketAbsen === 'IZIN') $izin_days++;
                    if ($ketAbsen === 'SAKIT') $sakit_days++;
                    if ($ketAbsen === 'CUTI') $cuti_days++;

                    $absenForUI[] = [
                        'no' => $i++,
                        'tanggal' => date('d M', strtotime($tgl)),
                        'hari' => date('D', strtotime($tgl)),
                        'jam' => 0,
                        'ket' => $ketAbsen
                    ];
                    continue;
                }

                // ===== OFF MURNI =====
                if ($ketAbsen === 'OFF' && $jam == 0) {
                    $absenForUI[] = [
                        'no' => $i++,
                        'tanggal' => date('d M', strtotime($tgl)),
                        'hari' => date('D', strtotime($tgl)),
                        'jam' => 0,
                        'ket' => 'OFF'
                    ];
                    continue;
                }

                // ===== SPL =====
                if (
                    $jam > 0 &&
                    (
                        strpos($ketAbsen, 'OFF') !== false ||
                        strpos($ketAbsen, 'SPL') !== false ||
                        strpos($ketAbsen, 'LEMBUR') !== false
                    )
                ) {
                    // Hari biasa: Normal 7 jam, OT1 jam ke-8, OT2 jam ke-9+
                    $h1_7 = min(7, $jam);
                    $h8 = ($jam > 7) ? min(1, $jam - 7) : 0;
                    $h9up = max(0, $jam - 8);

                    $total_spl_1_7 += $h1_7 * floatval($karier['spl']);
                    $total_spl_8 += $h8 * floatval($karier['ot1_spl']);
                    $total_spl_9_up += $h9up * floatval($karier['ot2_spl']);

                    $total_jam_spl_1_7 += $h1_7;
                    $total_jam_spl_8 += $h8;
                    $total_jam_spl_9_up += $h9up;

                    // if ($hariNama === 'friday') {
                    //     // Jumat: Normal 5 jam, OT1 jam ke-6, OT2 jam ke-7+
                    //     $h1_5 = min(5, $jam);
                    //     $h6 = ($jam > 5) ? min(1, $jam - 5) : 0;
                    //     $h7up = max(0, $jam - 6);

                    //     $total_spl_1_7 += $h1_5 * floatval($karier['spl']);
                    //     $total_spl_8 += $h6 * floatval($karier['ot1_spl']);
                    //     $total_spl_9_up += $h7up * floatval($karier['ot2_spl']);

                    //     $total_jam_spl_1_7 += $h1_5;
                    //     $total_jam_spl_8 += $h6;
                    //     $total_jam_spl_9_up += $h7up;
                    // } else {
                    //     // Hari biasa: Normal 7 jam, OT1 jam ke-8, OT2 jam ke-9+
                    //     $h1_7 = min(7, $jam);
                    //     $h8 = ($jam > 7) ? min(1, $jam - 7) : 0;
                    //     $h9up = max(0, $jam - 8);

                    //     $total_spl_1_7 += $h1_7 * floatval($karier['spl']);
                    //     $total_spl_8 += $h8 * floatval($karier['ot1_spl']);
                    //     $total_spl_9_up += $h9up * floatval($karier['ot2_spl']);

                    //     $total_jam_spl_1_7 += $h1_7;
                    //     $total_jam_spl_8 += $h8;
                    //     $total_jam_spl_9_up += $h9up;
                    // }

                    $absenForUI[] = [
                        'no' => $i++,
                        'tanggal' => date('d M', strtotime($tgl)),
                        'hari' => date('D', strtotime($tgl)),
                        'jam' => $jam,
                        'ket' => 'SPL'
                    ];
                    continue;
                }

                // ===== HADIR =====
                if ($jam > 0) {
                    $wl = 0;
                    $ot = 0;

                    if ($hariNama === 'friday') {
                        $wl = min(1, max(0, $jam - 5));
                        $ot = max(0, $jam - 5 - $wl);
                    } else {
                        $wl = min(1, max(0, $jam - 7));
                        $ot = max(0, $jam - 7 - $wl);
                    }

                    $total_lembur += $wl * floatval($karier['ot_pertama']);
                    $total_overtime += $ot * floatval($karier['ot_kedua']);
                    $total_jam_wl += $wl;
                    $total_jam_ot += $ot;

                    if ($jam < $normal_hours) {
                        $kurang = $normal_hours - $jam;
                        $total_jam_kurang += $kurang;
                        $total_potongan_perjam += $kurang * (floatval($karier['pot_izin']) / $normal_hours);
                    }

                    $hari_kerja_count++;

                    $absenForUI[] = [
                        'no' => $i++,
                        'tanggal' => date('d M', strtotime($tgl)),
                        'hari' => date('D', strtotime($tgl)),
                        'jam' => $jam,
                        'ket' => 'HADIR'
                    ];
                    continue;
                }
            }

            // ================= TIDAK ADA DATA =================
            // ================= LOGIKA BARU: CEK RESIGN =================
            if (!empty($tgl_selesai_karier) && $tgl > $tgl_selesai_karier) {
                // Jika tanggal absen > tgl_selesai_karier dan tidak ada data absen
                $resign_count++;

                $absenForUI[] = [
                    'no' => $i++,
                    'tanggal' => date('d M', strtotime($tgl)),
                    'hari' => date('D', strtotime($tgl)),
                    'jam' => 0,
                    'ket' => 'RESIGN'
                ];
                continue;
            }

            // Jika bukan resign, berarti mangkir
            $mangkir_count++;

            $absenForUI[] = [
                'no' => $i++,
                'tanggal' => date('d M', strtotime($tgl)),
                'hari' => date('D', strtotime($tgl)),
                'jam' => 0,
                'ket' => 'MANGKIR'
            ];
        }

        // ================= 6) Final Perhitungan =================
        $basic = floatval($karier['gaji_pokok']);
        $total_pot_izin = floatval($karier['pot_izin']) * $izin_days;
        // $total_pot_sakit = ($sakit_days * (750000/25));
        $total_pot_mangkir = floatval($karier['pot_mangkir']) * $mangkir_count;

        // PKWT potongan untuk hari NEW
        $total_pot_pkwt = 0;
        if ($new_count > 0) {
            $total_pot_pkwt = (($total_tunjangan_db + $basic) / 25) * $new_count;
        }

        // PKWT potongan untuk hari RESIGN
        if ($resign_count > 0) {
            $total_pot_pkwt += (($total_tunjangan_db + $basic) / 25) * $resign_count;
        }

        // Kelebihan hari kerja (jika hari kerja > 25)
        $kelebihan_hari = 0;
        $pendapatan_kelebihan_hari = 0;
        if ($hari_kerja_count > 25) {
            $kelebihan_hari = $hari_kerja_count - 25;
            $pendapatan_kelebihan_hari = $kelebihan_hari * ($basic / 25);
        }

        $total_pendapatan = $basic + $total_tunjangan_db + $total_lembur + $total_overtime +
            $total_spl_1_7 + $total_spl_8 + $total_spl_9_up +
            $total_rapel + $pendapatan_kelebihan_hari;

        $total_potongan = $total_potongan_db + $total_potongan_pinjaman +
            $total_pot_izin  + $total_pot_mangkir +
            $total_potongan_perjam + $total_pot_pkwt;

        $gaji_bersih = $total_pendapatan - $total_potongan;

        // Build Detail Lists untuk Output
        $listPendapatan = array_merge(
            [['nama' => 'Gaji Pokok', 'jumlah' => $basic]],
            $tunjanganRows,
            $rapelRows
        );
        if ($total_lembur > 0) $listPendapatan[] = ['nama' => 'Lembur WL', 'jumlah' => $total_lembur];
        if ($total_overtime > 0) $listPendapatan[] = ['nama' => 'Lembur OT', 'jumlah' => $total_overtime];
        if ($total_spl_1_7 > 0) $listPendapatan[] = ['nama' => 'SPL 1-7', 'jumlah' => $total_spl_1_7];
        if ($total_spl_8 > 0) $listPendapatan[] = ['nama' => 'SPL 8', 'jumlah' => $total_spl_8];
        if ($total_spl_9_up > 0) $listPendapatan[] = ['nama' => 'SPL 9+', 'jumlah' => $total_spl_9_up];
        if ($pendapatan_kelebihan_hari > 0) $listPendapatan[] = ['nama' => 'Kelebihan Hari Kerja', 'jumlah' => $pendapatan_kelebihan_hari];

        $listPotongan = array_merge($potonganRows, $pinjamanRows);
        if ($total_pot_izin > 0) $listPotongan[] = ['nama' => 'Potongan Izin', 'jumlah' => $total_pot_izin];
        // if ($total_pot_sakit > 0) $listPotongan[] = ['nama' => 'Potongan Sakit', 'jumlah' => $total_pot_sakit];
        if ($total_pot_mangkir > 0) $listPotongan[] = ['nama' => 'Potongan Mangkir', 'jumlah' => $total_pot_mangkir];
        if ($total_potongan_perjam > 0) $listPotongan[] = ['nama' => 'Potongan Kurang Jam', 'jumlah' => $total_potongan_perjam];
        if ($total_pot_pkwt > 0) $listPotongan[] = ['nama' => 'Potongan PKWT (NEW/RESIGN)', 'jumlah' => $total_pot_pkwt];

        return [
            'nik' => $k['nik_karyawan'],
            'nama' => $k['nama_lengkap'],
            'jabatan' => $karier['nama_jabatan'] ?? '',
            'period' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)),
            'gajiBersih' => $gaji_bersih,
            'totalPendapatan' => $total_pendapatan,
            'totalPotongan' => $total_potongan,
            'pendapatan' => $listPendapatan,
            'potongan' => $listPotongan,

            'hariKerja' => $hari_kerja_count,
            'mangkir' => $mangkir_count,
            'izin' => $izin_days,
            'sakit' => $sakit_days,
            'cuti' => $cuti_days,
            'newDays' => $new_count,
            'resignDays' => $resign_count,
            'kelebihanHari' => $kelebihan_hari,

            'totalJamKurang' => $total_jam_kurang,
            'totalJamWL' => $total_jam_wl,
            'totalJamOT' => $total_jam_ot,
            'absensi' => $absenForUI
        ];
    }
}

// ====================================================================
//          API ENDPOINT HANDLER
// ====================================================================
$id_karyawan = $_GET['id_karyawan'] ?? null;
$startDate = $_GET['startDate'] ?? null;
$endDate = $_GET['endDate'] ?? null;

if (!$id_karyawan || !$startDate || !$endDate) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter id_karyawan, startDate, atau endDate tidak lengkap.']);
    exit;
}

$response = generateSlipGaji($id_karyawan, $startDate, $endDate);

if (isset($koneksi) && is_object($koneksi)) $koneksi->close();

if (isset($response['error'])) {
    http_response_code(404);
    echo json_encode($response);
    exit;
}

echo json_encode($response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
