<?php
include "../../config/koneksi.php";

$sql = "SELECT pa.*, k.nama_lengkap, k.nik_karyawan, m.nama_apd 
        FROM tb_pengajuan_apd pa
        JOIN tb_karyawan k ON k.id_karyawan = pa.id_karyawan 
        JOIN master_apd m ON m.id_apd = pa.id_apd
        ORDER BY pa.tgl_pengajuan DESC";
$q = mysqli_query($koneksi, $sql);

if (mysqli_num_rows($q) == 0) {
    echo '<tr><td colspan="7" class="text-center py-5 text-muted">Belum ada data pengajuan APD.</td></tr>';
    exit;
}

while ($row = mysqli_fetch_assoc($q)) {
    $id = $row['id_pengajuan_apd'];
    $status = $row['status_pengajuan'];
    
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
                <span class="text-dark">'.date('d M Y', strtotime($row['tgl_pengajuan'])).'</span>
                <small class="text-muted">'.date('H:i', strtotime($row['tgl_pengajuan'])).'</small>
            </div>
        </td>
        <td>
            <span class="badge bg-label-info mb-1">'.$row['nama_apd'].'</span><br>
            <small class="fw-bold">'.$row['jumlah'].' Pcs</small>
        </td>
        <td>
            <p class="mb-0 small text-wrap" style="max-width: 200px; white-space: normal;">'.$row['alasan'].'</p>
        </td>
        <td class="text-center">';
    
    if (!empty($row['bukti_rusak'])) {
        $path = 'app/'.$row['bukti_rusak'];
        echo '
        <div class="preview-thumbnail shadow-sm border rounded p-1 d-inline-block" onclick="previewBuktiApd(\''.$path.'\')">
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
                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown" data-bs-boundary="window">
                    <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0">
                    <div class="dropdown-header text-uppercase small fw-bold">Ubah Status</div>';

                    if ($status != 'pending') {
                        echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="updateStatusApd('.$id.', \'pending\')"><i class="bx bx-time me-2 text-warning"></i> Set Pending</a></li>';
                    }
                    if ($status != 'disetujui') {
                        echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="updateStatusApd('.$id.', \'disetujui\')"><i class="bx bx-check-circle me-2 text-success"></i> Setujui</a></li>';
                    }
                    if ($status != 'ditolak') {
                        echo '<li><a class="dropdown-item" href="javascript:void(0)" onclick="updateStatusApd('.$id.', \'ditolak\')"><i class="bx bx-x-circle me-2 text-danger"></i> Tolak</a></li>';
                    }

                    echo '<div class="dropdown-divider"></div>
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deletePengajuanApd('.$id.')"><i class="bx bx-trash me-2"></i> Hapus</a></li>
                </div>
            </div>
        </td>
    </tr>';
}
?>