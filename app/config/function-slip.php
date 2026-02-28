<?php
// funtion-slip.php
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
        $tgl_selesai_karier = $karier['tgl_selesai']; // bisa NULL

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
        $sqlAbs = "SELECT tgl_absen, jam_kerja, ket_absen
           FROM tb_absen
           WHERE id_karyawan='$id_karyawan'
           AND tgl_absen BETWEEN '$startDate' AND '$endDate'
           ORDER BY tgl_absen ASC";
        $qabs = mysqli_query($koneksi, $sqlAbs);
        while ($r = mysqli_fetch_assoc($qabs)) {
            $absenRows[$r['tgl_absen']] = $r; // pakai index tanggal
        }

        // ================= Hitung izin/sakit/cuti langsung dari tb_absen =================
        $izin_days = 0;
        $sakit_days = 0;
        $cuti_days = 0;

        foreach ($absenRows as $r) {
            $ketRawUpper = strtoupper(trim((string)$r['ket_absen']));

            if ($ketRawUpper === 'IZIN')  $izin_days++;
            if ($ketRawUpper === 'SAKIT') $sakit_days++;
            if ($ketRawUpper === 'CUTI')  $cuti_days++;
        }

        // ================= 5) Hitung Absen =================
        $absenForUI = [];
        $hari_kerja_count = 0;
        $mangkir_count = 0;
        $resign_count = 0;

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

        $new_count = 0;

        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            (new DateTime($endDate))->modify('+1 day')
        );

        foreach ($period as $date) {

            $tgl = $date->format('Y-m-d');
            $hariNama = strtolower(date('l', strtotime($tgl)));
            $normal_hours = ($hariNama === 'friday') ? 5 : 7;

            // =========== NEW DI ABSEN SEBELUM TGL MASUK ===========
            // if ($tgl < $tgl_mulai_karier) {
            //     $isMinggu = ($hariNama === 'sunday');
            //     $absenForUI[] = [
            //         'tanggal' => $tgl,
            //         'jam' => 0,
            //         'wl' => 0,
            //         'ot' => 0,
            //         'ket' => 'NEW'
            //     ];
            //     // Hanya tambah count jika BUKAN hari minggu
            //     if (!$isMinggu) {
            //         $new_count++;
            //     }
            //     continue;
            // }

            // ================= ADA DATA ABSEN =================
            if (isset($absenRows[$tgl])) {

                $ketAbsen = strtoupper(trim((string)$absenRows[$tgl]['ket_absen']));
                $jamRaw   = trim((string)$absenRows[$tgl]['jam_kerja']);
                $jam      = is_numeric($jamRaw) ? floatval($jamRaw) : 0;

                // ===== NEW =====
                if ($ketAbsen === 'NEW') {
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

                // ===== RESIGN =====
                if ($ketAbsen === 'RESIGN' || $ketAbsen === 'R') {
                    $absenForUI[] = [
                        'tanggal' => $tgl,
                        'jam' => 0,
                        'wl' => 0,
                        'ot' => 0,
                        'ket' => 'RESIGN'
                    ];
                    $resign_count++; // Ini akan menambah total pemotong PKWT di bawah
                    continue;
                }

                // ===== IZIN / SAKIT / CUTI =====
                if (in_array($ketAbsen, ['IZIN', 'SAKIT', 'CUTI'])) {
                    $absenForUI[] = [
                        'tanggal' => $tgl,
                        'jam' => 0,
                        'wl' => 0,
                        'ot' => 0,
                        'ket' => $ketAbsen
                    ];
                    continue;
                }

                // ===== OFF MURNI (tidak ada jam kerja) =====
                if ($ketAbsen === 'OFF' && $jam == 0) {
                    $absenForUI[] = [
                        'tanggal' => $tgl,
                        'jam' => 0,
                        'wl' => 0,
                        'ot' => 0,
                        'ket' => 'OFF'
                    ];
                    continue;
                }

                // ===== SPL (ket OFF/SPL/LEMBUR dengan jam kerja) =====
                if (
                    $jam > 0 &&
                    (
                        strpos($ketAbsen, 'OFF') !== false ||
                        strpos($ketAbsen, 'SPL') !== false ||
                        strpos($ketAbsen, 'LEMBUR') !== false
                    )
                ) {

                    // SPL: Normal selalu 7 jam di semua hari (termasuk Jumat)
                    // OT1 jam ke-8, OT2 jam ke-9+
                    $h1_7 = min(7, $jam);
                    $h8   = ($jam > 7) ? min(1, $jam - 7) : 0;
                    $h9up = max(0, $jam - 8);

                    $total_spl_1_7  += $h1_7 * floatval($karier['spl']);
                    $total_spl_8    += $h8   * floatval($karier['ot1_spl']);
                    $total_spl_9_up += $h9up * floatval($karier['ot2_spl']);

                    $total_jam_spl_1_7  += $h1_7;
                    $total_jam_spl_8    += $h8;
                    $total_jam_spl_9_up += $h9up;

                    $absenForUI[] = [
                        'tanggal' => $tgl,
                        'jam' => $jam,
                        'wl' => 0,
                        'ot' => 0,
                        'ket' => 'SPL'
                    ];
                    continue;
                }

                // ===== HADIR (ket bukan OFF/SPL/LEMBUR/IZIN/SAKIT/CUTI) =====
                if ($jam > 0) {

                    $wl = 0;
                    $ot = 0;

                    if ($hariNama === 'friday') {
                        // Jumat: jam normal 5, WL jam ke-6, OT jam ke-7+
                        $wl = min(1, max(0, $jam - 5));
                        $ot = max(0, $jam - 5 - $wl);
                    } else {
                        // Hari biasa: jam normal 7, WL jam ke-8, OT jam ke-9+
                        $wl = min(1, max(0, $jam - 7));
                        $ot = max(0, $jam - 7 - $wl);
                    }

                    $total_lembur += $wl * floatval($karier['ot_pertama']);
                    $total_overtime += $ot * floatval($karier['ot_kedua']);
                    $total_jam_wl += $wl;
                    $total_jam_ot += $ot;

                    // Potongan jika jam kerja kurang dari normal
                    if ($jam < $normal_hours) {
                        $kurang = $normal_hours - $jam;
                        $total_jam_kurang += $kurang;
                        $total_potongan_perjam += $kurang * (floatval($karier['pot_izin']) / $normal_hours);
                    }

                    $hari_kerja_count++;

                    $absenForUI[] = [
                        'tanggal' => $tgl,
                        'jam' => $jam,
                        'wl' => $wl,
                        'ot' => $ot,
                        'ket' => 'HADIR'
                    ];
                    continue;
                }
            }

            // ================= TIDAK ADA DATA ABSEN =================
            // ================= CEK RESIGN =================
            if (!empty($tgl_selesai_karier) && $tgl > $tgl_selesai_karier) {
                $resign_count++;

                $absenForUI[] = [
                    'tanggal' => $tgl,
                    'jam' => 0,
                    'wl' => 0,
                    'ot' => 0,
                    'ket' => 'RESIGN'
                ];
                continue;
            }

            // Jika bukan resign, berarti mangkir
            $mangkir_count++;

            $absenForUI[] = [
                'tanggal' => $tgl,
                'jam' => 0,
                'wl' => 0,
                'ot' => 0,
                'ket' => 'MANGKIR'
            ];
        }

        // ================= Hitung total pendapatan & potongan =================
        $basic = floatval($karier['gaji_pokok']);
        
        $total_pot_izin = floatval($karier['pot_izin']) * $izin_days;
        $total_pot_mangkir = floatval($karier['pot_mangkir']) * $mangkir_count;

        // PKWT potongan untuk hari NEW
        $total_potongan_pkwt = 0;
        if ($new_count > 0) {
            $total_potongan_pkwt = (($total_tunjangan + $basic) / 28) * $new_count;
        }

        // PKWT potongan untuk hari RESIGN
        if ($resign_count > 0) {
            $total_potongan_pkwt += (($total_tunjangan + $basic) / 28) * $resign_count;
        }

        $total_potongan =
            $total_potongan_tunjangan +
            $total_potongan_pinjaman +
            $total_pot_izin +
            $total_pot_mangkir +
            $total_potongan_perjam +
            $total_potongan_pkwt;

        $total_tunjangan = $total_tunjangan ?? 0;

        // ===== Perhitungan kelebihan hari kerja (jika hari kerja > 25) =====
        // $kelebihan_hari = 0;
        // $pendapatan_kelebihan_hari = 0;
        // if ($hari_kerja_count > 25) {
        //     $kelebihan_hari = $hari_kerja_count - 25;
        //     $pendapatan_kelebihan_hari = $kelebihan_hari * ($basic / 25);
        // }

        // ================= Hitung total pendapatan =================
        $total_pendapatan =
            $basic +
            $total_tunjangan +
            $total_lembur +
            $total_overtime +
            $total_spl_1_7 +
            $total_spl_8 +
            $total_spl_9_up +
            $total_rapel;
        // $pendapatan_kelebihan_hari;

        $gaji_bersih = $total_pendapatan - $total_potongan;

        // ================= Build response =================
        return [
            'nik' => $nik,
            'nama' => $k['nama_lengkap'],
            'jabatan' => $karier['nama_jabatan'] ?? '',
            'period' => "$startDate - $endDate",
            'new' => $new_count,
            'resign' => $resign_count,
            'rapel' => $rapelRows,
            'total_rapel' => $total_rapel,

            // 'kelebihan_hari' => $kelebihan_hari,
            // 'pendapatan_kelebihan_hari' => $pendapatan_kelebihan_hari,

            'basic' => $basic,

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

            'bpjs_kesehatan' => $bpjs_kesehatan,
            'jht' => $jht,
            'pph21' => $pph21,
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
            'total_jam_kurang' => $total_jam_kurang,
            'potongan_perjam' => $total_potongan_perjam,
            'absen' => $absenForUI
        ];
    }
}
