<?php
include __DIR__ . "/../../config/koneksi.php";

$query = mysqli_query($koneksi, "SELECT * FROM tb_priode ORDER BY tgl_mulai DESC");

if (mysqli_num_rows($query) == 0) {
    echo '<tr><td colspan="4" class="text-center py-5 text-muted">Belum ada data periode.</td></tr>';
} else {
    while ($row = mysqli_fetch_assoc($query)) {
        $status     = $row['status_priode']; 
        $nextStatus = ($status === 'Open') ? 'Closed' : 'Open';
        $badgeClass = ($status === 'Open') ? 'bg-label-success' : 'bg-label-secondary';
        $iconStatus = ($status === 'Open') ? 'bx-lock-open-alt' : 'bx-lock-alt';
        $typeColor  = ($row['type_priode'] === 'absen') ? 'bg-label-info' : 'bg-label-warning';

        echo '
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <span class="badge ' . $typeColor . ' p-2 me-2 text-capitalize"><i class="bx bx-receipt me-1"></i>' . $row['type_priode'] . '</span>
                </div>
            </td>
            <td>
                <div class="small fw-bold text-dark">' . date('d/m/Y', strtotime($row['tgl_mulai'])) . ' - ' . date('d/m/Y', strtotime($row['tgl_selesai'])) . '</div>
                <small class="text-muted">Periode Aktif</small>
            </td>
            <td>
                <button onclick="toggleStatus(' . $row['id_priode'] . ', \'' . $nextStatus . '\')" class="btn btn-sm ' . $badgeClass . ' border-0 shadow-none">
                    <i class="bx ' . $iconStatus . ' me-1"></i> ' . $status . '
                </button>
            </td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-primary" onclick="editData(' . $row['id_priode'] . ')" title="Edit">
                        <i class="bx bx-edit me-1"></i>Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteData(' . $row['id_priode'] . ')" title="Hapus">
                        <i class="bx bx-trash me-1"></i>Hapus
                    </button>
                </div>
            </td>
        </tr>';
    }
}