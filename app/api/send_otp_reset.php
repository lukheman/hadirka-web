<?php
// Tampilkan error saat tahap development (matikan saat rilis production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

include 'koneksi-api.php';

// Import class PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Panggil file PHPMailer yang sudah di-upload
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

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
$email_tujuan = $data['email_pribadi'];
$status = strtolower(trim($data['status_karyawan']));
$password_tersimpan = $data['password'];

// 1. Pengecekan Keamanan (Status Aktif & Sudah Punya Akun)
$status_diizinkan = ['aktif', 'cuti'];
if (!in_array($status, $status_diizinkan) || empty($password_tersimpan)) {
    echo json_encode(['status' => 'error', 'message' => 'Akun tidak memenuhi syarat untuk mereset kata sandi.']);
    exit;
}

if (empty($email_tujuan)) {
    echo json_encode(['status' => 'error', 'message' => 'Email belum terdaftar. Hubungi HRD.']);
    exit;
}

// 2. Generate dan Simpan OTP
$otp = rand(100000, 999999);
$expired = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// Hapus OTP lama yang belum terpakai
mysqli_query($koneksi, "DELETE FROM tb_reset_otp WHERE id_karyawan='$id_karyawan' AND is_used=0");

// Insert OTP Baru
mysqli_query($koneksi,"
    INSERT INTO tb_reset_otp(id_karyawan, otp_code, expired_at)
    VALUES('$id_karyawan','$otp','$expired')
");

// Sensor email untuk ditampilkan di HP (agar aman)
$parts = explode("@", $email_tujuan);
$sensor_email = substr($parts[0], 0, 2) . str_repeat("*", strlen($parts[0]) - 2) . "@" . $parts[1];

// 3. PROSES KIRIM EMAIL DENGAN PHPMAILER
$mail = new PHPMailer(true);

try {
    // KONFIGURASI SMTP HOSTING
    $mail->isSMTP();
    $mail->Host       = 'hris.triputrajayasultra.com'; // Biasanya mail.namadomain.com atau cek di cPanel (Email Routing)
    $mail->SMTPAuth   = true;
    $mail->Username   = 'admin@triputrajayasultra.com'; // Email yang Anda buat
    $mail->Password   = '@Triputra1505'; // Password email admin Anda
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Gunakan ENCRYPTION_STARTTLS jika port 587
    $mail->Port       = 465; // Port SMTP SSL (Gunakan 587 jika TLS)

    // Pengirim & Penerima
    $mail->setFrom('admin@triputrajayasultra.com', 'HRIS Triputra Jaya Sultra');
    $mail->addAddress($email_tujuan); // Email karyawan

    // Konten Email
    $mail->isHTML(true);
    $mail->Subject = 'Kode OTP Reset Password - HRIS';
    $mail->Body    = "
        <h3>Halo,</h3>
        <p>Anda telah meminta untuk mereset kata sandi akun HRIS Anda.</p>
        <p>Berikut adalah kode OTP Anda:</p>
        <h2 style='background: #f4f4f4; padding: 10px; display: inline-block; letter-spacing: 5px;'>$otp</h2>
        <p>Kode ini hanya berlaku selama <b>5 menit</b>.</p>
        <p><i>Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini.</i></p>
    ";
    $mail->AltBody = "Kode OTP Reset Password HRIS Anda adalah: $otp. Berlaku 5 menit.";

    $mail->send();
    
    // Jika sukses terkirim
    echo json_encode(['status' => 'success', 'message' => "OTP berhasil dikirim ke email $sensor_email"]);

} catch (Exception $e) {
    // Jika gagal terkirim, kita hapus OTP dari database agar tidak nyangkut
    mysqli_query($koneksi, "DELETE FROM tb_reset_otp WHERE id_karyawan='$id_karyawan' AND otp_code='$otp'");
    
    // Tampilkan pesan error mailer untuk debugging (Bisa diganti dengan pesan umum nanti)
    echo json_encode(['status' => 'error', 'message' => "Gagal mengirim email. Error: {$mail->ErrorInfo}"]);
}
?>