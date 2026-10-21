<?php
// slip_helper_kontrak.php — HELPER perhitungan slip gaji KARYAWAN KONTRAK (bulanan).
//
// gaji_pokok di tb_karier = GAJI BULANAN TETAP.
// Rumus:
//   total_pendapatan = basic(bulanan) + tunjangan + lembur + overtime + SPL + rapel
//   gaji_bersih      = total_pendapatan - total_potongan
//   Potongan absen (izin/sakit/cuti/mangkir/NEW/RESIGN) = basic / 25 per hari.
//
// Cara pakai:
//   global $koneksi; // PDO
//   require_once __DIR__ . '/slip_helper_kontrak.php';
//   $slip = generateSlipGajiByIdKontrak($id_karyawan, $startDate, $endDate);
//   atau $slip = generateSlipGajiKontrak($nik, $startDate, $endDate);
//
// Return: array slip canonical atau ['error'=>...] / ['skip'=>true,...].

if (!function_exists('hitungSlipGajiKontrak')) {
    /**
     * Komputasi slip gaji KONTRAK dari dataset mentah (tanpa query database).
     *
     * @param array $k         Data tb_karyawan (termasuk tipe_karyawan, nik_karyawan, nama_lengkap)
     * @param array $karier    Data tb_karier terbaru (termasuk gaji_pokok, nama_jabatan, id_karier, tgl_mulai, tgl_selesai)
     * @param array $tunjanganRawRows  tb_tunjangan
     * @param array $potonganRows      tb_potongan
     * @param array $rapelRows         tb_rapel
     * @param array $pinjamanRows      tb_pinjaman
     * @param array $potonganLainRows  tb_potongan_tidak_tetap
     * @param array $absenRows         tb_absen (key = tgl_absen)
     * @param string $startDate YYYY-MM-DD
     * @param string $endDate   YYYY-MM-DD
     * @return array
     */
    function hitungSlipGajiKontrak($k, $karier, $tunjanganRawRows, $potonganRows, $rapelRows, $pinjamanRows, $potonganLainRows, $absenRows, $startDate, $endDate)
    {
        $gaji_pokok = $karier['gaji_pokok'] ?? null;
        if ($gaji_pokok === null || $gaji_pokok === '' || (float)$gaji_pokok <= 0) {
            return [
                'error' => 'Slip gaji tidak dapat dibuat karena gaji pokok karyawan belum terisi.'
            ];
        }

        $tgl_mulai_karier  = $karier['tgl_mulai'];
        $tgl_selesai_karier = $karier['tgl_selesai']; // bisa NULL

        // Normal hours per hari dari jam kerja tipe karyawan (default 7, Jumat 5)
        $normal_hours_default = 7;
        $normal_hours_friday = 5;
        if (!empty($k['jam_kerja_mulai']) && !empty($k['jam_kerja_selesai'])) {
            $jm = strtotime($k['jam_kerja_mulai']);
            $js = strtotime($k['jam_kerja_selesai']);
            $diff_hours = ($js - $jm) / 3600;
            if ($diff_hours > 0) {
                $normal_hours_default = (float)$diff_hours;
                $normal_hours_friday = max(1, (float)$diff_hours - 2); // Jumat pulang lebih awal 2 jam
            }
        }

        // Base lembur (kolom sudah dihapus dari DB, aman jika tidak ada)
        $base_lembur = floatval($karier['lembur'] ?? 0);

        // Ekstrak BPJS / JHT / PPH dari potonganRows (jika ada)
        $bpjs_kesehatan = 0;
        $jht = 0;
        $pph21 = 0;
        $total_potongan_tunjangan = 0;
        foreach ($potonganRows as $pr) {
            $jumlah = floatval($pr['jumlah'] ?? 0);
            $total_potongan_tunjangan += $jumlah;
            $n = strtolower((string)($pr['nama'] ?? ''));
            if (strpos($n, 'bpjs') !== false) {
                $bpjs_kesehatan += $jumlah;
            } elseif (strpos($n, 'jht') !== false) {
                $jht += $jumlah;
            } elseif (strpos($n, 'pph') !== false) {
                $pph21 += $jumlah;
            }
        }

        $total_rapel = array_sum(array_column($rapelRows, 'jumlah'));
        $total_potongan_pinjaman = array_sum(array_column($pinjamanRows, 'jumlah'));
        $total_potongan_lainnya = array_sum(array_column($potonganLainRows, 'jumlah'));

        // Hitung izin/sakit/cuti langsung dari absen
        $izin_days = 0;
        $sakit_days = 0;
        $cuti_days = 0;
        foreach ($absenRows as $r) {
            $ketRawUpper = strtoupper(trim((string)($r['ket_absen'] ?? '')));
            if ($ketRawUpper === 'IZIN')  $izin_days++;
            if ($ketRawUpper === 'SAKIT') $sakit_days++;
            if ($ketRawUpper === 'CUTI')  $cuti_days++;
        }

        // ================= Loop absen per hari =================
        $absenForUI = [];
        $hari_kerja_count = 0;
        $mangkir_count = 0;
        $resign_count = 0;
        $new_count = 0;

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

        foreach ($period as $date) {
            $tgl = $date->format('Y-m-d');
            $hariNama = strtolower(date('l', strtotime($tgl)));
            $normal_hours = ($hariNama === 'friday') ? 5 : 7;

            if (isset($absenRows[$tgl])) {
                $ketAbsen = strtoupper(trim((string)($absenRows[$tgl]['ket_absen'] ?? '')));
                $jamRaw   = trim((string)($absenRows[$tgl]['jam_kerja'] ?? ''));
                $jam      = is_numeric($jamRaw) ? floatval($jamRaw) : 0;

                if ($ketAbsen === 'NEW') {
                    $absenForUI[] = ['tanggal' => $tgl, 'jam' => 0, 'wl' => 0, 'ot' => 0, 'ket' => 'NEW'];
                    $new_count++;
                    continue;
                }

                if ($ketAbsen === 'RESIGN' || $ketAbsen === 'R') {
                    $absenForUI[] = ['tanggal' => $tgl, 'jam' => 0, 'wl' => 0, 'ot' => 0, 'ket' => 'RESIGN'];
                    $resign_count++;
                    continue;
                }

                if (in_array($ketAbsen, ['IZIN', 'SAKIT', 'CUTI'], true)) {
                    $absenForUI[] = ['tanggal' => $tgl, 'jam' => 0, 'wl' => 0, 'ot' => 0, 'ket' => $ketAbsen];
                    continue;
                }

                if ($ketAbsen === 'OFF' && $jam == 0) {
                    $absenForUI[] = ['tanggal' => $tgl, 'jam' => 0, 'wl' => 0, 'ot' => 0, 'ket' => 'OFF'];
                    continue;
                }

                if ($jam > 0 && (strpos($ketAbsen, 'OFF') !== false || strpos($ketAbsen, 'SPL') !== false || strpos($ketAbsen, 'LEMBUR') !== false)) {
                    $h1_7 = min($normal_hours_default, $jam);
                    $h8   = ($jam > $normal_hours_default) ? min(1, $jam - $normal_hours_default) : 0;
                    $h9up = max(0, $jam - ($normal_hours_default + 1));

                    $total_spl_1_7  += $h1_7 * ($base_lembur * 2);
                    $total_spl_8    += $h8   * ($base_lembur * 3);
                    $total_spl_9_up += $h9up * ($base_lembur * 4);

                    $total_jam_spl_1_7  += $h1_7;
                    $total_jam_spl_8    += $h8;
                    $total_jam_spl_9_up += $h9up;

                    $absenForUI[] = ['tanggal' => $tgl, 'jam' => $jam, 'wl' => 0, 'ot' => 0, 'ket' => 'SPL'];
                    continue;
                }

                if ($jam > 0) {
                    $wl = 0;
                    $ot = 0;

                    if ($hariNama === 'friday') {
                        $wl = min(1, max(0, $jam - $normal_hours_friday));
                        $ot = max(0, $jam - $normal_hours_friday - $wl);
                    } else {
                        $wl = min(1, max(0, $jam - $normal_hours_default));
                        $ot = max(0, $jam - $normal_hours_default - $wl);
                    }

                    $total_lembur   += $wl * ($base_lembur * 1.5);
                    $total_overtime += $ot * ($base_lembur * 2.0);
                    $total_jam_wl   += $wl;
                    $total_jam_ot   += $ot;

                    // Potongan jika jam kerja kurang dari normal
                    if ($jam < $normal_hours_default) {
                        $kurang = $normal_hours_default - $jam;
                        $total_jam_kurang += $kurang;
                        $total_potongan_perjam += $kurang * (floatval($karier['pot_izin'] ?? 0) / $normal_hours_default);
                    }

                    $hari_kerja_count++;

                    $absenForUI[] = ['tanggal' => $tgl, 'jam' => $jam, 'wl' => $wl, 'ot' => $ot, 'ket' => 'HADIR'];
                    continue;
                }
            }

            // Tidak ada data absen
            if (!empty($tgl_selesai_karier) && $tgl > $tgl_selesai_karier) {
                $resign_count++;
                $absenForUI[] = ['tanggal' => $tgl, 'jam' => 0, 'wl' => 0, 'ot' => 0, 'ket' => 'RESIGN'];
                continue;
            }

            $mangkir_count++;
            $absenForUI[] = ['tanggal' => $tgl, 'jam' => 0, 'wl' => 0, 'ot' => 0, 'ket' => 'MANGKIR'];
        }

        // ================= Tunjangan dinamis =================
        $tunjanganRows = [];
        $total_tunjangan = 0;
        $nik_khusus = ['TJS-00168', 'TJS-00169', 'TJS-00245', 'TJS-00275'];
        $is_khusus = in_array($k['nik_karyawan'] ?? '', $nik_khusus, true);

        if ($is_khusus) {
            $total_ketidakhadiran = $sakit_days + $izin_days + $mangkir_count + $new_count + $resign_count;
            $batas_absen = 5;
        } else {
            $total_ketidakhadiran = $sakit_days + $izin_days + $cuti_days + $mangkir_count + $new_count + $resign_count;
            $batas_absen = 3;
        }

        $potongan_cuti_khusus = 0;

        foreach ($tunjanganRawRows as $tr) {
            $nama_t = (string)($tr['nama_tunjangan'] ?? '');
            $jumlah_t = floatval($tr['jumlah_tunjangan'] ?? 0);

            if (stripos($nama_t, 'makan') !== false) {
                $jumlah_t = $jumlah_t * $hari_kerja_count;
            }

            if (stripos($nama_t, 'performance') !== false || stripos($nama_t, 'kehadiran') !== false) {
                $nama_t = "Bonus Kehadiran";
                $original_bonus = $jumlah_t;

                if ($is_khusus && $cuti_days > 0) {
                    $satuan_potongan_cuti = $original_bonus / 12;
                    $potongan_cuti_khusus = $satuan_potongan_cuti * $cuti_days;
                }

                if ($total_ketidakhadiran > $batas_absen) {
                    $jumlah_t = 0;
                    $nama_t .= " (Hangus: Absen > $batas_absen Hari)";
                }
            }

            $tunjanganRows[] = ['nama' => $nama_t, 'jumlah' => $jumlah_t];
            $total_tunjangan += $jumlah_t;
        }

        // ================= Perhitungan gaji pokok & potongan (KONTRAK: bulanan) =================
        $basic = (float)$gaji_pokok;

        // Potongan izin/mangkir per hari (pakai pot_izin/pot_mangkir jika ada, else basic/25)
        $pot_izin_rate  = floatval($karier['pot_izin'] ?? 0);
        $pot_mangkir_rate = floatval($karier['pot_mangkir'] ?? 0);
        if ($pot_izin_rate <= 0)  $pot_izin_rate = $basic / 25;
        if ($pot_mangkir_rate <= 0) $pot_mangkir_rate = $basic / 25;

        $total_pot_izin  = $pot_izin_rate * $izin_days;
        $total_pot_mangkir = $pot_mangkir_rate * $mangkir_count;

        // PKWT potongan untuk hari NEW / RESIGN
        $total_potongan_pkwt = 0;
        if ($new_count > 0) {
            $total_potongan_pkwt = (($total_tunjangan + $basic) / 25) * $new_count;
        }
        if ($resign_count > 0) {
            $total_potongan_pkwt += (($total_tunjangan + $basic) / 25) * $resign_count;
        }

        $total_potongan =
            $total_potongan_tunjangan +
            $total_potongan_pinjaman +
            $total_potongan_lainnya +
            $total_pot_izin +
            $total_pot_mangkir +
            $total_potongan_perjam +
            $total_potongan_pkwt +
            $potongan_cuti_khusus;

        // ================= Total pendapatan =================
        $total_pendapatan =
            $basic +
            $total_tunjangan +
            $total_lembur +
            $total_overtime +
            $total_spl_1_7 +
            $total_spl_8 +
            $total_spl_9_up +
            $total_rapel;

        $gaji_bersih = $total_pendapatan - $total_potongan;

        return [
            'nik' => $k['nik_karyawan'],
            'nama' => $k['nama_lengkap'],
            'jabatan' => $karier['nama_jabatan'] ?? '',
            'period' => "$startDate - $endDate",
            'new' => $new_count,
            'resign' => $resign_count,
            'rapel' => $rapelRows,
            'total_rapel' => $total_rapel,

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
            'potongan_lain' => $potonganLainRows,
            'total_potongan_lainnya' => $total_potongan_lainnya,
            'mangkir' => $mangkir_count,
            'total_jam_kurang' => $total_jam_kurang,
            'potongan_perjam' => $total_potongan_perjam,
            'potongan_cuti_khusus' => $potongan_cuti_khusus,
            'absen' => $absenForUI
        ];
    }
}

