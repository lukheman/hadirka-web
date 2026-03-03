<?php
// File: update_profile.php (FINAL CODE - PERBAIKAN KRITIS)

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
function safe($v) {
    return trim($v ?? '');
}

// =======================
// FIELD VERIFIKASI KEAMANAN
// =======================
$nik_verifikasi = safe($data['nik_verifikasi'] ?? '');
$password_verifikasi = safe($data['password_verifikasi'] ?? '');

if (empty($nik_verifikasi) || empty($password_verifikasi)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Otorisasi gagal: NIK dan Password verifikasi harus diisi"
    ]);
    exit;
}

// Enkripsi password input dengan MD5 (sesuai standar db kamu)
$hashed_password_verif = md5($password_verifikasi);

// =======================
// CEK VERIFIKASI KE DATABASE
// =======================
$stmt_auth = $koneksi->prepare("
    SELECT k.id_karyawan 
    FROM tb_karyawan k
    INNER JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
    WHERE k.id_karyawan = ? AND i.nik_ktp = ? AND k.password = ?
");
$stmt_auth->bind_param("sss", $id_karyawan, $nik_verifikasi, $hashed_password_verif);
$stmt_auth->execute();
$res_auth = $stmt_auth->get_result();

if ($res_auth->num_rows === 0) {
    // Jika tidak cocok, tolak proses update
    $stmt_auth->close();
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Verifikasi Gagal: NIK KTP atau Password yang Anda masukkan salah."
    ]);
    exit;
}
$stmt_auth->close();


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
$bpjs_tk          = safe(str_replace(['-', ' '], '', $data['bpjs_tk'])); // Sanitasi jika user input format
$bpjs_kes         = safe(str_replace(['-', ' '], '', $data['bpjs_kes'])); // Sanitasi jika user input format
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

$nama_bank  = safe($data['nama_bank']);
$no_rek  = safe($data['no_rek']);
$atas_nama_rek  = safe($data['atas_nama_rek']);

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
    
    // Simpan status update untuk tb_karyawan
    $updatedKaryawan = $koneksi->affected_rows;


    // UPDATE tb_identitas
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
            education_level = ?,
            nama_bank = ?,
            no_rek = ?,
            atas_nama_rek = ?
        WHERE id_karyawan = ?
    ");	


    $stmt->bind_param(
        "sssssssssssssssssss",
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
        $nama_bank,
        $no_rek,
        $atas_nama_rek,
        $id_karyawan
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal update data identitas: " . $stmt->error);
    }
    $stmt->close();

    // PERBAIKAN KRITIS DI SINI:
    // Tidak perlu memeriksa affected_rows === 0 untuk tb_identitas, 
    // karena update tb_karyawan mungkin sudah berhasil. Commit tetap dilakukan.
    
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