<?php
// cek_nik_reset.php
include 'koneksi-api.php';

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = isset($_POST['nik']) ? mysqli_real_escape_string($koneksi, $_POST['nik']) : '';

    if (empty($nik)) {
        $response = ['status' => 'error', 'message' => 'NIK wajib diisi.'];
    } else {
        // Cek NIK di tb_identitas
        $query_POST_id = "SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = '$nik'";
        $result_POST_id = mysqli_query($koneksi, $query_POST_id);

        if (!$result_POST_id || mysqli_num_rows($result_POST_id) == 0) {
            $response = ['status' => 'error', 'message' => 'NIK tidak terdaftar di sistem.'];
        } else {
            $data_identitas = mysqli_fetch_assoc($result_POST_id);
            $id_karyawan = $data_identitas['id_karyawan'];

            // Cek status DAN password di tb_karyawan
            $query_check_status = "SELECT status_karyawan, password FROM tb_karyawan WHERE id_karyawan = '$id_karyawan'";
            $result_check_status = mysqli_query($koneksi, $query_check_status);

            if (!$result_check_status || mysqli_num_rows($result_check_status) == 0) {
                $response = ['status' => 'error', 'message' => 'Data karyawan tidak ditemukan.'];
            } else {
                $data_karyawan = mysqli_fetch_assoc($result_check_status);
                $status = $data_karyawan['status_karyawan'];
                $password_tersimpan = $data_karyawan['password'];

                // Cek apakah status = 1 ATAU password tidak kosong
                if (strtolower($status) === 'aktif' && !empty($password_tersimpan)) {
                    // Berhasil: Akun aktif (atau sudah punya password), boleh reset
                    $response = ['status' => 'success', 'message' => 'NIK ditemukan. Silakan masukkan kata sandi baru.'];
                } else {
                    // Gagal: Akun belum aktif dan belum punya password
                    $response = ['status' => 'error', 'message' => 'Akun belum diaktivasi. Silakan lakukan Aktivasi Akun terlebih dahulu.'];
                }
            }
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
mysqli_close($koneksi);
