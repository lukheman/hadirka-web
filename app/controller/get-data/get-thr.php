<?php

/**
 * ============================================
 * GET THR DATA - DISPLAY TABLE
 * ============================================
 */

require '../../config/koneksi.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

function formatRupiah($angka)
{
    return number_format($angka, 0, ',', '.');
}
function escapeHtml($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// 1. JIKA KONEKSI GAGAL -> JANGAN CETAK APAPUN
if (!isset($koneksi) || !$koneksi) {
    echo "";
    exit;
}

// 1. Tangkap parameter periode (jika ada)
$periode = isset($_GET['periode']) ? trim($_GET['periode']) : '';
$whereClause = "";

// 2. Jika filter tahun dipilih, tambahkan WHERE clause
if (!empty($periode)) {
    // Pastikan input aman (casting ke integer karena format tahun)
    $periode = intval($periode);
    $whereClause = " WHERE r.periode = '$periode' ";
}

// 3. Sisipkan $whereClause ke dalam Query
$sql = "SELECT 
            r.*, k.nik_karyawan, k.nama_lengkap
        FROM tb_thr r
        INNER JOIN tb_karyawan k ON r.id_karyawan = k.id_karyawan
        $whereClause
        ORDER BY r.periode DESC, r.id_thr DESC";

$result = $koneksi->query($sql);

// 2. JIKA QUERY ERROR ATAU DATA KOSONG -> JANGAN CETAK APAPUN
if (!$result || $result->num_rows == 0) {
    echo "";
    exit;
}

// 3. JIKA ADA DATA -> CETAK PERSIS 6 KOLOM
$no = 1;
while ($row = $result->fetch_assoc()) {
    $id_thr = intval($row['id_thr']);
    $id_karyawan = intval($row['id_karyawan']);
    $nik_karyawan = escapeHtml($row['nik_karyawan']);
    $nama_lengkap = escapeHtml($row['nama_lengkap']);
    $periode = escapeHtml($row['periode']);
    $masa_kerja = escapeHtml($row['masa_kerja']);

    $nominal_thr = floatval($row['nominal_thr']);
    $nominal_format = formatRupiah($nominal_thr);

    $initial = strtoupper(substr($nama_lengkap, 0, 1));

    echo "<tr>
            <td class='text-center fw-bold'>{$no}</td>
            <td>
                <div class='d-flex align-items-center'>
                    <div class='avatar avatar-sm bg-label-success rounded-circle me-2'>
                        <span class='fw-bold'>{$initial}</span>
                    </div>
                    <div>
                        <div class='fw-bold'>{$nama_lengkap}</div>
                        <small class='text-muted'>NIK: {$nik_karyawan}</small>
                    </div>
                </div>
            </td>
            <td class='text-center'>
                <span class='badge bg-label-primary'>Tahun {$periode}</span>
            </td>
            <td class='text-center'>
                <span class='fw-bold'>{$masa_kerja} Bulan</span>
            </td>
            <td class='text-end'>
                <span class='fw-bold text-success'>Rp {$nominal_format}</span>
            </td>
            <td class='text-center'>
                <div class='btn-group btn-group-sm' role='group'>
                    <button class='btn btn-outline-primary btnEdit' 
                            data-id='{$id_thr}'
                            data-karyawan='{$id_karyawan}'
                            data-mulai='{$row['tgl_mulai']}'
                            data-raya='{$row['tgl_hari_raya']}'
                            data-gapok='{$row['gaji_pokok']}'
                            data-lokasi='{$row['tunjangan_lokasi']}'
                            data-kebutuhan='{$row['tunjangan_kebutuhan']}'
                            data-masa='{$row['masa_kerja']}'
                            data-nominal='{$row['nominal_thr']}'
                            data-bs-toggle='tooltip' title='Edit Data'>
                        <i class='fa fa-edit'></i>
                    </button>
                    <button class='btn btn-outline-danger btnDelete' 
                            data-id='{$id_thr}'
                            data-bs-toggle='tooltip' title='Hapus Data'>
                        <i class='fa fa-trash'></i>
                    </button>
                </div>
            </td>
          </tr>";
    $no++;
}
