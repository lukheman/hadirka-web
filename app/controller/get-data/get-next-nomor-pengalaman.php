<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/pengalaman_schema.php';

ensure_pengalaman_schema($koneksi);

$lastNumber = (int) $koneksi
    ->query('SELECT terakhir FROM tb_nomor_surat_pengalaman WHERE id = 1')
    ->fetchColumn();

$nextNumber = $lastNumber + 1;
$year = (int) ($_GET['tahun'] ?? date('Y'));
$month = romanMonth((int) date('n'));

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'nomor' => sprintf('%03d/SPK/PT-IPM/%s/%d', $nextNumber, $month, $year),
]);

function romanMonth(int $month): string
{
    $months = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
        5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
        9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    return $months[$month] ?? 'I';
}
