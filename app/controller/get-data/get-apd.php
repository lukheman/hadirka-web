<?php
include "../../config/koneksi.php";

$query = mysqli_query($koneksi, "SELECT * FROM master_apd ORDER BY created_at DESC");
$total = mysqli_num_rows($query);
$aktif = 0;
$html = "";

if ($total == 0) {
    $html = '<tr><td colspan="5" class="text-center py-4">Belum ada data APD</td></tr>';
} else {
    while ($row = mysqli_fetch_assoc($query)) {
        if($row['status_aktif'] == 'aktif') $aktif++;
        
        $badge = ($row['status_aktif'] == 'aktif') ? 'bg-label-success' : 'bg-label-danger';
        
        $html .= '
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar-initial rounded bg-label-secondary p-2 me-2">
                        <i class="bx bx-wrench"></i>
                    </div>
                    <div>
                        <span class="fw-bold d-block">' . $row['nama_apd'] . '</span>
                        <small class="text-muted">' . $row['kategori_apd'] . '</small>
                    </div>
                </div>
            </td>
            <td><small>' . (substr($row['deskripsi'], 0, 30)) . '...</small></td>
            <td>' . $row['masa_pakai_hari'] . ' Hari</td>
            <td><span class="badge ' . $badge . '">' . $row['status_aktif'] . '</span></td>
            <td class="text-center">
                <button class="btn btn-sm btn-icon" onclick="editAPD(' . $row['id_apd'] . ')"><i class="bx bx-edit text-primary"></i></button>
                <button class="btn btn-sm btn-icon" onclick="deleteAPD(' . $row['id_apd'] . ')"><i class="bx bx-trash text-danger"></i></button>
            </td>
        </tr>';
    }
}

echo json_encode([
    'html' => $html,
    'total' => $total,
    'aktif' => $aktif
]);