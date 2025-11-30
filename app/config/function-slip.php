<?php
// header('Content-Type: application/json; charset=utf-8');

if (!function_exists('generateSlipGaji')) {
    function generateSlipGaji($nik, $startDate, $endDate)
    {
        global $koneksi;

        // ================= Helper functions =================
        if (!function_exists('daysOverlap')) {
            function daysOverlap($a_start, $a_end, $b_start, $b_end)
            {
                $s = max(strtotime($a_start), strtotime($b_start));
                $e = min(strtotime($a_end), strtotime($b_end));
                if ($e < $s) return 0;
                return floor(($e - $s) / 86400) + 1;
            }
        }

        if (!function_exists('getOverlapDays')) {
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
        }

        // ================= 1) Get Karyawan =================
        $sql = "SELECT * FROM tb_karyawan WHERE nik_karyawan='$nik' LIMIT 1";
        $q = mysqli_query($koneksi, $sql);
        $k = mysqli_fetch_assoc($q);
        if (!$k) return ['error' => 'Karyawan tidak ditemukan'];
        $id_karyawan = $k['id_karyawan'];

        // ================= 2) Get Karier aktif =================
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
        $tgl_selesai_karier = $karier['tgl_selesai'] ?? null;

        // ================= 2a) Ambil Tunjangan (Pendapatan) dari tb_tunjangan =================
        $tunjanganRows = [];
        $total_tunjangan = 0;
        $sqlTunj = "SELECT nama_tunjangan, jumlah_tunjangan 
                    FROM tb_tunjangan 
                    WHERE id_karier = '" . $karier['id_karier'] . "'";
        $qt = mysqli_query($koneksi, $sqlTunj);
        while ($tr = mysqli_fetch_assoc($qt)) {
            $row = [
                'nama' => $tr['nama_tunjangan'],
                'jumlah' => floatval($tr['jumlah_tunjangan'])
            ];
            $tunjanganRows[] = $row;
            $total_tunjangan += floatval($tr['jumlah_tunjangan']);
        }

        // ================= Ambil Potongan (BPJS/JHT/PPH21 dll) dari tb_potongan =================
        // NOTE: sesuai perubahan, tb_potongan memakai id_karier
        $potonganRows = [];
        $total_potongan_tunjangan = 0;
        $sqlPot = "SELECT jenis_potongan, jumlah_potongan 
                   FROM tb_potongan 
                   WHERE id_karier = '" . $karier['id_karier'] . "'";
        $qp = mysqli_query($koneksi, $sqlPot);
        while ($p = mysqli_fetch_assoc($qp)) {
            $row = [
                'nama' => $p['jenis_potongan'],
                'jumlah' => floatval($p['jumlah_potongan'])
            ];
            $potonganRows[] = $row;
            $total_potongan_tunjangan += floatval($p['jumlah_potongan']);
        }

        // dari potonganRows, ekstrak bpjs/jht/pph angka (jika perlu di output)
        $bpjs_kesehatan = 0;
        $jht = 0;
        $pph21 = 0;
        foreach ($potonganRows as $pr) {
            $n = strtolower($pr['nama']);
            if (strpos($n, 'bpjs') !== false) {
                $bpjs_kesehatan += $pr['jumlah'];
            } elseif (strpos($n, 'jht') !== false || strpos($n, 'jht ') !== false) {
                $jht += $pr['jumlah'];
            } elseif (strpos($n, 'pph') !== false) {
                $pph21 += $pr['jumlah'];
            }
        }

        // ================= 3) Get Rapel =================
        $total_rapel = 0;
        $rapelRows = [];
        $sqlRapel = "SELECT tgl_rapel, nominal_rapel, ket_rapel
             FROM tb_rapel
             WHERE id_karyawan = '$id_karyawan'
             AND tgl_rapel BETWEEN '$startDate' AND '$endDate'";
        $qRapel = mysqli_query($koneksi, $sqlRapel);
        while ($r = mysqli_fetch_assoc($qRapel)) {
            $nominal = floatval($r['nominal_rapel']);
            $total_rapel += $nominal;
            $rapelRows[] = [
                'tanggal' => $r['tgl_rapel'],
                'nama' => $r['ket_rapel'],
                'jumlah' => $nominal
            ];
        }

        // ================= 3b) Hitung Pinjaman berdasarkan jatuh tempo =================
        $total_potongan_pinjaman = 0;
        $pinjamanRows = [];
        $sqlPinjaman = "SELECT nominal_pinjaman, ket_pinjaman, tgl_bayar_pinjaman 
                FROM tb_pinjaman
                WHERE id_karyawan = '$id_karyawan'
                AND tgl_bayar_pinjaman BETWEEN '$startDate' AND '$endDate'";
        $qpin = mysqli_query($koneksi, $sqlPinjaman);
        while ($pin = mysqli_fetch_assoc($qpin)) {
            $nominal = floatval($pin['nominal_pinjaman']);
            $total_potongan_pinjaman += $nominal;
            $pinjamanRows[] = [
                'nama' => $pin['ket_pinjaman'],
                'jumlah' => $nominal,
                'tgl_jatuh_tempo' => $pin['tgl_bayar_pinjaman']
            ];
        }

        // ================= 4) Get Absen =================
        $absenRows = [];
        $sqlAbs = "SELECT id_absen, tgl_absen, jam_kerja, ket_absen
        FROM tb_absen
        WHERE id_karyawan='$id_karyawan'
        AND tgl_absen BETWEEN '$startDate' AND '$endDate'
        ORDER BY tgl_absen ASC";
        $qabs = mysqli_query($koneksi, $sqlAbs);
        while ($r = mysqli_fetch_assoc($qabs)) $absenRows[] = $r;

        // ================= 4b) Overlap days (izin, sakit, cuti) =================
        $izin_days  = getOverlapDays($koneksi, "tb_izin",  $id_karyawan, $startDate, $endDate, "tgl_awal_izin", "tgl_akhir_izin");
        $sakit_days = getOverlapDays($koneksi, "tb_sakit", $id_karyawan, $startDate, $endDate, "tgl_awal_sakit", "tgl_akhir_sakit");
        $cuti_days  = getOverlapDays($koneksi, "tb_cuti",  $id_karyawan, $startDate, $endDate, "tgl_awal_cuti", "tgl_akhir_cuti");

        // ================= 5) Hitung Absen =================
        $absenForUI = [];
        $hari_kerja_count = 0;
        $mangkir_count = 0;
        $off_count = 0;
        $total_lembur = 0;
        $total_overtime = 0;
        $total_spl_1_7 = 0;
        $total_spl_8 = 0;
        $total_spl_9_up = 0;

        $total_jam_wl = 0;
        $total_jam_ot = 0;

        $total_jam_spl_1_7 = 0;
        $total_jam_spl_8 = 0;
        $total_jam_spl_9_up = 0;

        $total_potongan_perjam = 0;
        $total_jam_kurang = 0;

        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            (new DateTime($endDate))->modify('+1 day')
        );

        $new_count = 0;
        $seenDates = []; // untuk mencegah duplikasi

        foreach ($period as $date) {
            $tgl = $date->format('Y-m-d');
            $found = false;

            $hariNama = strtolower(date('l', strtotime($tgl)));
            $normal_hours = ($hariNama === 'friday') ? 5 : 7;

            // Tandai sudah diproses
            $seenDates[$tgl] = true;

            // 1) Sebelum mulai karier = NEW
            if ($tgl < $tgl_mulai_karier) {
                $absenForUI[] = [
                    'tanggal' => $tgl,
                    'jam' => 0,
                    'wl' => 0,
                    'ot' => 0,
                    'ket' => 'NEW'
                ];
                $new_count++;
                continue;
            }

            // 2) Setelah tgl_selesai karier = OFF (jangan mangkir)
            if ($tgl_selesai_karier && $tgl > $tgl_selesai_karier) {
                $absenForUI[] = [
                    'tanggal' => $tgl,
                    'jam' => 0,
                    'wl' => 0,
                    'ot' => 0,
                    'ket' => 'OFF'
                ];
                $off_count++;
                continue;
            }

            // 3) Cek data absen yang tersedia
            foreach ($absenRows as $a) {
                if ($a['tgl_absen'] == $tgl) {
                    $found = true;
                    $jam = floatval($a['jam_kerja']);
                    $ket = strtoupper($a['ket_absen']);

                    // ===== LOGIKA SPL/WL/OVERTIME =====
                    if ($ket === 'OFF') {
                        // untuk OFF hitung SPL (sama seperti semula)
                        if ($hariNama === 'friday') {
                            $h1_5 = min(5, $jam);
                            $h6   = ($jam > 5 ? 1 : 0);
                            $h7up = max(0, $jam - 6);

                            $total_spl_1_7 += $h1_5 * floatval($karier['spl']);
                            $total_spl_8   += $h6 * floatval($karier['ot1_spl']);
                            $total_spl_9_up += $h7up * floatval($karier['ot2_spl']);

                            $total_jam_spl_1_7 += $h1_5;
                            $total_jam_spl_8   += $h6;
                            $total_jam_spl_9_up += $h7up;
                        } else {
                            $h1_7 = min(7, $jam);
                            $h8   = ($jam > 7 ? 1 : 0);
                            $h9up = max(0, $jam - 8);

                            $total_spl_1_7 += $h1_7 * floatval($karier['spl']);
                            $total_spl_8   += $h8 * floatval($karier['ot1_spl']);
                            $total_spl_9_up += $h9up * floatval($karier['ot2_spl']);

                            $total_jam_spl_1_7 += $h1_7;
                            $total_jam_spl_8   += $h8;
                            $total_jam_spl_9_up += $h9up;
                        }

                        $wl = 0;
                        $ot = 0;
                    } else {
                        // WL maksimal 1 jam, sisanya Overtime
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

                        // Potongan per jam (pot_izin tersedia di tb_karier)
                        if ($jam < $normal_hours && $jam > 0) {
                            $kurang = $normal_hours - $jam;
                            $total_jam_kurang += $kurang;
                            // bagi normal_hours untuk membagi proporsional
                            $total_potongan_perjam += $kurang * ($karier['pot_izin'] / $normal_hours);
                        }
                    }

                    $absenForUI[] = [
                        'tanggal' => $tgl,
                        'jam' => $jam,
                        'wl' => isset($wl) ? $wl : 0,
                        'ot' => isset($ot) ? $ot : 0,
                        'ket' => $ket
                    ];

                    if ($ket !== 'OFF' && $ket !== 'IZIN' && $ket !== 'SAKIT' && $ket !== 'CUTI' && $ket !== 'MANGKIR') {
                        $hari_kerja_count++;
                    }
                    break;
                }
            }

            if ($found) continue;

            // 4) Cek izin, sakit, cuti
            $types = [
                ['table' => 'tb_izin',  'start' => 'tgl_awal_izin', 'end' => 'tgl_akhir_izin', 'ket' => 'IZIN'],
                ['table' => 'tb_sakit', 'start' => 'tgl_awal_sakit', 'end' => 'tgl_akhir_sakit', 'ket' => 'SAKIT'],
                ['table' => 'tb_cuti',  'start' => 'tgl_awal_cuti', 'end' => 'tgl_akhir_cuti', 'ket' => 'CUTI'],
            ];

            foreach ($types as $t) {
                $sql = "SELECT 1 FROM {$t['table']} 
                    WHERE id_karyawan='$id_karyawan' 
                    AND '$tgl' BETWEEN {$t['start']} AND {$t['end']} LIMIT 1";
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
        } // end foreach period

        // ================= Hitung total pendapatan & potongan =================
        $basic = floatval($karier['gaji_pokok']);

        // t_hidup/t_daerah/t_makan/t_skil sudah dipindah ke tb_tunjangan,
        // untuk backward-compatibility set 0 (nilai sebenarnya sudah di $total_tunjangan)
        $t_hidup = 0;
        $t_daerah = 0;
        $t_makan = 0;
        $t_skil = 0;

        // nilai bpjs/jht/pph21 diambil dari tb_potongan
        // ($bpjs_kesehatan, $jht, $pph21) sudah dihitung sebelumnya

        $pot_sio = floatval($karier['pot_sio'] ?? 0);
        $pot_pkwt = floatval($karier['pot_pkwt'] ?? 0);

        $total_pot_izin = floatval($karier['pot_izin']) * $izin_days;
        $total_pot_mangkir = floatval($karier['pot_mangkir']) * $mangkir_count;

        // ================= PKWT: gunakan jumlah hari normal selama kontrak berjalan (Mon-Fri)
        // Hitung interval kontrak yang berlaku di periode ini:
        $contract_start = max($startDate, $tgl_mulai_karier);
        $contract_end = $endDate;
        if ($tgl_selesai_karier && $tgl_selesai_karier < $contract_end) {
            $contract_end = $tgl_selesai_karier;
        }

        // Pastikan tanggal valid
        $normal_contract_days = 0;
        if (strtotime($contract_start) <= strtotime($contract_end)) {
            $periodContract = new DatePeriod(
                new DateTime($contract_start),
                new DateInterval('P1D'),
                (new DateTime($contract_end))->modify('+1 day')
            );
            foreach ($periodContract as $d) {
                $dow = strtolower($d->format('l'));
                // Hitung hanya hari kerja biasa: Monday - Friday (exclude Saturday, Sunday)
                if ($dow !== 'saturday' && $dow !== 'sunday') {
                    $normal_contract_days++;
                }
            }
        }

        // PKWT potongan berdasarkan (25 - jumlah hari normal kontrak)
        $hari_tidak_bekerja = 25 - $normal_contract_days;
        if ($hari_tidak_bekerja < 0) $hari_tidak_bekerja = 0;

        $total_potongan_pkwt = 0;
        if ($hari_tidak_bekerja > 0) {
            $total_potongan_pkwt = (($basic + $total_tunjangan) / 25) * $hari_tidak_bekerja;
        }

        $total_potongan =
            $total_potongan_tunjangan +   // (BPJS,JHT,PPH21,dll) dari tb_potongan
            $total_potongan_pinjaman +
            $total_pot_izin +
            $total_pot_mangkir +
            $total_potongan_perjam +
            $total_potongan_pkwt;

        $total_tunjangan = $total_tunjangan ?? 0;

        // ================= Hitung total pendapatan =================
        $total_pendapatan =
            $basic +
            $total_tunjangan +      // semua tunjangan yang ada di tb_tunjangan
            $total_lembur +
            $total_overtime +
            $total_spl_1_7 +
            $total_spl_8 +
            $total_spl_9_up +
            $total_rapel;

        $gaji_bersih = $total_pendapatan - $total_potongan;

        // ================= Build response =================
        return [
            'nik' => $nik,
            'nama' => $k['nama_lengkap'],
            'jabatan' => $karier['nama_jabatan'] ?? '',
            'period' => "$startDate - $endDate",
            'new' => $new_count,
            'rapel' => $rapelRows,
            'total_rapel' => $total_rapel,

            'basic' => $basic,
            // keep legacy keys but values are moved into tunjangan list
            't_hidup' => $t_hidup,
            't_daerah' => $t_daerah,
            't_makan' => $t_makan,
            't_skil' => $t_skil,

            'total_lembur' => $total_lembur,
            'total_overtime' => $total_overtime,
            'total_spl_1_7' => $total_spl_1_7,
            'total_spl_8' => $total_spl_8,
            'total_spl_9_up' => $total_spl_9_up,

            'total_jam_wl' => $total_jam_wl,
            'total_jam_ot' => $total_jam_ot,

            'total_jam_spl_1_7' => $total_jam_spl_1_7,
            'total_jam_spl_8' => $total_jam_spl_8,
            'total_jam_spl_9_up' => $total_jam_spl_9_up,

            // potongan spesifik hasil ekstraksi dari tb_potongan
            'bpjs_kesehatan' => $bpjs_kesehatan,
            'jht' => $jht,
            'pph21' => $pph21,
            'pot_sio' => $pot_sio,
            'pot_pkwt' => $total_potongan_pkwt,

            'pot_izin' => $total_pot_izin,
            'pot_mangkir' => $total_pot_mangkir,
            'total_pendapatan' => $total_pendapatan,
            'total_potongan' => $total_potongan,
            'gaji_bersih' => $gaji_bersih,
            'hari_kerja' => $hari_kerja_count,
            'izin' => $izin_days,
            'sakit' => $sakit_days,
            'cuti' => $cuti_days,

            'tunjangan_pendapatan' => $tunjanganRows,
            'total_tunjangan_pendapatan' => $total_tunjangan,
            'potongan_tunjangan' => $potonganRows,
            'total_potongan_tunjangan' => $total_potongan_tunjangan,

            'pinjaman' => $pinjamanRows,
            'total_potongan_pinjaman' => $total_potongan_pinjaman,
            'mangkir' => $mangkir_count,
            'off' => $off_count,
            'total_jam_kurang' => $total_jam_kurang,
            'potongan_perjam' => $total_potongan_perjam,
            'absen' => $absenForUI,
            // tambahan info untuk debugging/cek
            'normal_contract_days' => $normal_contract_days,
            'hari_tidak_bekerja' => $hari_tidak_bekerja
        ];
    }
}

// ================= Panggil function (contoh) =================
// $input = json_decode(file_get_contents('php://input'), true);
// $nik = $input['nik'] ?? null;
// $startDate = $input['startDate'] ?? null;
// $endDate = $input['endDate'] ?? null;
//
// if (!$nik || !$startDate || !$endDate) {
//     http_response_code(400);
//     echo json_encode(['error' => 'nik, startDate, endDate wajib']);
//     exit;
// }
//
// $response = generateSlipGaji($nik, $startDate, $endDate);
// echo json_encode($response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
