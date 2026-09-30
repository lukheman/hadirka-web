<?php
// Service import khusus template baru IPIP:
//   assets/template-csv/Format_Impor_Database_Karyawan_IPIP.xlsx
// Template lama (44 kolom, header baris 1) tetap ditangani
// csv-karyawan.php. File ini HANYA menerima format IPIP
// (judul di baris 2-4, header di baris 5, sub-header baris 6,
//  nomor kolom baris 7, data mulai baris 8, kolom A-CQ).
//
// TIDAK ada perubahan database: hanya kolom yang punya padanan
// di tabel existing yang diambil, sisanya diabaikan.
//
// Pembersihan sebelum import (spasi & simbol tidak penting dibuang):
//   - Semua kolom teks: trim + spasi ganda/tab/newline jadi satu spasi.
//   - NIK Karyawan, RT/RW: seluruh whitespace dibuang (strip tetap ada).
//   - NIK KTP, No KK, NPWP, BPJS TK/KES, No Rekening: digit murni
//     ("74.011.812.039.900.001", "09.123.456.7-890.000" -> digit saja).
//   - Telepon: buang spasi/koma/titik/strip/kurung, "+62" -> "0",
//     "O822..." -> "0822...", "812..." -> "0812...".
//   - Email: buang spasi + huruf kecil semua.
//   - Jabatan: tulisan Cina dibuang ("Op.Grader/..." -> "OPERATOR GRADER").
// Pemetaan (notasi baris 8 = baris data pertama):
//   A8 No -> diabaikan
//   B8 NIK Karyawan -> tb_karyawan.nik_karyawan (duplikat -> dilewati)
//   C8 Nama Lengkap Karyawan -> tb_karyawan.nama_lengkap
//   D8 Jenis Kelamin -> tb_karyawan.jenis_kelamin ("Laki-Laki"->L)
//   E8 Perusahaan -> diabaikan (badan usaha, bukan site penempatan)
//   F8 Department -> tb_karier.id_departemen (Cina dibuang)
//   G8 DIVISI -> diabaikan (tidak ada kolom DB)
//   H8 Jabatan Karyawan -> tb_karier.id_jabatan ("Op."->OPERATOR)
//   I8 Level-Gol, J8 Level-Gol (kedua), K8 Level-Level -> diabaikan
//   L8 POH -> diabaikan
//   M8 Roster-Roster, N8 Roster-Travel -> diabaikan
//   O8 Working Location -> tb_karier.id_site (kosong = default PT. IPIP)
//   P8 Tgl Masuk Site -> tb_karier.tgl_mulai (utama)
//   Q8 Join Date (in IPIP) -> tb_karier.tgl_mulai (fallback bila P8 kosong)
//   R8 LOS (yy.mm), S8 LOS (yy), T8 LOS Tag -> diabaikan
//   U8 Payroll Base -> diabaikan
//   V8 Tempat Tanggal Lahir -> tb_karyawan.tempat_lahir
//   W8 Tanggal Lahir -> tb_karyawan.tgl_lahir
//   X8 Age (yy:mm), Y8 Age Tag -> diabaikan
//   Z8 Emp. Typ (TKI/TKA) -> diabaikan
//   AA8 Nationality -> tb_karyawan.nationality
//   AB8 Religion -> tb_karyawan.agama ("Islam/..."->Islam)
//   AC8 Last Education-Education Level -> tb_identitas.education_level
//   AD8 Last Education-Education Tag -> tb_identitas.last_education
//   AE8 Last Education-Major -> tb_identitas.major
//   AF8 Rekening Bank-Account Name -> tb_identitas.atas_nama_rek
//   AG8 Rekening Bank-Account Number -> tb_identitas.no_rek
//   AH8 Rekening Bank-Name & Branch -> tb_identitas.nama_bank
//   AI8 Nama Ibu Kandung -> tb_identitas.nama_ibu
//   AJ8 NIK KTP -> tb_identitas.nik_ktp
//   AK8 DIGIT NIK KTP -> diabaikan (info validasi di Excel saja)
//   AL8 Alamat Sesuai KTP -> tb_identitas.alamat_ktp
//   AM8 RT/RW -> tb_identitas.rt_rw
//   AN8 Desa/Kelurahan -> tb_identitas.desa
//   AO8 Kecamatan -> tb_identitas.kecamatan
//   AP8 Kota/Kabupaten -> tb_identitas.kabupaten
//   AQ8 Provinsi -> tb_identitas.provinsi
//   AR8 Ring -> diabaikan
//   AS8 Personal Email -> tb_karyawan.email_pribadi
//   AT8 No Telp/HP Karyawan -> tb_karyawan.no_telp ("O822..."->"0822...")
//   AU8/AV8/AW8 Emergency 1 (Nama/Hubungan/Telp) -> tb_kontak_darurat urut 1
//   AX8/AY8/AZ8 Emergency 2 (Nama/Hubungan/Telp) -> tb_kontak_darurat urut 2
//   BA8 Jenis Hubungan Kerja -> diabaikan (mis. CONTRACT, tanpa kolom DB)
//   BB8 Status Karyawan -> diabaikan untuk status (berisi jenis kontrak
//     mis. CONTRACT, bukan enum status); status diambil dari CJ8/CF8/CI8
//   BC8 Nomor NPWP -> tb_identitas.npwp
//   BD8 Nomor BPJS TK -> tb_identitas.bpjs_tk
//   BE8 Nomor BPJS KES -> tb_identitas.bpjs_kes
//   BF8 Status Pernikahan -> tb_karyawan.status_pernikahan
//   BG8 No Kartu Keluarga -> tb_identitas.kk_number
//   BH8-CE8 Keluarga (pasangan/anak 1-5) -> diabaikan (tanpa kolom DB)
//   CF8 Non Aktif-Exit Date -> tb_tereliminasi.tgl_keluar
//   CG8 Bulan + CH8 Tahun Keluar -> fallback tgl_keluar (YYYY-MM-01)
//   CI8 Non Aktif-Exit Reason -> jenis keluar + bagian alasan
//   CJ8 Remark Aktif/Non Aktif -> penentu status Aktif vs Non Aktif
//   CK8 Personal File -> diabaikan (tanpa kolom DB)
//   CL8 Keterangan -> tambahan alasan_keluar
//   CM8, CN8 (tanpa header) -> diabaikan
//   CO8 Ring 1, CP8 Ring 2, CQ8 Ring 3 -> diabaikan
//   Status PTKP -> '' (tidak ada di template IPIP)
//   Tgl selesai kontrak -> NULL (tidak ada di template IPIP)

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300');

header("Content-Type: application/json");

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../config/Excel.php'; // Excel::parseDateFlexible()/esc()

use PhpOffice\PhpSpreadsheet\IOFactory;

// ---------------------------------------------------------------- normalisasi

