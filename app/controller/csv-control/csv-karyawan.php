<?php
// Naikkan batas memory dan waktu eksekusi untuk PhpSpreadsheet
ini_set('memory_limit', '1024M'); 
ini_set('max_execution_time', '300'); 

header("Content-Type: application/json");

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Tambah 3 bulan dari tanggal mulai
 */
function tglAkhir3Bulan($tgl_mulai)
{
    if (empty($tgl_mulai)) return null;
    return date('Y-m-d', strtotime("+3 months", strtotime($tgl_mulai)));
}

/**
 * Escape string untuk query SQL
 */
function escapeString($koneksi, $value)
{
    return mysqli_real_escape_string($koneksi, trim($value));
}

/**
 * Format tanggal dari Excel
 */
function formatTanggal($value)
{
    if (empty($value)) return null;
    try {
        return date('Y-m-d', strtotime($value));
    } catch (Exception $e) {
        return null;
    }
}

// Response awal
$response = [
    "success" => false,
    "berhasil" => 0,
    "gagal" => 0,
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
function addUpdateField(&$fields, $column, $value, $allowNull = false)
{
    if ($allowNull) {
        if ($value === null || $value === '') {
            $fields[] = "$column = NULL";
        } else {
            $fields[] = "$column = '$value'";
        }
    } else {
        if ($value !== '' && $value !== null) {
            $fields[] = "$column = '$value'";
        }
    }
}

// =========================================================================
// OPTIMASI: CACHING DATA MASTER AGAR TIDAK QUERY BERULANG DI DALAM LOOPING
// =========================================================================
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
// =========================================================================

try {
    // Load Excel file
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    
    foreach ($sheet as $rowNum => $row) {
        // Skip header
        if ($rowNum == 1) continue;

        // skip baris kosong
        if (trim($row['B'] ?? '') === '' && trim($row['C'] ?? '') === '') continue;

        // Mulai transaksi
    $koneksi->begin_transaction();

        try {
            // === Mapping data kolom Excel ===
            $nik_karyawan  = escapeString($koneksi, $row['B'] ?? '');
            $nama_lengkap  = escapeString($koneksi, $row['C'] ?? '');
            $jenis_kelamin = escapeString($koneksi, $row['D'] ?? '');
            
            // Raw value untuk pencarian dari Cache
            $raw_nama_site       = strtolower(trim($row['E'] ?? ''));
            $raw_nama_departemen = strtolower(trim($row['F'] ?? ''));
            $raw_nama_jabatan    = strtolower(trim($row['G'] ?? ''));

            // Tanggal
            $tgl_masuk_site      = formatTanggal($row['H'] ?? '');
            $tgl_selesai_kontrak = formatTanggal($row['I'] ?? '');

            // Data pribadi
            $tempat_lahir  = escapeString($koneksi, $row['J'] ?? '');
            $tgl_lahir     = formatTanggal($row['K'] ?? '');
            $nationality   = escapeString($koneksi, $row['L'] ?? '');
            $agama         = escapeString($koneksi, $row['M'] ?? '');

            // Pendidikan
            $education_level  = escapeString($koneksi, $row['N'] ?? ''); 
            $last_education   = escapeString($koneksi, $row['O'] ?? ''); 
            $major            = escapeString($koneksi, $row['P'] ?? ''); 

            // Data bank & Identitas
            $atas_nama_rek = escapeString($koneksi, $row['Q'] ?? '');
            $no_rek        = escapeString($koneksi, $row['R'] ?? '');
            $nama_bank     = escapeString($koneksi, $row['S'] ?? '');
            $nama_ibu      = escapeString($koneksi, $row['T'] ?? '');
            $nik_ktp       = escapeString($koneksi, $row['U'] ?? '');

            // Alamat
            $alamat_ktp    = escapeString($koneksi, $row['V'] ?? '');
            $rt_rw         = escapeString($koneksi, $row['W'] ?? '');
            $desa          = escapeString($koneksi, $row['X'] ?? '');
            $kecamatan     = escapeString($koneksi, $row['Y'] ?? '');
            $kabupaten     = escapeString($koneksi, $row['Z'] ?? '');
            $provinsi      = escapeString($koneksi, $row['AA'] ?? '');

            // Kontak
            $email_pribadi = escapeString($koneksi, $row['AB'] ?? '');
            $no_telp       = escapeString($koneksi, $row['AC'] ?? '');

            // Kontak Darurat 1 & 2
            $nama_kontak1     = escapeString($koneksi, $row['AD'] ?? '');
            $hubungan1        = escapeString($koneksi, $row['AE'] ?? '');
            $no_telp_darurat1 = escapeString($koneksi, $row['AF'] ?? '');

            $nama_kontak2     = escapeString($koneksi, $row['AG'] ?? '');
            $hubungan2        = escapeString($koneksi, $row['AH'] ?? '');
            $no_telp_darurat2 = escapeString($koneksi, $row['AI'] ?? '');

            // Status dan dokumen
            $npwp              = escapeString($koneksi, $row['AJ'] ?? '');
            $bpjs_tk           = escapeString($koneksi, $row['AK'] ?? '');
            $bpjs_kes          = escapeString($koneksi, $row['AL'] ?? '');
            $status_pernikahan = escapeString($koneksi, $row['AM'] ?? '');
            $status_ptkp       = escapeString($koneksi, $row['AN'] ?? ''); 
            $kk_number         = escapeString($koneksi, $row['AO'] ?? '');
            
            $raw_status_karyawan = trim($row['AP'] ?? '') === '' ? 'Aktif' : $row['AP'];
            $status_karyawan     = escapeString($koneksi, $raw_status_karyawan);
            
            $tgl_keluar          = formatTanggal($row['AQ'] ?? '');
            $alasan_keluar       = escapeString($koneksi, $row['AR'] ?? '');

            // === Validasi wajib ===
            if (empty($nik_karyawan) || empty($nama_lengkap)) {
                $response["gagal"]++;
                $response["errors"][] = "Baris $rowNum: NIK atau Nama kosong.";
                continue;
            }

            // === Ambil ID referensi DARI CACHE (Super Cepat) ===
            $id_site       = $cache_site[$raw_nama_site] ?? null;
            $id_departemen = $cache_dept[$raw_nama_departemen] ?? null;
            $id_jabatan    = $cache_jabatan[$raw_nama_jabatan] ?? null;

            if (!$id_site || !$id_departemen || !$id_jabatan) {
                $response["gagal"]++;
                $response["errors"][] = "Baris $rowNum: Site '$raw_nama_site', Dept '$raw_nama_departemen', atau Jabatan '$raw_nama_jabatan' tidak ditemukan/salah ketik.";
                continue;
            }

            // === Cek apakah karyawan sudah pernah tereliminasi ===
            $cekEliminasi = $koneksi->query("
                SELECT te.id_tereliminasi, tk.id_karyawan 
                FROM tb_tereliminasi te
                JOIN tb_karyawan tk ON te.id_karyawan = tk.id_karyawan
                WHERE tk.nik_karyawan = '$nik_karyawan'
            ");

            if ($cekEliminasi->num_rows > 0) {
                $rowElim = $cekEliminasi->fetch_assoc();
                $id_karyawan = $rowElim['id_karyawan'];

                // hapus eliminasi & update status aktif
                $koneksi->query("DELETE FROM tb_tereliminasi WHERE id_karyawan='$id_karyawan'");
                $koneksi->query("UPDATE tb_karyawan SET status_karyawan='Aktif' WHERE id_karyawan='$id_karyawan'");
            }
            
            // === Cek apakah karyawan sudah ada ===
            $cekKaryawan = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik_karyawan'");

            if ($cekKaryawan->num_rows > 0) {
                // ==== UPDATE KARYAWAN ====
                $id_karyawan = $cekKaryawan->fetch_assoc()['id_karyawan'];

                $updateFields = [];
                addUpdateField($updateFields, 'nama_lengkap', $nama_lengkap);
                addUpdateField($updateFields, 'jenis_kelamin', $jenis_kelamin);
                addUpdateField($updateFields, 'tempat_lahir', $tempat_lahir);
                addUpdateField($updateFields, 'email_pribadi', $email_pribadi);
                addUpdateField($updateFields, 'no_telp', $no_telp);
                addUpdateField($updateFields, 'nationality', $nationality);
                addUpdateField($updateFields, 'agama', $agama);
                addUpdateField($updateFields, 'status_pernikahan', $status_pernikahan);
                addUpdateField($updateFields, 'status_ptkp', $status_ptkp);
                addUpdateField($updateFields, 'status_karyawan', $status_karyawan);
                addUpdateField($updateFields, 'tgl_lahir', $tgl_lahir);

                if (!empty($updateFields)) {
                    $sqlKaryawan = "UPDATE tb_karyawan SET " . implode(", ", $updateFields) . " WHERE id_karyawan = '$id_karyawan'";
                    if (!$koneksi->query($sqlKaryawan)) throw new Exception("Error update tb_karyawan: " . $koneksi->error);
                }

                $updateIdentitas = [];
                addUpdateField($updateIdentitas, 'nik_ktp', $nik_ktp);
                addUpdateField($updateIdentitas, 'kk_number', $kk_number);
                addUpdateField($updateIdentitas, 'npwp', $npwp);
                addUpdateField($updateIdentitas, 'bpjs_tk', $bpjs_tk);
                addUpdateField($updateIdentitas, 'bpjs_kes', $bpjs_kes);
                addUpdateField($updateIdentitas, 'alamat_ktp', $alamat_ktp);
                addUpdateField($updateIdentitas, 'provinsi', $provinsi);
                addUpdateField($updateIdentitas, 'kabupaten', $kabupaten);
                addUpdateField($updateIdentitas, 'kecamatan', $kecamatan);
                addUpdateField($updateIdentitas, 'desa', $desa);
                addUpdateField($updateIdentitas, 'rt_rw', $rt_rw);
                addUpdateField($updateIdentitas, 'nama_ibu', $nama_ibu);
                addUpdateField($updateIdentitas, 'last_education', $last_education);
                addUpdateField($updateIdentitas, 'major', $major);
                addUpdateField($updateIdentitas, 'education_level', $education_level);
                addUpdateField($updateIdentitas, 'nama_bank', $nama_bank);
                addUpdateField($updateIdentitas, 'no_rek', $no_rek);
                addUpdateField($updateIdentitas, 'atas_nama_rek', $atas_nama_rek);

                if (!empty($updateIdentitas)) {
                    $sqlIdentitas = "UPDATE tb_identitas SET " . implode(", ", $updateIdentitas) . " WHERE id_karyawan = '$id_karyawan'";
                    if (!$koneksi->query($sqlIdentitas)) throw new Exception("Error update tb_identitas: " . $koneksi->error);
                }

                // Reset kontak darurat
                if (!empty($nama_kontak1) || !empty($nama_kontak2)) {
                    $koneksi->query("DELETE FROM tb_kontak_darurat WHERE id_karyawan='$id_karyawan'");
                }

            } else {
                // ==== INSERT KARYAWAN BARU ====
                $sqlKaryawan = "INSERT INTO tb_karyawan (
                    nik_karyawan, nama_lengkap, jenis_kelamin, tgl_lahir, tempat_lahir,
                    email_pribadi, no_telp, nationality, agama, status_pernikahan, status_ptkp,
                    status_karyawan
                ) VALUES (
                    '$nik_karyawan', '$nama_lengkap', '$jenis_kelamin',
                    " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",
                    '$tempat_lahir', '$email_pribadi', '$no_telp', '$nationality',
                    '$agama', '$status_pernikahan', '$status_ptkp', '$status_karyawan'
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
            }

            // Insert kontak darurat (berlaku untuk update & insert)
            if (!empty($nama_kontak1) && !empty($no_telp_darurat1)) {
                $sqlKontak1 = "INSERT INTO tb_kontak_darurat (id_karyawan, nama_kontak, hubungan, no_telp_darurat, nomor_urut) VALUES ('$id_karyawan', '$nama_kontak1', '$hubungan1', '$no_telp_darurat1', 1)";
                if (!$koneksi->query($sqlKontak1)) throw new Exception("Error insert kontak 1: " . $koneksi->error);
            }
            if (!empty($nama_kontak2) && !empty($no_telp_darurat2)) {
                $sqlKontak2 = "INSERT INTO tb_kontak_darurat (id_karyawan, nama_kontak, hubungan, no_telp_darurat, nomor_urut) VALUES ('$id_karyawan', '$nama_kontak2', '$hubungan2', '$no_telp_darurat2', 2)";
                if (!$koneksi->query($sqlKontak2)) throw new Exception("Error insert kontak 2: " . $koneksi->error);
            }

            // === Update/Insert tb_karier ===
            $cekKarier = $koneksi->query("SELECT id_karier FROM tb_karier WHERE id_karyawan='$id_karyawan' ORDER BY tgl_mulai DESC LIMIT 1");

            if ($cekKarier->num_rows > 0) {
                // Update
                $idKarier = $cekKarier->fetch_assoc()['id_karier'];
                $updateKarier = [];
                addUpdateField($updateKarier, 'id_site', $id_site);
                addUpdateField($updateKarier, 'id_departemen', $id_departemen);
                addUpdateField($updateKarier, 'id_jabatan', $id_jabatan);
                addUpdateField($updateKarier, 'tgl_mulai', $tgl_masuk_site, true);
                addUpdateField($updateKarier, 'tgl_selesai', $tgl_selesai_kontrak, true);

                if (!empty($updateKarier)) {
                    $sqlKarierUpdate = "UPDATE tb_karier SET " . implode(", ", $updateKarier) . " WHERE id_karier = '$idKarier'";
                    if (!$koneksi->query($sqlKarierUpdate)) throw new Exception("Error tb_karier: " . $koneksi->error);
                }
            } else {
                // Insert
                $sqlKarierInsert = "INSERT INTO tb_karier (
                    id_karyawan, id_site, id_departemen, id_jabatan, tgl_mulai, tgl_selesai
                ) VALUES (
                    '$id_karyawan', '$id_site', '$id_departemen', '$id_jabatan',
                    " . ($tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL") . ",
                    " . ($tgl_selesai_kontrak ? "'$tgl_selesai_kontrak'" : "NULL") . "
                )";
                if (!$koneksi->query($sqlKarierInsert)) throw new Exception("Error tb_karier: " . $koneksi->error);
            }

            // === PROSES tb_tereliminasi ===
            if (!empty($status_karyawan) && strtolower($status_karyawan) != 'aktif') {
                
                // Cari id_jenis_keluar DARI CACHE (Super Cepat)
                $key_jenis_keluar = strtolower(trim($raw_status_karyawan));
                $id_jenis_keluar  = $cache_jenis_keluar[$key_jenis_keluar] ?? null;
                
                if (!$id_jenis_keluar) throw new Exception("Jenis keluar '$raw_status_karyawan' tidak ditemukan di m_jenis_keluar");
                if (empty($tgl_keluar)) throw new Exception("Status '$status_karyawan' tapi tgl_keluar kosong");

                $cekEliminasiDb = $koneksi->query("SELECT id_tereliminasi FROM tb_tereliminasi WHERE id_karyawan='$id_karyawan'");

                if ($cekEliminasiDb->num_rows > 0) {
                    $sqlEliminasi = "UPDATE tb_tereliminasi SET tgl_keluar = '$tgl_keluar', id_jenis_keluar = '$id_jenis_keluar', alasan_keluar = '$alasan_keluar' WHERE id_karyawan = '$id_karyawan'";
                } else {
                    $sqlEliminasi = "INSERT INTO tb_tereliminasi (id_karyawan, tgl_keluar, id_jenis_keluar, alasan_keluar) VALUES ('$id_karyawan', '$tgl_keluar', '$id_jenis_keluar', '$alasan_keluar')";
                }
                if (!$koneksi->query($sqlEliminasi)) throw new Exception("Error tb_tereliminasi: " . $koneksi->error);
                
            } else {
                $koneksi->query("DELETE FROM tb_tereliminasi WHERE id_karyawan='$id_karyawan'");
            }

            $response["berhasil"]++;
            $response["debug"][] = "Baris $rowNum: Berhasil - NIK: $nik_karyawan";

            // TAMBAHKAN DI SINI: Commit hanya jika semua query di baris ini sukses
            $koneksi->commit();
            
        } catch (Exception $e) {
            // TAMBAHKAN DI SINI: Batalkan seluruh perubahan pada baris ini jika ada 1 query yang gagal
            $koneksi->rollback();

            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: " . $e->getMessage();
        }
    }

    $response["success"] = true;
    $response["message"] = "Import selesai. Berhasil: {$response['berhasil']}, Gagal: {$response['gagal']}";
    
} catch (Exception $e) {
    if (isset($koneksi)) $koneksi->rollback();
    
    $response["error"] = "Gagal membaca file Excel: " . $e->getMessage();
}

echo json_encode($response);
exit;