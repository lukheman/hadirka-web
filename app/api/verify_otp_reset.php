<?php
include 'koneksi-api.php';
header('Content-Type: application/json');

$nik = $_POST['nik'] ?? '';
$otp = $_POST['otp'] ?? '';

if(empty($nik) || empty($otp)){
  echo json_encode(['status'=>'error','message'=>'NIK dan OTP wajib diisi']);
  exit;
}

try {
  // Cari id_karyawan dari NIK
  $st_id = $koneksi->prepare("SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = ?");
  $st_id->execute([$nik]);
  $row_id = $st_id->fetch();
  if(!$row_id){
      echo json_encode(['status'=>'error','message'=>'NIK tidak valid']);
      exit;
  }
  $id_karyawan = $row_id['id_karyawan'];

  // Cek OTP
  $st = $koneksi->prepare("
      SELECT * FROM tb_reset_otp
      WHERE id_karyawan = ?
      AND otp_code = ?
      AND is_used = 0
      AND expired_at > NOW()
      ORDER BY id_otp DESC LIMIT 1
  ");
  $st->execute([$id_karyawan, $otp]);
  $row_otp = $st->fetch();

  if(!$row_otp){
    echo json_encode(['status'=>'error','message'=>'OTP tidak valid atau sudah kadaluarsa']);
    exit;
  }

  // Tandai OTP sudah terpakai
  $st_used = $koneksi->prepare("
      UPDATE tb_reset_otp SET is_used = 1
      WHERE id_karyawan = ? AND otp_code = ?
  ");
  $st_used->execute([$id_karyawan, $otp]);
} catch (PDOException $e) {
  error_log('verify_otp_reset gagal: ' . $e->getMessage());
  echo json_encode(['status'=>'error','message'=>'Terjadi kesalahan database.']);
  exit;
}

echo json_encode(['status'=>'success','message'=>'OTP valid. Silakan buat kata sandi baru.']);
?>
