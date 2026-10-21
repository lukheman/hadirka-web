<?php
// slip_helper.php — DISPATCHER perhitungan slip gaji (satu-satunya entri, DRY).
//
// Deteksi otomatis tipe_karyawan dari tb_karyawan lalu mendelegasikan ke:
//   - slip_helper_kontrak.php  -> gaji bulanan tetap
//   - slip_helper_harian.php   -> upah harian × hari kerja
//
// Semua fungsi publik di sini (generateSlipGajiById, generateSlipGaji,
// generateSlipGajiBatch) tetap kompatibel dengan pemanggil lama.
// Perhitungan murni ada di hitungSlipGajiKontrak / hitungSlipGajiHarian
// (tanpa query database) agar single & batch hasilnya identik.
//
// Cara pakai:
//   global $koneksi; // PDO
//   require_once __DIR__ . '/slip_helper.php';
//   $slip = generateSlipGajiById($id_karyawan, $startDate, $endDate);
//   $slip = generateSlipGaji($nik, $startDate, $endDate);
//
// Return: array slip canonical atau ['error'=>...] / ['skip'=>true,...].

require_once __DIR__ . '/slip_helper_kontrak.php';
require_once __DIR__ . '/slip_helper_harian.php';

if (!function_exists('_resolveTipeKaryawan')) {
    /**
     * Ambil tipe_karyawan + id_karyawan dari tb_karyawan berdasarkan id.
     * @return array|null ['id_karyawan'=>int, 'tipe_karyawan'=>string] atau null
     */
    function _resolveTipeKaryawan(int $id_karyawan): ?array
    {
        global $koneksi;
        try {
            $stmt = $koneksi->prepare("SELECT id_karyawan, tipe_karyawan FROM tb_karyawan WHERE id_karyawan = ? LIMIT 1");
            $stmt->execute([$id_karyawan]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('slip_helper resolveTipe: ' . $e->getMessage());
            return null;
        }
        return $row ? ['id_karyawan' => (int)$row['id_karyawan'], 'tipe_karyawan' => $row['tipe_karyawan'] ?? 'Kontrak'] : null;
    }
}

if (!function_exists('_resolveTipeKaryawanByNik')) {
    /**
     * Ambil id_karyawan + tipe_karyawan dari NIK.
     * @return array|null
     */
    function _resolveTipeKaryawanByNik(string $nik): ?array
    {
        global $koneksi;
        try {
            $stmt = $koneksi->prepare("SELECT id_karyawan, tipe_karyawan FROM tb_karyawan WHERE nik_karyawan = ? ORDER BY (status_karyawan='Aktif') DESC, id_karyawan DESC LIMIT 1");
            $stmt->execute([$nik]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('slip_helper resolveTipeByNik: ' . $e->getMessage());
            return null;
        }
        return $row ? ['id_karyawan' => (int)$row['id_karyawan'], 'tipe_karyawan' => $row['tipe_karyawan'] ?? 'Kontrak'] : null;
    }
}

if (!function_exists('generateSlipGajiById')) {
    /**
     * SATU-SATUNYA entri perhitungan slip gaji (DRY).
     * Deteksi tipe_karyawan lalu delegasi ke kontrak atau harian.
     */
    function generateSlipGajiById($id_karyawan, $startDate, $endDate)
    {
        $id_karyawan = (int)$id_karyawan;
        if ($id_karyawan <= 0) return ['error' => 'ID karyawan tidak valid'];

        $info = _resolveTipeKaryawan($id_karyawan);
        if (!$info) return ['error' => 'Karyawan tidak ditemukan'];

        $tipe = $info['tipe_karyawan'] ?? 'Kontrak';

        if ($tipe === 'Harian') {
            return generateSlipGajiByIdHarian($id_karyawan, $startDate, $endDate);
        }

        // Default: Kontrak
        return generateSlipGajiByIdKontrak($id_karyawan, $startDate, $endDate);
    }
}

if (!function_exists('generateSlipGajiBatch')) {
    /**
     * Versi BULK: deteksi tipe per karyawan, lalu delegasi ke batch masing-masing.
     * Grouping per tipe untuk efisiensi query.
     * Return: [id_karyawan => slip canonical | error | skip].
     */
    function generateSlipGajiBatch(array $id_karyawans, $startDate, $endDate)
    {
        global $koneksi;

        $ids = array_values(array_unique(array_filter(array_map('intval', $id_karyawans), fn($v) => $v > 0)));
        if (empty($ids)) return [];

        $in = implode(',', $ids);

        // Ambil tipe_karyawan untuk semua id sekaligus
        $tipeMap = [];
        try {
            $stmt = $koneksi->prepare("SELECT id_karyawan, tipe_karyawan FROM tb_karyawan WHERE id_karyawan IN ($in)");
            $stmt->execute();
            while ($r = $stmt->fetch()) {
                $tipeMap[(int)$r['id_karyawan']] = $r['tipe_karyawan'] ?? 'Kontrak';
            }
        } catch (PDOException $e) {
            error_log('slip_helper batch resolveTipe: ' . $e->getMessage());
        }

        $idsKontrak = [];
        $idsHarian = [];

        foreach ($ids as $id) {
            $tipe = $tipeMap[$id] ?? 'Kontrak';
            if ($tipe === 'Harian') {
                $idsHarian[] = $id;
            } else {
                $idsKontrak[] = $id;
            }
        }

        $out = [];

        if (!empty($idsKontrak)) {
            $out = array_merge($out, generateSlipGajiBatchKontrak($idsKontrak, $startDate, $endDate));
        }
        if (!empty($idsHarian)) {
            $out = array_merge($out, generateSlipGajiBatchHarian($idsHarian, $startDate, $endDate));
        }

        // Pastikan semua id memiliki entri (karyawan tidak ditemukan di DB)
        foreach ($ids as $id) {
            if (!isset($out[$id])) {
                $out[$id] = ['error' => 'Karyawan tidak ditemukan'];
            }
        }

        return $out;
    }
}

if (!function_exists('generateSlipGaji')) {
    /**
     * Wrapper kompatibilitas: pemanggil lama pakai NIK.
     * Deteksi tipe_karyawan lalu delegasi.
     */
    function generateSlipGaji($nik, $startDate, $endDate)
    {
        global $koneksi;
        $nik = trim((string)$nik);
        if ($nik === '') return ['error' => 'NIK tidak valid'];

        $info = _resolveTipeKaryawanByNik($nik);
        if (!$info) return ['error' => 'Karyawan tidak ditemukan'];

        $tipe = $info['tipe_karyawan'] ?? 'Kontrak';
        $id_karyawan = $info['id_karyawan'];

        if ($tipe === 'Harian') {
            return generateSlipGajiByIdHarian($id_karyawan, $startDate, $endDate);
        }

        return generateSlipGajiByIdKontrak($id_karyawan, $startDate, $endDate);
    }
}

// Alias kompatibilitas dengan kode lama yang mungkin memanggil hitungSlipGaji()
// (versi sederhana lama). Dipertahankan agar tidak ada Fatal Error undefined function.
if (!function_exists('hitungSlipGaji')) {
    function hitungSlipGaji($k, $karier, $tunjanganRawRows, $potonganRows, $rapelRows, $pinjamanRows, $potonganLainRows, $absenRows, $startDate, $endDate)
    {
        // Delegasikan ke kontrak (default) untuk kompatibilitas lamanya.
        $tipe = $k['tipe_karyawan'] ?? 'Kontrak';
        if ($tipe === 'Harian') {
            return hitungSlipGajiHarian($k, $karier, $tunjanganRawRows, $potonganRows, $rapelRows, $pinjamanRows, $potonganLainRows, $absenRows, $startDate, $endDate);
        }
        return hitungSlipGajiKontrak($k, $karier, $tunjanganRawRows, $potonganRows, $rapelRows, $pinjamanRows, $potonganLainRows, $absenRows, $startDate, $endDate);
    }
}
