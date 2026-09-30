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
// Pemetaan (notasi baris 8 = baris data pertama):
//   A8 No -> diabaikan
//   B8 NIK Karyawan -> tb_karyawan.nik_karyawan (duplikat -> dilewati)
//   C8 Nama Lengkap Karyawan -> tb_karyawan.nama_lengkap
//   D8 Jenis Kelamin -> tb_karyawan.jenis_kelamin ("Laki-Laki"->L)
//   E8 Perusahaan -> kandidat tb_karier.id_site (dipakai bila O8 kosong)
//   F8 Department -> tb_karier.id_departemen (Cina dibuang)
//   G8 DIVISI -> diabaikan (tidak ada kolom DB)
//   H8 Jabatan Karyawan -> tb_karier.id_jabatan ("Op."->OPERATOR)
//   I8 Level-Gol, J8 Level-Gol (kedua), K8 Level-Level -> diabaikan
//   L8 POH -> diabaikan
//   M8 Roster-Roster, N8 Roster-Travel -> diabaikan
//   O8 Working Location -> kandidat utama tb_karier.id_site
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
    // Buang karakter CJK (Han/Hiragana/Katakana/Hangul) yang menempel tanpa spasi.
    $v = preg_replace('/[\x{4E00}-\x{9FFF}\x{3400}-\x{4DBF}\x{20000}-\x{2A6DF}\x{3040}-\x{30FF}\x{AC00}-\x{D7AF}]/u', '', $v);
    return trim(preg_replace('/\s+/', ' ', $v));
}

function ipipKey($value)
{
    return strtolower(ipipBersihMaster($value));
}

function ipipKeyJabatan($value)
{
    $j = ipipBersihMaster($value);
    $j = preg_replace('/^op\.?\s*/i', 'OPERATOR ', $j);
    $j = preg_replace('/^opr\.?\s*/i', 'OPERATOR ', $j);
    $j = str_replace('.', ' ', $j);
    return strtolower(trim(preg_replace('/\s+/', ' ', $j)));
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
    // Typo umum di template: "O822..." (huruf O) maksudnya "0822..."
    if (preg_match('/^[Oo](\d+)$/', $t, $m)) $t = '0' . $m[1];
    return $t;
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
    "berhasil" => 0,
    "gagal" => 0,
    "dilewati" => 0,
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

$file = $_FILES['file_excel']['tmp_name'];
$file_ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, ['xls', 'xlsx'])) {
    $response["error"] = "Format file tidak didukung. Harus XLS atau XLSX.";
    echo json_encode($response);
    exit;
}

// ---------------------------------------------------------------- cache master

$cache_site = [];
$qSite = $koneksi->query("SELECT id_site, LOWER(TRIM(nama_site)) as nama FROM tb_site");
if ($qSite) while ($r = $qSite->fetch_assoc()) $cache_site[$r['nama']] = $r['id_site'];

$cache_dept = [];
$qDept = $koneksi->query("SELECT id_departemen, LOWER(TRIM(nama_departemen)) as nama FROM tb_departemen");
if ($qDept) while ($r = $qDept->fetch_assoc()) $cache_dept[$r['nama']] = $r['id_departemen'];

$cache_jabatan = [];
$qJabatan = $koneksi->query("SELECT id_jabatan, LOWER(TRIM(nama_jabatan)) as nama FROM tb_jabatan");
if ($qJabatan) while ($r = $qJabatan->fetch_assoc()) $cache_jabatan[$r['nama']] = $r['id_jabatan'];

$cache_jenis_keluar = [];
$qJenis = $koneksi->query("SELECT id_jenis_keluar, LOWER(TRIM(nama_kategori)) as nama FROM m_jenis_keluar");
if ($qJenis) while ($r = $qJenis->fetch_assoc()) $cache_jenis_keluar[$r['nama']] = $r['id_jenis_keluar'];

