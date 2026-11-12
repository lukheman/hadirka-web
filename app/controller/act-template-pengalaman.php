<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/koneksi.php';
    require_once __DIR__ . '/../config/pengalaman_schema.php';

    ensure_pengalaman_schema($koneksi);

    $templateDirectory = __DIR__ . '/../uploads/template-pengalaman/';
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($action === 'list') {
        $statement = $koneksi->query(
            'SELECT * FROM tb_template_pengalaman
             ORDER BY id_template DESC
             LIMIT 1'
        );

        echo json_encode([
            'status' => 'success',
            'data' => $statement->fetchAll(),
        ]);
        exit;
    }

    if ($action === 'upload') {
        uploadTemplate($koneksi, $templateDirectory);
        echo json_encode([
            'status' => 'success',
            'message' => 'Template berhasil diunggah.',
        ]);
        exit;
    }

    if ($action === 'update') {
        $templateId = (int) ($_POST['id_template'] ?? 0);
        $templateName = trim((string) ($_POST['nama_template'] ?? ''));

        if ($templateId <= 0 || $templateName === '') {
            throw new RuntimeException('Data template tidak valid.');
        }

        $statement = $koneksi->prepare(
            'UPDATE tb_template_pengalaman
             SET nama_template = ?
             WHERE id_template = ?'
        );
        $statement->execute([$templateName, $templateId]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Template berhasil diperbarui.',
        ]);
        exit;
    }

    if ($action === 'delete') {
        $templateId = (int) ($_POST['id_template'] ?? 0);
        $statement = $koneksi->prepare(
            'SELECT file FROM tb_template_pengalaman WHERE id_template = ?'
        );
        $statement->execute([$templateId]);
        $template = $statement->fetch();

        if (!$template) {
            throw new RuntimeException('Template tidak ditemukan.');
        }

        $koneksi->prepare(
            'DELETE FROM tb_template_pengalaman WHERE id_template = ?'
        )->execute([$templateId]);

        @unlink($templateDirectory . basename((string) $template['file']));

        echo json_encode([
            'status' => 'success',
            'message' => 'Template berhasil dihapus.',
        ]);
        exit;
    }

    throw new RuntimeException('Aksi tidak dikenal.');
} catch (Throwable $error) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $error->getMessage(),
    ]);
}

function uploadTemplate(PDO $db, string $directory): void
{
    $file = $_FILES['file_template'] ?? null;
    $templateName = trim((string) ($_POST['nama_template'] ?? ''));

    if (!$file || $file['error'] !== UPLOAD_ERR_OK || $templateName === '') {
        throw new RuntimeException('Nama dan file template wajib diisi.');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['doc', 'docx'], true)) {
        throw new RuntimeException('Template harus berupa file Word (.doc/.docx).');
    }

    if ($file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Ukuran template maksimal 5 MB.');
    }

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder template tidak dapat dibuat.');
    }

    $storedFile = 'pengalaman_'
        . date('YmdHis')
        . '_'
        . bin2hex(random_bytes(4))
        . '.'
        . $extension;

    if (!move_uploaded_file($file['tmp_name'], $directory . $storedFile)) {
        throw new RuntimeException('Gagal menyimpan template.');
    }

    try {
        $oldTemplates = $db->query(
            'SELECT file FROM tb_template_pengalaman ORDER BY id_template DESC'
        )->fetchAll();

        $db->beginTransaction();
        $db->exec('DELETE FROM tb_template_pengalaman');

        $statement = $db->prepare(
            'INSERT INTO tb_template_pengalaman
                (nama_template, file, nama_asli, ukuran)
             VALUES (?, ?, ?, ?)'
        );
        $statement->execute([
            $templateName,
            $storedFile,
            basename($file['name']),
            (int) $file['size'],
        ]);
        $db->commit();

        foreach ($oldTemplates as $oldTemplate) {
            @unlink($directory . basename((string) $oldTemplate['file']));
        }
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        @unlink($directory . $storedFile);
        throw $error;
    }
}
