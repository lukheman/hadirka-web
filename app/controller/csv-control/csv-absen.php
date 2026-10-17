<?php
// Import Rekapitulasi Bulanan — HANYA format PAYROLL ANTAM:
//   Baris 5 header: NO. | NAMA | JABATAN | PROYEK | E-AH tgl 1-30
//                 | AI HARI | AJ POKOK | AK GAJI | AL PANJAR | AM BERSIH
//                 | AN BANK | AO REKENING | AP A/N
//   Baris 6 = tanggal 1-30, baris 8-45 data (B kosong = pemisah).
//   Nilai E-AH = HARI (0, 0.5, 1, 1.5, 2) disimpan apa adanya ke tb_absen.jam_kerja.
//   Periode target (bulan YYYY-MM) = periode ABSEN yang Open.
//   Keterangan periode di file (nama sheet / A3) diabaikan.
//   Kolom hari ke-N dimasukkan sebagai tanggal N di bulan target tersebut.
//   NO. tidak unik -> kunci = (proyek, nama). Rekening tetap string.
//
// Perilaku:
//   - Tulis semua ke database (absen + karyawan baru + karier/gaji + identitas/rekening + panjar).
//   - Karyawan tidak cocok -> buat baru (NIK IPM-xxxxx berlanjut).
//   - Nilai absen simpan apa adanya (tidak x8).
ini_set('memory_limit', '-1');
set_time_limit(0);
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!$koneksi) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']);
    exit;
}

if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan atau error upload']);
    exit;
}

// ============================================================
// Helper umum
// ============================================================
if (!function_exists('getCellValue')) {
    // Baca nilai sel; rumus dibaca HASILNYA (bukan rumusnya) via getCalculatedValue().
    function getCellValue($sheet, $cell)
    {
        try {
            $cellObj = $sheet->getCell($cell);
            $isFormula = false;
            try {
                $formula = $cellObj->getValue();
                if (is_string($formula) && strpos($formula, '=') === 0) {
                    $isFormula = true;
                    $value = $cellObj->getCalculatedValue();
                } else {
                    $value = $cellObj->getValue();
                }
            } catch (Exception $e) {
                $value = $cellObj->getValue();
            }
            if ($value === null) $value = '';
            if (is_string($value)) {
                $value = trim($value);
                $value = str_replace("\xc2\xa0", '', $value);
                $value = preg_replace('/\s+/', ' ', $value);
            }
            return ['value' => $value, 'isFormula' => $isFormula, 'dataType' => $cellObj->getDataType()];
        } catch (Exception $e) {
            return ['value' => '', 'isFormula' => false, 'dataType' => 'error', 'error' => $e->getMessage()];
        }
    }
}

