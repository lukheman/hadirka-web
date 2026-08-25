<?php
// login.php
include 'koneksi-api.php'; // Pastikan jalur koneksi benar

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = isset($_POST['username']) ? mysqli_real_escape_string($koneksi, $_POST['username']) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($koneksi, $_POST['password']) : '';

    if (empty($nik) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'NIK dan Kata Sandi wajib diisi.'];
    } else {
        // --- STEP 1: Cari id_karyawan dari tb_identitas menggunakan NIK ---
        $query_get_id = "SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = '$nik'";
        $result_get_id = mysqli_query($koneksi, $query_get_id);
        
        if (mysqli_num_rows($result_get_id) == 0) {
            $response = ['status' => 'error', 'message' => 'NIK tidak terdaftar atau salah.'];
        } else {
            $data_identitas = mysqli_fetch_assoc($result_get_id);
            $id_karyawan = $data_identitas['id_karyawan'];

            // --- STEP 2: Verifikasi Password ---
            $query_login = "SELECT * FROM tb_karyawan WHERE id_karyawan = '$id_karyawan'";

            $result_login = mysqli_query($koneksi, $query_login);

            if (mysqli_num_rows($result_login) > 0) {
                $data_karyawan = mysqli_fetch_assoc($result_login);
                $stored_password = (string)$data_karyawan['password'];
                $password_valid = password_verify($password, $stored_password)
                    || hash_equals($stored_password, md5($password));

                if (!$password_valid) {
                    $response = ['status' => 'error', 'message' => 'Kata sandi salah.'];
                    echo json_encode($response);
                    mysqli_close($koneksi);
                    exit;
                }

                $status = $data_karyawan['status_karyawan'];
                
                // Cek apakah statusnya 'Aktif' (atau 1 sesuai database Anda)
                if ($status == 'Aktif' || $status == 'aktif') {
                    // Login Berhasil
                    $response = [
                        'status' => 'success', 
                        'message' => 'Login berhasil!',
                        'id_karyawan' => $id_karyawan,
                    ];
                } else {
                    // Karyawan tidak aktif
                    $response = ['status' => 'error', 'message' => 'Karyawan sudah tidak aktif'];
                }
            } else {
                // Jika data tidak ditemukan di sini, berarti password salah
                $response = ['status' => 'error', 'message' => 'Kata sandi salah.'];
            }
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
mysqli_close($koneksi);
?>
