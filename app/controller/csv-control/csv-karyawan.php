<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Cari ID berdasarkan nama (case-insensitive, trimmed)
 */
function getId($koneksi, $table, $nama_field, $nama_value, $id_field)
{
    // Whitelist untuk keamanan
    $allowed = [
        'tb_site' => ['nama_site', 'id_site'],
        'tb_departemen' => ['nama_departemen', 'id_departemen'],
        'tb_jabatan' => ['nama_jabatan', 'id_jabatan']
    ];
    
    if (!isset($allowed[$table]) || 
        !in_array($nama_field, $allowed[$table]) || 
        !in_array($id_field, $allowed[$table])) {
        error_log("Invalid table/field combination: $table.$nama_field");
        return null;
    }
    
    $nama_value = mysqli_real_escape_string($koneksi, strtolower(trim($nama_value)));
    
    $query = "SELECT $id_field FROM $table WHERE LOWER(TRIM($nama_field))='$nama_value'";
    $result = $koneksi->query($query);
    
    return $result && $result->num_rows > 0 ? $result->fetch_assoc()[$id_field] : null;
}

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
    if ($value === null) return '';
    return mysqli_real_escape_string($koneksi, trim($value));
}

/**
 * Format tanggal dari Excel - IMPROVED VERSION
 */
function formatTanggal($value)
{
    if (empty($value)) return null;
    
    // Jika berupa angka dan dalam range Excel date yang valid (1900-2100 = 1-73050)
    // Ini mencegah NIK/nomor lain yang panjang dianggap sebagai tanggal
    if (is_numeric($value) && $value > 0 && $value < 73050) {
        try {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            error_log("Excel date conversion failed for value: $value - " . $e->getMessage());
        }
    }
    
    // Jika berupa string tanggal
    if (is_string($value)) {
        $value = trim($value);
        
        // Handle format Indonesia: dd/mm/yyyy atau dd-mm-yyyy
        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $value, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "$year-$month-$day";
        }
        
        // Handle format ISO: yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        
        // Coba strtotime sebagai fallback
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
    }
    
    error_log("Cannot parse date: " . var_export($value, true));
    return null;
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
    // Load Excel file dengan RAW VALUE (tanpa auto-format)
    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Ambil data manual untuk menghindari auto-format tanggal
    $sheet = [];
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    for ($row = 1; $row <= $highestRow; $row++) {
        $rowData = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $cell = $worksheet->getCell($col . $row);
            // Ambil raw value, bukan formatted value
            $rowData[$col] = $cell->getValue();
        }
        $sheet[$row] = $rowData;
    }
    
    error_log("Total rows loaded: " . count($sheet));

    // Mulai transaksi
    $koneksi->begin_transaction();

    foreach ($sheet as $rowNum => $row) {
        // Skip header
        if ($rowNum == 1) continue;
        
        // Skip baris kosong
        if (empty(trim($row['B'] ?? '')) && empty(trim($row['C'] ?? ''))) {
            continue;
        }

        try {
            // === Mapping data kolom Excel ===
            $nik_karyawan  = escapeString($koneksi, $row['B'] ?? '');
            $nama_lengkap  = escapeString($koneksi, $row['C'] ?? '');
            $jenis_kelamin = escapeString($koneksi, $row['D'] ?? '');

            $nama_site     = escapeString($koneksi, $row['E'] ?? '');
            $departemen    = escapeString($koneksi, $row['F'] ?? '');
            $jabatan       = escapeString($koneksi, $row['G'] ?? '');

            // Tanggal - sekarang aman dengan raw value
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

            // Status dan dokumen - PERBAIKAN: default value 'Aktif'
            $status_karyawan   = escapeString($koneksi, $row['AB'] ?? 'Aktif');
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
            
            if ($cekKaryawan->num_rows > 0) {
                // UPDATE - Karyawan sudah ada
                $id_karyawan = $cekKaryawan->fetch_assoc()['id_karyawan'];

                // Update tb_karyawan
                $sqlKaryawan = "UPDATE tb_karyawan SET
                    nama_lengkap = '$nama_lengkap',
                    jenis_kelamin = '$jenis_kelamin',
                    tgl_lahir = " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",
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
                    " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",
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
                    tgl_mulai = " . ($tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL") . ",
                    tgl_selesai = " . ($tgl_akhir ? "'$tgl_akhir'" : "NULL") . "
                WHERE id_karier = '$idKarier'";
            } else {
                // Insert karier baru
                $sqlKarier = "INSERT INTO tb_karier (
                    id_karyawan, id_site, id_departemen, id_jabatan, tgl_mulai, tgl_selesai
                ) VALUES (
                    '$id_karyawan', '$id_site', '$id_departemen', '$id_jabatan',
                    " . ($tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL") . ",
                    " . ($tgl_akhir ? "'$tgl_akhir'" : "NULL") . "
                )";
            }

            if (!$koneksi->query($sqlKarier)) {
                throw new Exception("Error tb_karier: " . $koneksi->error);
            }

            $response["berhasil"]++;
            $response["debug"][] = "Baris $rowNum: Berhasil - NIK: $nik_karyawan";

        } catch (Exception $e) {
            $response["gagal"]++;
            $errorMsg = "Baris $rowNum: " . $e->getMessage();
            $response["errors"][] = $errorMsg;
            error_log($errorMsg);
        }
    }

    // Commit transaksi
    $koneksi->commit();
    $response["success"] = true;
    $response["message"] = "Import selesai. Berhasil: {$response['berhasil']}, Gagal: {$response['gagal']}";

} catch (Exception $e) {
    // Rollback jika terjadi error
    if (isset($koneksi) && $koneksi->ping()) {
        $koneksi->rollback();
    }

    // LOG ERROR ke file log server
    error_log("=== Import CSV Error ===");
    error_log("Message: " . $e->getMessage());
    error_log("File: " . $e->getFile());
    error_log("Line: " . $e->getLine());
    error_log("Trace: " . $e->getTraceAsString());

    // Response ke frontend
    $response["success"] = false;
    $response["error"] = "Error: " . $e->getMessage();
    $response["error_detail"] = [
        "file" => basename($e->getFile()),
        "line" => $e->getLine()
    ];
}

// Hapus display_errors sebelum production!
// ini_set('display_errors', 0);

echo json_encode($response);
exit;