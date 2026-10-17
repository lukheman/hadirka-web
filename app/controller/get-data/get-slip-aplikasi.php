<?php
require_once __DIR__ . '/../../config/koneksi.php';

/**
 * Baris tabel HTML slip gaji aplikasi (tb_slip_gaji).
 * Filter: bulan (format YYYY-MM) & karyawan (id_karyawan).
 */

/**
 * Konversi YYYY-MM -> "Nama Bulan YYYY" (samakan dengan label periode aplikasi).
 */
function label_periode_slip($ym)
{
    if (!preg_match('/^\d{4}-\d{2}$/', (string)$ym)) {
        return '';
    }

    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    [$tahun, $bln] = explode('-', $ym);

    return ($bulan[(int)$bln] ?? '') . ' ' . $tahun;
}

$bulan = $_GET['bulan'] ?? '';
$karyawan = (int)($_GET['karyawan'] ?? 0);

$where = [];
$params = [];

$label = label_periode_slip($bulan);
if ($label !== '') {
    $where[] = "s.periode = ?";
    $params[] = $label;
}
if ($karyawan > 0) {
    $where[] = "s.id_karyawan = ?";
    $params[] = $karyawan;
}

$where_sql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT s.*, k.nama_lengkap, k.nik_karyawan
        FROM tb_slip_gaji s
        JOIN tb_karyawan k ON k.id_karyawan = s.id_karyawan
        $where_sql
        ORDER BY s.dibuat_pada DESC, s.id_slip_gaji DESC";

$stmt = $koneksi->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

if (count($rows) === 0) {
    echo '<tr><td colspan="9" class="text-center py-5 text-muted">Belum ada slip gaji yang diterbitkan untuk filter ini.</td></tr>';
    exit;
}

$no = 1;

foreach ($rows as $row) {
    $nama = htmlspecialchars((string)$row['nama_lengkap'], ENT_QUOTES);
    $nik = htmlspecialchars((string)$row['nik_karyawan'], ENT_QUOTES);
    $inisial = strtoupper(substr((string)$row['nama_lengkap'], 0, 1));
    $periode = htmlspecialchars((string)$row['periode'], ENT_QUOTES);
    $catatan = htmlspecialchars((string)($row['catatan'] ?? ''), ENT_QUOTES);

    echo '<tr>
        <td class="text-center">' . $no . '</td>
        <td class="text-nowrap">' . date('d/m/Y H:i', strtotime((string)$row['dibuat_pada'])) . '</td>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded-circle bg-label-primary">' . $inisial . '</span>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-dark">' . $nama . '</span>
                    <small class="text-muted">' . $nik . '</small>
                </div>
            </div>
        </td>
        <td>
            <span class="badge bg-label-info">' . $periode . '</span>'
            . ($catatan !== '' ? '<br><small class="text-muted">' . $catatan . '</small>' : '') . '
        </td>
        <td class="text-end">Rp ' . number_format((float)$row['gaji_pokok'], 0, ',', '.') . '</td>
        <td class="text-end">Rp ' . number_format((float)$row['tunjangan'], 0, ',', '.') . '</td>
        <td class="text-end text-danger">- Rp ' . number_format((float)$row['potongan'], 0, ',', '.') . '</td>
        <td class="text-end fw-bold text-success">Rp ' . number_format((float)$row['total_diterima'], 0, ',', '.') . '</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="hapusSlip(' . (int)$row['id_slip_gaji'] . ')" title="Hapus">
                <i class="bx bx-trash"></i>
            </button>
        </td>
    </tr>';

    $no++;
}
