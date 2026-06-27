<?php
include __DIR__."/../../config/koneksi.php";

$start = $_GET['start_date'] ?? '';
$end = $_GET['end_date'] ?? '';
$site = $_GET['site'] ?? '';
$jabatan = $_GET['jabatan'] ?? '';

$where = ["1=1"];
if ($start && $end) {
    $where[] = "s.tgl_sks BETWEEN '$start' AND '$end'";
}
if ($site) {
    $where[] = "kr.id_site = '$site'";
}
if ($jabatan) {
    $where[] = "kr.id_jabatan = '$jabatan'";
}

$where_sql = implode(' AND ', $where);

$sql = "SELECT s.*, k.nama_lengkap, k.nik_karyawan 
        FROM tb_bukti_sks s
        JOIN tb_karyawan k ON k.id_karyawan = s.id_karyawan 
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan AND (kr.tgl_selesai IS NULL OR kr.tgl_selesai >= CURDATE())
        WHERE $where_sql
        GROUP BY s.id_bukti_sks
        ORDER BY s.tgl_kirim DESC";

$q = mysqli_query($koneksi, $sql);

if (mysqli_num_rows($q) == 0) {
    echo '<tr><td colspan="8" class="text-center py-5 text-muted">Belum ada data pengajuan SKS.</td></tr>';
    exit;
}

while ($row = mysqli_fetch_assoc($q)) {
    $id = $row['id_bukti_sks'];
    $status = $row['status']; // Enum: Pending, Diterima, Ditolak
    $ket_sks = !empty($row['ket_sks']) ? htmlspecialchars($row['ket_sks']) : '-';
    
    $badge = [
        'Pending'  => 'warning',
        'Diterima' => 'success',
        'Ditolak'  => 'danger'
    ][$status];

    // --- PERBAIKAN TANGGAL DIPROSES ---
    // Cek apakah tgl_diterima null, kosong, atau default 0000-00-00
    if (!empty($row['tgl_diterima']) && $row['tgl_diterima'] != '0000-00-00 00:00:00') {
        $tgl_diproses = date('d/m/y H:i', strtotime($row['tgl_diterima']));
    } else {
        $tgl_diproses = '-'; // Tampilkan strip jika belum diproses
    }
    // ----------------------------------

    echo '<tr>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded-circle bg-label-info">'.strtoupper(substr($row['nama_lengkap'], 0, 1)).'</span>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-dark">'.$row['nama_lengkap'].'</span>
                    <small class="text-muted">'.$row['nik_karyawan'].'</small>
                </div>
            </div>
        </td>
        <td><i class="bx bx-calendar-event me-1 text-primary"></i>'.date('d/m/Y', strtotime($row['tgl_sks'])).'</td>
        
        <td><small class="text-muted">'.date('d/m/y H:i', strtotime($row['tgl_kirim'])).'</small></td>

        <td><small class="text-muted">'.$ket_sks.'</small></td>
        
        <td><small class="text-muted">'.$tgl_diproses.'</small></td>
        
        <td class="text-center">';
    
    if (!empty($row['bukti_sks'])) {
        $path = 'app/'.$row['bukti_sks'];
        echo '
        <div class="cursor-pointer border rounded p-1 d-inline-block bg-light" onclick="previewSKS(\''.$path.'\')">
            <img src="'.$path.'" class="rounded" style="width:35px; height:35px; object-fit:cover;">
        </div>';
    } else { echo '<span class="text-muted small">No File</span>'; }

    echo '</td>
        <td><span class="badge bg-label-'.$badge.' rounded-pill w-px-100">'.$status.'</span></td>
        <td>
            <div class="dropdown text-center">
                <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0">
                    <a class="dropdown-item text-success" href="javascript:void(0)" onclick="updateStatusSKS('.$id.', \'Diterima\')"><i class="bx bx-check me-2"></i> Terima</a>
                    <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="updateStatusSKS('.$id.', \'Ditolak\')"><i class="bx bx-x me-2"></i> Tolak</a>
                    <a class="dropdown-item text-warning" href="javascript:void(0)" onclick="updateStatusSKS('.$id.', \'Pending\')"><i class="bx bx-time-five me-2"></i> Pending</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-muted" href="javascript:void(0)" onclick="deleteSKS('.$id.')"><i class="bx bx-trash me-2"></i> Hapus</a>
                </div>
            </div>
        </td>
    </tr>';
}
?>