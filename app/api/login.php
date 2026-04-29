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
        $hashed_password = md5($password); 

        // --- STEP 1: Cari id_karyawan dari tb_identitas menggunakan NIK ---
        $query_get_id = "SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = '$nik'";
        $result_get_id = mysqli_query($koneksi, $query_get_id);
        
        if (mysqli_num_rows($result_get_id) == 0) {
            $response = ['status' => 'error', 'message' => 'NIK tidak terdaftar atau salah.'];
        } else {
            $data_identitas = mysqli_fetch_assoc($result_get_id);
            $id_karyawan = $data_identitas['id_karyawan'];

            // --- STEP 2: Verifikasi Password (Tanpa Status Dulu) ---
            $query_login = "SELECT * FROM tb_karyawan 
                            WHERE id_karyawan = '$id_karyawan' 
                            AND password = '$hashed_password'"; 

            $result_login = mysqli_query($koneksi, $query_login);

            if (mysqli_num_rows($result_login) > 0) {
                // Password benar, sekarang cek status karyawannya
                $data_karyawan = mysqli_fetch_assoc($result_login);
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