function ipipBersihMaster($value)
{
    $v = trim((string)$value);
    if ($v === '') return '';
    // "Op.Excavator/挖掘机司机" atau "Islam/伊斯兰教": ambil segmen latin pertama.
    if (strpos($v, '/') !== false) {
        foreach (explode('/', $v) as $part) {
            if (preg_match('/[A-Za-z]/', $part)) {
                $v = $part;
                break;
            }
        }
    }
    // Buang karakter CJK: Han/Hiragana/Katakana/Hangul yang menempel tanpa spasi,
    // plus blok simbol & tanda baca CJK (、。，〈〉《》 dsb., U+3000-303F) dan
    // bentuk fullwidth (：，．（） dsb., U+FF00-FFEF).
    $v = preg_replace('/[\x{2E80}-\x{2EFF}\x{2F00}-\x{2FDF}\x{3000}-\x{303F}\x{3200}-\x{32FF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{20000}-\x{2A6DF}\x{3040}-\x{30FF}\x{31F0}-\x{31FF}\x{AC00}-\x{D7AF}\x{FF00}-\x{FFEF}]/u', '', $v);
    // Buang simbol sisa di ujung (mis. "NAMA 、" -> "NAMA").
    $v = preg_replace('/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/u', '', $v);
    return trim(preg_replace('/\s+/', ' ', $v));
}

function ipipKey($value)
{
    return strtolower(ipipBersihMaster($value));
}

function ipipKeyJabatan($value)
{
    return strtolower(ipipNamaJabatanBaru($value));
}

/**
 * Nama jabatan bersih untuk disimpan sebagai master baru.
 * Cina dibuang ("Op.Excavator/..." -> "Op.Excavator"), singkatan
 * dikembangkan ("Op." -> "OPERATOR"), disimpan huruf besar mengikuti
 * konvensi master existing ("OPERATOR EXCAVATOR").
 */
function ipipNamaJabatanBaru($value)
{
    $j = ipipBersihMaster($value);
    $j = preg_replace('/^op\.?\s*/i', 'OPERATOR ', $j);
    $j = preg_replace('/^opr\.?\s*/i', 'OPERATOR ', $j);
    $j = str_replace('.', ' ', $j);
    $j = trim(preg_replace('/\s+/', ' ', $j));
    if ($j === '') return '';
    return strtoupper($j);
}

function ipipJenisKelamin($value)
{
    $v = trim((string)$value);
    if ($v === '') return null;
    if (stripos($v, 'laki') !== false) return 'L';
    if (stripos($v, 'perempuan') !== false || stripos($v, 'wanita') !== false) return 'P';
    if (strpos($v, '女') !== false) return 'P'; // Cina: perempuan
    if (strpos($v, '男') !== false) return 'L'; // Cina: laki-laki
    $upper = strtoupper(trim(preg_replace('/^\[\$-[^\]]+\]/', '', $v)));
    if ($upper === 'L' || $upper === 'P') return $upper;
    return null;
}

function ipipAgama($value)
{
    // "Islam/伊斯兰教" -> "Islam"
    return ipipBersihMaster($value);
}

function ipipTelepon($value)
{
    $t = trim(preg_replace('/\s+/', '', (string)$value));
    // Buang simbol format: ribuan Excel, titik/strip/kurung ("(0821) 435-210" -> "0821435210").
    $t = str_replace([',', '.', '-', '(', ')'], '', $t);
    // Prefix internasional -> lokal ("+62812..." -> "0812...").
    if (str_starts_with($t, '+62')) $t = '0' . substr($t, 3);
    elseif (str_starts_with($t, '+')) $t = substr($t, 1);
    // Typo umum di template: "O822..." (huruf O) maksudnya "0822..."
    if (preg_match('/^[Oo](\d+)$/', $t, $m)) $t = '0' . $m[1];
    // Nomor HP yang kehilangan nol depan saat diketik di Excel (0812...->812...)
    if (preg_match('/^8\d{8,12}$/', $t)) $t = '0' . $t;
    return $t;
}

/**
 * Rapikan teks: buang spasi depan/belakang + ciutkan run whitespace
 * (spasi ganda, tab, newline dari sel Excel) menjadi satu spasi.
 */
function ipipRapikanTeks($value)
{
    return trim((string)preg_replace('/\s+/u', ' ', (string)$value));
}

/** Escape + rapikan untuk kolom teks bebas. */
function ipipEsc($koneksi, $value)
{
    return Excel::esc($koneksi, ipipRapikanTeks($value));
}

/**
 * Bersihkan nomor dokumen menjadi digit murni.
 * Buang spasi, titik/strip ribuan, koma, dan simbol lain
 * ("74.011.812.039.900.001", "09.123.456.7-890.000" -> digit saja).
 */
function ipipBersihId($value)
{
    return preg_replace('/\D+/', '', (string)$value);
}

/** Buang seluruh whitespace (untuk NIK karyawan, RT/RW, email). */
function ipipTanpaSpasi($value)
{
    return preg_replace('/\s+/u', '', (string)$value);
}

/**
 * Stringify angka Excel mentah tanpa cacat presisi/scientific.
 * (string)(float) memakai precision=14 dan bisa membulatkan NIK 16 digit.
 */
function ipipAngka($value)
{
    if (is_int($value)) return (string)$value;
    if (is_float($value)) {
        if (floor($value) == $value && abs($value) < 1e18) {
            return number_format($value, 0, '', '');
        }
        return (string)$value;
    }
    return $value;
}

function ipipStatusPernikahan($value)
{
    $v = strtolower(trim((string)$value));
    if ($v === '') return '';
    if (strpos($v, 'belum') !== false || strpos($v, 'single') !== false || strpos($v, 'lajang') !== false) return 'Belum Menikah';
    if (strpos($v, 'cerai mati') !== false || strpos($v, 'widow') !== false) return 'Cerai Mati';
    if (strpos($v, 'cerai') !== false || strpos($v, 'divorc') !== false) return 'Cerai Hidup';
    if (strpos($v, 'menikah') !== false || strpos($v, 'married') !== false || strpos($v, 'kawin') !== false) return 'Menikah';
    return trim((string)$value);
}

function ipipJenisKeluar($value)
{
    $v = strtolower(trim((string)$value));
    if ($v === '') return null;
    $aliases = [
        'aktif' => 'Aktif',
        'resign' => 'Resign',
        'resignation' => 'Resign',
        'mengundurkan' => 'Resign',
        'habis kontrak' => 'Habis Kontrak',
        'end contract' => 'Habis Kontrak',
        'contract expired' => 'Habis Kontrak',
        'expired' => 'Habis Kontrak',
        'pensiun' => 'Pensiun',
        'pension' => 'Pensiun',
        'phk' => 'PHK',
        'termination' => 'PHK',
        'pecat' => 'PHK',
        'black list' => 'Black List',
        'blacklist' => 'Black List',
        'cuti' => 'Cuti',
    ];
    foreach ($aliases as $key => $label) {
        if (strpos($v, $key) !== false) return $label;
    }
    return null;
}

function ipipAddUpdateField(&$fields, $column, $value, $allowNull = false)
{
    if ($allowNull) {
        if ($value === null || $value === '') {
            $fields[] = "$column = NULL";
        } else {
            $fields[] = "$column = '$value'";
        }
    } elseif ($value !== '' && $value !== null) {
        $fields[] = "$column = '$value'";
    }
}

// ---------------------------------------------------------------- response

$response = [
    "success" => false,
    "preview" => true,
    "berhasil" => 0,
    "diupdate" => 0,
    "gagal" => 0,
    "dilewati" => 0,
    "tertunda" => 0,
    "errors" => [],
    "updated" => [],
    "baru_list" => [],
    "jabatan_baru" => [],
    "jabatan_ditambah" => [],
    "debug" => [],
    "sheet" => "",
    "error" => ""
];

