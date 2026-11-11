<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    http_response_code(403);
    exit('Akses ditolak.');
}

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/pengalaman_schema.php';

ensure_pengalaman_schema($koneksi);

$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$manualMode = !empty($input['manual']);
$employeeId = (int) ($input['id_karyawan'] ?? 0);
$templateId = (int) ($input['id_template'] ?? 0);
$year = (int) ($input['tahun_surat'] ?? date('Y'));
$manualNumber = trim((string) ($input['manual_nomor_surat'] ?? ''));

if ($year < 2000 || $year > 2100) {
    exit('Tahun surat tidak valid.');
}

if (strlen($manualNumber) > 100) {
    exit('Nomor surat maksimal 100 karakter.');
}

$manualNumber = formatManualNumber($manualNumber, $year);
$template = findTemplate($koneksi, $templateId);
$templateFile = __DIR__ . '/../../uploads/template-pengalaman/'
    . basename((string) $template['file']);

if (!is_file($templateFile)) {
    exit('File template tidak ditemukan.');
}

$employee = $manualMode ? [] : findEliminatedEmployee($koneksi, $employeeId);
$posted = static function (string $key, string $fallback = '') use ($input): string {
    $value = trim((string) ($input[$key] ?? ''));
    return $value !== '' ? $value : $fallback;
};

$manualValues = [
    'nama' => $posted('data_nama'),
    'tanggal_lahir' => $posted('data_tanggal_lahir'),
    'nomor_id_karyawan' => $posted('data_nomor_id'),
    'jabatan_terakhir' => $posted('data_jabatan'),
    'alasan_berhenti' => $posted('data_alasan'),
];

$number = null;
if ($manualNumber === '') {
    $number = getNextNumber($koneksi);
}

$months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];
$startDate = $posted(
    'data_periode_mulai',
    $manualMode ? '' : ($employee['tgl_mulai'] ?? '')
);
$endDate = $posted(
    'data_periode_selesai',
    $manualMode ? '' : ($employee['tgl_selesai'] ?? ($employee['tgl_keluar'] ?? ''))
);

$values = [
    'nomor_surat' => $manualNumber !== ''
        ? $manualNumber
        : sprintf('%03d/SPK/PT-IPM/%s/%d', $number, romanMonth((int) date('n')), $year),
    'nama' => $manualValues['nama'] ?: ($manualMode ? '' : ($employee['nama_lengkap'] ?? '')),
    'tanggal_lahir' => $manualValues['tanggal_lahir']
        ?: ($manualMode ? '' : formatDate($employee['tgl_lahir'] ?? '', $months)),
    'nomor_id_karyawan' => $manualValues['nomor_id_karyawan']
        ?: ($manualMode ? '' : ($employee['nik_karyawan'] ?? '')),
    'jabatan_terakhir' => $manualValues['jabatan_terakhir']
        ?: ($manualMode ? '' : ($employee['nama_jabatan'] ?? '')),
    'periode_mulai' => formatDate($startDate, $months),
    'periode_selesai' => formatDate($endDate, $months),
    'alasan_berhenti' => $manualValues['alasan_berhenti']
        ?: ($manualMode ? '' : (($employee['alasan_keluar'] ?? '') ?: ($employee['status_karyawan'] ?? ''))),
    'tanggal_tanda_tangan' => formatDate(date('Y-m-d'), $months),
];

$values = array_map(
    static fn ($value): string => trim((string) $value) ?: '-',
    $values
);

$outputFile = tempnam(sys_get_temp_dir(), 'spk_') . '.docx';
copy($templateFile, $outputFile);

$zip = new ZipArchive();
if ($zip->open($outputFile) !== true) {
    exit('Template Word tidak valid.');
}

for ($index = 0; $index < $zip->numFiles; $index++) {
    $entryName = $zip->getNameIndex($index);
    if (!preg_match('#^word/(document|header\d*|footer\d*)\.xml$#', (string) $entryName)) {
        continue;
    }

    $xml = $zip->getFromIndex($index);
    foreach ($values as $key => $value) {
        $xml = str_replace(
            '${' . $key . '}',
            htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8'),
            $xml
        );
    }
    $zip->addFromString($entryName, $xml);
}

$zip->close();

$safeName = preg_replace(
    '#[\\/:*?"<>|]+#',
    '-',
    trim((string) ($values['nama'] ?? ''))
);
$safeName = trim((string) $safeName, ' .-') ?: 'Karyawan';
$downloadName = 'SURAT PENGALAMAN KERJA - ' . $safeName . '.docx';

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($outputFile));
readfile($outputFile);
@unlink($outputFile);

function findTemplate(PDO $db, int $templateId): array
{
    $statement = $templateId > 0
        ? $db->prepare('SELECT * FROM tb_template_pengalaman WHERE id_template = ?')
        : $db->prepare('SELECT * FROM tb_template_pengalaman ORDER BY id_template DESC LIMIT 1');

    $statement->execute($templateId > 0 ? [$templateId] : []);
    $template = $statement->fetch();

    if (!$template) {
        exit('Template tidak ditemukan.');
    }

    return $template;
}

function findEliminatedEmployee(PDO $db, int $employeeId): array
{
    $statement = $db->prepare(
        "SELECT
            k.*,
            t.tgl_keluar,
            t.alasan_keluar,
            j.nama_jabatan,
            kr.tgl_mulai,
            kr.tgl_selesai
        FROM tb_karyawan k
        JOIN tb_tereliminasi t ON t.id_karyawan = k.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karier = (
            SELECT x.id_karier
            FROM tb_karier x
            WHERE x.id_karyawan = k.id_karyawan
            ORDER BY x.tgl_mulai DESC, x.id_karier DESC
            LIMIT 1
        )
        LEFT JOIN tb_jabatan j ON j.id_jabatan = kr.id_jabatan
        WHERE k.id_karyawan = ?
        ORDER BY t.id_tereliminasi DESC
        LIMIT 1"
    );
    $statement->execute([$employeeId]);
    $employee = $statement->fetch();

    if (!$employee) {
        exit('Data karyawan tereliminasi tidak ditemukan.');
    }

    return $employee;
}

function getNextNumber(PDO $db): int
{
    $db->beginTransaction();
    try {
        $last = (int) $db
            ->query('SELECT terakhir FROM tb_nomor_surat_pengalaman WHERE id = 1 FOR UPDATE')
            ->fetchColumn();
        $number = $last + 1;

        $db->prepare(
            'UPDATE tb_nomor_surat_pengalaman SET terakhir = ? WHERE id = 1'
        )->execute([$number]);
        $db->commit();

        return $number;
    } catch (Throwable $error) {
        $db->rollBack();
        exit('Gagal membuat nomor surat.');
    }
}

function formatManualNumber(string $number, int $year): string
{
    if (!preg_match('/^\d+$/', $number)) {
        return $number;
    }

    return sprintf('%03d/SPK/PT-IPM/%s/%d', (int) $number, romanMonth((int) date('n')), $year);
}

function romanMonth(int $month): string
{
    $months = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
        5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
        9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    return $months[$month] ?? 'I';
}

function formatDate($value, array $months): string
{
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', (string) $value, $match)) {
        return '-';
    }

    return (int) $match[3]
        . ' '
        . ($months[(int) $match[2] - 1] ?? $match[2])
        . ' '
        . $match[1];
}
