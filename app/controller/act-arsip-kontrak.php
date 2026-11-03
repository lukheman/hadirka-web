<?php
declare(strict_types=1);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/koneksi.php';
    require_once __DIR__ . '/../config/kontrak_schema.php';
    ensure_kontrak_schema($koneksi);

    $action = $_POST['action'] ?? '';
    $dir = __DIR__ . '/../uploads/arsip-kontrak/';
    if ($action === 'upload') {
        $id = (int)($_POST['id_karyawan'] ?? 0);
        $file = $_FILES['file_kontrak'] ?? null;
        if ($id <= 0 || !$file || $file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Pilih file PDF terlebih dahulu.');
        if ($file['size'] <= 0 || $file['size'] > 10 * 1024 * 1024) throw new RuntimeException('Ukuran file harus 1 byte sampai 10MB.');
        if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') throw new RuntimeException('Format file harus PDF.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, ['application/pdf', 'application/x-pdf'], true)) throw new RuntimeException('File tidak terdeteksi sebagai PDF yang valid.');
        $check = $koneksi->prepare('SELECT 1 FROM tb_karyawan WHERE id_karyawan = ?');
        $check->execute([$id]);
        if (!$check->fetchColumn()) throw new RuntimeException('Data karyawan tidak ditemukan.');
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) throw new RuntimeException('Gagal menyiapkan folder upload.');
        $name = 'arsip_' . $id . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.pdf';
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) throw new RuntimeException('Gagal menyimpan file di server.');
        try {
            $st = $koneksi->prepare('INSERT INTO tb_arsip_kontrak (id_karyawan,file,nama_asli,ukuran,keterangan) VALUES (?,?,?,?,?)');
            $st->execute([$id, $name, substr(basename((string)$file['name']), 0, 255), (int)$file['size'], substr(trim((string)($_POST['keterangan'] ?? '')), 0, 255) ?: null]);
        } catch (Throwable $e) { @unlink($dir . $name); throw $e; }
        echo json_encode(['status' => 'success', 'message' => 'Dokumen kontrak berhasil diunggah.', 'id_arsip' => (int)$koneksi->lastInsertId(), 'file' => $name]);
        exit;
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id_arsip'] ?? 0);
        $st = $koneksi->prepare('SELECT file FROM tb_arsip_kontrak WHERE id_arsip = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) throw new RuntimeException('Data arsip tidak ditemukan.');
        $koneksi->prepare('DELETE FROM tb_arsip_kontrak WHERE id_arsip = ?')->execute([$id]);
        @unlink($dir . basename((string)$row['file']));
        echo json_encode(['status' => 'success', 'message' => 'Arsip kontrak berhasil dihapus.']);
        exit;
    }
    throw new RuntimeException('Aksi tidak dikenal.');
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
