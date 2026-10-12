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

        $totalNilai = $core['total_nilai_kehadiran'] ?? $core['hari_kerja'] ?? 0;
        $listPendapatan = array_merge(
            [['nama' => 'Gaji Pokok (' . $totalNilai . ' x ' . ($core['basic'] ?? 0) . ')', 'jumlah' => $core['total_pendapatan'] ?? 0]],
            $core['tunjangan_pendapatan'] ?? [],
            $core['rapel'] ?? []
        );

        $listPotongan = array_merge(
            $core['potongan_tunjangan'] ?? [],
            $core['pinjaman'] ?? [],
            $core['potongan_lain'] ?? []
        );

        if (($core['panjar'] ?? 0) > 0 && empty($core['potongan_lain'])) $listPotongan[] = ['nama' => 'Panjar', 'jumlah' => $core['panjar']];
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
