<?php
// File: update_profile.php (FINAL CODE - PERBAIKAN KRITIS)

include __DIR__ . '/koneksi-api.php';
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
$status_ptkp       = safe($data['status_ptkp']);

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

// =======================================================
// CEK PERUBAHAN REKENING & VERIFIKASI KEAMANAN
// =======================================================
$nik_verifikasi = safe($data['nik_verifikasi'] ?? '');
$password_verifikasi = safe($data['password_verifikasi'] ?? '');

$stmt_old_bank = $koneksi->prepare("SELECT nama_bank, no_rek, atas_nama_rek FROM tb_identitas WHERE id_karyawan = ?");
$stmt_old_bank->bind_param("s", $id_karyawan);
$stmt_old_bank->execute();
$old_bank_data = $stmt_old_bank->get_result()->fetch_assoc();
$stmt_old_bank->close();

$is_bank_changed = false;

if ($old_bank_data) {
    $existing_no_rek = trim($old_bank_data['no_rek'] ?? '');

    // JIKA DI DATABASE SUDAH ADA NO REKENING
    if ($existing_no_rek !== '') {
        // Paksa gunakan data lama dari database (mengabaikan inputan user/Flutter)
        $nama_bank     = $old_bank_data['nama_bank'];
        $no_rek        = $old_bank_data['no_rek'];
        $atas_nama_rek = $old_bank_data['atas_nama_rek'];

        // Data bank dipastikan tidak berubah karena ditimpa data lama
        $is_bank_changed = false;
    } else {
        // JIKA DI DATABASE MASIH KOSONG / NULL
        if (
            $nama_bank !== $old_bank_data['nama_bank'] ||
            $no_rek !== $old_bank_data['no_rek'] ||
            $atas_nama_rek !== $old_bank_data['atas_nama_rek']
        ) {
            $is_bank_changed = true; // Ada perubahan baru yang dimasukkan user
        }
    }
}

// Proses verifikasi jika ada perubahan data bank yang diizinkan (saat null pertama kali diisi)
if ($is_bank_changed) {
    if (empty($nik_verifikasi) || empty($password_verifikasi)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Otorisasi gagal: Mengubah data rekening WAJIB memasukkan NIK dan Password."
        ]);
        exit;
    }

    $hashed_password_verif = md5($password_verifikasi);
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
        $stmt_auth->close();
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Verifikasi Gagal: NIK KTP atau Password salah."
        ]);
        exit;
    }
    $stmt_auth->close();
}

// =======================
// PROSES UPLOAD FOTO PROFIL (BASE64)
// =======================
$foto_base64 = $data['foto_base64'] ?? '';
$nama_file_foto = "";

if (!empty($foto_base64)) {
    $target_dir = dirname(__DIR__) . '/uploads/profil/'; 
    if (!file_exists($target_dir)) { 
        mkdir($target_dir, 0755, true); 
    }

    // --- 1. CARI DAN HAPUS FOTO LAMA ---
    $stmt_old_foto = $koneksi->prepare("SELECT foto FROM tb_karyawan WHERE id_karyawan = ?");
    $stmt_old_foto->bind_param("s", $id_karyawan);
    $stmt_old_foto->execute();
    $res_old_foto = $stmt_old_foto->get_result()->fetch_assoc();
    $stmt_old_foto->close();

    if ($res_old_foto && !empty($res_old_foto['foto'])) {
        $path_foto_lama = $target_dir . $res_old_foto['foto'];
        // Cek apakah file fisik benar-benar ada di folder, lalu hapus
        if (file_exists($path_foto_lama) && is_file($path_foto_lama)) {
            unlink($path_foto_lama); 
        }
    }

    // --- 2. PROSES FOTO BARU ---
    // Bersihkan header base64 jika ada
    if (strpos($foto_base64, ',') !== false) {
        @list($type, $foto_base64) = explode(',', $foto_base64);
    }
    
    // Decode base64
    $foto_data = base64_decode($foto_base64);
    
    // Ambil NIK (dikirim dari Flutter)
    $nik_karyawan = safe($data['nik_karyawan'] ?? 'NONIK');
    
    // Bersihkan Nama Lengkap
    $nama_bersih = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($nama_lengkap));
    $nama_bersih = preg_replace('/_+/', '_', $nama_bersih); 
    $nama_bersih = trim($nama_bersih, '_');
    
    // Generate nama file baru
    $nama_file_foto = $nik_karyawan . "_" . $nama_bersih . ".jpg";
    $file_path = $target_dir . $nama_file_foto;

    // Simpan file baru ke server
    file_put_contents($file_path, $foto_data);
}

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
            status_pernikahan = ?,
            status_ptkp = ?
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
        $status_ptkp,
        $id_karyawan
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal update data karyawan: " . $stmt->error);
    }
    $stmt->close();

    // Simpan status update untuk tb_karyawan
    $updatedKaryawan = $koneksi->affected_rows;

    // ==== QUERY UPDATE FOTO ====
    if ($nama_file_foto !== "") {
        $stmt_foto = $koneksi->prepare("UPDATE tb_karyawan SET foto = ? WHERE id_karyawan = ?");
        $stmt_foto->bind_param("ss", $nama_file_foto, $id_karyawan);
        $stmt_foto->execute();
        $stmt_foto->close();
    }

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
