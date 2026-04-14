<?php
include "../../config/koneksi.php";

$sql = "SELECT pi.*, k.nama_lengkap, k.nik_karyawan FROM tb_pengajuan_izin pi
        JOIN tb_karyawan k ON k.id_karyawan = pi.id_karyawan ORDER BY pi.tgl_izin DESC";
$q = mysqli_query($koneksi, $sql);

if (mysqli_num_rows($q) == 0) {
    echo '<tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data pengajuan izin.</td></tr>';
    exit;
}

while ($row = mysqli_fetch_assoc($q)) {
    $id = $row['id_pengajuan_izin'];
    $status = $row['status_izin'];
    
    // Konfigurasi Badge & Ikon
    $status_config = [
        'pending'   => ['color' => 'warning', 'icon' => 'bx-time-five'],
        'disetujui' => ['color' => 'success', 'icon' => 'bx-check-circle'],
        'ditolak'   => ['color' => 'danger',  'icon' => 'bx-x-circle']
    ];
    
    $conf = $status_config[$status] ?? ['color' => 'secondary', 'icon' => 'bx-info-circle'];
    $inisial = strtoupper(substr($row['nama_lengkap'], 0, 1));
    
    echo '<tr>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded-circle bg-label-primary">'.$inisial.'</span>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-dark">'.$row['nama_lengkap'].'</span>
                    <small class="text-muted">'.$row['nik_karyawan'].'</small>
                </div>
            </div>
        </td>
        <td>
            <div class="d-flex flex-column">
                <span class="text-dark">'.date('d M Y', strtotime($row['tgl_izin'])).'</span>
                <small class="text-muted">'.date('H:i', strtotime($row['tgl_izin'])).'</small>
            </div>
        </td>
        <td>
            <p class="mb-0 small text-wrap" style="max-width: 200px; white-space: normal;">
                '.$row['alasan_izin'].'
            </p>
        </td>
        <td class="text-center">';
    
    if (!empty($row['bukti_izin'])) {
        $path = 'app/'.$row['bukti_izin'];
        echo '
        <div class="preview-thumbnail shadow-sm border rounded p-1 d-inline-block" onclick="previewBukti(\''.$path.'\')">
            <img src="'.$path.'" class="rounded" style="width:35px; height:35px; object-fit:cover; cursor:zoom-in;">
        </div>';
    } else { 
        echo '<span class="badge bg-label-secondary"><i class="bx bx-minus"></i></span>'; 
    }

    echo '</td>
        <td>
            <span class="badge bg-label-'.$conf['color'].' d-flex align-items-center w-px-100 justify-content-center rounded-pill">
                <i class="bx '.$conf['icon'].' me-1"></i> '.ucfirst($status).'
            </span>
        </td>
        <td class="text-center">
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0">
                    <div class="dropdown-header text-uppercase small fw-bold">Ubah Status</div>
                    <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="updateStatus('.$id.', \'pending\')"><i class="bx bx-time me-2 text-warning"></i> Set Pending</a></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="updateStatus('.$id.', \'disetujui\')"><i class="bx bx-check-circle me-2 text-success"></i> Setujui</a></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="updateStatus('.$id.', \'ditolak\')"><i class="bx bx-x-circle me-2 text-danger"></i> Tolak</a></li>
                    <div class="dropdown-divider"></div>
                    <li><a class="dropdown-item d-flex align-items-center text-danger" href="javascript:void(0)" onclick="deleteIzin('.$id.')"><i class="bx bx-trash me-2"></i> Hapus</a></li>
                </div>
            </div>
        </td>
    </tr>';
}
?>