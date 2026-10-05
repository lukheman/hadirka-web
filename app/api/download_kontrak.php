<?php
// File: download_kontrak.php

include __DIR__ . '/koneksi-api.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_karyawan = isset($_POST['id_karyawan']) ? $_POST['id_karyawan'] : '';
    $nik_verifikasi = isset($_POST['nik_verifikasi']) ? $_POST['nik_verifikasi'] : '';
    $password_verifikasi = isset($_POST['password_verifikasi']) ? $_POST['password_verifikasi'] : '';

    if (empty($id_karyawan) || empty($nik_verifikasi) || empty($password_verifikasi)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Data verifikasi tidak lengkap.']);
        exit;
    }

    // 1. VERIFIKASI NIK DAN PASSWORD
    $hashed_password = md5($password_verifikasi);
    
    try {
        $st_auth = $koneksi->prepare("
            SELECT k.id_karyawan 
            FROM tb_karyawan k
            INNER JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
            WHERE k.id_karyawan = ? AND i.nik_ktp = ? AND k.password = ?
        ");
        $st_auth->execute([$id_karyawan, $nik_verifikasi, $hashed_password]);
        $row_auth = $st_auth->fetch();

        if (!$row_auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Verifikasi Gagal: NIK KTP atau Kata Sandi salah.']);
            exit;
        }

        // 2. CARI FILE KONTRAK DI KARIER AKTIF
        $today = date('Y-m-d');
        $st_kontrak = $koneksi->prepare("
            SELECT file_kontrak 
            FROM tb_karier 
            WHERE id_karyawan = ? 
            AND tgl_mulai <= ?
            AND (tgl_selesai IS NULL OR tgl_selesai = '0000-00-00' OR tgl_selesai >= ?)
            ORDER BY tgl_mulai DESC 
            LIMIT 1
        ");
        $st_kontrak->execute([$id_karyawan, $today, $today]);
        $data_kontrak = $st_kontrak->fetch();
    } catch (PDOException $e) {
        error_log('download_kontrak gagal: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database.']);
        exit;
    }

    if (!$data_kontrak || empty($data_kontrak['file_kontrak'])) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Dokumen kontrak belum tersedia untuk jabatan Anda saat ini.']);
        exit;
    }

    // 3. GENERATE URL FILE
    $file_name = $data_kontrak['file_kontrak'];
    
    // Sesuaikan URL ini dengan struktur folder Anda (file kontrak hasil per karyawan ada di app/uploads/kontrak/,
    // sedangkan template master ada di app/uploads/template-kontrak/)
    // Gunakan host dinamis agar fleksibel
    $host = $_SERVER['HTTP_HOST'];
    $file_url = "http://" . $host . "/uploads/kontrak/" . $file_name;

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
?>
