<?php
// login.php
include 'koneksi-api.php'; // Pastikan jalur koneksi benar

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($nik) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'NIK dan Kata Sandi wajib diisi.'];
    } else {
        try {
            // --- STEP 1: Cari id_karyawan dari tb_identitas menggunakan NIK ---
            $st_get_id = $koneksi->prepare("SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = ?");
            $st_get_id->execute([$nik]);
            $data_identitas = $st_get_id->fetch();

            if (!$data_identitas) {
                $response = ['status' => 'error', 'message' => 'NIK tidak terdaftar atau salah.'];
            } else {
                $id_karyawan = $data_identitas['id_karyawan'];

                // --- STEP 2: Verifikasi Password ---
                $st_login = $koneksi->prepare("SELECT * FROM tb_karyawan WHERE id_karyawan = ?");
                $st_login->execute([$id_karyawan]);
                $data_karyawan = $st_login->fetch();

                if ($data_karyawan) {
                    $stored_password = (string)$data_karyawan['password'];
                    $password_valid = password_verify($password, $stored_password)
                        || hash_equals($stored_password, md5($password));

                    if (!$password_valid) {
                        $response = ['status' => 'error', 'message' => 'Kata sandi salah.'];
                        echo json_encode($response);
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
        } catch (PDOException $e) {
            error_log('login gagal: ' . $e->getMessage());
            $response = ['status' => 'error', 'message' => 'Terjadi kesalahan database.'];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
?>
