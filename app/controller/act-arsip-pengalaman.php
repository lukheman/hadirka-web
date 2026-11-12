<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/koneksi.php';
    require_once __DIR__ . '/../config/pengalaman_schema.php';

    ensure_pengalaman_schema($koneksi);

    $uploadDirectory = __DIR__ . '/../uploads/arsip-pengalaman/';
    $action = $_POST['action'] ?? '';

    if ($action === 'list_all') {
        $statement = $koneksi->query(
            "SELECT
                a.id_arsip,
                a.id_karyawan,
                a.nama_karyawan,
                a.file,
                a.nama_asli,
                a.ukuran,
                a.keterangan,
                a.created_at,
                k.nama_lengkap,
                k.nik_karyawan
            FROM tb_arsip_pengalaman a
            LEFT JOIN tb_karyawan k ON k.id_karyawan = a.id_karyawan
            ORDER BY a.created_at DESC, a.id_arsip DESC"
        );

        echo json_encode([
            'status' => 'success',
            'data' => $statement->fetchAll(),
        ]);
        exit;
    }

    if ($action === 'list') {
        $employeeId = (int) ($_POST['id_karyawan'] ?? 0);
        $statement = $koneksi->prepare(
            'SELECT * FROM tb_arsip_pengalaman
             WHERE id_karyawan = ?
             ORDER BY created_at DESC, id_arsip DESC'
        );
        $statement->execute([$employeeId]);

        echo json_encode([
            'status' => 'success',
            'data' => $statement->fetchAll(),
        ]);
        exit;
    }

    if ($action === 'upload') {
        $employeeId = (int) ($_POST['id_karyawan'] ?? 0);
        $employeeName = trim((string) ($_POST['nama_karyawan'] ?? ''));
        $file = $_FILES['file_arsip'] ?? null;

        if ($employeeName === '') {
            throw new RuntimeException('Nama karyawan wajib diisi.');
        }

        validateArchiveUpload($file);

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $storedFile = createArchiveFilename($employeeName, $extension);

        ensureUploadDirectory($uploadDirectory);

        if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . $storedFile)) {
            throw new RuntimeException('Gagal menyimpan arsip.');
        }

        try {
            $statement = $koneksi->prepare(
                'INSERT INTO tb_arsip_pengalaman
                    (id_karyawan, nama_karyawan, file, nama_asli, ukuran, keterangan)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $statement->execute([
                $employeeId > 0 ? $employeeId : null,
                $employeeName,
                $storedFile,
                basename($file['name']),
                (int) $file['size'],
                cleanDescription($_POST['keterangan'] ?? null),
            ]);
        } catch (Throwable $error) {
            @unlink($uploadDirectory . $storedFile);
            throw $error;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Arsip berhasil diunggah.',
        ]);
        exit;
    }

    if ($action === 'update') {
        updateArchive($koneksi, $uploadDirectory);
        echo json_encode([
            'status' => 'success',
            'message' => 'Arsip berhasil diperbarui.',
        ]);
        exit;
    }

    if ($action === 'delete') {
        $archiveId = (int) ($_POST['id_arsip'] ?? 0);
        $statement = $koneksi->prepare(
            'SELECT file FROM tb_arsip_pengalaman WHERE id_arsip = ?'
        );
        $statement->execute([$archiveId]);
        $archive = $statement->fetch();

        if (!$archive) {
            throw new RuntimeException('Arsip tidak ditemukan.');
        }

        $koneksi->prepare(
            'DELETE FROM tb_arsip_pengalaman WHERE id_arsip = ?'
        )->execute([$archiveId]);

        @unlink($uploadDirectory . basename((string) $archive['file']));

        echo json_encode([
            'status' => 'success',
            'message' => 'Arsip dihapus.',
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

function validateArchiveUpload(?array $file): void
{
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Pilih file arsip.');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['pdf', 'doc', 'docx'], true)) {
        throw new RuntimeException('Arsip harus PDF atau Word.');
    }

    if ($file['size'] <= 0 || $file['size'] > 10 * 1024 * 1024) {
        throw new RuntimeException('Ukuran arsip maksimal 10 MB.');
    }
}

function ensureUploadDirectory(string $directory): void
{
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Folder arsip tidak dapat dibuat.');
    }
}

function createArchiveFilename(string $employeeName, string $extension): string
{
    $safeName = preg_replace(
        '#[^A-Za-z0-9_-]+#',
        '-',
        strtoupper($employeeName)
    );
    $safeName = trim((string) $safeName, '-') ?: 'KARYAWAN';

    return 'ARSIP SURAT PENGALAMAN KERJA - '
        . $safeName
        . ' - '
        . date('YmdHis')
        . '_'
        . bin2hex(random_bytes(4))
        . '.'
        . $extension;
}

function cleanDescription($description): ?string
{
    $description = trim((string) $description);
    return $description !== '' ? $description : null;
}

function updateArchive(PDO $db, string $uploadDirectory): void
{
    $archiveId = (int) ($_POST['id_arsip'] ?? 0);
    $statement = $db->prepare(
        'SELECT file FROM tb_arsip_pengalaman WHERE id_arsip = ?'
    );
    $statement->execute([$archiveId]);
    $oldArchive = $statement->fetch();

    if (!$oldArchive) {
        throw new RuntimeException('Arsip tidak ditemukan.');
    }

    $employeeName = trim((string) ($_POST['nama_karyawan'] ?? ''));
    if ($employeeName === '') {
        throw new RuntimeException('Nama karyawan wajib diisi.');
    }

    $replacement = $_FILES['file_arsip'] ?? null;
    if (!$replacement || $replacement['error'] === UPLOAD_ERR_NO_FILE) {
        $statement = $db->prepare(
            'UPDATE tb_arsip_pengalaman
             SET nama_karyawan = ?, keterangan = ?
             WHERE id_arsip = ?'
        );
        $statement->execute([
            $employeeName,
            cleanDescription($_POST['keterangan'] ?? null),
            $archiveId,
        ]);
        return;
    }

    validateArchiveUpload($replacement);
    ensureUploadDirectory($uploadDirectory);

    $extension = strtolower(pathinfo($replacement['name'], PATHINFO_EXTENSION));
    $newFile = createArchiveFilename($employeeName, $extension);

    if (!move_uploaded_file($replacement['tmp_name'], $uploadDirectory . $newFile)) {
        throw new RuntimeException('Gagal menyimpan file pengganti.');
    }

    try {
        $statement = $db->prepare(
            'UPDATE tb_arsip_pengalaman
             SET nama_karyawan = ?, file = ?, nama_asli = ?, ukuran = ?, keterangan = ?
             WHERE id_arsip = ?'
        );
        $statement->execute([
            $employeeName,
            $newFile,
            basename($replacement['name']),
            (int) $replacement['size'],
            cleanDescription($_POST['keterangan'] ?? null),
            $archiveId,
        ]);
    } catch (Throwable $error) {
        @unlink($uploadDirectory . $newFile);
        throw $error;
    }

    @unlink($uploadDirectory . basename((string) $oldArchive['file']));
}
