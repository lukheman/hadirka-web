<?php
header("Content-Type: application/json");

require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Cari ID berdasarkan nama (case-insensitive, trimmed)
 */
function getId($koneksi, $table, $nama_field, $nama_value, $id_field)
{
    $nama_value = mysqli_real_escape_string($koneksi, strtolower(trim($nama_value)));
    $nama_field_escaped = mysqli_real_escape_string($koneksi, $nama_field);
    $id_field_escaped = mysqli_real_escape_string($koneksi, $id_field);
    
    $query = "SELECT $id_field_escaped FROM $table WHERE LOWER(TRIM($nama_field_escaped))='$nama_value'";
    $result = $koneksi->query($query);
    
    return $result && $result->num_rows > 0 ? $result->fetch_assoc()[$id_field] : null;
}

/**
 * Tambah 3 bulan dari tanggal mulai dengan penanganan error
 */
function tglAkhir3Bulan($tgl_mulai)
{
    if (empty($tgl_mulai)) return null;
    
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
    return mysqli_real_escape_string($koneksi, trim($value));
}

/**
 * Format tanggal dari Excel dengan validasi yang lebih kuat
 */
function formatTanggalExcel($value)
{
    if (empty($value)) return null;
    
    // Jika nilai adalah string kosong atau whitespace
    if (is_string($value) && trim($value) === '') {
        return null;
    }
    
    // Jika nilai adalah angka yang sangat besar (nilai Excel yang salah)
    if (is_numeric($value) && $value > 100000) {
        error_log("Nilai tanggal terlalu besar: $value");
        return null;
    }
    
    try {
        // Jika nilai adalah serial number Excel
        if (is_numeric($value) && $value > 0 && $value < 100000) {
            try {
                // Konversi dari nilai serial Excel ke tanggal
                $timestamp = Date::excelToTimestamp($value);
                return date('Y-m-d', $timestamp);
            } catch (Exception $e) {
                // Fallback: jika excelToTimestamp gagal
                $unixTimestamp = ($value - 25569) * 86400; // Konversi manual
                return date('Y-m-d', $unixTimestamp);
            }
        }
        
        // Jika berupa string tanggal
        $value = trim($value);
        
        // Cek format yang umum
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'm/d/Y',
            'd-m-Y',
            'm-d-Y',
            'd M Y',
            'd F Y',
            'Y/m/d',
        ];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $value);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }
        
        // Fallback ke strtotime
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Error formatTanggalExcel: " . $e->getMessage() . " | Value: " . $value);
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
    $response["error"] = "File Excel belum dipilih atau terjadi error saat upload.";
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

