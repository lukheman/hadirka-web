<?php
// ============================================
// DEBUGGING MODE & HEADERS
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$log_file = __DIR__ . '/debug_izin.log';
file_put_contents($log_file, "\n\n=== " . date('Y-m-d H:i:s') . " ===\nPOST: " . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true), FILE_APPEND);

function sendResponse($status, $message, $data = null, $code = 200) {
    global $log_file;
    $response = ['status' => $status, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    file_put_contents($log_file, "RESPONSE: " . json_encode($response) . "\n", FILE_APPEND);
    http_response_code($code);
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Hanya menerima POST request', null, 405);
}

// KONEKSI DATABASE
require_once __DIR__ . '/koneksi-api.php';
if (mysqli_connect_errno()) {
    sendResponse('error', 'Database error: ' . mysqli_connect_error(), null, 500);
}

// ============================================
// FUNGSI UPLOAD BUKTI IZIN
// ============================================
function uploadBuktiIzin($file_input_name, $nik, $nama, $tgl_format) {
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] === UPLOAD_ERR_NO_FILE) {
        sendResponse('error', 'Bukti izin wajib diupload. Harap lampirkan foto/dokumen pendukung.', null, 400);
    }

    $file_tmp = $_FILES[$file_input_name]['tmp_name'];
    $file_name_original = $_FILES[$file_input_name]['name'];
    $file_size = $_FILES[$file_input_name]['size'];
    
    // Validasi ekstensi
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $file_ext = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        sendResponse('error', "Format file tidak didukung: $file_ext. Gunakan JPG/PNG/WEBP/PDF.", null, 400);
    }
    if ($file_size > 5 * 1024 * 1024) {
        sendResponse('error', "Ukuran file melebihi 5MB.", null, 400);
    }
    
    $upload_dir = dirname(__DIR__) . '/uploads/izin/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
    
    // 👇 FORMAT NAMA FILE BARU 👇
    $file_name = 'izin_' . $nik . '_' . $nama . '_' . $tgl_format . '_' . time() . '.' . $file_ext;
    // 👆 ======================= 👆

    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($file_tmp, $target_path)) {
        return 'uploads/izin/' . $file_name;
    } else {
        sendResponse('error', "Gagal menyimpan file bukti izin ke server.", null, 500);
    }
}

// ============================================
// ROUTING BERDASARKAN ACTION
// ============================================
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

switch ($action) {
    
    // ---------------------------------------------------------
    // AKSI 1: AJUKAN IZIN BARU
    // ---------------------------------------------------------
    case 'ajukan':
        $id_karyawan = isset($_POST['id_karyawan']) ? trim($_POST['id_karyawan']) : '';
        $tgl_izin    = isset($_POST['tgl_izin']) ? trim($_POST['tgl_izin']) : '';
        $alasan_izin = isset($_POST['alasan_izin']) ? trim($_POST['alasan_izin']) : '';

        if (empty($id_karyawan) || empty($tgl_izin) || empty($alasan_izin)) {
            sendResponse('error', 'Semua form wajib diisi', null, 400);
        }

        $id_karyawan_safe = mysqli_real_escape_string($koneksi, $id_karyawan);
        $tgl_izin_safe    = mysqli_real_escape_string($koneksi, $tgl_izin);
        $alasan_izin_safe = mysqli_real_escape_string($koneksi, $alasan_izin);

        // 👇 TAMBAHKAN KODE INI UNTUK AMBIL NIK & NAMA 👇
        $nik_karyawan = 'UNKNOWN';
        $nama_karyawan = 'UNKNOWN';
        $query_kar = mysqli_query($koneksi, "SELECT nik_karyawan, nama_lengkap FROM tb_karyawan WHERE id_karyawan = '$id_karyawan_safe'");
        if ($row_kar = mysqli_fetch_assoc($query_kar)) {
            $nik_karyawan = $row_kar['nik_karyawan'];
            // Hapus spasi pada nama (Budi Santoso -> BudiSantoso) agar link gambar tidak rusak
            $nama_karyawan = str_replace(' ', '', $row_kar['nama_lengkap']); 
        }
        
        // Ubah format tanggal 2026-04-11 menjadi 20260411
        $tgl_format_file = date('Ymd', strtotime($tgl_izin_safe));
        // 👆 ========================================== 👆

        // Cek Duplikat
        $check_sql = "SELECT id_pengajuan_izin FROM tb_pengajuan_izin WHERE id_karyawan = '$id_karyawan_safe' AND DATE(tgl_izin) = '$tgl_izin_safe'";
        if (mysqli_num_rows(mysqli_query($koneksi, $check_sql)) > 0) {
            sendResponse('error', 'Anda sudah mengajukan izin pada tanggal ' . $tgl_izin_safe, null, 400);
        }

        // 👇 UBAH PEMANGGILAN FUNGSI UPLOAD 👇
        $bukti_izin = uploadBuktiIzin('bukti_izin', $nik_karyawan, $nama_karyawan, $tgl_format_file);
        // 👆 =============================== 👆

        // Insert
        $waktu_saat_ini = date('H:i:s');
        $tgl_izin_timestamp = $tgl_izin_safe . ' ' . $waktu_saat_ini;
        
        $sql = "INSERT INTO tb_pengajuan_izin (id_karyawan, tgl_izin, alasan_izin, bukti_izin, status_izin)
                VALUES ('$id_karyawan_safe', '$tgl_izin_timestamp', '$alasan_izin_safe', '$bukti_izin', 'pending')";

        if (mysqli_query($koneksi, $sql)) {
            sendResponse('success', 'Pengajuan izin berhasil disubmit!');
        } else {
            sendResponse('error', 'Gagal insert database: ' . mysqli_error($koneksi), null, 500);
        }
        break;

    // ---------------------------------------------------------
    // AKSI 2: BATALKAN / HAPUS PENGAJUAN IZIN
    // ---------------------------------------------------------
    case 'batalkan':
        $id_pengajuan = mysqli_real_escape_string($koneksi, $_POST['id_pengajuan'] ?? '');
        if (empty($id_pengajuan)) sendResponse('error', 'ID Pengajuan tidak ditemukan', null, 400);

        $cek_sql = "SELECT status_izin, bukti_izin FROM tb_pengajuan_izin WHERE id_pengajuan_izin = '$id_pengajuan'";
        $cek_result = mysqli_query($koneksi, $cek_sql);

        if ($row = mysqli_fetch_assoc($cek_result)) {
            if ($row['status_izin'] !== 'pending') {
                sendResponse('error', 'Gagal dibatalkan. Pengajuan sudah diproses oleh Admin.', null, 400);
            }

            // Hapus file fisik
            $base_dir = dirname(__DIR__) . '/';
            if (!empty($row['bukti_izin']) && file_exists($base_dir . $row['bukti_izin'])) {
                unlink($base_dir . $row['bukti_izin']);
            }

            // Hapus dari database
            if (mysqli_query($koneksi, "DELETE FROM tb_pengajuan_izin WHERE id_pengajuan_izin = '$id_pengajuan'")) {
                sendResponse('success', 'Pengajuan izin berhasil dibatalkan dan dihapus.');
            } else {
                sendResponse('error', 'Gagal menghapus data dari database.', null, 500);
            }
        } else {
            sendResponse('error', 'Data pengajuan tidak ditemukan.', null, 404);
        }
        break;

    // ---------------------------------------------------------
    // DEFAULT
    // ---------------------------------------------------------
    default:
        sendResponse('error', 'Action tidak valid atau tidak ditemukan', null, 400);
        break;
}

mysqli_close($koneksi);
?>