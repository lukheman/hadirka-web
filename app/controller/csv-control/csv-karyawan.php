<?php
header("Content-Type: application/json");

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Jangan tampilkan error ke browser, kirim via JSON
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/csv_error.log'); // Pastikan folder logs ada dan writable

require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Cari ID berdasarkan nama (case-insensitive, trimmed)
 */
function getId($koneksi, $table, $nama_field, $nama_value, $id_field)
{
    // PERBAIKAN: Jangan escape field name, hanya value
    $nama_value_clean = strtolower(trim($nama_value));
    $nama_value_escaped = mysqli_real_escape_string($koneksi, $nama_value_clean);
    
    // Whitelist table dan field untuk keamanan
    $allowed_tables = ['tb_site', 'tb_departemen', 'tb_jabatan'];
    if (!in_array($table, $allowed_tables)) {
        throw new Exception("Invalid table name: $table");
    }
    
    $query = "SELECT `$id_field` FROM `$table` WHERE LOWER(TRIM(`$nama_field`))='$nama_value_escaped' LIMIT 1";
    $result = $koneksi->query($query);
    
    if (!$result) {
        throw new Exception("Query error in getId: " . $koneksi->error);
    }
    
    return $result->num_rows > 0 ? $result->fetch_assoc()[$id_field] : null;
}

/**
 * Tambah 3 bulan dari tanggal mulai
 */