if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    $response["error"] = empty($_FILES)
        ? "File terlalu besar. Cek post_max_size di php.ini."
        : "File Excel belum dipilih atau terjadi error saat upload.";
    echo json_encode($response);
    exit;
}

$file = $_FILES['file_excel']['tmp_name'];
$file_ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, ['xls', 'xlsx'])) {
    $response["error"] = "Format file tidak didukung. Harus XLS atau XLSX.";
    echo json_encode($response);
    exit;
}

// ---------------------------------------------------------------- mode
// Dua tahap: pratinjau dulu, terapkan setelah konfirmasi user.
// Tulis ke database HANYA bila mode=apply & confirm=1; selain itu
// service berjalan dry-run (tidak ada perubahan database).
$mode = strtolower(trim((string)($_POST['mode'] ?? 'preview')));
$dryRun = !($mode === 'apply' && trim((string)($_POST['confirm'] ?? '')) === '1');
$response["preview"] = $dryRun;

// Daftar jabatan baru yang disetujui user pada tahap konfirmasi (JSON array).
$approved = [];
if (!$dryRun) {
    $dec = json_decode((string)($_POST['jabatan_disetujui'] ?? '[]'), true);
    if (is_array($dec)) {
        foreach ($dec as $nm) {
            $nm = trim((string)$nm);
            if ($nm !== '') $approved[strtolower($nm)] = $nm;
        }
    }
}

// ---------------------------------------------------------------- cache master

$cache_site = [];
$qSite = $koneksi->query("SELECT id_site, LOWER(TRIM(nama_site)) as nama FROM tb_site ORDER BY id_site ASC");
if ($qSite) while ($r = $qSite->fetch_assoc()) {
    // Master mengandung duplikat nama: pakai id terkecil agar konsisten.
    if (!isset($cache_site[$r['nama']])) $cache_site[$r['nama']] = $r['id_site'];
}

$cache_dept = [];
$qDept = $koneksi->query("SELECT id_departemen, LOWER(TRIM(nama_departemen)) as nama FROM tb_departemen ORDER BY id_departemen ASC");
if ($qDept) while ($r = $qDept->fetch_assoc()) {
    if (!isset($cache_dept[$r['nama']])) $cache_dept[$r['nama']] = $r['id_departemen'];
}

$cache_jabatan = [];
$qJabatan = $koneksi->query("SELECT id_jabatan, LOWER(TRIM(nama_jabatan)) as nama FROM tb_jabatan ORDER BY id_jabatan ASC");
if ($qJabatan) while ($r = $qJabatan->fetch_assoc()) {
    if (!isset($cache_jabatan[$r['nama']])) $cache_jabatan[$r['nama']] = $r['id_jabatan'];
}

$cache_jenis_keluar = [];
$qJenis = $koneksi->query("SELECT id_jenis_keluar, LOWER(TRIM(nama_kategori)) as nama FROM m_jenis_keluar ORDER BY id_jenis_keluar ASC");
if ($qJenis) while ($r = $qJenis->fetch_assoc()) {
    if (!isset($cache_jenis_keluar[$r['nama']])) $cache_jenis_keluar[$r['nama']] = $r['id_jenis_keluar'];
}

// Nama master untuk laporan perubahan (id -> nama).
$nama_site = [];
$q = $koneksi->query("SELECT id_site, nama_site FROM tb_site");
if ($q) while ($r = $q->fetch_assoc()) $nama_site[(int)$r['id_site']] = $r['nama_site'];
$nama_dept = [];
$q = $koneksi->query("SELECT id_departemen, nama_departemen FROM tb_departemen");
if ($q) while ($r = $q->fetch_assoc()) $nama_dept[(int)$r['id_departemen']] = $r['nama_departemen'];
$nama_jabatan = [];
$q = $koneksi->query("SELECT id_jabatan, nama_jabatan FROM tb_jabatan");
if ($q) while ($r = $q->fetch_assoc()) $nama_jabatan[(int)$r['id_jabatan']] = $r['nama_jabatan'];
$nama_jenis_keluar = [];
$q = $koneksi->query("SELECT id_jenis_keluar, nama_kategori FROM m_jenis_keluar");
if ($q) while ($r = $q->fetch_assoc()) $nama_jenis_keluar[(int)$r['id_jenis_keluar']] = $r['nama_kategori'];

// ---------------------------------------------------------------- proses

