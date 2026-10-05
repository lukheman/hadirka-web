<?php
include __DIR__."/../../config/koneksi.php";

$start = $_GET['start_date'] ?? '';
$end = $_GET['end_date'] ?? '';
$site = $_GET['site'] ?? '';
$jabatan = $_GET['jabatan'] ?? '';

$where = ["1=1"];
$params = [];
if ($start && $end) {
    $where[] = "s.tgl_sks BETWEEN ? AND ?";
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

$sql = "SELECT s.*, k.nama_lengkap, k.nik_karyawan 
        FROM tb_bukti_sks s
        JOIN tb_karyawan k ON k.id_karyawan = s.id_karyawan 
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan AND (kr.tgl_selesai IS NULL OR kr.tgl_selesai >= CURDATE())
        WHERE $where_sql
        GROUP BY s.id_bukti_sks
        ORDER BY s.tgl_kirim DESC";

$stSks = $koneksi->prepare($sql);
$stSks->execute($params);
$rowsSks = $stSks->fetchAll();

if (count($rowsSks) == 0) {
    echo '<tr><td colspan="8" class="text-center py-5 text-muted">Belum ada data pengajuan SKS.</td></tr>';
    exit;
}

foreach ($rowsSks as $row) {
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
            <div class="d-flex justify-content-center gap-1">';

                    if ($status != 'Diterima') {
                        echo '<button type="button" class="btn btn-sm btn-success" onclick="updateStatusSKS('.$id.', \'Diterima\')" title="Terima"><i class="bx bx-check me-1"></i>Terima</button>';
                    }
                    if ($status != 'Ditolak') {
                        echo '<button type="button" class="btn btn-sm btn-warning" onclick="updateStatusSKS('.$id.', \'Ditolak\')" title="Tolak"><i class="bx bx-x me-1"></i>Tolak</button>';
                    }
                    if ($status != 'Pending') {
                        echo '<button type="button" class="btn btn-sm btn-secondary" onclick="updateStatusSKS('.$id.', \'Pending\')" title="Pending"><i class="bx bx-time me-1"></i>Pending</button>';
                    }

                    echo '<button type="button" class="btn btn-sm btn-danger" onclick="deleteSKS('.$id.')" title="Hapus"><i class="bx bx-trash me-1"></i>Hapus</button>
            </div>
        </td>
    </tr>';
}
?>