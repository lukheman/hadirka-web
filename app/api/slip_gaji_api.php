<?php
// File: slip_gaji_api.php (Endpoint Detail Slip Gaji)
// DRY: satu-satunya perhitungan ada di app/helpers/slip_helper.php
// (generateSlipGajiById). File ini hanya adapter format respons API.

header('Content-Type: application/json; charset=utf-8');

include 'koneksi-api.php';
require_once __DIR__ . '/../helpers/slip_helper.php';

if (!function_exists('buildSlipGajiApiResponse')) {
    // Ubah hasil canonical (snake_case) menjadi kontrak API lama (camelCase).
    // Label, syarat tampil (> 0), dan format tanggal dipertahankan persis.
    function buildSlipGajiApiResponse($core, $startDate, $endDate)
    {
        if (!is_array($core) || isset($core['error']) || isset($core['skip'])) {
            return $core;
        }

        $listPendapatan = array_merge(
            [['nama' => 'Gaji Pokok', 'jumlah' => $core['basic'] ?? 0]],
            $core['tunjangan_pendapatan'] ?? [],
            $core['rapel'] ?? []
        );
        if (($core['total_lembur'] ?? 0) > 0) $listPendapatan[] = ['nama' => 'Lembur WL (' . ($core['total_jam_wl'] ?? 0) . ' Jam)', 'jumlah' => $core['total_lembur']];
        if (($core['total_overtime'] ?? 0) > 0) $listPendapatan[] = ['nama' => 'Lembur OT (' . ($core['total_jam_ot'] ?? 0) . ' Jam)', 'jumlah' => $core['total_overtime']];
        if (($core['total_spl_1_7'] ?? 0) > 0) $listPendapatan[] = ['nama' => 'SPL 1-7 (' . ($core['total_jam_spl_1_7'] ?? 0) . ' Jam)', 'jumlah' => $core['total_spl_1_7']];
        if (($core['total_spl_8'] ?? 0) > 0) $listPendapatan[] = ['nama' => 'SPL 8 (' . ($core['total_jam_spl_8'] ?? 0) . ' Jam)', 'jumlah' => $core['total_spl_8']];
        if (($core['total_spl_9_up'] ?? 0) > 0) $listPendapatan[] = ['nama' => 'SPL 9+ (' . ($core['total_jam_spl_9_up'] ?? 0) . ' Jam)', 'jumlah' => $core['total_spl_9_up']];

        $listPotongan = array_merge(
            $core['potongan_tunjangan'] ?? [],
            $core['pinjaman'] ?? [],
            $core['potongan_lain'] ?? []
        );

        if (($core['pot_izin'] ?? 0) > 0) $listPotongan[] = ['nama' => 'Potongan Izin', 'jumlah' => $core['pot_izin']];
        if (($core['pot_mangkir'] ?? 0) > 0) $listPotongan[] = ['nama' => 'Potongan Mangkir', 'jumlah' => $core['pot_mangkir']];
        if (($core['potongan_perjam'] ?? 0) > 0) $listPotongan[] = ['nama' => 'Potongan Kurang Jam', 'jumlah' => $core['potongan_perjam']];
        if (($core['pot_pkwt'] ?? 0) > 0) $listPotongan[] = ['nama' => 'Potongan PKWT (NEW/RESIGN)', 'jumlah' => $core['pot_pkwt']];
        if (($core['potongan_cuti_khusus'] ?? 0) > 0) $listPotongan[] = ['nama' => 'Potongan Cuti Harian', 'jumlah' => $core['potongan_cuti_khusus']];

        $absensi = [];
        $no = 1;
        foreach (($core['absen'] ?? []) as $r) {
            $tgl = $r['tanggal'] ?? '';
            $ts = $tgl !== '' ? strtotime($tgl) : false;
            $absensi[] = [
                'no' => $no++,
                'tanggal' => $ts !== false ? date('d M', $ts) : $tgl,
                'hari' => $ts !== false ? date('D', $ts) : '',
                'jam' => $r['jam'] ?? 0,
                'ket' => $r['ket'] ?? '',
            ];
        }

        return [
            'nik' => $core['nik'] ?? null,
            'nama' => $core['nama'] ?? null,
            'jabatan' => $core['jabatan'] ?? '',
            'period' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)),
            'gajiBersih' => $core['gaji_bersih'] ?? 0,
            'totalPendapatan' => $core['total_pendapatan'] ?? 0,
            'totalPotongan' => $core['total_potongan'] ?? 0,
            'pendapatan' => $listPendapatan,
            'potongan' => $listPotongan,

            'hariKerja' => $core['hari_kerja'] ?? 0,
            'mangkir' => $core['mangkir'] ?? 0,
            'izin' => $core['izin'] ?? 0,
            'sakit' => $core['sakit'] ?? 0,
            'cuti' => $core['cuti'] ?? 0,
            'newDays' => $core['new'] ?? 0,
            'resignDays' => $core['resign'] ?? 0,

            'totalJamKurang' => $core['total_jam_kurang'] ?? 0,
            'totalJamWL' => $core['total_jam_wl'] ?? 0,
            'totalJamOT' => $core['total_jam_ot'] ?? 0,
            'totalJamSPL1_7' => $core['total_jam_spl_1_7'] ?? 0,
            'totalJamSPL8' => $core['total_jam_spl_8'] ?? 0,
            'totalJamSPL9Up' => $core['total_jam_spl_9_up'] ?? 0,
            'absensi' => $absensi
        ];
    }
}

// ====================================================================
//          API ENDPOINT HANDLER (kontrak tidak berubah)
// ====================================================================
$id_karyawan = $_GET['id_karyawan'] ?? null;
$startDate = $_GET['startDate'] ?? null;
$endDate = $_GET['endDate'] ?? null;

if (!$id_karyawan || !$startDate || !$endDate) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter id_karyawan, startDate, atau endDate tidak lengkap.']);
    exit;
}

$core = generateSlipGajiById($id_karyawan, $startDate, $endDate);

if (isset($core['error'])) {
    http_response_code(404);
    echo json_encode($core);
    exit;
}

$response = buildSlipGajiApiResponse($core, $startDate, $endDate);

echo json_encode($response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
