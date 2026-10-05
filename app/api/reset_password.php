<?php
// reset_password.php
include 'koneksi-api.php'; 

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = isset($_POST['nik']) ? $_POST['nik'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($nik) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'NIK dan Kata Sandi Baru wajib diisi.'];
    } else {
        $hashed_password = md5($password); 
        
        try {
            // 1. Dapatkan id_karyawan dari tb_identitas
            $st_get_id = $koneksi->prepare("SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = ?");
            $st_get_id->execute([$nik]);
            $data_identitas = $st_get_id->fetch();

            if (!$data_identitas) {
                 throw new Exception("NIK tidak terdaftar di sistem.");
            }
            
            $id_karyawan = $data_identitas['id_karyawan'];

            // 2. UPDATE password di tb_karyawan
            $st_update = $koneksi->prepare("UPDATE tb_karyawan SET password = ? WHERE id_karyawan = ?");
            $st_update->execute([$hashed_password, $id_karyawan]);

            // 3. CEK APAKAH ADA BARIS YANG BENAR-BENAR BERUBAH
            if ($st_update->rowCount() == 0) {
                // Mengecek apakah password baru sama dengan password lama (database tidak melakukan update jika datanya sama)
                $st_check = $koneksi->prepare("SELECT password FROM tb_karyawan WHERE id_karyawan = ? AND password = ?");
                $st_check->execute([$id_karyawan, $hashed_password]);
                if ($st_check->fetch()) {
                    throw new Exception("Kata sandi baru tidak boleh sama dengan kata sandi saat ini.");
                } else {
                    throw new Exception("Gagal mereset kata sandi. Data karyawan tidak ditemukan di tabel akun.");
                }
            }

            $response = ['status' => 'success', 'message' => 'Kata sandi berhasil diubah. Silakan login.'];

        } catch (PDOException $e) {
            error_log('reset_password gagal: ' . $e->getMessage());
            $response = ['status' => 'error', 'message' => 'Gagal mereset kata sandi. Terjadi kesalahan database.'];
        } catch (Exception $e) {
            $response = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
?>
