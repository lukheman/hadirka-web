<?php
include 'koneksi-api.php';
header('Content-Type: application/json');

$nik = $_POST['nik'] ?? '';

if (empty($nik)) {
    echo json_encode(['status' => 'error', 'message' => 'NIK wajib diisi']);
    exit;
}

// Cari data berdasarkan NIK
$q = mysqli_query($koneksi,"
    SELECT k.id_karyawan, k.email_pribadi, k.status_karyawan, k.password 
    FROM tb_identitas i
    JOIN tb_karyawan k ON i.id_karyawan = k.id_karyawan
    WHERE i.nik_ktp = '$nik'
");

if (mysqli_num_rows($q) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'NIK tidak ditemukan di sistem']);
    exit;
}

$data = mysqli_fetch_assoc($q);
$id_karyawan = $data['id_karyawan'];
$email = $data['email_pribadi'];
$status = strtolower(trim($data['status_karyawan']));
$password_tersimpan = $data['password'];

// 1. Pengecekan Keamanan (Status Aktif & Sudah Punya Akun)
$status_diizinkan = ['aktif', 'cuti'];
if (!in_array($status, $status_diizinkan) || empty($password_tersimpan)) {
    echo json_encode(['status' => 'error', 'message' => 'Akun tidak memenuhi syarat untuk mereset kata sandi.']);
    exit;
}

if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Email belum terdaftar. Hubungi HRD.']);
    exit;
}

// 2. Generate dan Simpan OTP
$otp = rand(100000, 999999);
$expired = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// Opsional: Hapus OTP lama yang belum terpakai agar tabel tidak penuh
mysqli_query($koneksi, "DELETE FROM tb_reset_otp WHERE id_karyawan='$id_karyawan' AND is_used=0");

mysqli_query($koneksi,"
    INSERT INTO tb_reset_otp(id_karyawan, otp_code, expired_at)
    VALUES('$id_karyawan','$otp','$expired')
");

// 3. Kirim Email (Catatan: fungsi mail() butuh konfigurasi SMTP di server agar jalan)
$subject = "Kode OTP Reset Password HRIS";
$message = "Kode OTP kamu: $otp\nBerlaku selama 5 menit. Jangan berikan kode ini ke siapapun.";
$headers = "From: no-reply@hris.com";

mail($email, $subject, $message, $headers);

// Sensor email untuk ditampilkan di HP (3 awal, 2 akhir)
$parts = explode("@", $email);
$local_part = $parts[0];
$domain_part = $parts[1];
$len = strlen($local_part);

if ($len > 5) {
    // Jika karakter sebelum @ lebih dari 5 huruf (misal: budisanto -> bud***to)
    $sensor_email = substr($local_part, 0, 3) . str_repeat("*", $len - 5) . substr($local_part, -2) . "@" . $domain_part;
} else {
    // Fallback jika nama email sangat pendek (misal: andi -> an**)
    // Pastikan minimal ambil 2 karakter depan saja agar tidak aneh
    $tampil = min(2, $len - 1); 
    $tampil = $tampil < 1 ? 1 : $tampil; // Jaga-jaga kalau cuma 1 huruf
    $sensor_email = substr($local_part, 0, $tampil) . str_repeat("*", $len - $tampil) . "@" . $domain_part;
}

echo json_encode(['status' => 'success', 'message' => "OTP berhasil dikirim ke email $sensor_email"]);