// ---------- Helper format PAYROLL ANTAM ----------
if (!function_exists('antamNormSpasi')) {
    function antamNormSpasi($v) { return trim((string)preg_replace('/\s+/u', ' ', (string)$v)); }
}
if (!function_exists('antamBulanKeAngka')) {
    function antamBulanKeAngka($nama)
    {
        $k = strtolower(trim((string)$nama));
        $map = [
            'januari' => 1, 'january' => 1, 'jan' => 1,
            'februari' => 2, 'february' => 2, 'feb' => 2, 'pebruari' => 2,
            'maret' => 3, 'march' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'june' => 6, 'jun' => 6,
            'juli' => 7, 'july' => 7, 'jul' => 7,
            'agustus' => 8, 'august' => 8, 'agu' => 8, 'agust' => 8, 'aug' => 8, 'augustus' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'oktober' => 10, 'october' => 10, 'okt' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11, 'nopember' => 11,
            'desember' => 12, 'december' => 12, 'des' => 12, 'dec' => 12,
        ];
        return $map[$k] ?? null;
    }
}
if (!function_exists('antamIsPayrollSheet')) {
    // Ciri sheet PAYROLL ANTAM: baris 5 = NO.|NAMA|JABATAN|PROYEK ... HARI|POKOK|GAJI|PANJAR|BERSIH|BANK|REKENING|A/N
    function antamIsPayrollSheet($sheet)
    {
        $b5 = strtoupper(antamNormSpasi(getCellValue($sheet, 'B5')['value'] ?? ''));
        $c5 = strtoupper(antamNormSpasi(getCellValue($sheet, 'C5')['value'] ?? ''));
        $d5 = strtoupper(antamNormSpasi(getCellValue($sheet, 'D5')['value'] ?? ''));
        $ai5 = strtoupper(antamNormSpasi(getCellValue($sheet, 'AI5')['value'] ?? ''));
        $aj5 = strtoupper(antamNormSpasi(getCellValue($sheet, 'AJ5')['value'] ?? ''));
        if (strpos($b5, 'NAMA') === false) return false;
        if (strpos($c5, 'JABATAN') === false) return false;
        if (strpos($d5, 'PROYEK') === false) return false;
        if ($ai5 !== 'HARI') return false;
        if ($aj5 !== 'POKOK') return false;
        return true;
    }
}
if (!function_exists('antamParsePeriode')) {
    // Hasil: ['tahun'=>Y,'bulan'=>m,'tgl_mulai'=>'Y-m-d','tgl_selesai'=>'Y-m-d','jml_hari'=>N,'label'=>...]
    function antamParsePeriode($sheet)
    {
        $title = trim((string)$sheet->getTitle());
        $bulanSheet = (ctype_digit($title) && (int)$title >= 1 && (int)$title <= 12) ? (int)$title : null;
        $a3 = antamNormSpasi(getCellValue($sheet, 'A3')['value'] ?? '');
        $tahun = null; $bulan = null; $d1 = 1; $d2 = null;
        if (preg_match('/(\d{1,2})\s*s\/d\s*(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/i', $a3, $m)) {
            $d1 = (int)$m[1]; $d2 = (int)$m[2];
            $bulan = antamBulanKeAngka($m[3]);
            $tahun = (int)$m[4];
        } elseif (preg_match('/(\d{4})/', $a3, $m)) {
            $tahun = (int)$m[1];
        }
        if ($tahun === null) $tahun = (int)date('Y');
        if ($bulanSheet !== null) $bulan = $bulanSheet;
        if ($bulan === null) $bulan = (int)date('m');
        $jmlHari = (int)date('t', strtotime(sprintf('%04d-%02d-01', $tahun, $bulan)));
        if ($d2 === null || $d2 < 1 || $d2 > $jmlHari) $d2 = $jmlHari;
        if ($d1 < 1) $d1 = 1;
        return [
            'tahun' => $tahun, 'bulan' => $bulan,
            'tgl_mulai' => sprintf('%04d-%02d-%02d', $tahun, $bulan, $d1),
            'tgl_selesai' => sprintf('%04d-%02d-%02d', $tahun, $bulan, $d2),
            'jml_hari' => $d2 - $d1 + 1,
            'hari_kalender' => $jmlHari,
            'sheet' => $title, 'label_periode' => $a3,
        ];
    }
}
if (!function_exists('antamCleanJabatan')) {
    function antamCleanJabatan($raw, &$warning)
    {
        $warning = '';
        $c = strtoupper(antamNormSpasi(str_replace(['`', "'", '"'], '', (string)$raw)));
        if ($c === '') { $warning = "Jabatan kosong/salah ketik (`) -> dipakai 'HL'."; return 'HL'; }
        // Normalisasi varian umum
        if (in_array($c, ['TK', 'TUKANG'], true)) return 'TK';
        if (in_array($c, ['HL', 'HELPER', 'HARIAN LEPAS'], true)) return 'HL';
        return $c; // simpan apa adanya (flag di log bila bukan TK/HL)
    }
}
if (!function_exists('antamToFloat')) {
    function antamToFloat($v)
    {
        if ($v === null || $v === '') return 0.0;
        if (is_int($v) || is_float($v)) return (float)$v;
        $s = trim((string)$v);
        if ($s === '' || $s === '-' || $s === '–' || strtoupper($s) === 'X') return 0.0;
        $s = str_replace(',', '.', $s);
        // buang "Rp", spasi, titik ribuan ganda: "6.000.000" -> "6000000"
        $s = preg_replace('/[^\d\.\-]/', '', $s);
        if (substr_count($s, '.') > 1) $s = str_replace('.', '', $s);
        return is_numeric($s) ? (float)$s : 0.0;
    }
}
if (!function_exists('antamToRupiah')) {
    function antamToRupiah($v) { return (int)round(antamToFloat($v)); }
}
if (!function_exists('antamRekeningToString')) {
    // Nomor rekening HARUS string (nol depan jangan hilang, mis. 021601019599532).
    function antamRekeningToString($v)
    {
        if ($v === null) return null;
        if (is_int($v)) return (string)$v;
        if (is_float($v)) {
            if (floor($v) == $v && abs($v) < 1e18) return number_format($v, 0, '', '');
            return (string)$v;
        }
        $s = trim((string)$v);
        if ($s === '' || $s === '-' || strtoupper($s) === 'X') return null;
        $s = preg_replace('/\s+/', '', $s);
        return $s === '' ? null : $s;
    }
}
if (!function_exists('antamBankToString')) {
    function antamBankToString($v)
    {
        $s = antamNormSpasi($v);
        if ($s === '' || $s === '-' || strtoupper($s) === 'X') return null;
        return $s;
    }
}
if (!function_exists('antamGenerateNik')) {
    // NIK baru IPM-xxxxx berlanjut dari suffix numerik terbesar (prefix IPM-).
    function antamGenerateNik($koneksi)
    {
        $max = 0;
        try {
            $q = $koneksi->query("SELECT nik_karyawan FROM tb_karyawan WHERE nik_karyawan LIKE 'IPM-%'");
            while ($r = $q->fetch()) {
                if (preg_match('/IPM-0*(\d+)$/i', trim((string)$r['nik_karyawan']), $m)) {
                    $n = (int)$m[1];
                    if ($n > $max) $max = $n;
                }
            }
        } catch (Throwable $t) { $max = 900; }
        if ($max < 900) $max = 900;
        for ($i = 1; $i <= 2000; $i++) {
            $cand = sprintf('IPM-%05d', $max + $i);
            try {
                $s = $koneksi->prepare("SELECT 1 FROM tb_karyawan WHERE nik_karyawan=? LIMIT 1");
                $s->execute([$cand]);
                if (!$s->fetch()) return $cand;
            } catch (Throwable $t) { return $cand; }
        }
        return sprintf('IPM-%05d', $max + 1) . '-' . time();
    }
}

