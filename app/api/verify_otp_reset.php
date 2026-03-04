<?php
include 'koneksi-api.php';
header('Content-Type: application/json');

$nik = $_POST['nik'] ?? '';
$otp = $_POST['otp'] ?? '';

if(empty($nik) || empty($otp)){
  echo json_encode(['status'=>'error','message'=>'NIK dan OTP wajib diisi']);
  exit;
}

// Cari id_karyawan dari NIK
$q_id = mysqli_query($koneksi, "SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = '$nik'");
if(mysqli_num_rows($q_id) == 0){
    echo json_encode(['status'=>'error','message'=>'NIK tidak valid']);
    exit;
}
$id_karyawan = mysqli_fetch_assoc($q_id)['id_karyawan'];

// Cek OTP
$q = mysqli_query($koneksi,"
    SELECT * FROM tb_reset_otp
    WHERE id_karyawan='$id_karyawan'
    AND otp_code='$otp'
    AND is_used=0
    AND expired_at > NOW()
    ORDER BY id_otp DESC LIMIT 1
");

if(mysqli_num_rows($q)==0){
  echo json_encode(['status'=>'error','message'=>'OTP tidak valid atau sudah kadaluarsa']);
  exit;
}

// Tandai OTP sudah terpakai
mysqli_query($koneksi,"
    UPDATE tb_reset_otp SET is_used=1
    WHERE id_karyawan='$id_karyawan' AND otp_code='$otp'
");

echo json_encode(['status'=>'success','message'=>'OTP valid. Silakan buat kata sandi baru.']);
?>