function tglAkhir3Bulan($tgl_mulai)
{
    if (empty($tgl_mulai) || $tgl_mulai === '0000-00-00') return null;
    
    try {
        $date = new DateTime($tgl_mulai);
        $date->modify('+3 months');
        return $date->format('Y-m-d');
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Escape string untuk query SQL
 */
function escapeString($koneksi, $value)
{
    if ($value === null) return '';
    return mysqli_real_escape_string($koneksi, trim($value));
}

/**
 * Format tanggal dari Excel
 */
function formatTanggal($value)
{
    if (empty($value)) return null;
    
    try {
        // Handle Excel date format (numeric)
        if (is_numeric($value)) {
            $unix_date = ($value - 25569) * 86400;
            return date('Y-m-d', $unix_date);
        }
        
        // Handle string date
        $timestamp = strtotime($value);
        if ($timestamp === false) return null;
        
        return date('Y-m-d', $timestamp);
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

try {
    // Validasi koneksi database
    if (!isset($koneksi) || $koneksi->connect_error) {
        throw new Exception("Database connection failed: " . ($koneksi->connect_error ?? 'Unknown error'));
    }

    // Validasi file upload
    if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (php.ini limit)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (form limit)',
            UPLOAD_ERR_PARTIAL => 'File hanya sebagian terupload',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension'
        ];
        
        $errorCode = $_FILES['file_excel']['error'] ?? UPLOAD_ERR_NO_FILE;
        $errorMsg = $uploadErrors[$errorCode] ?? 'Unknown upload error';
        
        throw new Exception("Upload error: $errorMsg");
    }

    $file = $_FILES['file_excel']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, ['xls', 'xlsx'])) {
        throw new Exception("Format file tidak didukung. Harus XLS atau XLSX.");
    }

    // Load Excel file
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    // Validasi sheet tidak kosong
    if (count($sheet) <= 1) {
        throw new Exception("File Excel kosong atau hanya berisi header.");
    }

    // Mulai transaksi
    $koneksi->begin_transaction();

    foreach ($sheet as $rowNum => $row) {
        // Skip header
        if ($rowNum == 1) continue;

        try {
            // === Mapping data kolom Excel ===
            $nik_karyawan  = escapeString($koneksi, $row['B'] ?? '');
            $nama_lengkap  = escapeString($koneksi, $row['C'] ?? '');
            $jenis_kelamin = escapeString($koneksi, $row['D'] ?? '');

            $nama_site     = escapeString($koneksi, $row['E'] ?? '');
            $departemen    = escapeString($koneksi, $row['F'] ?? '');
            $jabatan       = escapeString($koneksi, $row['G'] ?? '');

            // Tanggal
            $tgl_masuk_site = formatTanggal($row['H'] ?? '');
            $tgl_join_ipip  = formatTanggal($row['I'] ?? '');
            $tgl_akhir      = tglAkhir3Bulan($tgl_masuk_site);
            
            // Data pribadi
            $tempat_lahir  = escapeString($koneksi, $row['J'] ?? '');
            $tgl_lahir     = formatTanggal($row['K'] ?? '');
            $nationality   = escapeString($koneksi, $row['L'] ?? '');
            $agama         = escapeString($koneksi, $row['M'] ?? '');
            
            // Pendidikan
            $education_level  = escapeString($koneksi, $row['N'] ?? '');
            $last_education   = escapeString($koneksi, $row['O'] ?? '');
            $major            = escapeString($koneksi, $row['P'] ?? '');

            // Data bank
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
            $email_pribadi = escapeString($koneksi, $row['AC'] ?? '');
            $no_telp       = escapeString($koneksi, $row['AD'] ?? '');

            // Status dan dokumen
            $status_karyawan   = escapeString($koneksi, $row['AL'] ?? '');
            $npwp              = escapeString($koneksi, $row['AM'] ?? '');
            $bpjs_tk           = escapeString($koneksi, $row['AN'] ?? '');
            $bpjs_kes          = escapeString($koneksi, $row['AO'] ?? '');
            $status_pernikahan = escapeString($koneksi, $row['AP'] ?? '');
            $kk_number         = escapeString($koneksi, $row['AQ'] ?? '');

            // === Validasi wajib ===
            if (empty($nik_karyawan) || empty($nama_lengkap)) {
                $response["gagal"]++;
                $response["errors"][] = "Baris $rowNum: NIK atau Nama kosong.";
                continue;
            }

            // === Ambil ID referensi ===
            $id_site       = getId($koneksi, 'tb_site', 'nama_site', $nama_site, 'id_site');
            $id_departemen = getId($koneksi, 'tb_departemen', 'nama_departemen', $departemen, 'id_departemen');
            $id_jabatan    = getId($koneksi, 'tb_jabatan', 'nama_jabatan', $jabatan, 'id_jabatan');

            // Validasi ID referensi
            if (!$id_site || !$id_departemen || !$id_jabatan) {
                $response["gagal"]++;
                $response["errors"][] = "Baris $rowNum: Site '$nama_site', Departemen '$departemen', atau Jabatan '$jabatan' tidak ditemukan.";
                continue;
            }

            // === Cek apakah karyawan sudah ada ===
            $cekKaryawan = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik_karyawan'");
            
            if (!$cekKaryawan) {
                throw new Exception("Error checking existing employee: " . $koneksi->error);
            }
            
            if ($cekKaryawan->num_rows > 0) {
                // UPDATE - Karyawan sudah ada
                $id_karyawan = $cekKaryawan->fetch_assoc()['id_karyawan'];

                // Update tb_karyawan - PERBAIKAN: Tambahkan backticks dan handle NULL
                $sqlKaryawan = "UPDATE `tb_karyawan` SET
                    `nama_lengkap` = '$nama_lengkap',
                    `jenis_kelamin` = '$jenis_kelamin',
                    `tgl_lahir` = " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",
                    `tempat_lahir` = '$tempat_lahir',
                    `email_pribadi` = '$email_pribadi',
                    `no_telp` = '$no_telp',
                    `nationality` = '$nationality',
                    `agama` = '$agama',
                    `status_pernikahan` = '$status_pernikahan',
                    `status_karyawan` = '$status_karyawan'
                WHERE `id_karyawan` = '$id_karyawan'";

                if (!$koneksi->query($sqlKaryawan)) {
                    throw new Exception("Error update tb_karyawan: " . $koneksi->error . " | SQL: " . $sqlKaryawan);
                }

                // Update tb_identitas
                $sqlIdentitas = "UPDATE `tb_identitas` SET
                    `nik_ktp` = '$nik_ktp',
                    `kk_number` = '$kk_number',
                    `npwp` = '$npwp',
                    `bpjs_tk` = '$bpjs_tk',
                    `bpjs_kes` = '$bpjs_kes',
                    `alamat_ktp` = '$alamat_ktp',
                    `provinsi` = '$provinsi',
                    `kabupaten` = '$kabupaten',
                    `kecamatan` = '$kecamatan',
                    `desa` = '$desa',
                    `rt_rw` = '$rt_rw',
                    `nama_ibu` = '$nama_ibu',
                    `last_education` = '$last_education',
                    `major` = '$major',
                    `education_level` = '$education_level',
                    `nama_bank` = '$nama_bank',
                    `no_rek` = '$no_rek',
                    `atas_nama_rek` = '$atas_nama_rek'
                WHERE `id_karyawan` = '$id_karyawan'";

                if (!$koneksi->query($sqlIdentitas)) {
                    throw new Exception("Error update tb_identitas: " . $koneksi->error);
                }

            } else {
                // INSERT - Karyawan baru
                $sqlKaryawan = "INSERT INTO `tb_karyawan` (
                    `nik_karyawan`, `nama_lengkap`, `jenis_kelamin`, `tgl_lahir`, `tempat_lahir`,
                    `email_pribadi`, `no_telp`, `nationality`, `agama`, `status_pernikahan`,
                    `status_karyawan`
                ) VALUES (
                    '$nik_karyawan', '$nama_lengkap', '$jenis_kelamin',
                    " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",
                    '$tempat_lahir', '$email_pribadi', '$no_telp', '$nationality',
                    '$agama', '$status_pernikahan', '$status_karyawan'
                )";

                if (!$koneksi->query($sqlKaryawan)) {
                    throw new Exception("Error insert tb_karyawan: " . $koneksi->error);
                }

                $id_karyawan = $koneksi->insert_id;

                // Insert tb_identitas
                $sqlIdentitas = "INSERT INTO `tb_identitas` (
                    `id_karyawan`, `nik_ktp`, `kk_number`, `npwp`, `bpjs_tk`, `bpjs_kes`,
                    `alamat_ktp`, `provinsi`, `kabupaten`, `kecamatan`, `desa`, `rt_rw`, `nama_ibu`,
                    `last_education`, `major`, `education_level`, `nama_bank`, `no_rek`, `atas_nama_rek`
                ) VALUES (
                    '$id_karyawan', '$nik_ktp', '$kk_number', '$npwp', '$bpjs_tk',
                    '$bpjs_kes', '$alamat_ktp', '$provinsi', '$kabupaten', '$kecamatan',
                    '$desa', '$rt_rw', '$nama_ibu', '$last_education', '$major',
                    '$education_level', '$nama_bank', '$no_rek', '$atas_nama_rek'
                )";

                if (!$koneksi->query($sqlIdentitas)) {
                    throw new Exception("Error insert tb_identitas: " . $koneksi->error);
                }
            }

            // === Update/Insert tb_karier ===
            $cekKarier = $koneksi->query("SELECT id_karier FROM `tb_karier` 
                WHERE `id_karyawan`='$id_karyawan' 
                ORDER BY `tgl_mulai` DESC LIMIT 1");

            if (!$cekKarier) {
                throw new Exception("Error checking karier: " . $koneksi->error);
            }

            if ($cekKarier->num_rows > 0) {
                // Update karier terakhir
                $idKarier = $cekKarier->fetch_assoc()['id_karier'];
                $sqlKarier = "UPDATE `tb_karier` SET
                    `id_site` = '$id_site',
                    `id_departemen` = '$id_departemen',
                    `id_jabatan` = '$id_jabatan',
                    `tgl_mulai` = " . ($tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL") . ",
                    `tgl_selesai` = " . ($tgl_akhir ? "'$tgl_akhir'" : "NULL") . "
                WHERE `id_karier` = '$idKarier'";
            } else {
                // Insert karier baru
                $sqlKarier = "INSERT INTO `tb_karier` (
                    `id_karyawan`, `id_site`, `id_departemen`, `id_jabatan`, `tgl_mulai`, `tgl_selesai`
                ) VALUES (
                    '$id_karyawan', '$id_site', '$id_departemen', '$id_jabatan',
                    " . ($tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL") . ",
                    " . ($tgl_akhir ? "'$tgl_akhir'" : "NULL") . "
                )";
            }

            if (!$koneksi->query($sqlKarier)) {
                throw new Exception("Error tb_karier: " . $koneksi->error . " | SQL: " . $sqlKarier);
            }

            $response["berhasil"]++;

        } catch (Exception $e) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: " . $e->getMessage();
            error_log("CSV Import Error Row $rowNum: " . $e->getMessage());
        }
    }

    // Commit transaksi
    $koneksi->commit();
    $response["success"] = true;
    $response["message"] = "Import selesai. Berhasil: {$response['berhasil']}, Gagal: {$response['gagal']}";

} catch (Exception $e) {
    // Rollback jika terjadi error
    if (isset($koneksi)) {
        $koneksi->rollback();
    }
    $response["error"] = $e->getMessage();
    error_log("CSV Import Fatal Error: " . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT);
exit;