<?php
// Naikkan batas memory dan waktu eksekusi untuk PhpSpreadsheet
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
require_once __DIR__ . '/../../config/Excel.php'; // Excel::parseDate()/esc() terpusat

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Tambah 3 bulan dari tanggal mulai
 */
function tglAkhir3Bulan($tgl_mulai)
{
    if (empty($tgl_mulai)) return null;
    return date('Y-m-d', strtotime("+3 months", strtotime($tgl_mulai)));
}

function normalizeStatusKaryawan($status)
{
    $status = strtolower(trim((string)$status));
    $aliases = [
        'aktif' => 'Aktif',
        'tetap' => 'Aktif',
        'kontrak' => 'Aktif',
        'contract' => 'Aktif',
        'harian' => 'Aktif',
        'resign' => 'Resign',
        'resignation' => 'Resign',
        'habis kontrak' => 'Habis Kontrak',
        'end contract' => 'Habis Kontrak',
        'pensiun' => 'Pensiun',
        'pension' => 'Pensiun',
        'phk' => 'PHK',
        'termination' => 'PHK',
        'black list' => 'Black List',
        'blacklist' => 'Black List',
        'cuti' => 'Cuti',
    ];

    return $aliases[$status] ?? null;
}

function normalizeJenisKelamin($value)
{
    $value = strtoupper(trim((string)$value));
    // Excel can prepend locale metadata, for example "[$-409]L".
    $value = preg_replace('/^\[\$-[^\]]+\]/', '', $value);
    $value = trim($value);

    $aliases = [
        'L' => 'L',
        'LAKI-LAKI' => 'L',
        'LAKI LAKI' => 'L',
        'P' => 'P',
        'PEREMPUAN' => 'P',
    ];

    return $aliases[$value] ?? null;
}

// Response awal
$response = [
    "success" => false,
    "berhasil" => 0,
    "gagal" => 0,
    "dilewati" => 0,
    "errors" => [],
    "debug" => [],
    "error" => ""
];

// Validasi file upload
if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    if (empty($_FILES)) {
        $response["error"] = "File terlalu besar. Cek post_max_size di php.ini.";
    } else {
        $response["error"] = "File Excel belum dipilih atau terjadi error saat upload.";
    }
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

// Fungsi batasi update untuk sell kosong
function addUpdateField(&$fields, &$params, $column, $value, $allowNull = false)
{
    if ($allowNull) {
        if ($value === null || $value === '') {
            $fields[] = "$column = NULL";
        } else {
            $fields[] = "$column = ?";
            $params[] = $value;
        }
    } else {
        if ($value !== '' && $value !== null) {
            $fields[] = "$column = ?";
            $params[] = $value;
        }
    }
}

// =========================================================================
// OPTIMASI: CACHING DATA MASTER AGAR TIDAK QUERY BERULANG DI DALAM LOOPING
// =========================================================================
try {
    $cache_site = [];
    $qSite = $koneksi->query("SELECT id_site, LOWER(TRIM(nama_site)) as nama FROM tb_site");
    while ($r = $qSite->fetch()) $cache_site[$r['nama']] = $r['id_site'];

    $cache_dept = [];
    $qDept = $koneksi->query("SELECT id_departemen, LOWER(TRIM(nama_departemen)) as nama FROM tb_departemen");
    while ($r = $qDept->fetch()) $cache_dept[$r['nama']] = $r['id_departemen'];

    $cache_jabatan = [];
    $qJabatan = $koneksi->query("SELECT id_jabatan, LOWER(TRIM(nama_jabatan)) as nama FROM tb_jabatan");
    while ($r = $qJabatan->fetch()) $cache_jabatan[$r['nama']] = $r['id_jabatan'];

    $cache_jenis_keluar = [];
    $qJenis = $koneksi->query("SELECT id_jenis_keluar, LOWER(TRIM(nama_kategori)) as nama FROM m_jenis_keluar");
    while ($r = $qJenis->fetch()) $cache_jenis_keluar[$r['nama']] = $r['id_jenis_keluar'];
} catch (PDOException $e) {
    error_log('csv-karyawan cache master: ' . $e->getMessage());
    $response["error"] = "Gagal memuat data master.";
    echo json_encode($response);
    exit;
}
// =========================================================================

