<?php
// cek_nik.php
include 'koneksi-api.php'; 

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = isset($_POST['nik']) ? $_POST['nik'] : '';

    if (empty($nik)) {
        $response = ['status' => 'error', 'message' => 'NIK wajib diisi.'];
    } else {
        try {
            // --- STEP 1: Cek apakah NIK (nik_ktp) ada di tb_identitas dan ambil id_karyawan ---
            $st_post_id = $koneksi->prepare("SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = ?");
            $st_post_id->execute([$nik]);
            $data_identitas = $st_post_id->fetch();

            if (!$data_identitas) {
                // NIK tidak ditemukan di data identitas
                $response = ['status' => 'error', 'message' => 'NIK tidak terdaftar di sistem.'];
            } else {
                $id_karyawan = $data_identitas['id_karyawan'];

                // --- STEP 2: Cek status_karyawan di tb_karyawan menggunakan id_karyawan ---
                $st_check_status = $koneksi->prepare("SELECT status_karyawan, password FROM tb_karyawan WHERE id_karyawan = ?");
                $st_check_status->execute([$id_karyawan]);
                $data_karyawan = $st_check_status->fetch();

                if (!$data_karyawan) {
                    // Kasus jarang: NIK ditemukan di identitas, tetapi data tidak lengkap di tb_karyawan
                    $response = ['status' => 'error', 'message' => 'Data karyawan tidak lengkap, hubungi HR.'];
                } else {
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
        } catch (PDOException $e) {
            error_log('cek_nik gagal: ' . $e->getMessage());
            $response = ['status' => 'error', 'message' => 'Terjadi kesalahan database.'];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
?>
