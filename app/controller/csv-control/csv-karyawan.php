<?php
// Import Data Karyawan — mendukung 3 format (otomatis dikenali):
//
// (1) FORMAT UTAMA 11 kolom (A-K):
//       NO. | ID KARYAWAN | NIK KARYAWAN | NAMA | JABATAN | TIPE KARYAWAN |
//       PROYEK | GAJI POKOK | BANK | REKENING | A/N
//       ID KARYAWAN berisi IPM-xxxxx; NIK KARYAWAN berisi NIK kependudukan.
// (2) FORMAT LAMA 8 kolom (A-H), tipe otomatis Kontrak:
//       Baris 1 header: NO. | NAMA | JABATAN | PROYEK | GAJI POKOK | BANK | REKENING | A/N
//       Baris 2 dst data. Kolom NO. diabaikan (tidak unik, direset per proyek).
// (3) ABSENSI Payroll ANTAM (template Rekap Absensi):
//       Baris 5 header: NO. | NAMA | JABATAN | PROYEK | ... | HARI | POKOK | GAJI | PANJAR
//                     | BERSIH | BANK | REKENING | A/N ; baris 8 dst data.
//       Yang diambil hanya data karyawan: B NAMA, C JABATAN, D PROYEK, AJ POKOK,
//       AN BANK, AO REKENING, AP A/N. Kolom harian & panjar DIABAIKAN
//       (itu ranah import Rekap Absensi).
//
// Pemetaan format utama:
//   B ID KARYAWAN -> tb_karyawan.nik_karyawan (IPM-xxxxx)
//   C NIK KARYAWAN -> tb_identitas.nik_ktp (NIK kependudukan)
//   D NAMA       -> tb_karyawan.nama_lengkap
//   E JABATAN    -> tb_jabatan + tb_karier.id_jabatan
//   F TIPE KARYAWAN -> tb_karyawan.tipe_karyawan + id_tipe_karyawan
//   G PROYEK     -> dicocokkan ke tb_site
//   H GAJI POKOK -> tb_karier.gaji_pokok
//   I BANK       -> tb_identitas.nama_bank
//   J REKENING   -> tb_identitas.no_rek         (selalu dibaca sebagai TEKS: nol depan aman,
//                                                format ilmiah 5.1E+14 dinormalisasi bila presisi utuh)
//   K A/N        -> tb_identitas.atas_nama_rek
//
// Perilaku: NAMA yang sudah ada di database DIUPDATE (jabatan, gaji, rekening);
// NAMA baru DIBUAT (karyawan + karier + identitas). Baris tanpa NAMA dilewati.
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300');

header("Content-Type: application/json");

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Finance (level 6) read-only: tolak import karyawan.
if ((($_SESSION['level'] ?? 99) == 6)) {
    echo json_encode(["success" => false, "error" => "Akses ditolak: akun Finance hanya boleh melihat data karyawan."]);
    exit;
}

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$response = [
    "success" => false,
    "berhasil" => 0,
    "baru" => 0,
    "diupdate" => 0,
    "dilewati" => 0,
    "gagal" => 0,
    "jabatan_baru" => [],
    "proyek_tanpa_site" => [],
    "errors" => [],
    "debug" => [],
    "error" => ""
];

if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    $response["error"] = empty($_FILES)
        ? "File terlalu besar. Cek post_max_size di php.ini."
        : "File Excel belum dipilih atau terjadi error saat upload.";
    echo json_encode($response);
    exit;
}

$file_ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
if (!in_array($file_ext, ['xls', 'xlsx'])) {
    $response["error"] = "Format file tidak didukung. Harus XLS atau XLSX.";
    echo json_encode($response);
    exit;
}

