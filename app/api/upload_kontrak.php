<?php
// File: upload_kontrak.php

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

        // 2. CEK APAKAH ADA KARIER AKTIF
        $today = date('Y-m-d');
        $st_karier = $koneksi->prepare("
            SELECT id_karier, file_kontrak 
            FROM tb_karier 
            WHERE id_karyawan = ? 
            AND tgl_mulai <= ?
            AND (tgl_selesai IS NULL OR tgl_selesai = '0000-00-00' OR tgl_selesai >= ?)
            ORDER BY tgl_mulai DESC 
            LIMIT 1
        ");
        $st_karier->execute([$id_karyawan, $today, $today]);
        $data_karier = $st_karier->fetch();
    } catch (PDOException $e) {
        error_log('upload_kontrak verifikasi gagal: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database.']);
        exit;
    }

    if (!$data_karier) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Tidak ditemukan jabatan aktif untuk mengunggah kontrak.']);
        exit;
    }

    $id_karier = $data_karier['id_karier'];
    $old_file = $data_karier['file_kontrak'];

    // 3. PROSES UPLOAD FILE
    if (isset($_FILES['file_kontrak']) && $_FILES['file_kontrak']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_kontrak']['tmp_name'];
        $file_name_ori = $_FILES['file_kontrak']['name'];
        $file_ext = strtolower(pathinfo($file_name_ori, PATHINFO_EXTENSION));
        
        // Validasi ekstensi (hanya perbolehkan pdf, jpg, jpeg, png)
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($file_ext, $allowed_ext)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Format file tidak diizinkan. Gunakan PDF atau Gambar.']);
            exit;
        }

        // Generate nama file baru: kontrak_IDKARYAWAN_TIMESTAMP.ext
        $new_file_name = "kontrak_" . $id_karyawan . "_" . time() . "." . $file_ext;
        
        // Path direktori upload (Sesuaikan dengan struktur folder Anda, sama seperti di download_kontrak.php)
        // Pastikan folder ini memiliki permission write (755 atau 777 jika perlu)
        $upload_dir = __DIR__ . '/../uploads/kontrak/'; 
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $upload_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $upload_path)) {
            
            // Hapus file lama jika ada agar server tidak penuh
            if (!empty($old_file) && file_exists($upload_dir . $old_file)) {
                unlink($upload_dir . $old_file);
            }

            // 4. UPDATE DATABASE
            try {
                $st_update = $koneksi->prepare("UPDATE tb_karier SET file_kontrak = ? WHERE id_karier = ?");
                $st_update->execute([$new_file_name, $id_karier]);
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Dokumen kontrak berhasil diunggah dan disimpan.'
                ]);
            } catch (PDOException $e) {
                error_log('upload_kontrak update gagal: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui database.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file di server.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada file yang diunggah atau terjadi kesalahan upload.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
}
?>
