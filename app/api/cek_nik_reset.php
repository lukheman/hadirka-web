<?php
// cek_nik_reset.php
include 'koneksi-api.php';

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = isset($_POST['nik']) ? $_POST['nik'] : '';

    if (empty($nik)) {
        $response = ['status' => 'error', 'message' => 'NIK wajib diisi.'];
    } else {
        try {
            // Cek NIK di tb_identitas
            $st_post_id = $koneksi->prepare("SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = ?");
            $st_post_id->execute([$nik]);
            $data_identitas = $st_post_id->fetch();

            if (!$data_identitas) {
                $response = ['status' => 'error', 'message' => 'NIK tidak terdaftar di sistem.'];
            } else {
                $id_karyawan = $data_identitas['id_karyawan'];

                // Cek status DAN password di tb_karyawan
                $st_check_status = $koneksi->prepare("SELECT status_karyawan, password FROM tb_karyawan WHERE id_karyawan = ?");
                $st_check_status->execute([$id_karyawan]);
                $data_karyawan = $st_check_status->fetch();

                if (!$data_karyawan) {
                    $response = ['status' => 'error', 'message' => 'Data karyawan tidak ditemukan.'];
                } else {
                    $status = $data_karyawan['status_karyawan'];
                    $password_tersimpan = $data_karyawan['password'];

                    // Cek apakah status = aktif DAN password tidak kosong
                    if (strtolower($status) === 'aktif' && !empty($password_tersimpan)) {
                        // Berhasil: Akun aktif (atau sudah punya password), boleh reset
                        $response = ['status' => 'success', 'message' => 'NIK ditemukan. Silakan masukkan kata sandi baru.'];
                    } else {
                        // Gagal: Akun belum aktif dan belum punya password
                        $response = ['status' => 'error', 'message' => 'Akun belum diaktivasi. Silakan lakukan Aktivasi Akun terlebih dahulu.'];
                    }
                }
            }
        } catch (PDOException $e) {
            error_log('cek_nik_reset gagal: ' . $e->getMessage());
            $response = ['status' => 'error', 'message' => 'Terjadi kesalahan database.'];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
