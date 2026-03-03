<?php
header("Content-Type: application/json");

require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

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

try {
    // Load Excel file
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    // Mulai transaksi
    $koneksi->begin_transaction();
    
    foreach ($sheet as $rowNum => $row) {
        // Skip header
        if ($rowNum == 1) continue;

        // skip baris kosong
        if (trim($row['B'] ?? '') === '' && trim($row['C'] ?? '') === '') {
            continue;
        }

        try {
            // === Mapping data kolom Excel ===
            $nik_karyawan  = escapeString($koneksi, $row['B'] ?? '');
            $nama_lengkap  = escapeString($koneksi, $row['C'] ?? '');
            $jenis_kelamin = escapeString($koneksi, $row['D'] ?? '');
            $nama_site     = escapeString($koneksi, $row['E'] ?? '');
            $nama_departemen = escapeString($koneksi, $row['F'] ?? '');
            $nama_jabatan  = escapeString($koneksi, $row['G'] ?? '');

            // Tanggal
            $tgl_masuk_site = formatTanggal($row['H'] ?? '');
            // $tgl_akhir      = tglAkhir3Bulan($tgl_masuk_site);
            $tgl_selesai_kontrak = formatTanggal($row['I'] ?? '');

            // Data pribadi
            $tempat_lahir  = escapeString($koneksi, $row['J'] ?? '');
            $tgl_lahir      = formatTanggal($row['K'] ?? '');
            $nationality   = escapeString($koneksi, $row['L'] ?? '');
            $agama         = escapeString($koneksi, $row['M'] ?? '');

            // Pendidikan - disesuaikan dengan kolom database
            $education_level  = escapeString($koneksi, $row['N'] ?? ''); // Jenjang pendidikan (jika ada di Excel)
            $last_education   = escapeString($koneksi, $row['O'] ?? ''); // Pendidikan terakhir
            $major            = escapeString($koneksi, $row['P'] ?? ''); // Jurusan (jika ada di Excel)

            // Data bank (jika ada di Excel, sesuaikan kolom)
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

            // Kontak Darurat 1
            $nama_kontak1 = escapeString($koneksi, $row['AD'] ?? '');
            $hubungan1       = escapeString($koneksi, $row['AE'] ?? '');
            $no_telp_darurat1 = escapeString($koneksi, $row['AF'] ?? '');

            // Kontak Darurat 2
            $nama_kontak2 = escapeString($koneksi, $row['AG'] ?? '');
            $hubungan2    = escapeString($koneksi, $row['AH'] ?? '');
            $no_telp_darurat2 = escapeString($koneksi, $row['AI'] ?? '');

            // Status dan dokumen
            $npwp              = escapeString($koneksi, $row['AJ'] ?? '');
            $bpjs_tk           = escapeString($koneksi, $row['AK'] ?? '');
            $bpjs_kes          = escapeString($koneksi, $row['AL'] ?? '');
            $status_pernikahan = escapeString($koneksi, $row['AM'] ?? '');
            $kk_number         = escapeString($koneksi, $row['AN'] ?? '');
            $status_karyawan = trim($row['AO'] ?? '') === ''
                ? 'Aktif'
                : escapeString($koneksi, $row['AO']);
            $tgl_keluar = formatTanggal($row['AP'] ?? '');
            $alasan_keluar   = escapeString($koneksi, $row['AQ'] ?? '');


            // === Validasi wajib ===
            if (empty($nik_karyawan) || empty($nama_lengkap)) {
                $response["gagal"]++;
                $response["errors"][] = "Baris $rowNum: NIK atau Nama kosong.";
                continue;
            }

            // === Ambil ID referensi ===
            $id_site       = getId($koneksi, 'tb_site', 'nama_site', $nama_site, 'id_site');
            $id_departemen = getId($koneksi, 'tb_departemen', 'nama_departemen', $nama_departemen, 'id_departemen');
            $id_jabatan    = getId($koneksi, 'tb_jabatan', 'nama_jabatan', $nama_jabatan, 'id_jabatan');

            // Validasi ID referensi
            if (!$id_site || !$id_departemen || !$id_jabatan) {
                $response["gagal"]++;
                $response["errors"][] = "Baris $rowNum: Site '$nama_site', Departemen '$nama_departemen', atau Jabatan '$nama_jabatan' tidak ditemukan.";
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
                
                // // kebijakan 1: tolak
                // $response["gagal"]++;
                // $response["errors"][] = "Baris $rowNum: NIK $nik_karyawan sudah terdaftar sebagai karyawan keluar (tb_tereliminasi).";
                // continue;

                // kebijakan 2: reaktivasi otomatis
                $rowElim = $cekEliminasi->fetch_assoc();
                $id_karyawan = $rowElim['id_karyawan'];

                // hapus eliminasi
                $koneksi->query("DELETE FROM tb_tereliminasi WHERE id_karyawan='$id_karyawan'");

                // update status aktif
                $koneksi->query("UPDATE tb_karyawan SET status_karyawan='Aktif' WHERE id_karyawan='$id_karyawan'");
            }
            
            // === Cek apakah karyawan sudah ada ===
            $cekKaryawan = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik_karyawan'");

            if ($cekKaryawan->num_rows > 0) {
                // UPDATE - Karyawan sudah ada
                $id_karyawan = $cekKaryawan->fetch_assoc()['id_karyawan'];

                // Update tb_karyawan
                $updateFields = [];

                addUpdateField($updateFields, 'nama_lengkap', $nama_lengkap);
                addUpdateField($updateFields, 'jenis_kelamin', $jenis_kelamin);
                addUpdateField($updateFields, 'tempat_lahir', $tempat_lahir);
                addUpdateField($updateFields, 'email_pribadi', $email_pribadi);
                addUpdateField($updateFields, 'no_telp', $no_telp);
                addUpdateField($updateFields, 'nationality', $nationality);
                addUpdateField($updateFields, 'agama', $agama);
                addUpdateField($updateFields, 'status_pernikahan', $status_pernikahan);
                addUpdateField($updateFields, 'status_karyawan', $status_karyawan);

                // tanggal lahir (boleh null hanya jika diisi)
                addUpdateField($updateFields, 'tgl_lahir', $tgl_lahir);

                if (!empty($updateFields)) {
                    $sqlKaryawan = "UPDATE tb_karyawan SET " . implode(", ", $updateFields) . "
                    WHERE id_karyawan = '$id_karyawan'";
                    if (!$koneksi->query($sqlKaryawan)) {
                        throw new Exception("Error update tb_karyawan: " . $koneksi->error);
                    }
                }

                // Update tb_identitas - disesuaikan dengan struktur database
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
                    $sqlIdentitas = "UPDATE tb_identitas SET " . implode(", ", $updateIdentitas) . "
                     WHERE id_karyawan = '$id_karyawan'";
                    if (!$koneksi->query($sqlIdentitas)) {
                        throw new Exception("Error update tb_identitas: " . $koneksi->error);
                    }
                }

                // Update tb_kontak_darurat dengan menghapus data lama terlebih dahulu
                if (!empty($nama_kontak1) || !empty($nama_kontak2)) {
                    $koneksi->query("DELETE FROM tb_kontak_darurat WHERE id_karyawan='$id_karyawan'");
                }

                // Insert kontak darurat 1
                if (!empty($nama_kontak1) && !empty($no_telp_darurat1)) {
                    $sqlKontak1 = "INSERT INTO tb_kontak_darurat 
                        (id_karyawan, nama_kontak, hubungan, no_telp_darurat, nomor_urut)
                        VALUES 
                        ('$id_karyawan', '$nama_kontak1', '$hubungan1', '$no_telp_darurat1', 1)";

                    if (!$koneksi->query($sqlKontak1)) {
                        throw new Exception("Error insert kontak darurat 1: " . $koneksi->error);
                    }
                }
                // Insert kontak darurat 2
                if (!empty($nama_kontak2) && !empty($no_telp_darurat2)) {
                    $sqlKontak2 = "INSERT INTO tb_kontak_darurat 
                        (id_karyawan, nama_kontak, hubungan, no_telp_darurat, nomor_urut)
                        VALUES 
                        ('$id_karyawan', '$nama_kontak2', '$hubungan2', '$no_telp_darurat2', 2)";

                    if (!$koneksi->query($sqlKontak2)) {
                        throw new Exception("Error insert kontak darurat 2: " . $koneksi->error);
                    }
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

                // Insert tb_identitas - disesuaikan dengan struktur database
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

            $sqlKarier = null;

            if ($cekKarier->num_rows > 0) {
                // Update karier terakhir
                $idKarier = $cekKarier->fetch_assoc()['id_karier'];

                $updateKarier = [];

                addUpdateField($updateKarier, 'id_site', $id_site);
                addUpdateField($updateKarier, 'id_departemen', $id_departemen);
                addUpdateField($updateKarier, 'id_jabatan', $id_jabatan);

                // boleh null overwrite
                addUpdateField($updateKarier, 'tgl_mulai', $tgl_masuk_site, true);
                addUpdateField($updateKarier, 'tgl_selesai', $tgl_selesai_kontrak, true);

                if (!empty($updateKarier)) {
                    $sqlKarier = "UPDATE tb_karier SET " . implode(", ", $updateKarier) . "
                  WHERE id_karier = '$idKarier'";
                    if (isset($sqlKarier)) {
                        if (!$koneksi->query($sqlKarier)) {
                            throw new Exception("Error tb_karier: " . $koneksi->error);
                        }
                    }
                }
            } else {
                // Insert karier baru
                $sqlKarier = "INSERT INTO tb_karier (
                    id_karyawan, id_site, id_departemen, id_jabatan, tgl_mulai, tgl_selesai
                ) VALUES (
                    '$id_karyawan', '$id_site', '$id_departemen', '$id_jabatan',
                    " . ($tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL") . ",
                    " . ($tgl_selesai_kontrak ? "'$tgl_selesai_kontrak'" : "NULL") . "
                )";
            }
            if ($sqlKarier !== null) {
                if (!$koneksi->query($sqlKarier)) {
                    throw new Exception("Error tb_karier: " . $koneksi->error);
                }
            }


            // Update/Insert ke tb_tereliminasi
            $id_jenis_keluar = null;
            // Cek apakah karyawan berstatus aktif atau bukan
            if (!empty($status_karyawan) && strtolower(trim($status_karyawan)) != 'aktif') {
                $id_jenis_keluar = getId(
                    $koneksi,
                    'm_jenis_keluar',
                    'nama_kategori',
                    $status_karyawan,
                    'id_jenis_keluar'
                );

                if (!$id_jenis_keluar) {
                    throw new Exception("Jenis keluar '$status_karyawan' tidak ditemukan di m_jenis_keluar");
                }
            }

            // === PROSES tb_tereliminasi ===
            if (!empty($status_karyawan) && strtolower($status_karyawan) != 'aktif') {

                if (empty($tgl_keluar)) {
                    throw new Exception("Status '$status_karyawan' tapi tgl_keluar kosong");
                }

                // cek apakah sudah ada data eliminasi
                $cekEliminasi = $koneksi->query("
                    SELECT id_tereliminasi 
                    FROM tb_tereliminasi 
                    WHERE id_karyawan='$id_karyawan'
                ");

                if ($cekEliminasi->num_rows > 0) {
                    // UPDATE
                    $sqlEliminasi = "UPDATE tb_tereliminasi SET
                        tgl_keluar = '$tgl_keluar',
                        id_jenis_keluar = '$id_jenis_keluar',
                        alasan_keluar = '$alasan_keluar'
                    WHERE id_karyawan = '$id_karyawan'";
                } else {
                    // INSERT
                    $sqlEliminasi = "INSERT INTO tb_tereliminasi
                        (id_karyawan, tgl_keluar, id_jenis_keluar, alasan_keluar)
                    VALUES
                        ('$id_karyawan', '$tgl_keluar', '$id_jenis_keluar', '$alasan_keluar')";
                }

                if (!$koneksi->query($sqlEliminasi)) {
                    throw new Exception("Error tb_tereliminasi: " . $koneksi->error);
                }
            } else {
                // Jika status kembali Aktif → hapus eliminasi (sinkronisasi data)
                $koneksi->query("DELETE FROM tb_tereliminasi WHERE id_karyawan='$id_karyawan'");
            }


            $response["berhasil"]++;
            $response["debug"][] = "Baris $rowNum: Berhasil - NIK: $nik_karyawan";
        } catch (Exception $e) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: " . $e->getMessage();
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
    $response["error"] = "Gagal membaca file Excel: " . $koneksi->error;
}

echo json_encode($response);
exit;
