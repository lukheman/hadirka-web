<?php
// cek_nik.php
include 'koneksi-api.php'; 

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = isset($_POST['nik']) ? mysqli_real_escape_string($koneksi, $_POST['nik']) : '';

    if (empty($nik)) {
        $response = ['status' => 'error', 'message' => 'NIK wajib diisi.'];
    } else {
        // --- STEP 1: Cek apakah NIK (nik_ktp) ada di tb_identitas dan ambil id_karyawan ---
        $query_POST_id = "SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = '$nik'";
        $result_POST_id = mysqli_query($koneksi, $query_POST_id);

        if (!$result_POST_id || mysqli_num_rows($result_POST_id) == 0) {
            // NIK tidak ditemukan di data identitas
            $response = ['status' => 'error', 'message' => 'NIK tidak terdaftar di sistem.'];
        } else {
            $data_identitas = mysqli_fetch_assoc($result_POST_id);
            $id_karyawan = $data_identitas['id_karyawan'];

            // --- STEP 2: Cek status_karyawan di tb_karyawan menggunakan id_karyawan ---
            $query_check_status = "SELECT status_karyawan, password FROM tb_karyawan WHERE id_karyawan = '$id_karyawan'";
            $result_check_status = mysqli_query($koneksi, $query_check_status);

            if (!$result_check_status || mysqli_num_rows($result_check_status) == 0) {
                // Kasus jarang: NIK ditemukan di identitas, tetapi data tidak lengkap di tb_karyawan
                $response = ['status' => 'error', 'message' => 'Data karyawan tidak lengkap, hubungi HR.'];
            } else {
                $data_karyawan = mysqli_fetch_assoc($result_check_status);
                $status = $data_karyawan['status_karyawan'];
                $password_tersimpan = $data_karyawan['password'];

                if (strtolower($status) === 'aktif' && !empty($password_tersimpan)) {
                    // Status 1: Akun sudah aktif
                    $response = ['status' => 'error', 'message' => 'Akun untuk NIK ini sudah aktif. Silakan login.'];
                } else {
                    // Status 0: NIK valid dan belum aktif, siap untuk pendaftaran
                    $response = ['status' => 'success', 'message' => 'NIK terverifikasi. Silakan lanjutkan aktivasi akun.'];
                }
            }
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
mysqli_close($koneksi);
?>