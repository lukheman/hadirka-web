<?php
// reset_password.php
include 'koneksi-api.php'; 

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = isset($_POST['nik']) ? mysqli_real_escape_string($koneksi, $_POST['nik']) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($koneksi, $_POST['password']) : '';

    if (empty($nik) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'NIK dan Kata Sandi Baru wajib diisi.'];
    } else {
        $hashed_password = md5($password); 
        
        try {
            // Dapatkan id_karyawan dari tb_identitas
            $query_get_id = "SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = '$nik'";
            $result_get_id = mysqli_query($koneksi, $query_get_id);

            if (!$result_get_id || mysqli_num_rows($result_get_id) == 0) {
                 throw new Exception("NIK tidak terdaftar di sistem.");
            }
            
            $data_identitas = mysqli_fetch_assoc($result_get_id);
            $id_karyawan = $data_identitas['id_karyawan'];

            // UPDATE password di tb_karyawan
            $query_update = "UPDATE tb_karyawan SET password = '$hashed_password' WHERE id_karyawan = '$id_karyawan'";

            if (!mysqli_query($koneksi, $query_update)) {
                throw new Exception("Gagal mereset kata sandi: " . mysqli_error($koneksi));
            }

            $response = ['status' => 'success', 'message' => 'Kata sandi berhasil diubah. Silakan login.'];

        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
mysqli_close($koneksi);
?>