function kimpNorm($v) { return trim((string)preg_replace('/\s+/u', ' ', (string)$v)); }
function kimpUpper($v) {
    $v = kimpNorm($v);
    return function_exists('mb_strtoupper') ? mb_strtoupper($v, 'UTF-8') : strtoupper($v);
}
function kimpGaji($v) {
    // Nominal gaji -> int rupiah. Terima angka Excel maupun string "200000" / "200.000" / "Rp 200.000".
    if ($v === null || $v === '') return null;
    if (is_int($v) || is_float($v)) return (int)round((float)$v);
    $s = trim((string)$v);
    if ($s === '' || $s === '-' || strtoupper($s) === 'X') return null;
    $s = preg_replace('/^(rp\.?\s*)+/i', '', $s);
    $s = str_replace(["\xc2\xa0", ' ', '_'], '', $s);
    if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $s)) {
        // Ribuan ala Indonesia: "200.000" / "1.234.567,00" -> buang titik, koma jadi desimal.
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    } else {
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^\d\.\-]/', '', $s);
        if (substr_count($s, '.') > 1) $s = str_replace('.', '', $s);
    }
    return is_numeric($s) ? (int)round((float)$s) : null;
}
function kimpRekening($v) {
    // Nomor rekening HARUS string: nol depan jangan hilang, notasi ilmiah dinormalisasi.
    if ($v === null) return null;
    if (is_int($v)) return (string)$v;
    if (is_float($v)) {
        if (floor($v) == $v && abs($v) < 1e18) return number_format($v, 0, '', '');
        return null;
    }
    $s = trim((string)$v);
    if ($s === '' || $s === '-' || strtoupper($s) === 'X') return null;
    $s = str_replace(["\xc2\xa0", ' ', "\t", '.', '-'], '', $s);
    // Notasi ilmiah dari Excel ("5.10101E+14"): hanya pulihkan bila presisi utuh.
    if (preg_match('/^(\d+(?:\.\d+)?)[eE]([+-]?\d+)$/', $s, $m)) {
        $mant = str_replace('.', '', $m[1]);
        $dec = strlen($m[1]) > strlen((string)(int)$m[1]) ? strlen(substr(strchr($m[1], '.'), 1)) : 0;
        $exp = (int)$m[2] - $dec;
        if ($exp < 0 || strlen($mant) > 18) return $s; // presisi sudah rusak di file
        $s = $mant . str_repeat('0', $exp);
    }
    $s = ltrim($s, "'");
    return $s === '' ? null : $s;
}
function kimpBank($v) {
    $s = kimpNorm($v);
    if ($s === '' || $s === '-' || strtoupper($s) === 'X') return null;
    return $s;
}
function kimpIdKaryawan($v) {
    $s = kimpUpper($v);
    if ($s === '' || $s === '-' || $s === 'X') return null;
    return preg_match('/^IPM-\d+$/', $s) ? $s : null;
}
function kimpNikKtp($v) {
    if ($v === null || $v === '') return null;
    $s = trim((string)$v);
    if ($s === '' || $s === '-' || strtoupper($s) === 'X') return null;
    $s = preg_replace('/\D+/', '', $s);
    return $s === '' ? null : $s;
}
function kimpNikBaru($koneksi) {
    $max = 0;
    try {
        $q = $koneksi->query("SELECT nik_karyawan FROM tb_karyawan WHERE nik_karyawan LIKE 'IPM-%'");
        while ($r = $q->fetch()) {
            if (preg_match('/IPM-0*(\d+)$/i', trim((string)$r['nik_karyawan']), $m)) {
                $n = (int)$m[1];
                if ($n > $max) $max = $n;
            }
        }
    } catch (Throwable $t) { /* abaikan, pakai max=0 */ }
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

try {
    // Cache jabatan + site agar tidak query berulang di dalam loop.
    $cache_jabatan = [];
    foreach ($koneksi->query("SELECT id_jabatan, nama_jabatan FROM tb_jabatan")->fetchAll() as $r) {
        $cache_jabatan[kimpUpper($r['nama_jabatan'])] = (int)$r['id_jabatan'];
    }
    $cache_site = [];
    foreach ($koneksi->query("SELECT id_site, nama_site FROM tb_site")->fetchAll() as $r) {
        $cache_site[kimpUpper($r['nama_site'])] = (int)$r['id_site'];
    }
    $stmtInsJabatan = $koneksi->prepare("INSERT INTO tb_jabatan (nama_jabatan) VALUES (?)");

    // Baca mentah (tanpa format) supaya REKENING numerik tidak rusak jadi "5.1E+14".
    $spreadsheet = IOFactory::load($_FILES['file_excel']['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

    $normH = function ($h) { return kimpUpper($h ?? ''); };
    $isModern = false;
    $isSimple = false;
    $headers = array_map($normH, array_values($sheet[1] ?? []));
    $expectedModern = ['NO.', 'ID KARYAWAN', 'NIK KARYAWAN', 'NAMA', 'JABATAN', 'TIPE KARYAWAN', 'PROYEK', 'GAJI POKOK', 'BANK', 'REKENING', 'A/N'];
    if (count($headers) >= 11 && array_slice($headers, 0, 11) === $expectedModern) {
        $isModern = true;
    }
    $headers = array_slice($headers, 0, 8);
    $expected = ['NO.', 'NAMA', 'JABATAN', 'PROYEK', 'GAJI POKOK', 'BANK', 'REKENING', 'A/N'];
    if (!$isModern && count($headers) === 8) {
        $isSimple = true;
        foreach ($expected as $i => $exp) {
            $got = $headers[$i];
            if ($i === 0 && ($got === 'NO.' || $got === 'NO')) continue;
            if ($got !== $exp) { $isSimple = false; break; }
        }
    }
    // Format Payroll ANTAM (template absensi): baris 5 =
    // NO. | NAMA | JABATAN | PROYEK ... HARI | POKOK | ... | BANK | REKENING | A/N
    $isPayroll = false;
    if (!$isSimple && isset($sheet[5])) {
        $r5 = $sheet[5];
        $isPayroll =
            strpos($normH($r5['B'] ?? ''), 'NAMA') !== false &&
            strpos($normH($r5['C'] ?? ''), 'JABATAN') !== false &&
            strpos($normH($r5['D'] ?? ''), 'PROYEK') !== false &&
            $normH($r5['AI'] ?? '') === 'HARI' &&
            $normH($r5['AJ'] ?? '') === 'POKOK';
    }
    if (!$isModern && !$isSimple && !$isPayroll) {
        throw new Exception('Format template tidak sesuai. Gunakan format utama 11 kolom (NO. | ID KARYAWAN | NIK KARYAWAN | NAMA | JABATAN | TIPE KARYAWAN | PROYEK | GAJI POKOK | BANK | REKENING | A/N), format lama 8 kolom, atau template Payroll ANTAM.');
    }
    $response['format'] = $isPayroll ? 'PAYROLL-ANTAM' : ($isModern ? 'UTAMA-10-KOLOM' : 'LAMA-8-KOLOM');

    // Normalisasi: B ID, C NIK KTP, D nama, E jabatan, F tipe,
    // G proyek, H gaji, I bank, J rekening, K a/n.
    $dataRows = [];
    if ($isModern) {
        foreach ($sheet as $rowNum => $row) {
            if ($rowNum == 1) continue;
            $dataRows[$rowNum] = $row;
        }
    } elseif ($isSimple) {
        foreach ($sheet as $rowNum => $row) {
            if ($rowNum == 1) continue;
            $dataRows[$rowNum] = [
                'B' => null, 'C' => null, 'D' => $row['B'] ?? null,
                'E' => $row['C'] ?? null, 'F' => 'Kontrak', 'G' => $row['D'] ?? null,
                'H' => $row['E'] ?? null, 'I' => $row['F'] ?? null,
                'J' => $row['G'] ?? null, 'K' => $row['H'] ?? null,
            ];
        }
    } else {
        foreach ($sheet as $rowNum => $row) {
            if ($rowNum < 8) continue;
            $dataRows[$rowNum] = [
                'B' => null,                // ID KARYAWAN tidak tersedia
                'C' => null,                // NIK KTP tidak tersedia
                'D' => $row['B'] ?? null,   // NAMA
                'E' => $row['C'] ?? null,   // JABATAN
                'F' => 'Kontrak',
                'G' => $row['D'] ?? null,   // PROYEK
                'H' => $row['AJ'] ?? null,  // POKOK -> gaji pokok
                'I' => $row['AN'] ?? null,  // BANK
                'J' => $row['AO'] ?? null,  // REKENING
                'K' => $row['AP'] ?? null,  // A/N
            ];
        }
    }

    $stmtFindById = $koneksi->prepare("SELECT id_karyawan, nik_karyawan, nama_lengkap FROM tb_karyawan WHERE UPPER(nik_karyawan)=? LIMIT 1");
    $stmtFindByName = $koneksi->prepare("SELECT id_karyawan, nik_karyawan, nama_lengkap FROM tb_karyawan WHERE UPPER(TRIM(nama_lengkap))=? ORDER BY id_karyawan ASC");
    $stmtFindTipe = $koneksi->prepare("SELECT id_tipe_karyawan, nama_tipe FROM tb_tipe_karyawan WHERE UPPER(nama_tipe)=? LIMIT 1");
    $stmtInsKaryawan = $koneksi->prepare("INSERT INTO tb_karyawan (nik_karyawan, nama_lengkap, jenis_kelamin, tipe_karyawan, id_tipe_karyawan, status_karyawan, password) VALUES (?,?,NULL,?,?,'Aktif','')");
    $stmtUpdTipe = $koneksi->prepare("UPDATE tb_karyawan SET tipe_karyawan=?, id_tipe_karyawan=? WHERE id_karyawan=?");
    $stmtFindKarier = $koneksi->prepare("SELECT id_karier, id_site, gaji_pokok, id_jabatan FROM tb_karier WHERE id_karyawan=? ORDER BY tgl_mulai DESC LIMIT 1");
    $stmtInsKarier = $koneksi->prepare("INSERT INTO tb_karier (id_karyawan, id_site, id_departemen, id_jabatan, gaji_pokok, tgl_mulai, tgl_selesai) VALUES (?,?,NULL,?,?,NULL,NULL)");
    $stmtUpdKarier = $koneksi->prepare("UPDATE tb_karier SET id_jabatan=?, gaji_pokok=?, id_site=? WHERE id_karier=?");
    $stmtFindIdent = $koneksi->prepare("SELECT id_identitas, nik_ktp, nama_bank, no_rek, atas_nama_rek FROM tb_identitas WHERE id_karyawan=? LIMIT 1");
    $stmtInsIdent = $koneksi->prepare("INSERT INTO tb_identitas (id_karyawan, nik_ktp, nama_bank, no_rek, atas_nama_rek) VALUES (?,?,?,?,?)");
    $stmtUpdIdent = $koneksi->prepare("UPDATE tb_identitas SET nik_ktp=?, nama_bank=?, no_rek=?, atas_nama_rek=? WHERE id_karyawan=?");

    // Alias kolom PROYEK -> nama site di tb_site.
    // File Payroll ANTAM memakai kode lokasi kerja (BG, CCR, P3, P5) yang
    // semuanya milik proyek Antam, sehingga otomatis dipetakan ke site
    // "PT. Antam". Tambahkan pemetaan baru di sini bila ada kode lain
    // (boleh pakai nama site penuh atau tanpa "PT.").
    $site_alias = [
        'BG'  => 'PT. Antam',
        'CCR' => 'PT. Antam',
        'P3'  => 'PT. Antam',
        'P5'  => 'PT. Antam',
    ];

    $jabatanBaru = [];
    $proyekLiar = [];

    foreach ($dataRows as $rowNum => $row) {
        $idIpm = kimpIdKaryawan($row['B'] ?? null);
        $nikKtp = kimpNikKtp($row['C'] ?? null);
        $nama = kimpNorm($row['D'] ?? '');
        if ($nama === '') { $response['dilewati']++; continue; } // baris kosong/pemisah
        if (strpos(strtoupper($nama), 'TOTAL') !== false || strpos(strtoupper($nama), 'JUMLAH') !== false) {
            $response['dilewati']++;
            $response['debug'][] = "Baris $rowNum: Dilewati - baris total.";
            continue;
        }

        $transOk = false;
        try {
            if (($row['B'] ?? null) !== null && kimpNorm($row['B']) !== '' && $idIpm === null) {
                throw new Exception('ID Karyawan harus berformat IPM-xxxxx.');
            }
            if ($nikKtp !== null && !preg_match('/^\d{16}$/', $nikKtp)) {
                throw new Exception('NIK Karyawan harus 16 digit.');
            }
            $jabRaw = kimpUpper($row['E'] ?? '');
            $jabatan = $jabRaw === '' ? 'HL' : $jabRaw;
            $tipe = kimpUpper($row['F'] ?? 'KONTRAK');
            if ($tipe === '') $tipe = 'KONTRAK';
            $proyek = kimpUpper($row['G'] ?? '');
            $gaji = kimpGaji($row['H'] ?? null);
            if (($row['H'] ?? null) !== null && trim((string)($row['H'] ?? '')) !== '' && $gaji === null) {
                throw new Exception("Gaji pokok '" . kimpNorm($row['H']) . "' tidak valid (harus angka).");
            }
            $bank = kimpBank($row['I'] ?? null);
            $norek = kimpRekening($row['J'] ?? null);
            $an = kimpNorm($row['K'] ?? '');
            if ($an === '') $an = null;

            // Jabatan: buat otomatis bila belum ada (seperti import absen).
            if (!isset($cache_jabatan[$jabatan])) {
                $stmtInsJabatan->execute([$jabatan]);
                $cache_jabatan[$jabatan] = (int)$koneksi->lastInsertId();
                $jabatanBaru[] = $jabatan;
            }
            $idJab = $cache_jabatan[$jabatan];

            // Proyek -> site (opsional): cocok persis, "PT. <proyek>", atau alias.
            $idSite = null;
            if ($proyek !== '' && $proyek !== '-') {
                if (isset($cache_site[$proyek])) $idSite = $cache_site[$proyek];
                elseif (isset($cache_site['PT. ' . $proyek])) $idSite = $cache_site['PT. ' . $proyek];
                else {
                    // Alias kode proyek (mis. BG -> PT. Antam).
                    $alias = isset($site_alias[$proyek]) ? kimpUpper($site_alias[$proyek]) : '';
                    if ($alias !== '' && isset($cache_site[$alias])) $idSite = $cache_site[$alias];
                    elseif ($alias !== '' && isset($cache_site['PT. ' . $alias])) $idSite = $cache_site['PT. ' . $alias];
                    else $proyekLiar[$proyek] = true;
                }
            }

            $namaKey = kimpUpper($nama);
            $koneksi->beginTransaction();
            $transOk = true;

            $cands = [];
            if ($idIpm !== null) {
                $stmtFindById->execute([$idIpm]);
                $foundById = $stmtFindById->fetch();
                if ($foundById) $cands = [$foundById];
            }
            if (empty($cands)) {
                $stmtFindByName->execute([$namaKey]);
                $cands = $stmtFindByName->fetchAll();
            }
            $stmtFindTipe->execute([$tipe]);
            $tipeRow = $stmtFindTipe->fetch();
            if (!$tipeRow) throw new Exception("Tipe karyawan '$tipe' tidak ditemukan di master.");
            $idTipe = (int)$tipeRow['id_tipe_karyawan'];
            if (!empty($cands)) {
                $idKaryawan = (int)$cands[0]['id_karyawan'];
                if (count($cands) > 1) {
                    $response['debug'][] = "Baris $rowNum ($nama): duplikat nama di database (" . count($cands) . "), dipakai NIK " . ($cands[0]['nik_karyawan'] ?? '?') . ".";
                }
                $isBaru = false;
            } else {
                $idIpm = $idIpm ?? kimpNikBaru($koneksi);
                $stmtInsKaryawan->execute([$idIpm, $namaKey, $tipeRow['nama_tipe'], $idTipe]);
                $idKaryawan = (int)$koneksi->lastInsertId();
                $isBaru = true;
            }
            if (!$isBaru) {
                $stmtUpdTipe->execute([$tipeRow['nama_tipe'], $idTipe, $idKaryawan]);
            }

            // Karier: baru -> insert (site bila cocok, gaji bila diisi); ada -> update jabatan + gaji (bila diisi).
            $stmtFindKarier->execute([$idKaryawan]);
            $kar = $stmtFindKarier->fetch();
            if (!$kar) {
                $stmtInsKarier->execute([$idKaryawan, $idSite, $idJab, $gaji === null ? null : (string)$gaji]);
            } else {
                $gajiBaru = $gaji === null ? ($kar['gaji_pokok'] ?? null) : (string)$gaji;
                // Site: isi otomatis bila karier ini belum punya site (tidak menimpa yang sudah terisi).
                $siteLama = $kar['id_site'] !== null ? (int)$kar['id_site'] : null;
                $siteBaru = $siteLama !== null ? $siteLama : $idSite;
                if ((int)$kar['id_jabatan'] !== $idJab
                    || (string)($kar['gaji_pokok'] ?? '') !== (string)($gajiBaru ?? '')
                    || ($siteBaru ?? 0) !== ($siteLama ?? 0)) {
                    $stmtUpdKarier->execute([$idJab, $gajiBaru, $siteBaru, (int)$kar['id_karier']]);
                }
            }

            // Identitas: baru -> insert (kosong = ''); ada -> timpa hanya kolom yang terisi di file.
            $stmtFindIdent->execute([$idKaryawan]);
            $ident = $stmtFindIdent->fetch();
            if (!$ident) {
                $stmtInsIdent->execute([$idKaryawan, $nikKtp ?? '', (string)($bank ?? ''), (string)($norek ?? ''), (string)($an ?? '')]);
            } elseif ($nikKtp !== null || $bank !== null || $norek !== null || $an !== null) {
                $nk = $nikKtp ?? ($ident['nik_ktp'] ?? '');
                $nb = $bank ?? ($ident['nama_bank'] ?? '');
                $nr = $norek ?? ($ident['no_rek'] ?? '');
                $na = $an ?? ($ident['atas_nama_rek'] ?? '');
                if ($nk !== ($ident['nik_ktp'] ?? '') || $nb !== ($ident['nama_bank'] ?? '') || $nr !== ($ident['no_rek'] ?? '') || $na !== ($ident['atas_nama_rek'] ?? '')) {
                    $stmtUpdIdent->execute([(string)$nk, (string)$nb, (string)$nr, (string)$na, $idKaryawan]);
                }
            }

            $koneksi->commit();
            $transOk = false;
            $response['berhasil']++;
            if ($isBaru) { $response['baru']++; }
            else { $response['diupdate']++; }
            $response['debug'][] = "Baris $rowNum: " . ($isBaru ? 'Baru' : 'Update') . " - $namaKey (jab $jabatan, gaji " . ($gaji === null ? '-' : $gaji) . ").";
        } catch (Exception $e) {
            if ($transOk && $koneksi->inTransaction()) $koneksi->rollBack();
            if ($e instanceof PDOException && (($e->errorInfo[1] ?? null) == 1062)) {
                error_log("csv-karyawan duplikat: " . $e->getMessage());
                $response["dilewati"]++;
                $response["debug"][] = "Baris $rowNum: Dilewati - data sudah terdaftar (duplikat).";
            } else {
                if ($e instanceof PDOException) error_log("csv-karyawan DB baris $rowNum: " . $e->getMessage());
                $response["gagal"]++;
                $response["errors"][] = "Baris $rowNum ($nama): " . ($e instanceof PDOException ? "Gagal menyimpan data." : $e->getMessage());
            }
        }
    }

    $response["jabatan_baru"] = array_values(array_unique($jabatanBaru));
    $response["proyek_tanpa_site"] = array_keys($proyekLiar);
    $response["success"] = true;
    $response["message"] = "Import selesai. Baru: {$response['baru']}, Diupdate: {$response['diupdate']}, Dilewati: {$response['dilewati']}, Gagal: {$response['gagal']}";
} catch (Exception $e) {
    error_log('csv-karyawan: ' . $e->getMessage());
    if (isset($koneksi) && $koneksi instanceof PDO && $koneksi->inTransaction()) {
        $koneksi->rollBack();
    }
    $response["error"] = "Gagal membaca file Excel: " . $e->getMessage();
}

echo json_encode($response);
exit;
