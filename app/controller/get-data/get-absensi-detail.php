<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

$id_karyawan = $_GET['id_karyawan'] ?? '';
$tgl_a = $_GET['tgl_a'] ?? date('Y-m-01');
$tgl_b = $_GET['tgl_b'] ?? date('Y-m-d');

if (!$id_karyawan) {
    echo "<p class='text-danger'>Karyawan tidak ditemukan</p>";
    exit;
}

// Fungsi range tanggal
function dateRange($start, $end)
{
    $dates = [];
    $current = strtotime($start);
    $endTime = strtotime($end);
    while ($current <= $endTime) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    return $dates;
}
$allDates = dateRange($tgl_a, $tgl_b);

// Ambil semua absen karyawan ( termasuk id_absen )
$absenAll = [];
$stAbsen = $koneksi->prepare("SELECT id_absen, tgl_absen, jam_kerja, ket_absen
                           FROM tb_absen
                           WHERE id_karyawan = ?
                           AND tgl_absen BETWEEN ? AND ?");
$stAbsen->execute([$id_karyawan, $tgl_a, $tgl_b]);
$absenQ = $stAbsen;
while ($a = $absenQ->fetch()) {
    $absenAll[$a['tgl_absen']] = $a;
}

// // Ambil izin dan sakit
// function loadRange($table, $id_karyawan, $tgl_a, $tgl_b, $koneksi)
// {
//     $arr = [];
//     $q = $koneksi->query("SELECT tgl_awal_$table AS awal, tgl_akhir_$table AS akhir 
//         FROM tb_$table WHERE id_karyawan='$id_karyawan' 
//         AND tgl_awal_$table <= '$tgl_b' 
//         AND tgl_akhir_$table >= '$tgl_a'");
//     while ($r = $q->fetch()) $arr[] = $r;
//     return $arr;
// }
// $izinAll  = loadRange('izin',  $id_karyawan, $tgl_a, $tgl_b, $koneksi);
// $sakitAll = loadRange('sakit', $id_karyawan, $tgl_a, $tgl_b, $koneksi);

$izinAll = [];
$sakitAll = [];

// ================= AMBIL DATA IZIN =================
// Asumsi tabel pengajuan izin bernama 'tb_pengajuan_izin' seperti gambar sebelumnya
$stIzin = $koneksi->prepare("
    SELECT DATE(tgl_izin) as tanggal
    FROM tb_pengajuan_izin
    WHERE id_karyawan = ?
    AND DATE(tgl_izin) <= ?
    AND DATE(tgl_izin) >= ?
    AND status_izin = 'disetujui'
");
$stIzin->execute([$id_karyawan, $tgl_b, $tgl_a]);
$qIzin = $stIzin;

if ($qIzin) {
    while ($r = $qIzin->fetch()) {
        $tgl = $r['tanggal'];
        // Jadikan tgl awal dan akhir sama karena strukturnya hanya 1 tanggal
        $izinAll[] = ['awal' => $tgl, 'akhir' => $tgl];
    }
}

// ================= AMBIL DATA SAKIT =================
// Menyesuaikan dengan struktur tb_sakit di gambar terbaru
$stSakit = $koneksi->prepare("
    SELECT tgl_awal_sakit, tgl_akhir_sakit
    FROM tb_sakit
    WHERE id_karyawan = ?
    AND tgl_awal_sakit <= ?
    AND tgl_akhir_sakit >= ?
    AND status_sakit = 'disetujui'
");
$stSakit->execute([$id_karyawan, $tgl_b, $tgl_a]);
$qSakit = $stSakit;

if ($qSakit) {
    while ($r = $qSakit->fetch()) {
        $sakitAll[] = ['awal' => $r['tgl_awal_sakit'], 'akhir' => $r['tgl_akhir_sakit']];
    }
}

// Ambil data karyawan
$stKaryawan = $koneksi->prepare("SELECT * FROM tb_karyawan
JOIN tb_karier ON tb_karyawan.id_karyawan=tb_karier.id_karyawan
JOIN tb_jabatan ON tb_karier.id_jabatan=tb_jabatan.id_jabatan
WHERE tb_karyawan.id_karyawan = ?");
$stKaryawan->execute([$id_karyawan]);
$karyawanQ = $stKaryawan;
$karyawan = $karyawanQ->fetch();
?>

<div class="table-responsive">
    <table class="table-bordered table-sm w-100 text-center">
        <thead class="bg-primary text-white">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIK</th>
                <th>Jabatan</th>
                <th>Tanggal</th>
                <th>Jam (Nilai)</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $totalNormal = 0;

            foreach ($allDates as $tgl) {
                $hariNama = strtolower(date('l', strtotime($tgl)));
                $normal_hours = ($hariNama === 'friday') ? 5 : 7;


                $absen = $absenAll[$tgl] ?? null;
                $jamNormal = '-';
                $keterangan = 'Mangkir';
                $rowClass = 'bg-danger text-white';

                // ==== CEK IZIN ====
                $isIzin = false;
                foreach ($izinAll as $r) {
                    if ($tgl >= $r['awal'] && $tgl <= $r['akhir']) {
                        $isIzin = true;
                        break;
                    }
                }

                // ==== CEK SAKIT ====
                $isSakit = false;
                foreach ($sakitAll as $r) {
                    if ($tgl >= $r['awal'] && $tgl <= $r['akhir']) {
                        $isSakit = true;
                        break;
                    }
                }

                // ==== CEK ABSEN ====
                if ($absen) {
                    $jk = floatval($absen['jam_kerja']);
                    $ket = strtoupper(trim($absen['ket_absen']));

                    // ===== PRIORITAS STATUS KHUSUS =====
                    if ($ket === 'IZIN') {
                        $keterangan = "Izin";
                        $rowClass = "bg-primary text-white";
                    } elseif ($ket === 'SAKIT') {
                        $keterangan = "Sakit";
                        $rowClass = "bg-info text-dark";
                    } elseif ($ket === 'CUTI') {
                        $keterangan = "Cuti";
                        $rowClass = "bg-warning text-dark";
                    } elseif ($ket === 'RESIGN') {
                        $keterangan = "Resign";
                        $rowClass = "bg-danger text-white";
                    } elseif ($ket === 'NEW') {
                        $keterangan = "New";
                        $rowClass = "bg-secondary text-white";
                    } elseif ($ket === 'OFF') {
                        if ($jk > 0) {
                            $keterangan = "SPL";
                            $rowClass = "bg-warning text-dark";
                            $jamNormal = min($jk, $normal_hours);
                        } else {
                            $keterangan = "OFF";
                            $rowClass = "bg-warning text-dark";
                        }
                    } else {
                        // ===== HARI KERJA NORMAL =====
                        if ($jk > 0) {

                            $jamNormal = min($jk, $normal_hours);

                            $keterangan = "Masuk";
                            $rowClass = "";
                        } else {
                            $keterangan = "Mangkir";
                            $rowClass = "bg-danger text-white";
                        }
                    }
                } else {
                    // JIKA DATA CSV KOSONG (Karyawan tidak absen di mesin)
                    // Sistem akan mengecek apakah ada pengajuan izin/sakit yang di-ACC
                    
                    if ($isIzin) {
                        $keterangan = 'Izin (Sistem)';
                        $rowClass = 'bg-primary text-white';
                    } elseif ($isSakit) {
                        $keterangan = 'Sakit (Sistem)';
                        $rowClass = 'bg-info text-dark';
                    } else {
                        $keterangan = 'Mangkir';
                        $rowClass = 'bg-danger text-white';
                    }
                }


                // ==== HITUNG TOTAL ====
                $totalNormal += is_numeric($jamNormal) ? $jamNormal : 0;
            ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= $no ?></td>
                    <td><?= htmlspecialchars($karyawan['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($karyawan['nik_karyawan']) ?></td>
                    <td><?= htmlspecialchars($karyawan['nama_jabatan']) ?></td>
                    <td><?= tanggal_indo($tgl, true) ?></td>
                    <td><?= $jamNormal ?></td>
                    <td><?= $keterangan ?></td>

                    <td>
                        <?php if ($absen): ?>
                            <!-- EDIT (pakai id_absen) -->
                            <button class="btn btn-sm btn-warning btnEditDetail mb-1"
                                data-id_absen="<?= $absen['id_absen'] ?>"
                                data-tgl="<?= $tgl ?>"
                                data-jk="<?= $absen['jam_kerja'] ?>"
                                data-ket="<?= $absen['ket_absen'] ?>"
                                data-id_karyawan="<?= $id_karyawan ?>">
                                <i class="fa fa-edit"></i>
                            </button>

                            <!-- HAPUS (pakai id_absen) -->
                            <button class="btn btn-sm btn-danger btnHapusDetail"
                                data-id_absen="<?= $absen['id_absen'] ?>"
                                data-tgl="<?= $tgl ?>"
                                data-id_karyawan="<?= $id_karyawan ?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        <?php else: ?>
                            <!-- TAMBAH (pakai id_karyawan + tgl) -->
                            <button class="btn btn-sm btn-success btnTambahDetail"
                                data-id_karyawan="<?= $id_karyawan ?>"
                                data-tgl="<?= $tgl ?>">
                                <i class="fa fa-plus"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php
                $no++;
            }
            ?>
            <!-- ================= TOTAL ================= -->
            <tr class="bg-secondary text-white fw-bold">
                <td colspan="5">Total</td>
                <td><?= $totalNormal ?></td>
                <td colspan="2">-</td>
            </tr>
        </tbody>

    </table>
</div>