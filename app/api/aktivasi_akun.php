<?php
// aktivasi_akun.php
include 'koneksi-api.php'; 

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil dan bersihkan input
    // Perhatian: Karena tb_karyawan Anda hanya memiliki 'password' (tanpa 'username'),
    // input username dari Flutter akan diabaikan, namun NIK dan Password tetap diproses.
    $nik = isset($_POST['nik']) ? $_POST['nik'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($nik) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'NIK dan Kata Sandi wajib diisi.'];
    } else {
        // Enkripsi password (Gunakan MD5 sesuai contoh sebelumnya)
        $hashed_password = md5($password); 
        
        // Mulai Transaksi
        $koneksi->beginTransaction();

        try {
            // --- STEP 1: Dapatkan id_karyawan dari tb_identitas menggunakan NIK (nik_ktp) ---
            $st_get_id = $koneksi->prepare("SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = ?");
            $st_get_id->execute([$nik]);
            $data_identitas = $st_get_id->fetch();

            if (!$data_identitas) {
                 throw new Exception("NIK tidak terdaftar di sistem identitas.");
            }
            
            $id_karyawan = $data_identitas['id_karyawan'];

            // --- STEP 2: Cek apakah akun sudah aktif di tb_karyawan ---
            $st_check_status = $koneksi->prepare("SELECT status_karyawan FROM tb_karyawan WHERE id_karyawan = ?");
            $st_check_status->execute([$id_karyawan]);
            $data_karyawan = $st_check_status->fetch();
            
            if (!$data_karyawan) {
                 // Kasus jarang: NIK ada di identitas, tapi ID tidak ada di tb_karyawan
                 throw new Exception("Data karyawan tidak ditemukan untuk NIK ini.");
            }
            
            if ($data_karyawan['status_karyawan'] == 1) {
                 throw new Exception("Akun untuk NIK ini sudah aktif.");
            }
            
            // --- STEP 3: UPDATE password dan status_karyawan di tb_karyawan ---
            $st_update = $koneksi->prepare("UPDATE tb_karyawan 
                             SET password = ?, status_karyawan = 1 
                             WHERE id_karyawan = ?");
            $st_update->execute([$hashed_password, $id_karyawan]);

            // Jika semua query sukses, commit transaksi
            $koneksi->commit();
            $response = ['status' => 'success', 'message' => 'Akun berhasil diaktifkan. Silakan login menggunakan NIK dan kata sandi baru Anda.'];

        } catch (PDOException $e) {
            // Error database: rollback + pesan generik (detail hanya di log)
            if ($koneksi->inTransaction()) {
                $koneksi->rollBack();
            }
            error_log('aktivasi_akun gagal: ' . $e->getMessage());
            $response = ['status' => 'error', 'message' => 'Terjadi kesalahan database. Silakan coba lagi.'];
        } catch (Exception $e) {
            // Jika ada kesalahan bisnis, rollback transaksi
            if ($koneksi->inTransaction()) {
                $koneksi->rollBack();
            }
            // Tangani error dan kembalikan pesan ke Flutter
            $response = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
?>
