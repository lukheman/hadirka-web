<?php
// path ini menyesuaikan struktur projectmu.
// Jika file ini berada di app/controller/get-data/get-absensi.php, gunakan '../../../config/koneksi.php'
require_once __DIR__ . '/../../config/koneksi.php'; // <-- ubah jika perlu
// require '../../../config/function.php'; // jika perlu helper tambahan

// Terima via POST
$tgl_a  = $_POST['tgl_a']  ?? '';
$tgl_b  = $_POST['tgl_b']  ?? '';
$site   = $_POST['site']   ?? '';
$jabatan = $_POST['jabatan'] ?? '';

// Validasi minimal
if (!$tgl_a || !$tgl_b || !$site) {
    // echo "<tr><td colspan='15' class='text-center text-danger'>Filter (site / tanggal) tidak lengkap!</td></tr>";
    exit;
}

// Helper: buat rentang tanggal (array YYYY-MM-DD)
function dateRangeArray($start, $end)
{
    $period = new DatePeriod(
        new DateTime($start),
        new DateInterval('P1D'),
        (new DateTime($end))->modify('+1 day')
    );
    $dates = [];
    foreach ($period as $d) $dates[] = $d->format('Y-m-d');
    return $dates;
}

$allDates = dateRangeArray($tgl_a, $tgl_b);

// -------------------------
// Ambil semua absen di rentang sekaligus
// -------------------------
$absenAll = []; // [id_karyawan][tgl] => row
$sqlAbs = "SELECT id_karyawan, tgl_absen, jam_kerja, ket_absen
           FROM tb_absen
           WHERE tgl_absen BETWEEN ? AND ?";
try {
    $stAbs = $koneksi->prepare($sqlAbs);
    $stAbs->execute([$tgl_a, $tgl_b]);
    $resAbs = $stAbs;
} catch (PDOException $e) {
    error_log('get-absensi absen: ' . $e->getMessage());
    exit;
}
while ($r = $resAbs->fetch()) {
    $id = $r['id_karyawan'];
    $tgl = $r['tgl_absen'];
    // Simpan raw values (trim utk safety)
    $r['jam_kerja'] = trim((string)$r['jam_kerja']);
    $r['ket_absen']  = isset($r['ket_absen']) ? trim((string)$r['ket_absen']) : '';
    $absenAll[$id][$tgl] = $r;
}

// -------------------------
// Ambil karyawan sesuai site/jabatan (aktif berdasarkan tb_karier jika ada)
// -------------------------
// Struktur join menyesuaikan DB-mu: beberapa project menyimpan jabatan/dept di tb_karier.
// Ubah nama kolom join bila berbeda.
$paramsKar = [$site];
$whereJab = "";
if ($jabatan) {
    $whereJab = " AND kar.id_jabatan = ? ";
    $paramsKar[] = $jabatan;
}

$sqlKar = "
SELECT k.id_karyawan, k.nik_karyawan, k.nama_lengkap,
       COALESCE(d.nama_departemen, '') AS nama_departemen,
       COALESCE(j.nama_jabatan, '') AS nama_jabatan,
       COALESCE(s.nama_site, '') AS nama_site,
       kar.tgl_mulai, kar.tgl_selesai
FROM tb_karyawan k
LEFT JOIN tb_karier kar ON kar.id_karyawan = k.id_karyawan
LEFT JOIN tb_jabatan j ON j.id_jabatan = kar.id_jabatan
LEFT JOIN tb_departemen d ON d.id_departemen = kar.id_departemen
LEFT JOIN tb_site s ON s.id_site = kar.id_site
WHERE kar.id_site = ?
$whereJab
GROUP BY k.id_karyawan
ORDER BY k.nama_lengkap ASC
";

try {
    $stKar = $koneksi->prepare($sqlKar);
    $stKar->execute($paramsKar);
    $qKar = $stKar;
} catch (PDOException $e) {
    error_log('get-absensi karyawan: ' . $e->getMessage());
    echo "<tr><td colspan='16' class='text-center text-danger'>Query karyawan error.</td></tr>";
    exit;
}