try {
    $fileTmpPath = $_FILES['file_csv']['tmp_name'];
    if (!file_exists($fileTmpPath)) throw new Exception('File temporary tidak ditemukan');

    $reader = IOFactory::createReaderForFile($fileTmpPath);
    $reader->setReadDataOnly(false);
    $spreadsheet = $reader->load($fileTmpPath);

    // Sheet yang dipakai: parameter POST 'sheet' (nama/index) atau sheet aktif.
    // Jika sheet aktif bukan payroll tapi ada sheet payroll lain, pakai yang pertama.
    $sheetParam = isset($_POST['sheet']) ? trim((string)$_POST['sheet']) : '';
    $sheetNames = $spreadsheet->getSheetNames();
    $sheet = null;
    if ($sheetParam !== '' && strtolower($sheetParam) !== 'auto') {
        if (ctype_digit($sheetParam) && isset($sheetNames[(int)$sheetParam])) {
            $sheet = $spreadsheet->getSheet((int)$sheetParam);
        } else {
            $found = null;
            foreach ($sheetNames as $idx => $nm) if (trim($nm) === $sheetParam) { $found = $idx; break; }
            if ($found === null) throw new Exception('Sheet "' . $sheetParam . '" tidak ditemukan. Pilihan: ' . implode(', ', $sheetNames));
            $sheet = $spreadsheet->getSheet($found);
        }
    } else {
        $sheet = $spreadsheet->getActiveSheet();
        if (!antamIsPayrollSheet($sheet)) {
            foreach ($sheetNames as $idx => $nm) {
                $cand = $spreadsheet->getSheet($idx);
                if (antamIsPayrollSheet($cand)) { $sheet = $cand; break; }
            }
        }
    }
    $sheetTitleUsed = $sheet->getTitle();

    // Format lama sudah dihapus: hanya file PAYROLL ANTAM yang diterima.
    if (!antamIsPayrollSheet($sheet)) {
        throw new Exception('Format file tidak didukung. Gunakan file PAYROLL ANTAM: baris 5 harus berisi NO. | NAMA | JABATAN | PROYEK | ... | HARI | POKOK | GAJI | PANJAR | BERSIH | BANK | REKENING | A/N.');
    }

    // Bulan target, prioritas: 1) bulan yang tampil di filter (POST bulan
    // YYYY-MM, dikirim form), 2) periode ABSEN yang Open. Keterangan periode
    // di file (nama sheet / A3) SENGAJA diabaikan — bukan sumber kebenaran.
    $tahun = null; $bulan = null; $sumberBulan = '';

    $bulanParam = isset($_POST['bulan']) ? trim((string)$_POST['bulan']) : '';
    if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $bulanParam, $mm)) {
        $tahun = (int)$mm[1]; $bulan = (int)$mm[2];
        $sumberBulan = "bulan tampil ($bulanParam)";
    }

    // Periode absen Open: dipakai bila form tak mengirim bulan.
    // (Panjar diposting pada akhir bulan target import, bukan tgl periode aktif.)
    $stPeriodeAktif = $koneksi->prepare("SELECT id_priode, tgl_mulai, tgl_selesai FROM tb_priode WHERE type_priode = 'absen' AND status_priode = 'Open' ORDER BY tgl_mulai DESC LIMIT 1");
    $stPeriodeAktif->execute();
    $periodeAktif = $stPeriodeAktif->fetch();
    if ($tahun === null || $bulan === null) {
        if (!$periodeAktif) {
            throw new Exception('Tidak ada periode absen yang Open. Buka dulu periodenya di menu Periode Penggajian.');
        }
        // Bulan target = bulan tgl_selesai periode aktif (periode kalender: 1 Agu-31 Agu => Agustus).
        $tahun = (int)date('Y', strtotime($periodeAktif['tgl_selesai']));
        $bulan = (int)date('m', strtotime($periodeAktif['tgl_selesai']));
        $sumberBulan = 'periode aktif';
    }
    if ($periodeAktif) {
        $idPeriodeAktif = (int)$periodeAktif['id_priode'];
        $tglMulai = $periodeAktif['tgl_mulai'];
        $tglSelesai = $periodeAktif['tgl_selesai'];
    } else {
        // Tanpa periode aktif: posting panjar memakai akhir bulan target.
        $idPeriodeAktif = 0;
        $tglMulai = sprintf('%04d-%02d-01', $tahun, $bulan);
        $tglSelesai = sprintf('%04d-%02d-%02d', $tahun, $bulan, (int)date('t', strtotime($tglMulai)));
    }
    $tglTargetMulai = sprintf('%04d-%02d-01', $tahun, $bulan);
    $tglTargetSelesai = sprintf('%04d-%02d-%02d', $tahun, $bulan, (int)date('t', strtotime($tglTargetMulai)));
    $highestRow = $sheet->getHighestRow();

    // Prepare DB
    try {
        $stmtFindByName = $koneksi->prepare("SELECT id_karyawan, nama_lengkap, nik_karyawan FROM tb_karyawan WHERE UPPER(TRIM(nama_lengkap))=? ORDER BY id_karyawan ASC");
        $stmtInsKaryawan = $koneksi->prepare("INSERT INTO tb_karyawan (nik_karyawan, nama_lengkap, jenis_kelamin, status_karyawan, password) VALUES (?,?,NULL,'Aktif','')");
        $stmtFindJab = $koneksi->prepare("SELECT id_jabatan FROM tb_jabatan WHERE UPPER(TRIM(nama_jabatan))=? LIMIT 1");
        $stmtInsJab = $koneksi->prepare("INSERT INTO tb_jabatan (nama_jabatan) VALUES (?)");
        $stmtFindKarier = $koneksi->prepare("SELECT id_karier, gaji_pokok, id_site, id_jabatan FROM tb_karier WHERE id_karyawan=? ORDER BY tgl_mulai DESC LIMIT 1");
        $stmtInsKarier = $koneksi->prepare("INSERT INTO tb_karier (id_karyawan, id_site, id_departemen, id_jabatan, gaji_pokok, tgl_mulai, tgl_selesai) VALUES (?,?,NULL,?,?,?,NULL)");
        $stmtUpdKarierGaji = $koneksi->prepare("UPDATE tb_karier SET gaji_pokok=?, id_jabatan=? WHERE id_karier=?");
        $stmtFindIdent = $koneksi->prepare("SELECT id_identitas, nama_bank, no_rek, atas_nama_rek FROM tb_identitas WHERE id_karyawan=? LIMIT 1");
        $stmtInsIdent = $koneksi->prepare("INSERT INTO tb_identitas (id_karyawan, nama_bank, no_rek, atas_nama_rek) VALUES (?,?,?,?)");
        $stmtUpdIdent = $koneksi->prepare("UPDATE tb_identitas SET nama_bank=?, no_rek=?, atas_nama_rek=? WHERE id_karyawan=?");
        $stmtCheckAbs = $koneksi->prepare("SELECT id_absen FROM tb_absen WHERE id_karyawan=? AND tgl_absen=?");
        $stmtInsAbs = $koneksi->prepare("INSERT INTO tb_absen (id_karyawan, tgl_absen, jam_kerja, ket_absen) VALUES (?,?,?,?)");
        $stmtUpdAbs = $koneksi->prepare("UPDATE tb_absen SET jam_kerja=?, ket_absen=? WHERE id_karyawan=? AND tgl_absen=?");
        $stmtCheckPot = $koneksi->prepare("SELECT id_potongan_tidak_tetap FROM tb_potongan_tidak_tetap WHERE id_karyawan=? AND tgl_potongan=? AND jenis_potongan=? AND ket_potongan=?");
        $stmtInsPot = $koneksi->prepare("INSERT INTO tb_potongan_tidak_tetap (id_karyawan, tgl_potongan, nominal_potongan, jenis_potongan, ket_potongan) VALUES (?,?,?,?,?)");
        $stmtUpdPot = $koneksi->prepare("UPDATE tb_potongan_tidak_tetap SET nominal_potongan=? WHERE id_karyawan=? AND tgl_potongan=? AND jenis_potongan=? AND ket_potongan=?");
        $stmtDelPot = $koneksi->prepare("DELETE FROM tb_potongan_tidak_tetap WHERE id_karyawan=? AND tgl_potongan=? AND jenis_potongan=? AND ket_potongan=?");
    } catch (PDOException $e) {
        error_log('csv-absen payroll prepare: ' . $e->getMessage());
        throw new Exception('Gagal menyiapkan database.');
    }

    // Site default = PT. Antam
    $idSiteAntam = null;
    try {
        $qS = $koneksi->query("SELECT id_site FROM tb_site WHERE LOWER(TRIM(nama_site))='pt. antam' LIMIT 1");
        if (($rS = $qS->fetch())) $idSiteAntam = (int)$rS['id_site'];
        if (!$idSiteAntam) $idSiteAntam = (int)$koneksi->query("SELECT id_site FROM tb_site ORDER BY id_site ASC LIMIT 1")->fetchColumn();
    } catch (Throwable $t) { $idSiteAntam = null; }

    // Cache jabatan TK/HL (buat bila belum ada)
    $cacheJab = [];
    $ensureJabatan = function ($nama) use ($koneksi, $stmtFindJab, $stmtInsJab, &$cacheJab) {
        $key = strtoupper(trim((string)$nama));
        if (isset($cacheJab[$key])) return $cacheJab[$key];
        $stmtFindJab->execute([$key]);
        if (($r = $stmtFindJab->fetch())) { $cacheJab[$key] = (int)$r['id_jabatan']; return $cacheJab[$key]; }
        $stmtInsJab->execute([$key]);
        $cacheJab[$key] = (int)$koneksi->lastInsertId();
        return $cacheJab[$key];
    };

    $koneksi->beginTransaction();
    $successAbsen = 0; $insAbsen = 0; $updAbsen = 0;
    $karyawanBaru = 0; $karierUpd = 0; $identUpd = 0; $panjarUpd = 0;
    $skipCount = 0; $errorCount = 0;
    $errorMessages = []; $debugLog = []; $validasi = []; $pekerjaOut = [];
    $rekPakai = []; // nomor rekening => [nama...]

    $debugLog[] = "=== PAYROLL ANTAM [" . $sheetTitleUsed . "] ===";
    $debugLog[] = "Bulan target $tahun-" . sprintf('%02d', $bulan) . " (sumber: $sumberBulan) => data $tglTargetMulai s/d $tglTargetSelesai; periode aktif: " . ($idPeriodeAktif > 0 ? "absen Open #$idPeriodeAktif ($tglMulai s/d $tglSelesai)" : "tidak ada");

    // Peringatan non-blokir bila bulan target di luar periode absen aktif
    // (gaji/panjar ikut periode Open).
    if ($idPeriodeAktif > 0 && ($tglTargetMulai > $tglSelesai || $tglTargetSelesai < $tglMulai)) {
        $validasi[] = "Bulan target $tahun-" . sprintf('%02d', $bulan) . " di luar periode absen aktif ($tglMulai s/d $tglSelesai). Data hari tetap tersimpan, tetapi buka periode yang sesuai agar gaji/panjar konsisten.";
    }

    for ($r = 8; $r <= $highestRow; $r++) {
        $namaRaw = getCellValue($sheet, 'B' . $r)['value'];
        $nama = antamNormSpasi($namaRaw);
        if ($nama === '') { $skipCount++; continue; } // baris pemisah proyek / total
        // Lewati baris total tak sengaja (mis. berisi 'TOTAL'/'JUMLAH')
        $up = strtoupper($nama);
        if (strpos($up, 'TOTAL') !== false || strpos($up, 'JUMLAH') !== false) { $skipCount++; continue; }

        $jabRaw = getCellValue($sheet, 'C' . $r)['value'];
        $jabWarn = '';
        $jabatan = antamCleanJabatan($jabRaw, $jabWarn);
        $proyek = strtoupper(antamNormSpasi(getCellValue($sheet, 'D' . $r)['value'] ?? ''));
        if ($proyek === '') $proyek = '-';

        // Absen E-AH: tanggal = nomor kolom - 4 (E=5 -> tgl 1, AH=34 -> tgl 30).
        // Semua kolom hari yang kalendernya valid langsung dimasukkan sebagai
        // tanggal tersebut di BULAN PERIODE AKTIF (tanpa batas rentang).
        $absen = [];
        for ($colIdx = 5; $colIdx <= 34; $colIdx++) {
            $tglNum = $colIdx - 4;
            if ($tglNum < 1 || $tglNum > 31) continue;
            if (!checkdate($bulan, $tglNum, $tahun)) continue; // mis. 29-30 Feb
            $colL = Coordinate::stringFromColumnIndex($colIdx);
            $vv = getCellValue($sheet, $colL . $r)['value'];
            $absen[$tglNum] = antamToFloat($vv);
        }
        $totalHariExcel = antamToFloat(getCellValue($sheet, 'AI' . $r)['value']);
        $upahHarian = antamToRupiah(getCellValue($sheet, 'AJ' . $r)['value']);
        $gajiKotorExcel = antamToRupiah(getCellValue($sheet, 'AK' . $r)['value']);
        $panjar = antamToRupiah(getCellValue($sheet, 'AL' . $r)['value']);
        $gajiBersihExcel = antamToRupiah(getCellValue($sheet, 'AM' . $r)['value']);
        $bank = antamBankToString(getCellValue($sheet, 'AN' . $r)['value']);
        $norek = antamRekeningToString(getCellValue($sheet, 'AO' . $r)['value']);
        $atasNama = antamNormSpasi(getCellValue($sheet, 'AP' . $r)['value'] ?? '');
        if ($atasNama === '') $atasNama = null;

        if ($norek !== null) {
            if (!isset($rekPakai[$norek])) $rekPakai[$norek] = [];
            $rekPakai[$norek][] = $nama;
        }

        // Hitung ulang untuk validasi (boleh selisih pembulatan)
        $sumAbsen = array_sum($absen);
        if (abs($sumAbsen - $totalHariExcel) > 0.001) {
            $validasi[] = "Baris $r ($nama): SUM(E:AH)=$sumAbsen != HARI(AI)=$totalHariExcel";
        }
        if ($upahHarian > 0 && abs($totalHariExcel * $upahHarian - $gajiKotorExcel) > 1) {
            $validasi[] = "Baris $r ($nama): HARI*POKOK=" . ($totalHariExcel * $upahHarian) . " != GAJI(AK)=$gajiKotorExcel";
        }
        if (abs($gajiKotorExcel - $panjar - $gajiBersihExcel) > 1) {
            $validasi[] = "Baris $r ($nama): GAJI-PANJAR=" . ($gajiKotorExcel - $panjar) . " != BERSIH(AM)=$gajiBersihExcel";
        }
        if ($jabWarn !== '' || ($jabatan !== 'TK' && $jabatan !== 'HL')) {
            $validasi[] = "Baris $r ($nama): jabatan '" . antamNormSpasi($jabRaw) . "' dibersihkan -> '$jabatan'." . ($jabWarn !== '' ? " $jabWarn" : '');
        }

        try {
            // --- cocokkan karyawan by NAMA (NO. tidak unik, jangan pakai kolom A) ---
            $namaKey = strtoupper($nama);
            $stmtFindByName->execute([$namaKey]);
            $cands = $stmtFindByName->fetchAll();
            if (count($cands) > 1) {
                $validasi[] = "Baris $r ($nama): duplikat nama di database (" . count($cands) . "), dipakai NIK " . ($cands[0]['nik_karyawan'] ?? '?') . ".";
            }
            if (!empty($cands)) {
                $idKaryawan = (int)$cands[0]['id_karyawan'];
                $isBaru = false;
            } else {
                $nikBaru = antamGenerateNik($koneksi);
                $stmtInsKaryawan->execute([$nikBaru, $namaKey]);
                $idKaryawan = (int)$koneksi->lastInsertId();
                $karyawanBaru++;
                $isBaru = true;
                $debugLog[] = "Baris $r: karyawan baru $nama ($nikBaru, proyek $proyek)";
            }

            // --- karier: pastikan ada, sinkron gaji_pokok = upah harian + jabatan ---
            $idJab = $ensureJabatan($jabatan);
            $stmtFindKarier->execute([$idKaryawan]);
            $kar = $stmtFindKarier->fetch();
            if (!$kar) {
                $stmtInsKarier->execute([$idKaryawan, $idSiteAntam, $idJab, $upahHarian, $tglTargetMulai]);
                $karierUpd++;
            } elseif ((int)($kar['gaji_pokok'] ?? 0) !== (int)$upahHarian) {
                $stmtUpdKarierGaji->execute([$upahHarian, $idJab, (int)$kar['id_karier']]);
                $karierUpd++;
            }

            // --- identitas: bank/rekening/atas nama (kosong = null/tunai, jangan timpa bila kosong) ---
            $stmtFindIdent->execute([$idKaryawan]);
            $ident = $stmtFindIdent->fetch();
            if (!$ident) {
                $stmtInsIdent->execute([$idKaryawan, (string)($bank ?? ''), (string)($norek ?? ''), (string)($atasNama ?? '')]);
                if ($bank !== null || $norek !== null) $identUpd++;
            } elseif ($bank !== null || $norek !== null || $atasNama !== null) {
                // Hanya timpa kolom yang terisi di Excel; kolom kosong dipertahankan.
                $nb = $bank ?? ($ident['nama_bank'] ?? '');
                $nr = $norek ?? ($ident['no_rek'] ?? '');
                $an = $atasNama ?? ($ident['atas_nama_rek'] ?? '');
                if (($nb !== ($ident['nama_bank'] ?? '')) || ($nr !== ($ident['no_rek'] ?? '')) || ($an !== ($ident['atas_nama_rek'] ?? ''))) {
                    $stmtUpdIdent->execute([(string)$nb, (string)$nr, (string)$an, $idKaryawan]);
                    $identUpd++;
                }
            }

            // --- absen harian: nilai HARI simpan apa adanya ke jam_kerja ---
            foreach ($absen as $tglNum => $nilai) {
                $tgl = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tglNum);
                $isSun = (date('N', strtotime($tgl)) == 7);
                if ($nilai > 0) {
                    $jam = $nilai; // simpan apa adanya (0.5/1/1.5/2)
                    $ket = $isSun ? 'OFF' : ''; // kerja Minggu = SPL/OFF, selainnya HADIR
                } else {
                    $jam = 0;
                    $ket = $isSun ? 'OFF' : 'ALPHA';
                }
                $stmtCheckAbs->execute([$idKaryawan, $tgl]);
                if ($stmtCheckAbs->fetch()) {
                    $stmtUpdAbs->execute([$jam, $ket, $idKaryawan, $tgl]);
                    $updAbsen++;
                } else {
                    $stmtInsAbs->execute([$idKaryawan, $tgl, $jam, $ket]);
                    $insAbsen++;
                }
                $successAbsen++;
            }

            // --- panjar (AL): simpan ke potongan tidak tetap jenis PANJAR ---
            // Tgl posting = akhir BULAN TARGET import (konsisten dgn rumus gaji
            // BETWEEN awal-akhir bulan tampil). Jangan pakai $tglSelesai periode
            // aktif: bulan import bisa beda dgn periode aktif (kasus Okt 2026:
            // panjar Okt keposting 09-20 hingga Sept terhitung 2x, Okt 0x).
            $tglPostingPanjar = $tglTargetSelesai;
            $ketPot = "Payroll ANTAM $proyek " . sprintf('%04d-%02d', $tahun, $bulan);
            $stmtCheckPot->execute([$idKaryawan, $tglPostingPanjar, 'PANJAR', $ketPot]);
            $adaPot = (bool)$stmtCheckPot->fetch();
            if ($panjar > 0) {
                if ($adaPot) $stmtUpdPot->execute([$panjar, $idKaryawan, $tglPostingPanjar, 'PANJAR', $ketPot]);
                else $stmtInsPot->execute([$idKaryawan, $tglPostingPanjar, $panjar, 'PANJAR', $ketPot]);
                $panjarUpd++;
            } elseif ($adaPot) {
                $stmtDelPot->execute([$idKaryawan, $tglPostingPanjar, 'PANJAR', $ketPot]);
            }

            // --- baris untuk JSON preview ---
            $absJson = [];
            foreach ($absen as $tglNum => $nilai) {
                $absJson[sprintf('%04d-%02d-%02d', $tahun, $bulan, $tglNum)] = $nilai;
            }
            $pekerjaOut[] = [
                'no' => null, 'nama' => $namaKey, 'jabatan' => $jabatan, 'proyek' => $proyek,
                'absen' => $absJson, 'total_hari' => $totalHariExcel,
                'upah_harian' => $upahHarian, 'gaji_kotor' => $gajiKotorExcel,
                'panjar' => $panjar, 'gaji_bersih' => $gajiBersihExcel,
                'rekening' => ['bank' => $bank, 'nomor' => $norek, 'atas_nama' => $atasNama],
                'id_karyawan_baru' => $isBaru,
            ];
        } catch (PDOException $e) {
            error_log("csv-absen payroll baris $r ($nama): " . $e->getMessage());
            $errorCount++;
            $errorMessages[] = "Baris $r ($nama): gagal database.";
        } catch (Throwable $e) {
            $errorCount++;
            $errorMessages[] = "Baris $r ($nama): " . $e->getMessage();
        }
    }

    // Rekening bersama: satu nomor dipakai >1 orang
    $rekBersama = [];
    foreach ($rekPakai as $nomor => $daftar) {
        $unik = array_values(array_unique($daftar));
        if (count($unik) > 1) $rekBersama[] = ['nomor' => (string)$nomor, 'dipakai_oleh' => $unik];
    }

    $koneksi->commit();
    $perusahaan = antamNormSpasi(getCellValue($sheet, 'A4')['value'] ?? '');
    $proyekLabel = antamNormSpasi(getCellValue($sheet, 'A2')['value'] ?? '');
    echo json_encode([
        'status' => $successAbsen > 0 ? 'success' : 'error',
        'message' => $successAbsen > 0 ? "Import PAYROLL ANTAM selesai: " . count($pekerjaOut) . " pekerja, $successAbsen sel absen." : 'Tidak ada data yang berhasil diproses',
        'format' => 'PAYROLL-ANTAM',
        'sheet' => $sheetTitleUsed,
        'perusahaan' => $perusahaan !== '' ? $perusahaan : null,
        'proyek' => $proyekLabel !== '' ? $proyekLabel : null,
        'periode' => ['mulai' => $tglTargetMulai, 'selesai' => $tglTargetSelesai, 'periode_aktif' => "$tglMulai s/d $tglSelesai", 'sumber_bulan' => $sumberBulan],
        'details' => [
            'pekerja' => count($pekerjaOut),
            'absen' => $successAbsen, 'absen_insert' => $insAbsen, 'absen_update' => $updAbsen,
            'karyawan_baru' => $karyawanBaru, 'karier_disinkron' => $karierUpd,
            'rekening_disinkron' => $identUpd, 'panjar_disinkron' => $panjarUpd,
            'skipped' => $skipCount, 'error' => $errorCount,
            'validasi_warning' => count($validasi), 'rekening_bersama' => count($rekBersama),
        ],
        'rekening_bersama' => $rekBersama,
        'validasi' => array_slice($validasi, 0, 50),
        'pekerja' => array_slice($pekerjaOut, 0, 50),
        'debug_log' => array_slice($debugLog, -100),
        'errors' => array_slice($errorMessages, 0, 20),
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    if (isset($koneksi) && $koneksi instanceof PDO && $koneksi->inTransaction()) {
        try { $koneksi->rollBack(); } catch (Throwable $t) {}
    }
    error_log("Import Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error sistem: ' . $e->getMessage()]);
}
