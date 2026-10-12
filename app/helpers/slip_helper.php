<?php
// slip_helper.php — HELPER perhitungan slip gaji (satu-satunya sumber, DRY).
//
// RUMUS SEDERHANA (berlaku sejak penyederhanaan Okt 2026, revisi panjar):
//   gaji_bersih = total_nilai_kehadiran x gaji_pokok_harian - panjar
// - gaji_pokok di tb_karier dianggap UPAH HARIAN.
// - total_nilai_kehadiran = SUM(tb_absen.jam_kerja) untuk hari berbayar
//   (HADIR + SPL; nilai per hari 0/0.5/1/1.5/2 dari import PAYROLL ANTAM).
// - IZIN/SAKIT/CUTI/OFF(murni)/MANGKIR/ALPHA/RESIGN/NEW = tidak dibayar (0).
// - panjar = SUM(tb_potongan_tidak_tetap.nominal_potongan)
//   WHERE jenis_potongan='PANJAR' AND tgl_potongan BETWEEN start AND end.
// - Tunjangan, potongan tetap, lembur-multiplier, rapel, pinjaman, THR: DIABAIKAN (0).
// - Kolom overtime/lembur di tb_karier (lembur, ot_pertama, ot_kedua, spl,
//   ot1_spl, ot2_spl, pot_izin, pot_mangkir) SUDAH DIHAPUS dari DB.
//
// Cara pakai ulang di mana saja (controller / API / view), cukup:
//   global $koneksi; // pastikan koneksi PDO $koneksi sudah ada
//   require_once __DIR__ . '/../helpers/slip_helper.php'; // sesuaikan path relatif dari file pemanggil
//   $slip = generateSlipGajiById($id_karyawan, $startDate, $endDate); // by ID, atau
//   $slip = generateSlipGaji($nik, $startDate, $endDate);             // by NIK (wrapper)
//
// Return: array slip canonical (snake_case) atau ['error' => ...] / ['skip' => true, ...].
// Key lama dipertahankan (bernilai 0/array kosong) agar semua konsumen
// (get-payrol, get-slip, posting-payroll, slip print, API mobile) tetap jalan.
// File lama app/config/function-slip.php kini hanya shim yang me-require file ini.
// header('Content-Type: application/json; charset=utf-8');

