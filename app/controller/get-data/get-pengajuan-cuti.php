<?php
include __DIR__ . "/../../config/koneksi.php";

$start = $_GET['start_date'] ?? '';
$end = $_GET['end_date'] ?? '';
$site = $_GET['site'] ?? '';
$jabatan = $_GET['jabatan'] ?? '';

$where = ["1=1"];
$params = [];
if ($start && $end) {
    // Memfilter berdasarkan tanggal mulai cuti
    $where[] = "pc.tgl_awal BETWEEN ? AND ?";
    $params[] = $start;
    $params[] = $end;
}
if ($site) {
    $where[] = "kr.id_site = ?";
    $params[] = $site;
}
if ($jabatan) {
    $where[] = "kr.id_jabatan = ?";
    $params[] = $jabatan;
}

$where_sql = implode(' AND ', $where);

$sql = "SELECT pc.*, k.nama_lengkap, k.nik_karyawan 
        FROM tb_pengajuan_cuti pc
        JOIN tb_karyawan k ON k.id_karyawan = pc.id_karyawan 
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan AND (kr.tgl_selesai IS NULL OR kr.tgl_selesai >= CURDATE())
        WHERE $where_sql
        GROUP BY pc.id_pengajuan_cuti
        ORDER BY pc.id_pengajuan_cuti DESC";

$stCuti = $koneksi->prepare($sql);
$stCuti->execute($params);
$rowsCuti = $stCuti->fetchAll();

if (count($rowsCuti) == 0) {
    echo '<tr><td colspan="7" class="text-center py-5 text-muted">Belum ada data pengajuan cuti.</td></tr>';
    exit;
}

foreach ($rowsCuti as $row) {
    $id = $row['id_pengajuan_cuti'];
    $status = $row['status_cuti'];
    
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
                <span class="text-dark">'.date('d M Y', strtotime($row['tgl_awal'])).'</span>
                <small class="text-muted">s/d '.date('d M Y', strtotime($row['tgl_akhir'])).'</small>
            </div>
        </td>
        <td><span class="fw-bold">'.$row['lama_cuti'].' Hari</span></td>
        <td>
            <p class="mb-0 small text-wrap" style="max-width: 200px; white-space: normal;">
                '.$row['alasan_cuti'].'
            </p>
        </td>
        <td class="text-center">';
    
    if (!empty($row['bukti_cuti'])) {
        $path = 'app/' . $row['bukti_cuti']; 
        echo '<div class="preview-thumbnail shadow-sm border rounded p-1 d-inline-block" onclick="previewBukti(\''.$path.'\')">
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
            <div class="d-flex justify-content-center gap-1">';

                    // Kondisi: Hanya tampilkan tombol yang statusnya BUKAN status saat ini
                    if ($status != 'disetujui') {
                        echo '<button type="button" class="btn btn-sm btn-success" onclick="updateStatus('.$id.', \'disetujui\')" title="Setujui"><i class="bx bx-check me-1"></i>Setujui</button>';
                    }
                    if ($status != 'ditolak') {
                        echo '<button type="button" class="btn btn-sm btn-warning" onclick="updateStatus('.$id.', \'ditolak\')" title="Tolak"><i class="bx bx-x me-1"></i>Tolak</button>';
                    }
                    if ($status != 'pending') {
                        echo '<button type="button" class="btn btn-sm btn-secondary" onclick="updateStatus('.$id.', \'pending\')" title="Set Pending"><i class="bx bx-time me-1"></i>Pending</button>';
                    }

                    echo '<button type="button" class="btn btn-sm btn-danger" onclick="deleteCuti('.$id.')" title="Hapus"><i class="bx bx-trash me-1"></i>Hapus</button>
            </div>
        </td>
    </tr>';
}
?>