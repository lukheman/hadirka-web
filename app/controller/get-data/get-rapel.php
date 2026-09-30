<?php
/**
 * ============================================
 * GET RAPEL DATA - DISPLAY TABLE
 * ============================================
 */

require '../../config/koneksi.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

function formatRupiah($angka) {
    return number_format($angka, 0, ',', '.');
}

function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Jika koneksi gagal, hentikan eksekusi (DataTables akan otomatis menampilkan tabel kosong)
if (!isset($koneksi) || !$koneksi) {
    exit;
}

$bulan = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';
$site = isset($_GET['site']) ? trim($_GET['site']) : '';
$jabatan = isset($_GET['jabatan']) ? trim($_GET['jabatan']) : '';

$where = ["1=1"];
if (!empty($bulan))   $where[] = "DATE_FORMAT(r.tgl_rapel, '%Y-%m') = '" . $koneksi->real_escape_string($bulan) . "'";
if (!empty($site))    $where[] = "kr.id_site = '" . $koneksi->real_escape_string($site) . "'";
if (!empty($jabatan)) $where[] = "kr.id_jabatan = '" . $koneksi->real_escape_string($jabatan) . "'";

$whereClause = " WHERE " . implode(" AND ", $where);

$sql = "SELECT 
            r.id_rapel,
            r.id_karyawan,
            k.nik_karyawan,
            k.nama_lengkap,
            r.tgl_rapel,
            r.nominal_rapel,
            r.ket_rapel
        FROM tb_rapel r
        JOIN tb_karyawan k ON r.id_karyawan = k.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
        $whereClause
        GROUP BY r.id_rapel
        ORDER BY r.tgl_rapel DESC, r.id_rapel DESC";

$result = $koneksi->query($sql);

// Jika query gagal, hentikan eksekusi
if (!$result) {
    exit;
}

// Looping data (jika num_rows == 0, while loop ini tidak akan jalan dan output kosong)
$no = 1;
while ($row = $result->fetch_assoc()) {
    $id_rapel = intval($row['id_rapel']);
    $id_karyawan = intval($row['id_karyawan']);
    $nik_karyawan = escapeHtml($row['nik_karyawan']);
    $nama_lengkap = escapeHtml($row['nama_lengkap']);
    $nominal_rapel = floatval($row['nominal_rapel']);
    $ket_rapel = $row['ket_rapel'] ? escapeHtml($row['ket_rapel']) : '';
    
    $nominal_format = formatRupiah($nominal_rapel);
    $tgl_display = date('d/m/Y', strtotime($row['tgl_rapel']));
    $tgl_edit = date('Y-m-d', strtotime($row['tgl_rapel']));
    
    $initial = strtoupper(substr($nama_lengkap, 0, 1));
    
    $badge = '';
    if ($nominal_rapel >= 5000000) {
        $badge = '<span class="badge bg-danger ms-2">High</span>';
    } elseif ($nominal_rapel >= 2000000) {
        $badge = '<span class="badge bg-warning ms-2">Medium</span>';
    }
    
    $ket_display = $ket_rapel ? $ket_rapel : '-';
    if (strlen($ket_display) > 50) {
        $ket_display = substr($ket_display, 0, 47) . '...';
    }
    
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
                <span class='badge bg-label-primary'>{$tgl_display}</span>
            </td>
            <td class='text-end'>
                <span class='fw-bold text-success'>Rp {$nominal_format}</span>
                {$badge}
            </td>
            <td>
                <small class='text-muted' title='{$ket_rapel}'>{$ket_display}</small>
            </td>
            <td class='text-center'>
                <div class='btn-group btn-group-sm' role='group'>
                    <button class='btn btn-warning btnEdit' 
                            data-id='{$id_rapel}'
                            data-karyawan='{$id_karyawan}'
                            data-tgl='{$tgl_edit}'
                            data-nominal='{$nominal_rapel}'
                            data-ket='{$ket_rapel}'
                            data-bs-toggle='tooltip' 
                            title='Edit Data'>
                        <i class='fa fa-edit'></i>
                    </button>
                    <button class='btn btn-outline-danger btnDelete' 
                            data-id='{$id_rapel}'
                            data-bs-toggle='tooltip' 
                            title='Hapus Data'>
                        <i class='fa fa-trash'></i>
                    </button>
                </div>
            </td>
          </tr>";
    $no++;
}

// Free result
$result->free();
?>