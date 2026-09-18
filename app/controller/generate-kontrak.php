<?php
// Generate surat kontrak (.docx) dari template + preview PDF.
// GET: ?id_karier=N&action=download|preview
//   - download : unduh .docx hasil merge.
//   - preview  : tampilkan PDF hasil konversi LibreOffice (di-cache, dibuat ulang bila docx lebih baru).

error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/kontrak_helper.php';

function kontrak_fail($msg, $code = 500)
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg;
    exit;
}

$id_karier = (int)($_GET['id_karier'] ?? 0);
$action = $_GET['action'] ?? 'download';
if ($id_karier <= 0) kontrak_fail('ID karier tidak valid.', 400);
if (!in_array($action, ['download', 'preview'], true)) kontrak_fail('Aksi tidak dikenal.', 400);

try {
    $built = build_kontrak_docx($koneksi, $id_karier);
} catch (Throwable $e) {
    kontrak_fail('Gagal membuat kontrak: ' . $e->getMessage());
}

$docx = $built['docx'];

if ($action === 'download') {
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $built['file'] . '"');
    header('Content-Length: ' . filesize($docx));
    readfile($docx);
    exit;
}

// --- preview PDF (konversi bila belum ada / kedaluwarsa) ---
$pdf = preg_replace('/\.docx$/i', '.pdf', $docx);
if (!is_file($pdf) || filemtime($pdf) < filemtime($docx)) {
    $home = '/tmp/opencode/soffice';
    if (!is_dir($home)) mkdir($home, 0700, true);
    $cmd = 'HOME=' . escapeshellarg($home)
        . ' timeout 90 soffice --headless --convert-to pdf --outdir ' . escapeshellarg(dirname($docx))
        . ' ' . escapeshellarg($docx) . ' 2>&1';
    exec($cmd, $out, $rc);
    if (!is_file($pdf) || filemtime($pdf) < filemtime($docx)) {
        kontrak_fail('Gagal mengonversi preview PDF. Silakan unduh versi Word.');
    }
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($pdf) . '"');
header('Content-Length: ' . filesize($pdf));
readfile($pdf);
exit;