try {
    // Load Excel file
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    $expected_headers = [
        'No', 'NIK Karyawan', 'Nama Lengkap', 'Jenis Kelamin (L/P)', 'Site',
        'Departemen', 'Jabatan', 'Tanggal Masuk Site (Y-M-D)',
        'Tanggal Selesai Kontrak  (Y-M-D)', 'Tempat Lahir', 'Tanggal Lahir (Y-M-D)',
        'Kewarganegaraan', 'Agama', 'Jenjang Pendidikan', 'Pendidikan Terakhir',
        'Jurusan', 'Atas Nama Rekening', 'Nomor Rekening', 'Nama Bank',
        'Nama Ibu Kandung', 'NIK KTP', 'Alamat KTP', 'RT/RW', 'Desa',
        'Kecamatan', 'Kabupaten', 'Provinsi', 'Email Pribadi', 'No Telepon',
        'Nama Kontak Darurat 1', 'Hubungan 1', 'No Telepon Darurat 1',
        'Nama Kontak Darurat 2', 'Hubungan 2', 'No Telepon Darurat 2',
        'NPWP', 'BPJS Ketenagakerjaan', 'BPJS Kesehatan', 'Status Pernikahan',
        'Status PTKP', 'Nomor KK', 'Status Karyawan', 'Tanggal Keluar (Y-M-D)',
        'Keterangan'
    ];
    $headers = array_values($sheet[1] ?? []);
    if (count($headers) !== 44 || $headers !== $expected_headers) {
        throw new Exception('Format template tidak sesuai. Gunakan template dengan 44 kolom A sampai AR.');
    }
    
    foreach ($sheet as $rowNum => $row) {
        // Skip header
        if ($rowNum == 1) continue;

        // skip baris kosong
        if (trim($row['B'] ?? '') === '' && trim($row['C'] ?? '') === '') continue;

        $transaction_started = false;
        try {
            // === Mapping data kolom Excel ===
            $nik_karyawan  = Excel::esc($koneksi, $row['B'] ?? '');
            $nama_lengkap  = Excel::esc($koneksi, $row['C'] ?? '');

            // NIK adalah kunci unik. Existing employee langsung dilewati,
            // tanpa memvalidasi atau mengubah kolom lainnya.
            if ($nik_karyawan !== '') {
                $stmtCekAwal = $koneksi->prepare(
                    "SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan=? LIMIT 1"
                );
                $stmtCekAwal->execute([$nik_karyawan]);
                if ($stmtCekAwal->fetch()) {
                    $response['dilewati']++;
                    $response['debug'][] = "Baris $rowNum: Dilewati - NIK '$nik_karyawan' sudah terdaftar.";
                    continue;
                }
            }

            $jenis_kelamin_raw = $row['D'] ?? '';
            $jenis_kelamin = normalizeJenisKelamin($jenis_kelamin_raw);
            
            // Raw value untuk pencarian dari Cache
            $raw_nama_site       = strtolower(trim($row['E'] ?? ''));
            $raw_nama_departemen = strtolower(trim($row['F'] ?? ''));
            $raw_nama_jabatan    = strtolower(trim($row['G'] ?? ''));

            // Tanggal
            $tgl_masuk_site      = Excel::parseDate($row['H'] ?? '');
            $tgl_selesai_kontrak = Excel::parseDate($row['I'] ?? '');

            // Data pribadi
            $tempat_lahir  = Excel::esc($koneksi, $row['J'] ?? '');
            $tgl_lahir     = Excel::parseDate($row['K'] ?? '');
            $nationality   = Excel::esc($koneksi, $row['L'] ?? '');
            $agama         = Excel::esc($koneksi, $row['M'] ?? '');

            // Pendidikan
            $education_level  = Excel::esc($koneksi, $row['N'] ?? ''); 
            $last_education   = Excel::esc($koneksi, $row['O'] ?? ''); 
            $major            = Excel::esc($koneksi, $row['P'] ?? ''); 

            // Data bank & Identitas
            $atas_nama_rek = Excel::esc($koneksi, $row['Q'] ?? '');
            $no_rek        = Excel::esc($koneksi, $row['R'] ?? '');
            $nama_bank     = Excel::esc($koneksi, $row['S'] ?? '');
            $nama_ibu      = Excel::esc($koneksi, $row['T'] ?? '');
            $nik_ktp       = Excel::esc($koneksi, $row['U'] ?? '');

            // Alamat
            $alamat_ktp    = Excel::esc($koneksi, $row['V'] ?? '');
            $rt_rw         = Excel::esc($koneksi, $row['W'] ?? '');
            $desa          = Excel::esc($koneksi, $row['X'] ?? '');
            $kecamatan     = Excel::esc($koneksi, $row['Y'] ?? '');
            $kabupaten     = Excel::esc($koneksi, $row['Z'] ?? '');
            $provinsi      = Excel::esc($koneksi, $row['AA'] ?? '');

            // Kontak
            $email_pribadi = Excel::esc($koneksi, $row['AB'] ?? '');
            $no_telp       = Excel::esc($koneksi, $row['AC'] ?? '');

            // Kontak Darurat 1 & 2
            $nama_kontak1     = Excel::esc($koneksi, $row['AD'] ?? '');
            $hubungan1        = Excel::esc($koneksi, $row['AE'] ?? '');
            $no_telp_darurat1 = Excel::esc($koneksi, $row['AF'] ?? '');

            $nama_kontak2     = Excel::esc($koneksi, $row['AG'] ?? '');
            $hubungan2        = Excel::esc($koneksi, $row['AH'] ?? '');
            $no_telp_darurat2 = Excel::esc($koneksi, $row['AI'] ?? '');

            // Status dan dokumen
            $npwp              = Excel::esc($koneksi, $row['AJ'] ?? '');
            $bpjs_tk           = Excel::esc($koneksi, $row['AK'] ?? '');
            $bpjs_kes          = Excel::esc($koneksi, $row['AL'] ?? '');
            $status_pernikahan = Excel::esc($koneksi, $row['AM'] ?? '');
            $status_ptkp       = Excel::esc($koneksi, $row['AN'] ?? ''); 
            $kk_number         = Excel::esc($koneksi, $row['AO'] ?? '');
            
            $raw_status_karyawan = trim($row['AP'] ?? '') === '' ? 'Aktif' : $row['AP'];
            $normalized_status = normalizeStatusKaryawan($raw_status_karyawan);
            if ($normalized_status === null) {
                throw new Exception("Status karyawan '$raw_status_karyawan' tidak dikenal.");
            }
            $status_karyawan = Excel::esc($koneksi, $normalized_status);
            
            $tgl_keluar          = Excel::parseDate($row['AQ'] ?? '');
            $alasan_keluar       = Excel::esc($koneksi, $row['AR'] ?? '');

            // === Validasi wajib ===
            if (empty($nik_karyawan) || empty($nama_lengkap)) {
                throw new Exception('NIK atau Nama kosong.');
            }
            if ($jenis_kelamin === null) {
                throw new Exception("Jenis kelamin '$jenis_kelamin_raw' tidak valid. Gunakan L atau P.");
            }
            if (!empty($email_pribadi) && !filter_var($email_pribadi, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email '$email_pribadi' tidak valid.");
            }
            if (($row['H'] ?? '') !== '' && $tgl_masuk_site === null) {
                throw new Exception('Tanggal masuk site tidak valid. Gunakan format YYYY-MM-DD.');
            }
            if (($row['I'] ?? '') !== '' && $tgl_selesai_kontrak === null) {
                throw new Exception('Tanggal selesai kontrak tidak valid. Gunakan format YYYY-MM-DD.');
            }
            if (($row['K'] ?? '') !== '' && $tgl_lahir === null) {
                throw new Exception('Tanggal lahir tidak valid. Gunakan format YYYY-MM-DD.');
            }
            if (($row['AQ'] ?? '') !== '' && $tgl_keluar === null) {
                throw new Exception('Tanggal keluar tidak valid. Gunakan format YYYY-MM-DD.');
            }
            if ($tgl_masuk_site && $tgl_selesai_kontrak && $tgl_selesai_kontrak < $tgl_masuk_site) {
                throw new Exception('Tanggal selesai kontrak tidak boleh sebelum tanggal masuk site.');
            }

            // === Ambil ID referensi DARI CACHE (Super Cepat) ===
            $id_site       = $cache_site[$raw_nama_site] ?? null;
            $id_departemen = $cache_dept[$raw_nama_departemen] ?? null;
            $id_jabatan    = $cache_jabatan[$raw_nama_jabatan] ?? null;

            if (!$id_site || !$id_departemen || !$id_jabatan) {
                throw new Exception("Site '$raw_nama_site', Dept '$raw_nama_departemen', atau Jabatan '$raw_nama_jabatan' tidak ditemukan/salah ketik.");
            }

            $koneksi->beginTransaction();
            $transaction_started = true;

            // ==== INSERT KARYAWAN BARU ====
            $stmtKaryawan = $koneksi->prepare("INSERT INTO tb_karyawan (
                    nik_karyawan, nama_lengkap, jenis_kelamin, tgl_lahir, tempat_lahir,
                    email_pribadi, no_telp, nationality, agama, status_pernikahan, status_ptkp,
                    status_karyawan, password
                ) VALUES (
                    ?, ?, ?,
                    ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ''
            )");
            $stmtKaryawan->execute([
                $nik_karyawan, $nama_lengkap, $jenis_kelamin,
                ($tgl_lahir ?: null),
                $tempat_lahir, $email_pribadi, $no_telp, $nationality,
                $agama, $status_pernikahan, $status_ptkp, $status_karyawan
            ]);
            $id_karyawan = (int)$koneksi->lastInsertId();

            $stmtIdentitas = $koneksi->prepare("INSERT INTO tb_identitas (
                    id_karyawan, nik_ktp, kk_number, npwp, bpjs_tk, bpjs_kes,
                    alamat_ktp, provinsi, kabupaten, kecamatan, desa, rt_rw, nama_ibu,
                    last_education, major, education_level, nama_bank, no_rek, atas_nama_rek
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?
                )");
            $stmtIdentitas->execute([
                $id_karyawan, $nik_ktp, $kk_number, $npwp, $bpjs_tk,
                $bpjs_kes, $alamat_ktp, $provinsi, $kabupaten, $kecamatan,
                $desa, $rt_rw, $nama_ibu, $last_education, $major,
                $education_level, $nama_bank, $no_rek, $atas_nama_rek
            ]);
            // Insert kontak darurat (berlaku untuk update & insert)
            if (!empty($nama_kontak1) && !empty($no_telp_darurat1)) {
                $stmtKontak1 = $koneksi->prepare("INSERT INTO tb_kontak_darurat (id_karyawan, nama_kontak, hubungan, no_telp_darurat, nomor_urut) VALUES (?,?,?,?,1)");
                $stmtKontak1->execute([$id_karyawan, $nama_kontak1, $hubungan1, $no_telp_darurat1]);
            }
            if (!empty($nama_kontak2) && !empty($no_telp_darurat2)) {
                $stmtKontak2 = $koneksi->prepare("INSERT INTO tb_kontak_darurat (id_karyawan, nama_kontak, hubungan, no_telp_darurat, nomor_urut) VALUES (?,?,?,?,2)");
                $stmtKontak2->execute([$id_karyawan, $nama_kontak2, $hubungan2, $no_telp_darurat2]);
            }

            // === Update/Insert tb_karier ===
            $stmtCekKarier = $koneksi->prepare("SELECT id_karier FROM tb_karier WHERE id_karyawan=? ORDER BY tgl_mulai DESC LIMIT 1");
            $stmtCekKarier->execute([$id_karyawan]);
            $rowKarier = $stmtCekKarier->fetch();

            if ($rowKarier) {
                // Update
                $idKarier = $rowKarier['id_karier'];
                $updateKarier = [];
                $paramsKarier = [];
                addUpdateField($updateKarier, $paramsKarier, 'id_site', $id_site);
                addUpdateField($updateKarier, $paramsKarier, 'id_departemen', $id_departemen);
                addUpdateField($updateKarier, $paramsKarier, 'id_jabatan', $id_jabatan);
                addUpdateField($updateKarier, $paramsKarier, 'tgl_mulai', $tgl_masuk_site, true);
                addUpdateField($updateKarier, $paramsKarier, 'tgl_selesai', $tgl_selesai_kontrak, true);

                if (!empty($updateKarier)) {
                    $paramsKarier[] = $idKarier;
                    $stmtKarierUpdate = $koneksi->prepare("UPDATE tb_karier SET " . implode(", ", $updateKarier) . " WHERE id_karier = ?");
                    $stmtKarierUpdate->execute($paramsKarier);
                }
            } else {
                // Insert
                $stmtKarierInsert = $koneksi->prepare("INSERT INTO tb_karier (
                    id_karyawan, id_site, id_departemen, id_jabatan,
                    tgl_mulai, tgl_selesai
                ) VALUES (
                    ?, ?, ?, ?,
                    ?,
                    ?
                )");
                $stmtKarierInsert->execute([
                    $id_karyawan, $id_site, $id_departemen, $id_jabatan,
                    ($tgl_masuk_site ?: null),
                    ($tgl_selesai_kontrak ?: null)
                ]);
            }

            // === PROSES tb_tereliminasi ===
            if (!empty($status_karyawan) && strtolower($status_karyawan) != 'aktif') {

                // Cari id_jenis_keluar berdasarkan status yang sudah dinormalisasi.
                $key_jenis_keluar = strtolower(trim($normalized_status));
                $id_jenis_keluar  = $cache_jenis_keluar[$key_jenis_keluar] ?? null;

                if (!$id_jenis_keluar) throw new Exception("Jenis keluar '$raw_status_karyawan' tidak ditemukan di m_jenis_keluar");
                if (empty($tgl_keluar)) throw new Exception("Status '$status_karyawan' tapi tgl_keluar kosong");

                $stmtCekEliminasi = $koneksi->prepare("SELECT id_tereliminasi FROM tb_tereliminasi WHERE id_karyawan=?");
                $stmtCekEliminasi->execute([$id_karyawan]);

                if ($stmtCekEliminasi->fetch()) {
                    $stmtEliminasi = $koneksi->prepare("UPDATE tb_tereliminasi SET tgl_keluar = ?, id_jenis_keluar = ?, alasan_keluar = ? WHERE id_karyawan = ?");
                    $stmtEliminasi->execute([$tgl_keluar, $id_jenis_keluar, $alasan_keluar, $id_karyawan]);
                } else {
                    $stmtEliminasi = $koneksi->prepare("INSERT INTO tb_tereliminasi (id_karyawan, tgl_keluar, id_jenis_keluar, alasan_keluar) VALUES (?,?,?,?)");
                    $stmtEliminasi->execute([$id_karyawan, $tgl_keluar, $id_jenis_keluar, $alasan_keluar]);
                }

            } else {
                $stmtDelEliminasi = $koneksi->prepare("DELETE FROM tb_tereliminasi WHERE id_karyawan=?");
                $stmtDelEliminasi->execute([$id_karyawan]);
            }

            $response["berhasil"]++;
            $response["debug"][] = "Baris $rowNum: Berhasil - NIK: $nik_karyawan";

            // TAMBAHKAN DI SINI: Commit hanya jika semua query di baris ini sukses
            $koneksi->commit();
            $transaction_started = false;

        } catch (Exception $e) {
            // TAMBAHKAN DI SINI: Batalkan seluruh perubahan pada baris ini jika ada 1 query yang gagal
            if ($transaction_started && $koneksi->inTransaction()) {
                $koneksi->rollBack();
            }

            // Duplikat NIK (balapan dengan pre-check di atas): perlakukan sebagai dilewati.
            if ($e instanceof PDOException && (($e->errorInfo[1] ?? null) == 1062)) {
                error_log("csv-karyawan duplikat: " . $e->getMessage());
                $response["dilewati"]++;
                $response["debug"][] = "Baris $rowNum: Dilewati - NIK sudah terdaftar (duplikat).";
            } else {
                if ($e instanceof PDOException) error_log("csv-karyawan DB baris $rowNum: " . $e->getMessage());
                $response["gagal"]++;
                $response["errors"][] = "Baris $rowNum: " . ($e instanceof PDOException ? "Gagal menyimpan data." : $e->getMessage());
            }
        }
    }

    $response["success"] = true;
    $response["message"] = "Import selesai. Berhasil: {$response['berhasil']}, Dilewati: {$response['dilewati']}, Gagal: {$response['gagal']}";
    
    } catch (Exception $e) {
        error_log('csv-karyawan: ' . $e->getMessage());
        if (isset($koneksi) && $koneksi instanceof PDO && $koneksi->inTransaction()) {
            $koneksi->rollBack();
        }

        $response["error"] = "Gagal membaca file Excel: " . $e->getMessage();
}

echo json_encode($response);
exit;
