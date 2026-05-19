<?php
// File: download_kontrak.php

include 'koneksi-api.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_karyawan = isset($_POST['id_karyawan']) ? mysqli_real_escape_string($koneksi, $_POST['id_karyawan']) : '';
    $nik_verifikasi = isset($_POST['nik_verifikasi']) ? mysqli_real_escape_string($koneksi, $_POST['nik_verifikasi']) : '';
    $password_verifikasi = isset($_POST['password_verifikasi']) ? mysqli_real_escape_string($koneksi, $_POST['password_verifikasi']) : '';

    if (empty($id_karyawan) || empty($nik_verifikasi) || empty($password_verifikasi)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Data verifikasi tidak lengkap.']);
        exit;
    }

    // 1. VERIFIKASI NIK DAN PASSWORD
    $hashed_password = md5($password_verifikasi);
    
    $query_auth = "
        SELECT k.id_karyawan 
        FROM tb_karyawan k
        INNER JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
        WHERE k.id_karyawan = '$id_karyawan' AND i.nik_ktp = '$nik_verifikasi' AND k.password = '$hashed_password'
    ";
    
    $result_auth = mysqli_query($koneksi, $query_auth);

    if (mysqli_num_rows($result_auth) == 0) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Verifikasi Gagal: NIK KTP atau Kata Sandi salah.']);
        exit;
    }

    // 2. CARI FILE KONTRAK DI KARIER AKTIF
    $today = date('Y-m-d');
    $query_kontrak = "
        SELECT file_kontrak 
        FROM tb_karier 
        WHERE id_karyawan = '$id_karyawan' 
        AND tgl_mulai <= '$today'
        AND (tgl_selesai IS NULL OR tgl_selesai = '0000-00-00' OR tgl_selesai >= '$today')
        ORDER BY tgl_mulai DESC 
        LIMIT 1
    ";

    $result_kontrak = mysqli_query($koneksi, $query_kontrak);
    $data_kontrak = mysqli_fetch_assoc($result_kontrak);

    if (!$data_kontrak || empty($data_kontrak['file_kontrak'])) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Dokumen kontrak belum tersedia untuk jabatan Anda saat ini.']);
        exit;
    }

    // 3. GENERATE URL FILE
    $file_name = $data_kontrak['file_kontrak'];
    
    // Sesuaikan URL ini dengan struktur folder Anda (berdasarkan file data-kontrak.php Anda, file ada di app/uploads/kontrak/)
    // Gunakan host dinamis agar fleksibel
    $host = $_SERVER['HTTP_HOST'];
    $file_url = "http://" . $host . "/app/uploads/kontrak/" . $file_name;

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Dokumen ditemukan.',
        'file_url' => $file_url
    ]);

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
}

mysqli_close($koneksi);
?>