<?php
// File: update_profile.php 

include 'koneksi-api.php';
header('Content-Type: application/json');

// =======================
// AMBIL JSON
// =======================
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Payload JSON tidak valid"
    ]);
    exit;
}

// =======================
// VALIDASI ID
// =======================
$id_karyawan = trim($data['id_karyawan'] ?? '');
if ($id_karyawan === '') {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "ID karyawan wajib diisi"
    ]);
    exit;
}

// Helper sanitasi
function safe($v)
{
    return trim($v ?? '');
}

// =======================
// FIELD tb_karyawan
// =======================
$nama_lengkap      = safe($data['nama_lengkap']);
$jenis_kelamin     = safe($data['jenis_kelamin']);
$tgl_lahir         = safe($data['tgl_lahir']);
$tempat_lahir      = safe($data['tempat_lahir']);
$email_pribadi     = safe($data['email_pribadi']);
$no_telp           = safe($data['no_telp']);
$nationality       = safe($data['nationality']);
$agama             = safe($data['agama']);
$status_pernikahan = safe($data['status_pernikahan']);

// =======================
// FIELD tb_identitas
// =======================
$nik_ktp          = safe($data['nik_ktp']);
$kk_number        = safe($data['kk_number']);
$npwp             = safe($data['npwp']);
$bpjs_tk          = safe(str_replace(['-', ' '], '', $data['bpjs_tk']));
$bpjs_kes         = safe(str_replace(['-', ' '], '', $data['bpjs_kes']));
$alamat_ktp       = safe($data['alamat_ktp']);
$provinsi         = safe($data['provinsi']);
$kabupaten        = safe($data['kabupaten']);
$kecamatan        = safe($data['kecamatan']);
$desa             = safe($data['desa']);
$rt_rw            = safe($data['rt_rw']);
$nama_ibu         = safe($data['nama_ibu']);
$last_education   = safe($data['last_education']);
$major            = safe($data['major']);
$education_level  = safe($data['education_level']);

// NOTE: Field rekening dihapus dari tangkapan dan tidak diupdate lagi.

// =======================
// TRANSAKSI
// =======================
$koneksi->begin_transaction();

try {

    // Lock & cek karyawan
    $cek = $koneksi->prepare(
        "SELECT id_karyawan FROM tb_karyawan WHERE id_karyawan = ? FOR UPDATE"
    );
    $cek->bind_param("s", $id_karyawan);
    $cek->execute();
    $res = $cek->get_result();

    if ($res->num_rows === 0) {
        throw new Exception("Data karyawan tidak ditemukan");
    }
    $cek->close();

    // UPDATE tb_karyawan
    $stmt = $koneksi->prepare("
        UPDATE tb_karyawan SET
            nama_lengkap = ?,
            jenis_kelamin = ?,
            tgl_lahir = ?,
            tempat_lahir = ?,
            email_pribadi = ?,
            no_telp = ?,
            nationality = ?,
            agama = ?,
            status_pernikahan = ?
        WHERE id_karyawan = ?
    ");

    $stmt->bind_param(
        "ssssssssss",
        $nama_lengkap,
        $jenis_kelamin,
        $tgl_lahir,
        $tempat_lahir,
        $email_pribadi,
        $no_telp,
        $nationality,
        $agama,
        $status_pernikahan,
        $id_karyawan
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal update data karyawan: " . $stmt->error);
    }
    $stmt->close();

    // UPDATE tb_identitas (Tanpa field nama_bank, no_rek, dan atas_nama_rek)
    $stmt = $koneksi->prepare("UPDATE tb_identitas SET
            nik_ktp = ?,
            kk_number = ?,
            npwp = ?,
            bpjs_tk = ?,
            bpjs_kes = ?,
            alamat_ktp = ?,
            provinsi = ?,
            kabupaten = ?,
            kecamatan = ?,
            desa = ?,
            rt_rw = ?,
            nama_ibu = ?,
            last_education = ?,
            major = ?,
            education_level = ?
        WHERE id_karyawan = ?
    ");

    $stmt->bind_param(
        "ssssssssssssssss", // S berjumlah 16
        $nik_ktp,
        $kk_number,
        $npwp,
        $bpjs_tk,
        $bpjs_kes,
        $alamat_ktp,
        $provinsi,
        $kabupaten,
        $kecamatan,
        $desa,
        $rt_rw,
        $nama_ibu,
        $last_education,
        $major,
        $education_level,
        $id_karyawan
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal update data identitas: " . $stmt->error);
    }
    $stmt->close();

    $koneksi->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Profil berhasil diperbarui"
    ]);
} catch (Exception $e) {

    $koneksi->rollback();
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Transaction Error: " . $e->getMessage()
    ]);
}

$koneksi->close();