$no = 1;
while ($k = $qKar->fetch()) {
    $idk = $k['id_karyawan'];

    // Inisialisasi counters
    $hadir = 0;
    $hadir_hari = 0;
    $ot1 = 0;
    $ot2 = 0;
    $spl = 0;
    $spl_hari = 0;
    $ot1_spl = 0;
    $ot2_spl = 0;
    $mangkir = 0;
    $izin = 0;
    $sakit = 0;
    $cuti = 0;

    // Ambil absensi untuk karyawan ini (jika ada)
    $absForK = $absenAll[$idk] ?? [];

    // Loop per tanggal
    foreach ($allDates as $tgl) {
        // Cek apakah ada record absensi
        if (isset($absForK[$tgl])) {
            $row = $absForK[$tgl];
            $jamRaw = trim((string)$row['jam_kerja']);
            $ket = strtoupper(trim((string)$row['ket_absen']));

            // PRIORITAS 0: R / NEW / M (tidak dihitung apa-apa)
            if (in_array($ket, ['RESIGN', 'R', 'NEW'])) {
                continue;
            }

            // PRIORITAS 1: status khusus dari ket_absen
            if (in_array($ket, ['IZIN', 'SAKIT', 'CUTI'])) {
                if ($ket === 'IZIN')  $izin++;
                if ($ket === 'SAKIT') $sakit++;
                if ($ket === 'CUTI')  $cuti++;
                continue;
            }

            // PRIORITAS 2: OFF atau SPL
            if ($ket === 'OFF') {
                // jika ada jam kerja (SPL di hari off)
                if (floatval($jamRaw) > 0) {
                    // case off marked in ket_absen with numeric jam in jam_kerja
                    $jk = floatval($jamRaw);
                    $spl_hari++;
                    $spl += min($jk, 7);
                    $ot1_spl += ($jk >= 8) ? 1 : 0;
                    $ot2_spl += ($jk >= 9) ? ($jk - 8) : 0;
                }
                continue;
            }

            // PRIORITAS 3: Hari kerja normal (hadir)
            if (is_numeric($jamRaw)) {
                $jk = floatval($jamRaw);

                if ($jk == 0) {
                    // Ada record tapi jam = 0 => mangkir
                    $mangkir++;
                    continue;
                }

                // jika normal day? hitung aturan hari kerja: friday = normal 5 jam else 7
                $hariNama = strtolower(date('l', strtotime($tgl)));
                $normal_hours = ($hariNama === 'friday') ? 5 : 7;

                // hitung hadir
                $hadir += min($jk, $normal_hours);
                $hadir_hari++;

                // hitung WL & OT2 sama persis dengan function-slip.php
                $wl = 0;
                $ot = 0;

                if ($hariNama === 'friday') {
                    // Jumat: normal 5, WL jam ke-6, OT jam ke-7+
                    $wl = min(1, max(0, $jk - 5));
                    $ot = max(0, $jk - 5 - $wl);
                } else {
                    // Hari biasa: normal 7, WL jam ke-8, OT jam ke-9+
                    $wl = min(1, max(0, $jk - 7));
                    $ot = max(0, $jk - 7 - $wl);
                }

                // akumulasi
                $ot1 += $wl;
                $ot2 += $ot;

                continue;
            }

            // Jika ket berisi 'MANGKIR' secara eksplisit
            if ($ket === 'MANGKIR') {
                $mangkir++;
                continue;
            }

            // Jika nilai tidak dikenali -> anggap mangkir
            $mangkir++;
            continue;
        } else {
            // Tidak ada record absen pada tanggal -> mangkir
            $mangkir++;
            continue;
        }
    } // end foreach dates

    // Cetak baris tabel sesuai kolom yang kamu mau (15 kolom)
    $nama = htmlspecialchars($k['nama_lengkap'] ?? '-', ENT_QUOTES);
    $nik = htmlspecialchars($k['nik_karyawan'] ?? '-', ENT_QUOTES);
    echo "
    <tr data-id='{$idk}'>
        <td class='text-center'>{$no}</td>
        <td>{$nama}<br><small class='text-muted'>ID: {$nik}</small></td>
        <td class='text-center'><button type='button' class='btn btn-sm btn-primary btnKalender' data-id='{$idk}' data-nama='{$nama}' title='Lihat rekap kalender'><i class='fa fa-calendar'></i> Kalender</button></td>
        <td>{$k['nama_departemen']}</td>
        <td>{$k['nama_jabatan']}</td>
        <td>{$k['nama_site']}</td>

        <td class='text-center'>{$hadir_hari} hari ({$hadir} jam)</td>
        <td class='text-center'>{$ot1}</td>
        <td class='text-center'>{$ot2}</td>
        <td class='text-center'>{$spl_hari} hari ({$spl} jam)</td>
        <td class='text-center'>{$ot1_spl}</td>
        <td class='text-center'>{$ot2_spl}</td>

        <td class='text-center'>{$mangkir}</td>
        <td class='text-center'>{$izin}</td>
        <td class='text-center'>{$sakit}</td>
        <td class='text-center'>{$cuti}</td>
    </tr>
    ";

    $no++;
}