// ---------------------------------------------------------------- proses

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    // Validasi header template IPIP (baris 5). Template lama ditolak di sini
    // dengan pesan yang mengarahkan ke service yang benar.
    $b5 = trim((string)($sheet[5]['B'] ?? ''));
    $c5 = trim((string)($sheet[5]['C'] ?? ''));
    if (stripos($b5, 'NIK') === false || stripos($c5, 'Nama') === false) {
        $b1 = trim((string)($sheet[1]['B'] ?? ''));
        if (strcasecmp($b1, 'NIK Karyawan') === 0) {
            throw new Exception('Ini template LAMA (header baris 1). Gunakan menu "Import Data", bukan "Import IPIP".');
        }
        throw new Exception('Bukan template IPIP. Baris 5 harus memuat header "NIK Karyawan" (kolom B) dan "Nama Lengkap Karyawan" (kolom C).');
    }

    foreach ($sheet as $rowNum => $row) {
        if ($rowNum < 8) continue; // baris 1-4 judul, 5 header, 6 sub-header, 7 nomor kolom
        if (trim((string)($row['B'] ?? '')) === '' && trim((string)($row['C'] ?? '')) === '') continue;

        $transaction_started = false;
        try {
            $nik_karyawan = Excel::esc($koneksi, $row['B'] ?? '');
            $nama_lengkap = Excel::esc($koneksi, $row['C'] ?? '');

            // NIK duplikat langsung dilewati tanpa validasi kolom lain.
            if ($nik_karyawan !== '') {
                $cek = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik_karyawan' LIMIT 1");
                if ($cek && $cek->num_rows > 0) {
                    $response['dilewati']++;
                    $response['debug'][] = "Baris $rowNum: Dilewati - NIK '$nik_karyawan' sudah terdaftar.";
                    continue;
                }
            }

            $jenis_kelamin = ipipJenisKelamin($row['D'] ?? '');

            // Site: utamakan Working Location (O), fallback Perusahaan (E).
            $raw_site_utama = (string)($row['O'] ?? '');
            $raw_site_cadang = (string)($row['E'] ?? '');
            $id_site = $cache_site[ipipKey($raw_site_utama)] ?? null;
            $site_terpakai = $raw_site_utama;
            if (!$id_site && trim($raw_site_cadang) !== '') {
                $id_site = $cache_site[ipipKey($raw_site_cadang)] ?? null;
                $site_terpakai = $raw_site_cadang;
            }

            $raw_dept = (string)($row['F'] ?? '');
            $id_departemen = $cache_dept[ipipKey($raw_dept)] ?? null;

            $raw_jabatan = (string)($row['H'] ?? '');
            $id_jabatan = $cache_jabatan[ipipKeyJabatan($raw_jabatan)] ?? null;

            $tgl_masuk_site = Excel::parseDateFlexible($row['P'] ?? '');
            $join_ipip      = Excel::parseDateFlexible($row['Q'] ?? '');
            $tgl_mulai = $tgl_masuk_site ?: $join_ipip;

            $tempat_lahir = Excel::esc($koneksi, $row['V'] ?? '');
            $tgl_lahir    = Excel::parseDateFlexible($row['W'] ?? '');

            $nationality = Excel::esc($koneksi, $row['AA'] ?? '');
            $agama       = Excel::esc($koneksi, ipipAgama($row['AB'] ?? ''));

            $education_level = Excel::esc($koneksi, $row['AC'] ?? '');
            $last_education  = Excel::esc($koneksi, $row['AD'] ?? '');
            $major           = Excel::esc($koneksi, $row['AE'] ?? '');

            $atas_nama_rek = Excel::esc($koneksi, $row['AF'] ?? '');
            $no_rek        = Excel::esc($koneksi, $row['AG'] ?? '');
            $nama_bank     = Excel::esc($koneksi, $row['AH'] ?? '');

            $nama_ibu = Excel::esc($koneksi, $row['AI'] ?? '');
            $nik_ktp  = Excel::esc($koneksi, $row['AJ'] ?? '');

            $alamat_ktp = Excel::esc($koneksi, $row['AL'] ?? '');
            $rt_rw      = Excel::esc($koneksi, $row['AM'] ?? '');
            $desa       = Excel::esc($koneksi, $row['AN'] ?? '');
            $kecamatan  = Excel::esc($koneksi, $row['AO'] ?? '');
            $kabupaten  = Excel::esc($koneksi, $row['AP'] ?? '');
            $provinsi   = Excel::esc($koneksi, $row['AQ'] ?? '');

            $email_pribadi = Excel::esc($koneksi, $row['AS'] ?? '');
            $no_telp       = Excel::esc($koneksi, ipipTelepon($row['AT'] ?? ''));

            $nama_kontak1     = Excel::esc($koneksi, $row['AU'] ?? '');
            $hubungan1        = Excel::esc($koneksi, $row['AV'] ?? '');
            $no_telp_darurat1 = Excel::esc($koneksi, ipipTelepon($row['AW'] ?? ''));

            $nama_kontak2     = Excel::esc($koneksi, $row['AX'] ?? '');
            $hubungan2        = Excel::esc($koneksi, $row['AY'] ?? '');
            $no_telp_darurat2 = Excel::esc($koneksi, ipipTelepon($row['AZ'] ?? ''));

            $npwp     = Excel::esc($koneksi, $row['BC'] ?? '');
            $bpjs_tk  = Excel::esc($koneksi, $row['BD'] ?? '');
            $bpjs_kes = Excel::esc($koneksi, $row['BE'] ?? '');

            $status_pernikahan = Excel::esc($koneksi, ipipStatusPernikahan($row['BF'] ?? ''));
            $kk_number         = Excel::esc($koneksi, $row['BG'] ?? '');

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
                $alasan_keluar = Excel::esc($koneksi, trim($raw_ci . ($raw_cl !== '' ? ' | ' . $raw_cl : '')));
                if ($alasan_keluar === '') $alasan_keluar = Excel::esc($koneksi, 'Non Aktif (template IPIP)');
            } else {
                $status_karyawan = 'Aktif';
                $alasan_keluar = '';
            }

            // --- validasi wajib ---
            if ($nik_karyawan === '' || $nama_lengkap === '') {
                throw new Exception('NIK (kolom B) atau Nama (kolom C) kosong.');
            }
            if ($jenis_kelamin === null) {
                throw new Exception("Jenis kelamin '" . trim((string)($row['D'] ?? '')) . "' tidak valid. Gunakan Laki-Laki/Perempuan atau L/P.");
            }
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
            if (!$id_site || !$id_departemen || !$id_jabatan) {
                throw new Exception("Site '{$site_terpakai}', Dept '$raw_dept', atau Jabatan '$raw_jabatan' tidak ditemukan di master (cek penulisan/cabang Cina).");
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
        } catch (Exception $e) {
            if ($transaction_started) $koneksi->rollback();
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: " . $e->getMessage();
        }
    }

    $response["success"] = true;
    $response["message"] = "Import IPIP selesai. Berhasil: {$response['berhasil']}, Dilewati: {$response['dilewati']}, Gagal: {$response['gagal']}";
} catch (Exception $e) {
    $response["error"] = "Gagal membaca file Excel: " . $e->getMessage();
}

echo json_encode($response);
exit;