if (!function_exists('generateSlipGajiByIdKontrak')) {
    function generateSlipGajiByIdKontrak($id_karyawan, $startDate, $endDate)
    {
        global $koneksi;

        $id_karyawan = (int)$id_karyawan;
        if ($id_karyawan <= 0) return ['error' => 'ID karyawan tidak valid'];

        $d1 = DateTime::createFromFormat('!Y-m-d', (string)$startDate);
        $d2 = DateTime::createFromFormat('!Y-m-d', (string)$endDate);
        if (!$d1 || !$d2 || $d1 > $d2) {
            return ['error' => 'Rentang periode tidak valid (YYYY-MM-DD).'];
        }
        $startDate = $d1->format('Y-m-d');
        $endDate   = $d2->format('Y-m-d');

        // 1) Get Karyawan + info tipe (jam kerja)
        try {
            $stmt = $koneksi->prepare("SELECT k.*, t.tgl_keluar, tp.nama_tipe, tp.jam_kerja_mulai, tp.jam_kerja_selesai
                FROM tb_karyawan k
                LEFT JOIN tb_tereliminasi t ON k.id_karyawan = t.id_karyawan
                LEFT JOIN tb_tipe_karyawan tp ON k.id_tipe_karyawan = tp.id_tipe_karyawan
                WHERE k.id_karyawan = ? LIMIT 1");
            $stmt->execute([$id_karyawan]);
            $k = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak karyawan: ' . $e->getMessage());
            return ['error' => 'Gagal mengambil data karyawan'];
        }
        if (!$k) return ['error' => 'Karyawan tidak ditemukan'];

        if ($k['status_karyawan'] !== 'Aktif' && !empty($k['tgl_keluar'])) {
            if (strtotime($k['tgl_keluar']) < strtotime($startDate)) {
                return ['skip' => true, 'error' => 'Karyawan telah keluar sebelum rentang periode ini.'];
            }
        }

        $id_karyawan = (int)$k['id_karyawan'];

        // 2) Get Karier terbaru
        try {
            $stmt = $koneksi->prepare("SELECT kar.*, jab.nama_jabatan
            FROM tb_karier kar
            LEFT JOIN tb_jabatan jab ON kar.id_jabatan = jab.id_jabatan
            WHERE kar.id_karyawan = ?
            ORDER BY kar.tgl_mulai DESC, kar.id_karier DESC LIMIT 1");
            $stmt->execute([$id_karyawan]);
            $karier = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak karier: ' . $e->getMessage());
            return ['error' => 'Gagal mengambil data karier'];
        }
        if (!$karier) return ['error' => 'Data karier karyawan tidak ditemukan'];

        $id_karier = (int)$karier['id_karier'];

        // 3) Tunjangan
        $tunjanganRawRows = [];
        try {
            $stmt = $koneksi->prepare("SELECT nama_tunjangan, jumlah_tunjangan FROM tb_tunjangan WHERE id_karier = ?");
            $stmt->execute([$id_karier]);
            while ($tr = $stmt->fetch()) {
                $tunjanganRawRows[] = $tr;
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak tunjangan: ' . $e->getMessage());
        }

        // 4) Potongan tetap
        $potonganRows = [];
        try {
            $stmt = $koneksi->prepare("SELECT jenis_potongan AS nama, jumlah_potongan AS jumlah FROM tb_potongan WHERE id_karier = ?");
            $stmt->execute([$id_karier]);
            while ($p = $stmt->fetch()) {
                $potonganRows[] = ['nama' => $p['nama'], 'jumlah' => floatval($p['jumlah'])];
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak potongan: ' . $e->getMessage());
        }

        // 5) Rapel
        $rapelRows = [];
        try {
            $stmt = $koneksi->prepare("SELECT tgl_rapel AS tanggal, ket_rapel AS nama, nominal_rapel AS jumlah
                FROM tb_rapel WHERE id_karyawan = ? AND tgl_rapel BETWEEN ? AND ?");
            $stmt->execute([$id_karyawan, $startDate, $endDate]);
            while ($r = $stmt->fetch()) {
                $rapelRows[] = ['tanggal' => $r['tanggal'], 'nama' => $r['nama'], 'jumlah' => floatval($r['jumlah'])];
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak rapel: ' . $e->getMessage());
        }

        // 6) Pinjaman
        $pinjamanRows = [];
        try {
            $stmt = $koneksi->prepare("SELECT ket_pinjaman AS nama, nominal_pinjaman AS jumlah, tgl_bayar_pinjaman AS tgl_jatuh_tempo
                FROM tb_pinjaman WHERE id_karyawan = ? AND tgl_bayar_pinjaman BETWEEN ? AND ?");
            $stmt->execute([$id_karyawan, $startDate, $endDate]);
            while ($pin = $stmt->fetch()) {
                $pinjamanRows[] = ['nama' => $pin['nama'], 'jumlah' => floatval($pin['jumlah']), 'tgl_jatuh_tempo' => $pin['tgl_jatuh_tempo']];
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak pinjaman: ' . $e->getMessage());
        }

        // 7) Potongan tidak tetap
        $potonganLainRows = [];
        try {
            $stmt = $koneksi->prepare("SELECT jenis_potongan, ket_potongan, nominal_potongan AS jumlah, tgl_potongan AS tgl_potong
                FROM tb_potongan_tidak_tetap WHERE id_karyawan = ? AND tgl_potongan BETWEEN ? AND ?");
            $stmt->execute([$id_karyawan, $startDate, $endDate]);
            while ($potlain = $stmt->fetch()) {
                $namaPotongan = $potlain['jenis_potongan'];
                $ket = trim((string)$potlain['ket_potongan']);
                if (!empty($ket)) $namaPotongan .= " (" . $ket . ")";
                $potonganLainRows[] = ['nama' => $namaPotongan, 'jumlah' => floatval($potlain['jumlah']), 'tgl_potong' => $potlain['tgl_potong']];
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak potongan_tidak_tetap: ' . $e->getMessage());
        }

        // 8) Absen
        $absenRows = [];
        try {
            $stmt = $koneksi->prepare("SELECT tgl_absen, jam_kerja, ket_absen
                FROM tb_absen WHERE id_karyawan = ? AND tgl_absen BETWEEN ? AND ? ORDER BY tgl_absen ASC");
            $stmt->execute([$id_karyawan, $startDate, $endDate]);
            while ($r = $stmt->fetch()) {
                $absenRows[$r['tgl_absen']] = $r;
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak absen: ' . $e->getMessage());
        }

        return hitungSlipGajiKontrak($k, $karier, $tunjanganRawRows, $potonganRows, $rapelRows, $pinjamanRows, $potonganLainRows, $absenRows, $startDate, $endDate);
    }
}

if (!function_exists('generateSlipGajiBatchKontrak')) {
    function generateSlipGajiBatchKontrak(array $id_karyawans, $startDate, $endDate)
    {
        global $koneksi;

        $ids = array_values(array_unique(array_filter(array_map('intval', $id_karyawans), fn($v) => $v > 0)));
        if (empty($ids)) return [];
        $in = implode(',', $ids);

        $d1 = DateTime::createFromFormat('!Y-m-d', (string)$startDate);
        $d2 = DateTime::createFromFormat('!Y-m-d', (string)$endDate);
        if (!$d1 || !$d2 || $d1 > $d2) return [];
        $startDate = $d1->format('Y-m-d');
        $endDate   = $d2->format('Y-m-d');

        $out = [];

        // 1) Karyawan + tgl_keluar
        $karyawan = [];
        try {
            $stmt = $koneksi->prepare("SELECT k.*, t.tgl_keluar FROM tb_karyawan k LEFT JOIN tb_tereliminasi t ON k.id_karyawan = t.id_karyawan WHERE k.id_karyawan IN ($in)");
            $stmt->execute();
            while ($r = $stmt->fetch()) $karyawan[(int)$r['id_karyawan']] = $r;
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak batch karyawan: ' . $e->getMessage());
            return [];
        }

        // 2) Karier terbaru per karyawan
        $karierByKaryawan = [];
        try {
            $stmt = $koneksi->prepare("SELECT kar.*, jab.nama_jabatan FROM tb_karier kar LEFT JOIN tb_jabatan jab ON kar.id_jabatan = jab.id_jabatan WHERE kar.id_karyawan IN ($in) ORDER BY kar.id_karyawan ASC, kar.tgl_mulai DESC, kar.id_karier DESC");
            $stmt->execute();
            while ($r = $stmt->fetch()) {
                $kid = (int)$r['id_karyawan'];
                if (!isset($karierByKaryawan[$kid])) $karierByKaryawan[$kid] = $r;
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak batch karier: ' . $e->getMessage());
        }

        // Filter skip/error
        $karierIds = [];
        foreach ($ids as $id) {
            if (!isset($karyawan[$id])) {
                $out[$id] = ['error' => 'Karyawan tidak ditemukan'];
                continue;
            }
            $k = $karyawan[$id];
            if ($k['status_karyawan'] !== 'Aktif' && !empty($k['tgl_keluar'])) {
                if (strtotime($k['tgl_keluar']) < strtotime($startDate)) {
                    $out[$id] = ['skip' => true, 'error' => 'Karyawan telah keluar sebelum rentang periode ini.'];
                    continue;
                }
            }
            if (!isset($karierByKaryawan[$id])) {
                $out[$id] = ['error' => 'Data karier karyawan tidak ditemukan'];
                continue;
            }
            $karierIds[] = (int)$karierByKaryawan[$id]['id_karier'];
        }
        $karierIn = $karierIds ? implode(',', array_unique($karierIds)) : '0';

        // 3) Tunjangan per karier
        $tunjByKarier = [];
        if ($karierIn !== '0') {
            try {
                $stmt = $koneksi->prepare("SELECT id_karier, nama_tunjangan, jumlah_tunjangan FROM tb_tunjangan WHERE id_karier IN ($karierIn) ORDER BY id_karier ASC, id_tunjangan ASC");
                $stmt->execute();
                while ($tr = $stmt->fetch()) {
                    $tunjByKarier[(int)$tr['id_karier']][] = ['nama_tunjangan' => $tr['nama_tunjangan'], 'jumlah_tunjangan' => $tr['jumlah_tunjangan']];
                }
            } catch (PDOException $e) {
                error_log('slip_helper_kontrak batch tunjangan: ' . $e->getMessage());
            }
        }

        // 4) Potongan per karier
        $potByKarier = [];
        if ($karierIn !== '0') {
            try {
                $stmt = $koneksi->prepare("SELECT id_karier, jenis_potongan, jumlah_potongan FROM tb_potongan WHERE id_karier IN ($karierIn) ORDER BY id_karier ASC, id_potongan ASC");
                $stmt->execute();
                while ($pr = $stmt->fetch()) {
                    $potByKarier[(int)$pr['id_karier']][] = ['nama' => $pr['jenis_potongan'], 'jumlah' => floatval($pr['jumlah_potongan'])];
                }
            } catch (PDOException $e) {
                error_log('slip_helper_kontrak batch potongan: ' . $e->getMessage());
            }
        }

        // 5) Rapel
        $rapelByKaryawan = [];
        try {
            $stmt = $koneksi->prepare("SELECT id_karyawan, tgl_rapel, nominal_rapel, ket_rapel FROM tb_rapel WHERE id_karyawan IN ($in) AND tgl_rapel BETWEEN ? AND ? ORDER BY id_karyawan ASC, id_rapel ASC");
            $stmt->execute([$startDate, $endDate]);
            while ($r = $stmt->fetch()) {
                $rapelByKaryawan[(int)$r['id_karyawan']][] = ['tanggal' => $r['tgl_rapel'], 'nama' => $r['ket_rapel'], 'jumlah' => floatval($r['nominal_rapel'])];
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak batch rapel: ' . $e->getMessage());
        }

        // 6) Pinjaman
        $pinByKaryawan = [];
        try {
            $stmt = $koneksi->prepare("SELECT id_karyawan, nominal_pinjaman, ket_pinjaman, tgl_bayar_pinjaman FROM tb_pinjaman WHERE id_karyawan IN ($in) AND tgl_bayar_pinjaman BETWEEN ? AND ? ORDER BY id_karyawan ASC, id_pinjaman ASC");
            $stmt->execute([$startDate, $endDate]);
            while ($r = $stmt->fetch()) {
                $pinByKaryawan[(int)$r['id_karyawan']][] = ['nama' => $r['ket_pinjaman'], 'jumlah' => floatval($r['nominal_pinjaman']), 'tgl_jatuh_tempo' => $r['tgl_bayar_pinjaman']];
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak batch pinjaman: ' . $e->getMessage());
        }

        // 7) Potongan tidak tetap
        $potLainByKaryawan = [];
        try {
            $stmt = $koneksi->prepare("SELECT id_karyawan, nominal_potongan, jenis_potongan, tgl_potongan, ket_potongan FROM tb_potongan_tidak_tetap WHERE id_karyawan IN ($in) AND tgl_potongan BETWEEN ? AND ? ORDER BY id_karyawan ASC, id_potongan_tidak_tetap ASC");
            $stmt->execute([$startDate, $endDate]);
            while ($r = $stmt->fetch()) {
                $namaPotongan = $r['jenis_potongan'];
                $ket = trim((string)$r['ket_potongan']);
                if ($ket !== '') $namaPotongan .= " (" . $ket . ")";
                $potLainByKaryawan[(int)$r['id_karyawan']][] = ['nama' => $namaPotongan, 'jumlah' => floatval($r['nominal_potongan']), 'tgl_potong' => $r['tgl_potongan']];
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak batch potongan_tidak_tetap: ' . $e->getMessage());
        }

        // 8) Absen
        $absenByKaryawan = [];
        try {
            $stmt = $koneksi->prepare("SELECT id_karyawan, tgl_absen, jam_kerja, ket_absen FROM tb_absen WHERE id_karyawan IN ($in) AND tgl_absen BETWEEN ? AND ? ORDER BY id_karyawan ASC, tgl_absen ASC");
            $stmt->execute([$startDate, $endDate]);
            while ($r = $stmt->fetch()) {
                $absenByKaryawan[(int)$r['id_karyawan']][$r['tgl_absen']] = $r;
            }
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak batch absen: ' . $e->getMessage());
        }

        // 9) Hitung per karyawan
        foreach ($ids as $id) {
            if (isset($out[$id])) continue;
            $kid_karier = (int)$karierByKaryawan[$id]['id_karier'];
            $out[$id] = hitungSlipGajiKontrak(
                $karyawan[$id],
                $karierByKaryawan[$id],
                $tunjByKarier[$kid_karier] ?? [],
                $potByKarier[$kid_karier] ?? [],
                $rapelByKaryawan[$id] ?? [],
                $pinByKaryawan[$id] ?? [],
                $potLainByKaryawan[$id] ?? [],
                $absenByKaryawan[$id] ?? [],
                $startDate,
                $endDate
            );
        }
        return $out;
    }
}

if (!function_exists('generateSlipGajiKontrak')) {
    function generateSlipGajiKontrak($nik, $startDate, $endDate)
    {
        global $koneksi;
        $nik = trim((string)$nik);
        if ($nik === '') return ['error' => 'NIK tidak valid'];
        try {
            $stmt = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = ? ORDER BY (status_karyawan='Aktif') DESC, id_karyawan DESC LIMIT 1");
            $stmt->execute([$nik]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('slip_helper_kontrak nik: ' . $e->getMessage());
            return ['error' => 'Gagal mencari karyawan'];
        }
        if (!$row) return ['error' => 'Karyawan tidak ditemukan'];
        return generateSlipGajiByIdKontrak((int)$row['id_karyawan'], $startDate, $endDate);
    }
}