if (!function_exists('generateSlipGajiById')) {
    // SATU-SATUNYA perhitungan slip gaji (DRY). Semua jalur (sistem + API) wajib pakai ini.
    function generateSlipGajiById($id_karyawan, $startDate, $endDate)
    {
        global $koneksi;

        $id_karyawan = (int)$id_karyawan;
        if ($id_karyawan <= 0) return ['error' => 'ID karyawan tidak valid'];
        $d1 = DateTime::createFromFormat('!Y-m-d', (string)$startDate);
        $d2 = DateTime::createFromFormat('!Y-m-d', (string)$endDate);
        if (!$d1 || !$d2 || $d1 > $d2) return ['error' => 'Rentang periode tidak valid (YYYY-MM-DD).'];
        $startDate = $d1->format('Y-m-d');
        $endDate = $d2->format('Y-m-d');

        // ================= 1) Get Karyawan =================
        try {
            $stmt = $koneksi->prepare("SELECT k.*, t.tgl_keluar
                FROM tb_karyawan k
                LEFT JOIN tb_tereliminasi t ON k.id_karyawan = t.id_karyawan
                WHERE k.id_karyawan = ? LIMIT 1");
            $stmt->execute([$id_karyawan]);
            $k = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('slip_helper karyawan: ' . $e->getMessage());
            return ['error' => 'Gagal mengambil data karyawan'];
        }

        if (!$k) return ['error' => 'Karyawan tidak ditemukan'];

        // Karyawan keluar sebelum periode -> skip (tetap dipertahankan)
        if ($k['status_karyawan'] !== 'Aktif' && !empty($k['tgl_keluar'])) {
            if (strtotime($k['tgl_keluar']) < strtotime($startDate)) {
                return [
                    'skip' => true,
                    'error' => 'Karyawan telah keluar sebelum rentang periode ini.'
                ];
            }
        }

        $id_karyawan = (int)$k['id_karyawan'];

        // ================= 2) Get Karier terbaru =================
        try {
            $stmt = $koneksi->prepare("SELECT kar.*, jab.nama_jabatan
            FROM tb_karier kar
            LEFT JOIN tb_jabatan jab ON kar.id_jabatan = jab.id_jabatan
            WHERE kar.id_karyawan = ?
            ORDER BY kar.tgl_mulai DESC LIMIT 1");
            $stmt->execute([$id_karyawan]);
            $karier = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('slip_helper karier: ' . $e->getMessage());
            return ['error' => 'Gagal mengambil data karier'];
        }
        if (!$karier) return ['error' => 'Data karier karyawan tidak ditemukan'];

        $gaji_pokok = floatval($karier['gaji_pokok'] ?? 0);
        if ($gaji_pokok <= 0) {
            return [
                'error' => 'Slip gaji tidak dapat dibuat karena gaji pokok karyawan belum terisi.'
            ];
        }

        $tgl_selesai_karier = $karier['tgl_selesai']; // bisa NULL

        // ================= 3) Get Absen =================
        try {
            $stmt = $koneksi->prepare("SELECT tgl_absen, jam_kerja, ket_absen
           FROM tb_absen
           WHERE id_karyawan = ?
           AND tgl_absen BETWEEN ? AND ?
           ORDER BY tgl_absen ASC");
            $stmt->execute([$id_karyawan, $startDate, $endDate]);
            $absenRows = [];
            while ($r = $stmt->fetch()) {
                $absenRows[$r['tgl_absen']] = $r; // index tanggal
            }
        } catch (PDOException $e) {
            error_log('slip_helper absen: ' . $e->getMessage());
            return ['error' => 'Gagal mengambil data absensi'];
        }

        // ================= 4) Klasifikasi per hari =================
        $absenForUI = [];
        $hadir_count = 0;   // jumlah hari HADIR + SPL (info tampilan)
        $total_nilai_kehadiran = 0.0; // SUM nilai jam_kerja hari berbayar (untuk rumus gaji)
        $spl_count = 0;     // subset hadir (info)
        $mangkir_count = 0;
        $izin_days = 0;
        $sakit_days = 0;
        $cuti_days = 0;
        $off_days = 0;
        $resign_count = 0;
        $new_count = 0;

        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            (new DateTime($endDate))->modify('+1 day')
        );

        foreach ($period as $date) {
            $tgl = $date->format('Y-m-d');
            $ket = 'MANGKIR';
            $jam = 0;

            if (isset($absenRows[$tgl])) {
                $ketAbsen = strtoupper(trim((string)$absenRows[$tgl]['ket_absen']));
                $jamRaw = trim((string)$absenRows[$tgl]['jam_kerja']);
                $jam = is_numeric($jamRaw) ? floatval($jamRaw) : 0;

                if ($ketAbsen === 'NEW') {
                    $ket = 'NEW';
                    $new_count++;
                } elseif ($ketAbsen === 'RESIGN' || $ketAbsen === 'R') {
                    $ket = 'RESIGN';
                    $resign_count++;
                } elseif (in_array($ketAbsen, ['IZIN', 'SAKIT', 'CUTI'], true)) {
                    $ket = $ketAbsen;
                    if ($ketAbsen === 'IZIN') $izin_days++;
                    elseif ($ketAbsen === 'SAKIT') $sakit_days++;
                    else $cuti_days++;
                } elseif ($ketAbsen === 'ALPHA' || ($jam <= 0 && $ketAbsen !== 'OFF')) {
                    // ALPHA atau tanpa jam (selain OFF murni) = mangkir
                    $ket = 'MANGKIR';
                    $mangkir_count++;
                } elseif ($ketAbsen === 'OFF' && $jam <= 0) {
                    $ket = 'OFF';
                    $off_days++;
                } elseif ($jam > 0 && (strpos($ketAbsen, 'OFF') !== false || strpos($ketAbsen, 'SPL') !== false || strpos($ketAbsen, 'LEMBUR') !== false)) {
                    // SPL/lembur: nilai jam_kerja dijumlahkan apa adanya, tanpa multiplier
                    $ket = 'SPL';
                    $spl_count++;
                    $hadir_count++;
                    $total_nilai_kehadiran += $jam;
                } elseif ($jam > 0) {
                    // HADIR: ket HADIR, kosong, atau lainnya dengan jam > 0
                    // Nilai bisa 0.5/1/1.5/2 (PAYROLL ANTAM) -> jumlahkan apa adanya.
                    $ket = 'HADIR';
                    $hadir_count++;
                    $total_nilai_kehadiran += $jam;
                } else {
                    $ket = 'MANGKIR';
                    $mangkir_count++;
                }
            } else {
                // Tanpa data absen: resign bila lewat tgl selesai karier, selain itu mangkir
                if (!empty($tgl_selesai_karier) && $tgl > $tgl_selesai_karier) {
                    $ket = 'RESIGN';
                    $resign_count++;
                } else {
                    $ket = 'MANGKIR';
                    $mangkir_count++;
                }
            }

            $absenForUI[] = [
                'tanggal' => $tgl,
                'jam' => $jam,
                'wl' => 0,
                'ot' => 0,
                'ket' => $ket,
            ];
        }

        // ================= 5) RUMUS: total_nilai_kehadiran x gaji pokok - panjar =================
        $panjar = 0.0;
        try {
            $stmt = $koneksi->prepare("SELECT COALESCE(SUM(nominal_potongan), 0)
                FROM tb_potongan_tidak_tetap
                WHERE id_karyawan = ?
                AND UPPER(TRIM(jenis_potongan)) = 'PANJAR'
                AND tgl_potongan BETWEEN ? AND ?");
            $stmt->execute([$id_karyawan, $startDate, $endDate]);
            $panjar = (float)$stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('slip_helper panjar: ' . $e->getMessage());
            $panjar = 0.0;
        }

        $total_nilai_kehadiran = round($total_nilai_kehadiran, 2);
        $gaji_kotor = $total_nilai_kehadiran * $gaji_pokok;
        $gaji_bersih = $gaji_kotor - $panjar;

        // ================= Build response (key lama dipertahankan) =================
        return [
            'nik' => $k['nik_karyawan'],
            'nama' => $k['nama_lengkap'],
            'jabatan' => $karier['nama_jabatan'] ?? '',
            'period' => "$startDate - $endDate",
            'new' => $new_count,
            'resign' => $resign_count,
            'rapel' => [],
            'total_rapel' => 0,

            'basic' => $gaji_pokok,

            'total_lembur' => 0,
            'total_overtime' => 0,
            'total_spl_1_7' => 0,
            'total_spl_8' => 0,
            'total_spl_9_up' => 0,

            'total_jam_wl' => 0,
            'total_jam_ot' => 0,
            'total_jam_spl_1_7' => 0,
            'total_jam_spl_8' => 0,
            'total_jam_spl_9_up' => 0,

            'bpjs_kesehatan' => 0,
            'jht' => 0,
            'pph21' => 0,
            'pot_pkwt' => 0,

            'pot_izin' => 0,
            'pot_mangkir' => 0,
            'panjar' => $panjar,
            'total_nilai_kehadiran' => $total_nilai_kehadiran,
            'total_pendapatan' => $gaji_kotor,
            'total_potongan' => $panjar,
            'gaji_bersih' => $gaji_bersih,
            'hari_kerja' => $total_nilai_kehadiran,
            'hadir' => $hadir_count,
            'hadir_hari' => $hadir_count,
            'spl' => $spl_count,
            'off' => $off_days,
            'izin' => $izin_days,
            'sakit' => $sakit_days,
            'cuti' => $cuti_days,

            'tunjangan_pendapatan' => [],
            'total_tunjangan_pendapatan' => 0,
            'potongan_tunjangan' => [],
            'total_potongan_tunjangan' => 0,

            'pinjaman' => [],
            'total_potongan_pinjaman' => 0,
            'potongan_lain' => $panjar > 0 ? [['nama' => 'Panjar', 'jumlah' => $panjar]] : [],
            'total_potongan_lainnya' => $panjar,
            'mangkir' => $mangkir_count,
            'total_jam_kurang' => 0,
            'potongan_perjam' => 0,
            'potongan_cuti_khusus' => 0,
            'absen' => $absenForUI,
        ];
    }
}

if (!function_exists('generateSlipGaji')) {
    // Wrapper kompatibilitas: pemanggil lama pakai NIK. DRY: delegasi ke generateSlipGajiById().
    function generateSlipGaji($nik, $startDate, $endDate)
    {
        global $koneksi;
        $nik = trim((string)$nik);
        if ($nik === '') return ['error' => 'NIK tidak valid'];
        try {
            $stmt = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = ? LIMIT 1");
            $stmt->execute([$nik]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('slip_helper nik: ' . $e->getMessage());
            return ['error' => 'Gagal mencari karyawan'];
        }
        if (!$row) return ['error' => 'Karyawan tidak ditemukan'];
        return generateSlipGajiById((int)$row['id_karyawan'], $startDate, $endDate);
    }
}