try {
    // Load Excel file dengan penanganan error
    $reader = IOFactory::createReaderForFile($file);
    $reader->setReadDataOnly(true); // Hanya baca data, bukan format
    $reader->setReadEmptyCells(false); // Abaikan cell kosong
    
    // Coba baca file
    $spreadsheet = $reader->load($file);
    
    // Set timezone untuk konversi tanggal
    date_default_timezone_set('Asia/Jakarta');
    
    // Ambil data sebagai array
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    
    // Mulai transaksi
    $koneksi->begin_transaction();
    
    // Counter untuk debugging
    $rowCount = 0;

    foreach ($sheet as $rowNum => $row) {
        // Skip header
        if ($rowNum == 1) continue;
        
        $rowCount++;
        
        try {
            // === Debug: Log data baris ===
            // error_log("Processing row $rowNum: " . json_encode($row));
            
            // === Mapping data kolom Excel ===
            $nik_karyawan  = escapeString($koneksi, $row['B'] ?? '');
            $nama_lengkap  = escapeString($koneksi, $row['C'] ?? '');
            $jenis_kelamin = escapeString($koneksi, $row['D'] ?? '');

            $nama_site     = escapeString($koneksi, $row['E'] ?? '');
            $departemen    = escapeString($koneksi, $row['F'] ?? '');
            $jabatan       = escapeString($koneksi, $row['G'] ?? '');

            // Tanggal - dengan penanganan khusus
            $tgl_masuk_site_raw = $row['H'] ?? '';
            $tgl_join_ipip_raw  = $row['I'] ?? '';
            
            // Konversi tanggal dengan fungsi yang aman
            $tgl_masuk_site = formatTanggalExcel($tgl_masuk_site_raw);
            $tgl_join_ipip  = formatTanggalExcel($tgl_join_ipip_raw);
            $tgl_akhir      = tglAkhir3Bulan($tgl_masuk_site);
            
            // Debug log untuk tanggal
            if ($rowNum <= 5) { // Log hanya 5 baris pertama untuk debug
                error_log("Row $rowNum - tgl_masuk_site_raw: '$tgl_masuk_site_raw' -> '$tgl_masuk_site'");
                error_log("Row $rowNum - tgl_join_ipip_raw: '$tgl_join_ipip_raw' -> '$tgl_join_ipip'");
            }
            
            // Data pribadi
            $tempat_lahir  = escapeString($koneksi, $row['J'] ?? '');
            $tgl_lahir_raw = $row['K'] ?? '';
            $tgl_lahir     = formatTanggalExcel($tgl_lahir_raw);
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
            $status_karyawan   = '';
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
            
            // Persiapkan nilai NULL untuk query
            $tgl_lahir_sql = $tgl_lahir ? "'$tgl_lahir'" : "NULL";
            
            if ($cekKaryawan->num_rows > 0) {
                // UPDATE - Karyawan sudah ada
                $id_karyawan = $cekKaryawan->fetch_assoc()['id_karyawan'];

                // Update tb_karyawan
                $sqlKaryawan = "UPDATE tb_karyawan SET
                    nama_lengkap = '$nama_lengkap',
                    jenis_kelamin = '$jenis_kelamin',
                    tgl_lahir = $tgl_lahir_sql,
                    tempat_lahir = '$tempat_lahir',
                    email_pribadi = '$email_pribadi',
                    no_telp = '$no_telp',
                    nationality = '$nationality',
                    agama = '$agama',
                    status_pernikahan = '$status_pernikahan',
                    status_karyawan = '$status_karyawan'
                WHERE id_karyawan = '$id_karyawan'";

                if (!$koneksi->query($sqlKaryawan)) {
                    throw new Exception("Error update tb_karyawan: " . $koneksi->error);
                }

                // Update tb_identitas
                $sqlIdentitas = "UPDATE tb_identitas SET
                    nik_ktp = '$nik_ktp',
                    kk_number = '$kk_number',
                    npwp = '$npwp',
                    bpjs_tk = '$bpjs_tk',
                    bpjs_kes = '$bpjs_kes',
                    alamat_ktp = '$alamat_ktp',
                    provinsi = '$provinsi',
                    kabupaten = '$kabupaten',
                    kecamatan = '$kecamatan',
                    desa = '$desa',
                    rt_rw = '$rt_rw',
                    nama_ibu = '$nama_ibu',
                    last_education = '$last_education',
                    major = '$major',
                    education_level = '$education_level',
                    nama_bank = '$nama_bank',
                    no_rek = '$no_rek',
                    atas_nama_rek = '$atas_nama_rek'
                WHERE id_karyawan = '$id_karyawan'";

                if (!$koneksi->query($sqlIdentitas)) {
                    throw new Exception("Error update tb_identitas: " . $koneksi->error);
                }

            } else {
                // INSERT - Karyawan baru
                $sqlKaryawan = "INSERT INTO tb_karyawan (
                    nik_karyawan, nama_lengkap, jenis_kelamin, tgl_lahir, tempat_lahir,
                    email_pribadi, no_telp, nationality, agama, status_pernikahan,
                    status_karyawan
                ) VALUES (
                    '$nik_karyawan', '$nama_lengkap', '$jenis_kelamin',
                    $tgl_lahir_sql,
                    '$tempat_lahir', '$email_pribadi', '$no_telp', '$nationality',
                    '$agama', '$status_pernikahan', '$status_karyawan'
                )";

                if (!$koneksi->query($sqlKaryawan)) {
                    throw new Exception("Error insert tb_karyawan: " . $koneksi->error);
                }

                $id_karyawan = $koneksi->insert_id;

                // Insert tb_identitas
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

                if (!$koneksi->query($sqlIdentitas)) {
                    throw new Exception("Error insert tb_identitas: " . $koneksi->error);
                }
            }

            // === Update/Insert tb_karier ===
            // Persiapkan nilai tanggal untuk query
            $tgl_masuk_site_sql = $tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL";
            $tgl_akhir_sql = $tgl_akhir ? "'$tgl_akhir'" : "NULL";
            
            $cekKarier = $koneksi->query("SELECT id_karier FROM tb_karier 
                WHERE id_karyawan='$id_karyawan' 
                ORDER BY tgl_mulai DESC LIMIT 1");

            if ($cekKarier->num_rows > 0) {
                // Update karier terakhir
                $idKarier = $cekKarier->fetch_assoc()['id_karier'];
                $sqlKarier = "UPDATE tb_karier SET
                    id_site = '$id_site',
                    id_departemen = '$id_departemen',
                    id_jabatan = '$id_jabatan',
                    tgl_mulai = $tgl_masuk_site_sql,
                    tgl_selesai = $tgl_akhir_sql
                WHERE id_karier = '$idKarier'";
            } else {
                // Insert karier baru
                $sqlKarier = "INSERT INTO tb_karier (
                    id_karyawan, id_site, id_departemen, id_jabatan, tgl_mulai, tgl_selesai
                ) VALUES (
                    '$id_karyawan', '$id_site', '$id_departemen', '$id_jabatan',
                    $tgl_masuk_site_sql,
                    $tgl_akhir_sql
                )";
            }

            if (!$koneksi->query($sqlKarier)) {
                throw new Exception("Error tb_karier: " . $koneksi->error);
            }

            $response["berhasil"]++;
            $response["debug"][] = "Baris $rowNum: Berhasil - NIK: $nik_karyawan, Tgl Masuk: $tgl_masuk_site";

        } catch (Exception $e) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: " . $e->getMessage();
            error_log("Error processing row $rowNum: " . $e->getMessage());
        }
    }

    // Commit transaksi
    $koneksi->commit();
    $response["success"] = true;
    $response["message"] = "Import selesai. Berhasil: {$response['berhasil']}, Gagal: {$response['gagal']}";
    $response["total_rows"] = $rowCount;

} catch (Exception $e) {
    // Rollback jika terjadi error
    if (isset($koneksi)) {
        $koneksi->rollback();
    }
    $response["error"] = "Gagal membaca file Excel: " . $e->getMessage();
    error_log("Excel import error: " . $e->getMessage());
}

echo json_encode($response);
exit;