try {
    $spreadsheet = IOFactory::load($file);

    // Pilihan sheet dari dropdown modal (nama atau index). Kosong = sheet aktif.
    $sheetParam = trim((string)($_POST['sheet'] ?? ''));
    $sheetNames = $spreadsheet->getSheetNames();
    if ($sheetParam === '' || strtolower($sheetParam) === 'auto') {
        $worksheet = $spreadsheet->getActiveSheet();
    } elseif (ctype_digit($sheetParam)) {
        $idx = (int)$sheetParam;
        if (!isset($sheetNames[$idx])) {
            throw new Exception('Sheet ke-' . $idx . ' tidak ada. Pilihan: ' . implode(', ', $sheetNames));
        }
        $worksheet = $spreadsheet->getSheet($idx);
    } else {
        $found = null;
        foreach ($sheetNames as $i => $nm) {
            if (trim($nm) === $sheetParam) { $found = $i; break; }
        }
        if ($found === null) {
            throw new Exception('Sheet "' . $sheetParam . '" tidak ditemukan. Pilihan: ' . implode(', ', $sheetNames));
        }
        $worksheet = $spreadsheet->getSheet($found);
    }
    $sheetTitle = $worksheet->getTitle();
    $response['sheet'] = $sheetTitle;

    // Baca nilai MENTAH (tanpa format tampilan Excel): format ribuan di
    // template ("81,234,567,890", "42.925...") merusak NIK/telepon/NPWP.
    // Serial tanggal tetap diparse Excel::parseDateFlexible(); angka ID
    // dinormalisasi ipipAngka() agar presisi (NIK 16 digit).
    $sheet = $worksheet->toArray(null, true, false, true);
    foreach ($sheet as $rn => $rv) {
        if (!is_array($rv)) continue;
        foreach ($rv as $ck => $cv) {
            if (is_int($cv) || is_float($cv)) $sheet[$rn][$ck] = ipipAngka($cv);
        }
    }

    // Validasi header template IPIP (baris 5). Template lama ditolak di sini
    // dengan pesan yang mengarahkan ke service yang benar.
    $b5 = trim((string)($sheet[5]['B'] ?? ''));
    $c5 = trim((string)($sheet[5]['C'] ?? ''));
    if (stripos($b5, 'NIK') === false || stripos($c5, 'Nama') === false) {
        $b1 = trim((string)($sheet[1]['B'] ?? ''));
        if (strcasecmp($b1, 'NIK Karyawan') === 0) {
            throw new Exception('Sheet "' . $sheetTitle . '" ini template LAMA (header baris 1). Gunakan menu "Import Data", bukan "Import IPIP".');
        }
        throw new Exception('Sheet "' . $sheetTitle . '" bukan template IPIP. Baris 5 harus memuat header "NIK Karyawan" (kolom B) dan "Nama Lengkap Karyawan" (kolom C).');
    }

    foreach ($sheet as $rowNum => $row) {
        if ($rowNum < 8) continue; // baris 1-4 judul, 5 header, 6 sub-header, 7 nomor kolom
        if (trim((string)($row['B'] ?? '')) === '' && trim((string)($row['C'] ?? '')) === '') continue;

        $transaction_started = false;
        try {
            $nik_karyawan = Excel::esc($koneksi, ipipTanpaSpasi($row['B'] ?? ''));
            $nama_lengkap = ipipEsc($koneksi, $row['C'] ?? '');

            // NIK sudah ada -> bandingkan & update bila berubah (di bawah).
            // NIK baru -> insert. Sel kosong pada baris update = pertahankan data lama.
            $existing_id = 0;
            if ($nik_karyawan !== '') {
                $cek = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik_karyawan' LIMIT 1");
                if ($cek && ($tmp = $cek->fetch_assoc())) $existing_id = (int)$tmp['id_karyawan'];
            }

            $jenis_kelamin = ipipJenisKelamin($row['D'] ?? '');

            // Site: dari Working Location (O8). O8 kosong -> default PT. IPIP
            // (file ini master SITE IPIP). E8 (Perusahaan) bukan site.
            $raw_site = trim((string)($row['O'] ?? ''));
            if ($raw_site === '') $raw_site = 'PT. IPIP';
            $id_site = $cache_site[ipipKey($raw_site)] ?? null;
            $site_terpakai = $raw_site;

            $raw_dept = (string)($row['F'] ?? '');
            $id_departemen = $cache_dept[ipipKey($raw_dept)] ?? null;

            $raw_jabatan = (string)($row['H'] ?? '');
            $jabKey = ipipKeyJabatan($raw_jabatan);
            $id_jabatan = $cache_jabatan[$jabKey] ?? null;
            if ($id_jabatan === null && trim($raw_jabatan) !== '') {
                // Cek ulang ke DB (anti balapan: mungkin baru ditambahkan).
                $kunci = $koneksi->real_escape_string($jabKey);
                if (($qj = $koneksi->query("SELECT id_jabatan FROM tb_jabatan WHERE LOWER(TRIM(nama_jabatan))='$kunci' ORDER BY id_jabatan ASC LIMIT 1"))
                    && ($tj = $qj->fetch_assoc())) {
                    $id_jabatan = (int)$tj['id_jabatan'];
                    $cache_jabatan[$jabKey] = $id_jabatan;
                }
            }
            if ($id_jabatan === null && trim($raw_jabatan) !== '') {
                $jabBaru = ipipNamaJabatanBaru($raw_jabatan);
                if ($dryRun) {
                    // Pratinjau: catat kandidat jabatan baru, baris menunggu persetujuan.
                    if (!isset($response['jabatan_baru'][$jabKey])) {
                        $response['jabatan_baru'][$jabKey] = ['nama' => $jabBaru, 'contoh' => trim($raw_jabatan), 'jumlah' => 0, 'baris' => []];
                    }
                    $response['jabatan_baru'][$jabKey]['jumlah']++;
                    $response['jabatan_baru'][$jabKey]['baris'][] = $rowNum;
                    $response['tertunda']++;
                    $response['debug'][] = "Baris $rowNum: Menunggu persetujuan jabatan baru '$jabBaru'.";
                    continue;
                }
                if (!isset($approved[$jabKey])) {
                    throw new Exception("Jabatan '$raw_jabatan' belum disetujui untuk ditambahkan ke master. Ulangi pratinjau dan centang persetujuannya.");
                }
                // Disetujui: tambahkan ke tb_jabatan (tulisan Cina sudah dibuang).
                if (!$koneksi->query("INSERT INTO tb_jabatan (nama_jabatan) VALUES ('" . $koneksi->real_escape_string($jabBaru) . "')")) {
                    throw new Exception('Gagal menambah jabatan: ' . $koneksi->error);
                }
                $id_jabatan = $koneksi->insert_id;
                $cache_jabatan[$jabKey] = $id_jabatan;
                $nama_jabatan[$id_jabatan] = $jabBaru;
                if (!in_array($jabBaru, $response['jabatan_ditambah'], true)) {
                    $response['jabatan_ditambah'][] = $jabBaru;
                }
            }

            $tgl_masuk_site = Excel::parseDateFlexible($row['P'] ?? '');
            $join_ipip      = Excel::parseDateFlexible($row['Q'] ?? '');
            $tgl_mulai = $tgl_masuk_site ?: $join_ipip;

            $tempat_lahir = ipipEsc($koneksi, $row['V'] ?? '');
            $tgl_lahir    = Excel::parseDateFlexible($row['W'] ?? '');

            $nationality = ipipEsc($koneksi, $row['AA'] ?? '');
            $agama       = ipipEsc($koneksi, ipipAgama($row['AB'] ?? ''));

            $education_level = ipipEsc($koneksi, $row['AC'] ?? '');
            $last_education  = ipipEsc($koneksi, $row['AD'] ?? '');
            $major           = ipipEsc($koneksi, $row['AE'] ?? '');

            $atas_nama_rek = ipipEsc($koneksi, $row['AF'] ?? '');
            $no_rek        = Excel::esc($koneksi, ipipBersihId($row['AG'] ?? ''));
            $nama_bank     = ipipEsc($koneksi, $row['AH'] ?? '');

            $nama_ibu = ipipEsc($koneksi, $row['AI'] ?? '');
            $nik_ktp  = Excel::esc($koneksi, ipipBersihId($row['AJ'] ?? ''));

            $alamat_ktp = ipipEsc($koneksi, $row['AL'] ?? '');
            $rt_rw      = Excel::esc($koneksi, ipipTanpaSpasi($row['AM'] ?? ''));
            $desa       = ipipEsc($koneksi, $row['AN'] ?? '');
            $kecamatan  = ipipEsc($koneksi, $row['AO'] ?? '');
            $kabupaten  = ipipEsc($koneksi, $row['AP'] ?? '');
            $provinsi   = ipipEsc($koneksi, $row['AQ'] ?? '');

            $email_pribadi = Excel::esc($koneksi, strtolower(ipipTanpaSpasi($row['AS'] ?? '')));
            $no_telp       = ipipEsc($koneksi, ipipTelepon($row['AT'] ?? ''));

            $nama_kontak1     = ipipEsc($koneksi, $row['AU'] ?? '');
            $hubungan1        = ipipEsc($koneksi, $row['AV'] ?? '');
            $no_telp_darurat1 = ipipEsc($koneksi, ipipTelepon($row['AW'] ?? ''));

            $nama_kontak2     = ipipEsc($koneksi, $row['AX'] ?? '');
            $hubungan2        = ipipEsc($koneksi, $row['AY'] ?? '');
            $no_telp_darurat2 = ipipEsc($koneksi, ipipTelepon($row['AZ'] ?? ''));

            $npwp     = Excel::esc($koneksi, ipipBersihId($row['BC'] ?? ''));
            $bpjs_tk  = Excel::esc($koneksi, ipipBersihId($row['BD'] ?? ''));
            $bpjs_kes = Excel::esc($koneksi, ipipBersihId($row['BE'] ?? ''));

            $status_pernikahan = ipipEsc($koneksi, ipipStatusPernikahan($row['BF'] ?? ''));
            $kk_number         = Excel::esc($koneksi, ipipBersihId($row['BG'] ?? ''));

            // Status: CJ "Aktif" vs "Non Aktif". Data exit (CF/CI) ikut menentukan.
            $raw_cf = $row['CF'] ?? '';
            $raw_ci = trim((string)($row['CI'] ?? ''));
            $raw_cj = trim((string)($row['CJ'] ?? ''));
            $raw_cl = trim((string)($row['CL'] ?? ''));
            $tgl_keluar = Excel::parseDateFlexible($raw_cf);
            if ($tgl_keluar === null) {
                // Fallback: CG bulan + CH tahun -> tanggal 1 bulan tersebut.
                $cg = trim((string)($row['CG'] ?? ''));
                $ch = trim((string)($row['CH'] ?? ''));
                if ($cg !== '' && $ch !== '' && is_numeric($cg) && is_numeric($ch)
                    && (int)$cg >= 1 && (int)$cg <= 12 && (int)$ch >= 1900 && (int)$ch <= 2100) {
                    $tgl_keluar = sprintf('%04d-%02d-01', (int)$ch, (int)$cg);
                }
            }
            $is_non_aktif = stripos($raw_cj, 'non aktif') !== false
                || stripos($raw_cj, 'nonaktif') !== false
                || $tgl_keluar !== null
                || $raw_ci !== '';

            if ($is_non_aktif) {
                $jenis_label = ipipJenisKeluar($raw_ci);
                if ($jenis_label === null) {
                    // CI kosong/tidak dikenal -> default Resign agar baris tetap bisa diproses.
                    $jenis_label = 'Resign';
                    if ($raw_ci !== '') {
                        $response['debug'][] = "Baris $rowNum: Alasan '$raw_ci' tidak dikenal, dipakai 'Resign'.";
                    }
                }
                $status_karyawan = $jenis_label;
                if ($tgl_keluar === null) {
                    throw new Exception("Karyawan Non Aktif tapi tanggal keluar kosong (kolom CF/CG-CH).");
                }
                $alasan_keluar = ipipEsc($koneksi, trim($raw_ci . ($raw_cl !== '' ? ' | ' . $raw_cl : '')));
                if ($alasan_keluar === '') $alasan_keluar = ipipEsc($koneksi, 'Non Aktif (template IPIP)');
            } else {
                $status_karyawan = 'Aktif';
                $alasan_keluar = '';
            }

            // --- validasi format (berlaku untuk insert & update) ---
            if ($email_pribadi !== '' && !filter_var($email_pribadi, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email '$email_pribadi' tidak valid.");
            }
            if (trim((string)($row['P'] ?? '')) !== '' && trim((string)($row['Q'] ?? '')) !== ''
                && $tgl_masuk_site === null && $join_ipip === null) {
                throw new Exception('Tanggal masuk site (P) / join IPIP (Q) tidak valid.');
            }
            if (trim((string)($row['W'] ?? '')) !== '' && $tgl_lahir === null) {
                throw new Exception('Tanggal lahir (W) tidak valid.');
            }
            if (trim((string)$raw_cf) !== '' && Excel::parseDateFlexible($raw_cf) === null
                && $tgl_keluar === null) {
                throw new Exception('Tanggal keluar (CF) tidak valid.');
            }

            if (!$existing_id) {
            // --- validasi wajib insert ---
            if ($nik_karyawan === '' || $nama_lengkap === '') {
                throw new Exception('NIK (kolom B) atau Nama (kolom C) kosong.');
            }
            if ($jenis_kelamin === null) {
                throw new Exception("Jenis kelamin '" . trim((string)($row['D'] ?? '')) . "' tidak valid. Gunakan Laki-Laki/Perempuan atau L/P.");
            }
            if (!$id_site || !$id_departemen || !$id_jabatan) {
                throw new Exception("Site '{$site_terpakai}' atau Dept '$raw_dept' tidak ditemukan di master.");
            }

            if ($dryRun) {
                // Pratinjau: catat tanpa menulis database.
                $response['berhasil']++;
                $response['baru_list'][] = ['nik' => $nik_karyawan, 'nama' => trim(stripslashes($nama_lengkap))];
                $response['debug'][] = "Baris $rowNum: Pratinjau - NIK '$nik_karyawan' akan ditambah.";
                continue;
            }

            $koneksi->begin_transaction();
            $transaction_started = true;

            $sqlKaryawan = "INSERT INTO tb_karyawan (
                    nik_karyawan, nama_lengkap, jenis_kelamin, tgl_lahir, tempat_lahir,
                    email_pribadi, no_telp, nationality, agama, status_pernikahan, status_ptkp,
                    status_karyawan, password
                ) VALUES (
                    '$nik_karyawan', '$nama_lengkap', '$jenis_kelamin',
                    " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",
                    '$tempat_lahir', '$email_pribadi', '$no_telp', '$nationality',
                    '$agama', '$status_pernikahan', '', '$status_karyawan', ''
            )";
            if (!$koneksi->query($sqlKaryawan)) throw new Exception("Error insert tb_karyawan: " . $koneksi->error);
            $id_karyawan = $koneksi->insert_id;

            $sqlIdentitas = "INSERT INTO tb_identitas (
                    id_karyawan, nik_ktp, kk_number, npwp, bpjs_tk, bpjs_kes,
                    alamat_ktp, provinsi, kabupaten, kecamatan, desa, rt_rw, nama_ibu,
                    last_education, major, education_level, nama_bank, no_rek, atas_nama_rek
                ) VALUES (
                    '$id_karyawan', '$nik_ktp', '$kk_number', '$npwp', '$bpjs_tk',
                    '$bpjs_kes', '$alamat_ktp', '$provinsi', '$kabupaten', '$kecamatan',
                    '$desa', '$rt_rw', '$nama_ibu', '$last_education', '$major',
                    '$education_level', '$nama_bank', '$no_rek', '$atas_nama_rek'
                )";
            if (!$koneksi->query($sqlIdentitas)) throw new Exception("Error insert tb_identitas: " . $koneksi->error);

            if ($nama_kontak1 !== '') {
                $sqlKontak1 = "INSERT INTO tb_kontak_darurat (id_karyawan, nama_kontak, hubungan, no_telp_darurat, nomor_urut) VALUES ('$id_karyawan', '$nama_kontak1', '$hubungan1', '$no_telp_darurat1', 1)";
                if (!$koneksi->query($sqlKontak1)) throw new Exception("Error insert kontak 1: " . $koneksi->error);
            }
            if ($nama_kontak2 !== '') {
                $sqlKontak2 = "INSERT INTO tb_kontak_darurat (id_karyawan, nama_kontak, hubungan, no_telp_darurat, nomor_urut) VALUES ('$id_karyawan', '$nama_kontak2', '$hubungan2', '$no_telp_darurat2', 2)";
                if (!$koneksi->query($sqlKontak2)) throw new Exception("Error insert kontak 2: " . $koneksi->error);
            }

            // Template IPIP tidak memuat tanggal selesai kontrak -> NULL.
            $sqlKarierInsert = "INSERT INTO tb_karier (
                    id_karyawan, id_site, id_departemen, id_jabatan,
                    spl, ot1_spl, ot2_spl, pot_izin, pot_mangkir,
                    tgl_mulai, tgl_selesai
                ) VALUES (
                    '$id_karyawan', '$id_site', '$id_departemen', '$id_jabatan',
                    '', '', '', '', '',
                    " . ($tgl_mulai ? "'$tgl_mulai'" : "NULL") . ",
                    NULL
                )";
            if (!$koneksi->query($sqlKarierInsert)) throw new Exception("Error tb_karier: " . $koneksi->error);

            if (strtolower($status_karyawan) !== 'aktif') {
                $id_jenis_keluar = $cache_jenis_keluar[strtolower($status_karyawan)] ?? null;
                if (!$id_jenis_keluar) throw new Exception("Jenis keluar '$status_karyawan' tidak ditemukan di m_jenis_keluar");
                $cekEliminasi = $koneksi->query("SELECT id_tereliminasi FROM tb_tereliminasi WHERE id_karyawan='$id_karyawan'");
                if ($cekEliminasi && $cekEliminasi->num_rows > 0) {
                    $sqlEliminasi = "UPDATE tb_tereliminasi SET tgl_keluar = '$tgl_keluar', id_jenis_keluar = '$id_jenis_keluar', alasan_keluar = '$alasan_keluar' WHERE id_karyawan = '$id_karyawan'";
                } else {
                    $sqlEliminasi = "INSERT INTO tb_tereliminasi (id_karyawan, tgl_keluar, id_jenis_keluar, alasan_keluar, created_at) VALUES ('$id_karyawan', '$tgl_keluar', '$id_jenis_keluar', '$alasan_keluar', NOW())";
                }
                if (!$koneksi->query($sqlEliminasi)) throw new Exception("Error tb_tereliminasi: " . $koneksi->error);
            } else {
                $koneksi->query("DELETE FROM tb_tereliminasi WHERE id_karyawan='$id_karyawan'");
            }

            $response["berhasil"]++;
            $response["debug"][] = "Baris $rowNum: Berhasil - NIK: $nik_karyawan";

            $koneksi->commit();
            $transaction_started = false;
            } else {
            // ================= UPDATE (NIK sudah terdaftar) =================
            // Sel template yang kosong = pertahankan data lama (tidak dihapus).
            if ($nik_karyawan === '') {
                throw new Exception('NIK (kolom B) kosong, tidak bisa mencocokkan data existing.');
            }
            $rq = $koneksi->query("SELECT * FROM tb_karyawan WHERE id_karyawan='$existing_id' LIMIT 1");
            $ex_k = $rq ? $rq->fetch_assoc() : null;
            if (!$ex_k) throw new Exception("Data existing NIK '$nik_karyawan' tidak ditemukan.");
            $ex_i = [];
            if (($q = $koneksi->query("SELECT * FROM tb_identitas WHERE id_karyawan='$existing_id' LIMIT 1")) && ($t = $q->fetch_assoc())) $ex_i = $t;
            $ex_kr = [];
            if (($q = $koneksi->query("SELECT * FROM tb_karier WHERE id_karyawan='$existing_id' ORDER BY id_karier DESC LIMIT 1")) && ($t = $q->fetch_assoc())) $ex_kr = $t;
            $ex_c1 = [];
            if (($q = $koneksi->query("SELECT * FROM tb_kontak_darurat WHERE id_karyawan='$existing_id' AND nomor_urut=1 LIMIT 1")) && ($t = $q->fetch_assoc())) $ex_c1 = $t;
            $ex_c2 = [];
            if (($q = $koneksi->query("SELECT * FROM tb_kontak_darurat WHERE id_karyawan='$existing_id' AND nomor_urut=2 LIMIT 1")) && ($t = $q->fetch_assoc())) $ex_c2 = $t;
            $ex_t = [];
            if (($q = $koneksi->query("SELECT * FROM tb_tereliminasi WHERE id_karyawan='$existing_id' LIMIT 1")) && ($t = $q->fetch_assoc())) $ex_t = $t;
            $ex_jenis_label = '';
            if (!empty($ex_t['id_jenis_keluar'])) {
                $qid = (int)$ex_t['id_jenis_keluar'];
                $ex_jenis_label = $nama_jenis_keluar[$qid] ?? '';
            }

            $perubahan = []; // tiap entri: [label, lama, baru]
            $setK = []; $setI = []; $setR = [];

            // Bandingkan kolom teks: sel kosong -> lewati (pertahankan lama).
            $cekTeks = function ($label, $kolom, $old, $newEsc, &$sets) use (&$perubahan) {
                $n = trim(stripslashes((string)$newEsc));
                if ($n === '') return;
                $o = trim((string)$old);
                if ($o === $n) return;
                $perubahan[] = [$label, $o === '' ? '-' : $o, $n];
                $sets[] = "$kolom = '$newEsc'";
            };
            $namaFk = function ($map, $id) {
                if ($id === null || $id === '') return '-';
                return $map[(int)$id] ?? ('#' . $id);
            };

            $cekTeks('Nama Lengkap', 'nama_lengkap', $ex_k['nama_lengkap'] ?? '', $nama_lengkap, $setK);
            if ($jenis_kelamin !== null) {
                $o = trim((string)($ex_k['jenis_kelamin'] ?? ''));
                if ($o !== $jenis_kelamin) {
                    $perubahan[] = ['Jenis Kelamin', $o === '' ? '-' : $o, $jenis_kelamin];
                    $setK[] = "jenis_kelamin = '$jenis_kelamin'";
                }
            }
            if ($tgl_lahir !== null) {
                $o = trim((string)($ex_k['tgl_lahir'] ?? ''));
                if ($o !== $tgl_lahir) {
                    $perubahan[] = ['Tanggal Lahir', $o === '' ? '-' : $o, $tgl_lahir];
                    $setK[] = "tgl_lahir = '$tgl_lahir'";
                }
            }
            $cekTeks('Tempat Lahir', 'tempat_lahir', $ex_k['tempat_lahir'] ?? '', $tempat_lahir, $setK);
            // Email dibandingkan case-insensitive.
            $oEm = trim((string)($ex_k['email_pribadi'] ?? ''));
            $nEm = trim(stripslashes($email_pribadi));
            if ($nEm !== '' && strtolower($oEm) !== strtolower($nEm)) {
                $perubahan[] = ['Email Pribadi', $oEm === '' ? '-' : $oEm, $nEm];
                $setK[] = "email_pribadi = '$email_pribadi'";
            }
            // Telepon dinormalisasi di kedua sisi sebelum dibandingkan.
            $oTel = ipipTelepon($ex_k['no_telp'] ?? '');
            if ($no_telp !== '' && $oTel !== $no_telp) {
                $perubahan[] = ['No Telepon', $oTel === '' ? '-' : $oTel, trim(stripslashes($no_telp))];
                $setK[] = "no_telp = '$no_telp'";
            }
            $cekTeks('Kewarganegaraan', 'nationality', $ex_k['nationality'] ?? '', $nationality, $setK);
            $cekTeks('Agama', 'agama', $ex_k['agama'] ?? '', $agama, $setK);
            $cekTeks('Status Pernikahan', 'status_pernikahan', $ex_k['status_pernikahan'] ?? '', $status_pernikahan, $setK);
            // Status selalu dibandingkan (ditentukan dari CJ8/CF8/CI8).
            $oSt = trim((string)($ex_k['status_karyawan'] ?? ''));
            if ($oSt !== $status_karyawan) {
                $perubahan[] = ['Status Karyawan', $oSt === '' ? '-' : $oSt, $status_karyawan];
                $setK[] = "status_karyawan = '$status_karyawan'";
            }

            $cekTeks('NIK KTP', 'nik_ktp', $ex_i['nik_ktp'] ?? '', $nik_ktp, $setI);
            $cekTeks('No KK', 'kk_number', $ex_i['kk_number'] ?? '', $kk_number, $setI);
            $cekTeks('NPWP', 'npwp', $ex_i['npwp'] ?? '', $npwp, $setI);
            $cekTeks('BPJS TK', 'bpjs_tk', $ex_i['bpjs_tk'] ?? '', $bpjs_tk, $setI);
            $cekTeks('BPJS Kesehatan', 'bpjs_kes', $ex_i['bpjs_kes'] ?? '', $bpjs_kes, $setI);
            $cekTeks('Alamat KTP', 'alamat_ktp', $ex_i['alamat_ktp'] ?? '', $alamat_ktp, $setI);
            $cekTeks('Provinsi', 'provinsi', $ex_i['provinsi'] ?? '', $provinsi, $setI);
            $cekTeks('Kabupaten/Kota', 'kabupaten', $ex_i['kabupaten'] ?? '', $kabupaten, $setI);
            $cekTeks('Kecamatan', 'kecamatan', $ex_i['kecamatan'] ?? '', $kecamatan, $setI);
            $cekTeks('Desa/Kelurahan', 'desa', $ex_i['desa'] ?? '', $desa, $setI);
            $cekTeks('RT/RW', 'rt_rw', $ex_i['rt_rw'] ?? '', $rt_rw, $setI);
            $cekTeks('Nama Ibu Kandung', 'nama_ibu', $ex_i['nama_ibu'] ?? '', $nama_ibu, $setI);
            $cekTeks('Pendidikan Terakhir', 'last_education', $ex_i['last_education'] ?? '', $last_education, $setI);
            $cekTeks('Jurusan', 'major', $ex_i['major'] ?? '', $major, $setI);
            $cekTeks('Jenjang Pendidikan', 'education_level', $ex_i['education_level'] ?? '', $education_level, $setI);
            $cekTeks('Nama Bank', 'nama_bank', $ex_i['nama_bank'] ?? '', $nama_bank, $setI);
            $cekTeks('Nomor Rekening', 'no_rek', $ex_i['no_rek'] ?? '', $no_rek, $setI);
            $cekTeks('Atas Nama Rekening', 'atas_nama_rek', $ex_i['atas_nama_rek'] ?? '', $atas_nama_rek, $setI);

            // Master penempatan: hanya dicek bila sel template terisi
            // (site selalu terisi karena default PT. IPIP).
            if (!$id_site) throw new Exception("Site '{$site_terpakai}' tidak ditemukan di master (tambahkan PT. IPIP bila O8 dikosongkan).");
            $deptDiisi = trim($raw_dept) !== '';
            if ($deptDiisi && !$id_departemen) throw new Exception("Departemen '$raw_dept' tidak ditemukan di master.");
            $jabDiisi = trim($raw_jabatan) !== '';
            if ($jabDiisi && !$id_jabatan) throw new Exception("Jabatan '$raw_jabatan' tidak ditemukan di master.");
            if ($id_site !== null) {
                // Bandingkan berdasarkan NAMA (case-insensitive): master mengandung
                // duplikat (mis. LOGISTIK id 2 & 7) sehingga id bisa beda padahal
                // nilainya sama. Id existing dipertahankan bila nama sama.
                $oName = $namaFk($nama_site, $ex_kr['id_site'] ?? '');
                $nName = $namaFk($nama_site, $id_site);
                if (strtolower(trim($oName)) !== strtolower(trim($nName))) {
                    $perubahan[] = ['Site', $oName, $nName];
                    $setR[] = "id_site = '$id_site'";
                }
            }
            if ($id_departemen !== null) {
                $oName = $namaFk($nama_dept, $ex_kr['id_departemen'] ?? '');
                $nName = $namaFk($nama_dept, $id_departemen);
                if (strtolower(trim($oName)) !== strtolower(trim($nName))) {
                    $perubahan[] = ['Departemen', $oName, $nName];
                    $setR[] = "id_departemen = '$id_departemen'";
                }
            }
            if ($id_jabatan !== null) {
                $oName = $namaFk($nama_jabatan, $ex_kr['id_jabatan'] ?? '');
                $nName = $namaFk($nama_jabatan, $id_jabatan);
                if (strtolower(trim($oName)) !== strtolower(trim($nName))) {
                    $perubahan[] = ['Jabatan', $oName, $nName];
                    $setR[] = "id_jabatan = '$id_jabatan'";
                }
            }
            if ($tgl_mulai !== null) {
                $o = trim((string)($ex_kr['tgl_mulai'] ?? ''));
                if ($o !== $tgl_mulai) {
                    $perubahan[] = ['Tgl Masuk Site', $o === '' ? '-' : $o, $tgl_mulai];
                    $setR[] = "tgl_mulai = '$tgl_mulai'";
                }
            }

            // Kontak darurat: nama kosong -> pertahankan.
            $kontakOps = [];
            foreach ([1 => [$ex_c1, $nama_kontak1, $hubungan1, $no_telp_darurat1],
                       2 => [$ex_c2, $nama_kontak2, $hubungan2, $no_telp_darurat2]] as $urut => $slot) {
                [$ex_c, $nmEsc, $hubEsc, $telEsc] = $slot;
                $n = trim(stripslashes($nmEsc));
                if ($n === '') continue;
                $h = trim(stripslashes($hubEsc));
                $t = trim(stripslashes($telEsc));
                if (empty($ex_c)) {
                    $perubahan[] = ["Kontak Darurat $urut", '-', "$n / $h / $t"];
                    $kontakOps[] = "INSERT INTO tb_kontak_darurat (id_karyawan, nama_kontak, hubungan, no_telp_darurat, nomor_urut) VALUES ('$existing_id', '$nmEsc', '$hubEsc', '$telEsc', $urut)";
                } else {
                    $oN = trim((string)($ex_c['nama_kontak'] ?? ''));
                    $oH = trim((string)($ex_c['hubungan'] ?? ''));
                    $oT = ipipTelepon($ex_c['no_telp_darurat'] ?? '');
                    if ($oN !== $n || $oH !== $h || $oT !== $t) {
                        $perubahan[] = ["Kontak Darurat $urut", "$oN / $oH / $oT", "$n / $h / $t"];
                        $kontakOps[] = "UPDATE tb_kontak_darurat SET nama_kontak='$nmEsc', hubungan='$hubEsc', no_telp_darurat='$telEsc' WHERE id_karyawan='$existing_id' AND nomor_urut=$urut";
                    }
                }
            }

            // Data keluar: rekonsiliasi mengikuti status baru.
            $elimOps = [];
            if (strtolower($status_karyawan) !== 'aktif') {
                $id_jenis_keluar = $cache_jenis_keluar[strtolower($status_karyawan)] ?? null;
                if (!$id_jenis_keluar) throw new Exception("Jenis keluar '$status_karyawan' tidak ditemukan di m_jenis_keluar");
                $nAl = trim(stripslashes($alasan_keluar));
                if (empty($ex_t)) {
                    $perubahan[] = ['Data Keluar', '-', "$status_karyawan, $tgl_keluar"];
                    $elimOps[] = "INSERT INTO tb_tereliminasi (id_karyawan, tgl_keluar, id_jenis_keluar, alasan_keluar, created_at) VALUES ('$existing_id', '$tgl_keluar', '$id_jenis_keluar', '$alasan_keluar', NOW())";
                } else {
                    $oTgl = trim((string)($ex_t['tgl_keluar'] ?? ''));
                    $oAl = trim((string)($ex_t['alasan_keluar'] ?? ''));
                    if ($oTgl !== $tgl_keluar || $ex_jenis_label !== $status_karyawan || $oAl !== $nAl) {
                        $oR = trim("$ex_jenis_label, $oTgl, $oAl", ', ');
                        $perubahan[] = ['Data Keluar', $oR === '' ? '-' : $oR, "$status_karyawan, $tgl_keluar, $nAl"];
                        $elimOps[] = "UPDATE tb_tereliminasi SET tgl_keluar='$tgl_keluar', id_jenis_keluar='$id_jenis_keluar', alasan_keluar='$alasan_keluar' WHERE id_karyawan='$existing_id'";
                    }
                }
            } elseif (!empty($ex_t)) {
                $oR = trim("$ex_jenis_label, " . ($ex_t['tgl_keluar'] ?? ''), ', ');
                $perubahan[] = ['Data Keluar', $oR === '' ? 'ada' : $oR, 'dihapus (kembali Aktif)'];
                $elimOps[] = "DELETE FROM tb_tereliminasi WHERE id_karyawan='$existing_id'";
            }

            if (empty($perubahan)) {
                $response['dilewati']++;
                $response['debug'][] = "Baris $rowNum: Dilewati - NIK '$nik_karyawan' tidak ada perubahan.";
                continue;
            }

            $namaTampil = trim(stripslashes($nama_lengkap)) !== '' ? trim(stripslashes($nama_lengkap)) : trim((string)($ex_k['nama_lengkap'] ?? ''));
            $daftar = [];
            foreach ($perubahan as $p) $daftar[] = "{$p[0]}: '{$p[1]}' → '{$p[2]}'";
            $response['diupdate']++;
            $response['updated'][] = ['nik' => $nik_karyawan, 'nama' => $namaTampil, 'perubahan' => $daftar];
            if ($dryRun) {
                // Pratinjau: catat tanpa menulis database.
                $response['debug'][] = "Baris $rowNum: Pratinjau - NIK '$nik_karyawan' akan diupdate (" . count($perubahan) . ' perubahan).';
                continue;
            }

            $koneksi->begin_transaction();
            $transaction_started = true;

            if (!empty($setK)) {
                if (!$koneksi->query("UPDATE tb_karyawan SET " . implode(', ', $setK) . " WHERE id_karyawan='$existing_id'")) {
                    throw new Exception('Error update tb_karyawan: ' . $koneksi->error);
                }
            }
            if (!empty($setI)) {
                if (!empty($ex_i)) {
                    if (!$koneksi->query("UPDATE tb_identitas SET " . implode(', ', $setI) . " WHERE id_karyawan='$existing_id'")) {
                        throw new Exception('Error update tb_identitas: ' . $koneksi->error);
                    }
                } else {
                    $sqlI = "INSERT INTO tb_identitas (id_karyawan, nik_ktp, kk_number, npwp, bpjs_tk, bpjs_kes, alamat_ktp, provinsi, kabupaten, kecamatan, desa, rt_rw, nama_ibu, last_education, major, education_level, nama_bank, no_rek, atas_nama_rek)
                        VALUES ('$existing_id', '$nik_ktp', '$kk_number', '$npwp', '$bpjs_tk', '$bpjs_kes', '$alamat_ktp', '$provinsi', '$kabupaten', '$kecamatan', '$desa', '$rt_rw', '$nama_ibu', '$last_education', '$major', '$education_level', '$nama_bank', '$no_rek', '$atas_nama_rek')";
                    if (!$koneksi->query($sqlI)) throw new Exception('Error insert tb_identitas: ' . $koneksi->error);
                }
            }
            if (!empty($setR)) {
                if (!empty($ex_kr)) {
                    if (!$koneksi->query("UPDATE tb_karier SET " . implode(', ', $setR) . " WHERE id_karier='" . (int)$ex_kr['id_karier'] . "'")) {
                        throw new Exception('Error update tb_karier: ' . $koneksi->error);
                    }
                } else {
                    $sqlR = "INSERT INTO tb_karier (id_karyawan, id_site, id_departemen, id_jabatan, spl, ot1_spl, ot2_spl, pot_izin, pot_mangkir, tgl_mulai, tgl_selesai)
                        VALUES ('$existing_id', '$id_site', '$id_departemen', '$id_jabatan', '', '', '', '', '', " . ($tgl_mulai ? "'$tgl_mulai'" : 'NULL') . ", NULL)";
                    if (!$koneksi->query($sqlR)) throw new Exception('Error insert tb_karier: ' . $koneksi->error);
                }
            }
            foreach ($kontakOps as $sqlOp) {
                if (!$koneksi->query($sqlOp)) throw new Exception('Error kontak darurat: ' . $koneksi->error);
            }
            foreach ($elimOps as $sqlOp) {
                if (!$koneksi->query($sqlOp)) throw new Exception('Error tb_tereliminasi: ' . $koneksi->error);
            }

            $koneksi->commit();
            $transaction_started = false;

            $response['debug'][] = "Baris $rowNum: Diupdate - NIK '$nik_karyawan' (" . count($perubahan) . ' perubahan).';
            }
        } catch (Exception $e) {
            if ($transaction_started) $koneksi->rollback();
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: " . $e->getMessage();
        }
    }

    $response["success"] = true;
    $response["jabatan_baru"] = array_values($response["jabatan_baru"]);
    $nJab = count($response["jabatan_baru"]);
    if ($dryRun) {
        $response["message"] = "Pratinjau IPIP [{$sheetTitle}] selesai. Baru: {$response['berhasil']}, Update: {$response['diupdate']}, Tertunda (jabatan baru): {$response['tertunda']}, Dilewati: {$response['dilewati']}, Gagal: {$response['gagal']}, Jabatan baru: $nJab. Belum ada perubahan database.";
    } else {
        $response["message"] = "Import IPIP [{$sheetTitle}] selesai. Berhasil: {$response['berhasil']}, Diupdate: {$response['diupdate']}, Dilewati: {$response['dilewati']}, Gagal: {$response['gagal']}, Jabatan ditambah: " . count($response['jabatan_ditambah']);
    }
} catch (Exception $e) {
    $response["error"] = "Gagal membaca file Excel: " . $e->getMessage();
}

echo json_encode($response);
exit;
