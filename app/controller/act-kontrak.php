<?php
// Upload / hapus file kontrak per karier (tb_karier.file_kontrak).
// Dipakai dari modal upload kontrak di halaman karier (web admin).
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$response = ["status" => "error", "message" => "Aksi tidak dikenal"];

try {
    require_once __DIR__ . '/../config/koneksi.php';

    if (!isset($koneksi) || !$koneksi) {
        throw new Exception("Koneksi database gagal.");
    }

    $action = $_POST['action'] ?? '';
    $upload_dir = __DIR__ . '/../uploads/kontrak/';
    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($action === "upload") {
        $id_karier = (int)($_POST['id_karier'] ?? 0);
        if ($id_karier <= 0) {
            throw new Exception("ID karier tidak valid.");
        }

        if (!isset($_FILES['file_kontrak']) || $_FILES['file_kontrak']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Tidak ada file yang diunggah.");
        }

        $file = $_FILES['file_kontrak'];
        if ($file['size'] > $max_size) {
            throw new Exception("Ukuran file maksimal 5MB.");
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext, true)) {
            throw new Exception("Format file tidak diizinkan. Gunakan PDF, JPG, atau PNG.");
        }

        $stmt = $koneksi->prepare("SELECT id_karyawan, file_kontrak FROM tb_karier WHERE id_karier = ?");
        $stmt->bind_param("i", $id_karier);
        $stmt->execute();
        $kar = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$kar) {
            throw new Exception("Data karier tidak ditemukan.");
        }

        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true)) {
            throw new Exception("Gagal menyiapkan folder upload.");
        }

        $new_name = "kontrak_" . (int)$kar['id_karyawan'] . "_" . time() . "." . $ext;
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
            throw new Exception("Gagal menyimpan file di server.");
        }

        // Hapus file lama agar server tidak penuh.
        if (!empty($kar['file_kontrak'])) {
            $old_path = $upload_dir . basename($kar['file_kontrak']);
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        $upd = $koneksi->prepare("UPDATE tb_karier SET file_kontrak = ? WHERE id_karier = ?");
        $upd->bind_param("si", $new_name, $id_karier);
        if (!$upd->execute()) {
            @unlink($upload_dir . $new_name);
            $upd->close();
            throw new Exception("Gagal memperbarui database.");
        }
        $upd->close();

        $response = ["status" => "success", "message" => "File kontrak berhasil diunggah.", "file" => $new_name];
    } elseif ($action === "delete") {
        $id_karier = (int)($_POST['id_karier'] ?? 0);
        if ($id_karier <= 0) {
            throw new Exception("ID karier tidak valid.");
        }

        $stmt = $koneksi->prepare("SELECT file_kontrak FROM tb_karier WHERE id_karier = ?");
        $stmt->bind_param("i", $id_karier);
        $stmt->execute();
        $kar = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$kar) {
            throw new Exception("Data karier tidak ditemukan.");
        }

        if (!empty($kar['file_kontrak'])) {
            $old_path = $upload_dir . basename($kar['file_kontrak']);
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        $upd = $koneksi->prepare("UPDATE tb_karier SET file_kontrak = NULL WHERE id_karier = ?");
        $upd->bind_param("i", $id_karier);
        if (!$upd->execute()) {
            $upd->close();
            throw new Exception("Gagal memperbarui database.");
        }
        $upd->close();

        $response = ["status" => "success", "message" => "File kontrak berhasil dihapus."];
    }
} catch (Exception $e) {
    $response = ["status" => "error", "message" => $e->getMessage()];
}

echo json_encode($response);
exit;
