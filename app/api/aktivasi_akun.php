<?php
// aktivasi_akun.php
include 'koneksi-api.php'; 

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil dan bersihkan input
    // Perhatian: Karena tb_karyawan Anda hanya memiliki 'password' (tanpa 'username'),
    // input username dari Flutter akan diabaikan, namun NIK dan Password tetap diproses.
    $nik = isset($_POST['nik']) ? mysqli_real_escape_string($koneksi, $_POST['nik']) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($koneksi, $_POST['password']) : '';

    if (empty($nik) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'NIK dan Kata Sandi wajib diisi.'];
    } else {
        // Enkripsi password (Gunakan MD5 sesuai contoh sebelumnya)
        $hashed_password = md5($password); 
        
        // Mulai Transaksi
        mysqli_begin_transaction($koneksi);

        try {
            // --- STEP 1: Dapatkan id_karyawan dari tb_identitas menggunakan NIK (nik_ktp) ---
            $query_get_id = "SELECT id_karyawan FROM tb_identitas WHERE nik_ktp = '$nik'";
            $result_get_id = mysqli_query($koneksi, $query_get_id);

            if (!$result_get_id || mysqli_num_rows($result_get_id) == 0) {
                 throw new Exception("NIK tidak terdaftar di sistem identitas.");
            }
            
            $data_identitas = mysqli_fetch_assoc($result_get_id);
            $id_karyawan = $data_identitas['id_karyawan'];

            // --- STEP 2: Cek apakah akun sudah aktif di tb_karyawan ---
            $query_check_status = "SELECT status_karyawan FROM tb_karyawan WHERE id_karyawan = '$id_karyawan'";
            $result_check_status = mysqli_query($koneksi, $query_check_status);
            
            if (!$result_check_status || mysqli_num_rows($result_check_status) == 0) {
                 // Kasus jarang: NIK ada di identitas, tapi ID tidak ada di tb_karyawan
                 throw new Exception("Data karyawan tidak ditemukan untuk NIK ini.");
            }

            $data_karyawan = mysqli_fetch_assoc($result_check_status);
            
            if ($data_karyawan['status_karyawan'] == 1) {
                 throw new Exception("Akun untuk NIK ini sudah aktif.");
            }
            
            // --- STEP 3: UPDATE password dan status_karyawan di tb_karyawan ---
            $query_update = "UPDATE tb_karyawan 
                             SET password = '$hashed_password', status_karyawan = 1 
                             WHERE id_karyawan = '$id_karyawan'";

            if (!mysqli_query($koneksi, $query_update)) {
                throw new Exception("Gagal memperbarui data karyawan: " . mysqli_error($koneksi));
            }

            // Jika semua query sukses, commit transaksi
            mysqli_commit($koneksi);
            $response = ['status' => 'success', 'message' => 'Akun berhasil diaktifkan. Silakan login menggunakan NIK dan kata sandi baru Anda.'];

        } catch (Exception $e) {
            // Jika ada kesalahan, rollback transaksi
            mysqli_rollback($koneksi);
            // Tangani error dan kembalikan pesan ke Flutter
            $response = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

echo json_encode($response);
mysqli_close($koneksi);
?>