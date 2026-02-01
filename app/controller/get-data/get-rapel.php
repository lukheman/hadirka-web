<?php
/**
 * ============================================
 * GET RAPEL DATA - DISPLAY TABLE
 * ============================================
 * File: get-rapel.php
 * Purpose: Fetch and display rapel data in table format
 */

require '../../config/koneksi.php';

// Set header untuk menghindari caching
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Function untuk format rupiah
function formatRupiah($angka) {
    return number_format($angka, 0, ',', '.');
}

// Function untuk escape HTML
function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Check database connection
if (!isset($koneksi) || !$koneksi) {
    echo "<tr>
            <td colspan='6' class='text-center py-5'>
                <div class='my-3'>
                    <i class='fa fa-exclamation-triangle fa-3x text-danger mb-3'></i>
                    <h6 class='text-danger'>Koneksi Database Gagal</h6>
                    <p class='text-muted small'>Silakan hubungi administrator</p>
                </div>
            </td>
          </tr>";
    exit;
}

// Prepare SQL query
$sql = "SELECT 
            r.id_rapel,
            r.id_karyawan,
            k.nik_karyawan,
            k.nama_lengkap,
            r.tgl_rapel,
            r.nominal_rapel,
            r.ket_rapel
        FROM tb_rapel r
        INNER JOIN tb_karyawan k ON r.id_karyawan = k.id_karyawan
        ORDER BY r.tgl_rapel DESC, r.id_rapel DESC";

// Execute query
$result = $koneksi->query($sql);

// Check for query errors
if (!$result) {
    echo "<tr>
            <td colspan='6' class='text-center py-5'>
                <div class='my-3'>
                    <i class='fa fa-exclamation-triangle fa-3x text-danger mb-3'></i>
                    <h6 class='text-danger'>Error Query Database</h6>
                    <p class='text-muted small'>" . escapeHtml($koneksi->error) . "</p>
                </div>
            </td>
          </tr>";
    exit;
}

// Check if data exists
if ($result->num_rows == 0) {
    echo "<tr>
            <td colspan='6' class='text-center py-5'>
                <div class='my-3'>
                    <i class='fa fa-inbox fa-3x text-muted mb-3'></i>
                    <h6 class='text-muted'>Belum ada data rapel</h6>
                    <p class='text-muted small'>Mulai dengan menambah data manual atau import dari CSV</p>
                </div>
            </td>
          </tr>";
} else {
    $no = 1;
    
    while ($row = $result->fetch_assoc()) {
        // Sanitize all data
        $id_rapel = intval($row['id_rapel']);
        $id_karyawan = intval($row['id_karyawan']);
        $nik_karyawan = escapeHtml($row['nik_karyawan']);
        $nama_lengkap = escapeHtml($row['nama_lengkap']);
        $nominal_rapel = floatval($row['nominal_rapel']);
        $ket_rapel = $row['ket_rapel'] ? escapeHtml($row['ket_rapel']) : '';
        
        // Format data
        $nominal_format = formatRupiah($nominal_rapel);
        $tgl_display = date('d/m/Y', strtotime($row['tgl_rapel']));
        $tgl_edit = date('Y-m-d', strtotime($row['tgl_rapel']));
        
        // Get initial for avatar
        $initial = strtoupper(substr($nama_lengkap, 0, 1));
        
        // Badge untuk nominal besar
        $badge = '';
        if ($nominal_rapel >= 5000000) {
            $badge = '<span class="badge bg-danger ms-2">High</span>';
        } elseif ($nominal_rapel >= 2000000) {
            $badge = '<span class="badge bg-warning ms-2">Medium</span>';
        }
        
        // Keterangan display
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
                        <button class='btn btn-outline-primary btnEdit' 
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
}

// Free result
$result->free();
?>