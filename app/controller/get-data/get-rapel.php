<?php
require '../../config/koneksi.php';

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
        ORDER BY r.tgl_rapel DESC";

$result = $koneksi->query($sql);

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
        $nominal = number_format($row['nominal_rapel'], 0, ',', '.');
        $tgl = date('d/m/Y', strtotime($row['tgl_rapel']));
        $tgl_sort = date('Y-m-d', strtotime($row['tgl_rapel']));
        
        // Badge untuk nominal besar
        $badge = '';
        if ($row['nominal_rapel'] >= 5000000) {
            $badge = '<span class="badge bg-danger ms-2">High</span>';
        } elseif ($row['nominal_rapel'] >= 2000000) {
            $badge = '<span class="badge bg-warning ms-2">Medium</span>';
        }
        
        echo "<tr>
                <td class='text-center fw-bold'>{$no}</td>
                <td>
                    <div class='d-flex align-items-center'>
                        <div class='avatar avatar-sm bg-label-success rounded-circle me-2'>
                            <span class='fw-bold'>" . substr($row['nama_lengkap'], 0, 1) . "</span>
                        </div>
                        <div>
                            <div class='fw-bold'>{$row['nama_lengkap']}</div>
                            <small class='text-muted'>NIK: {$row['nik_karyawan']}</small>
                        </div>
                    </div>
                </td>
                <td class='text-center'>
                    <span class='badge bg-label-primary'>{$tgl}</span>
                </td>
                <td class='text-end'>
                    <span class='fw-bold text-success'>Rp {$nominal}</span>
                    {$badge}
                </td>
                <td>
                    <small class='text-muted'>" . ($row['ket_rapel'] ?: '-') . "</small>
                </td>
                <td class='text-center'>
                    <div class='btn-group btn-group-sm' role='group'>
                        <button class='btn btn-outline-primary btnEdit' 
                                data-id='{$row['id_rapel']}'
                                data-karyawan='{$row['id_karyawan']}'
                                data-tgl='{$tgl_sort}'
                                data-nominal='{$row['nominal_rapel']}'
                                data-ket='{$row['ket_rapel']}'
                                data-bs-toggle='tooltip' 
                                title='Edit Data'>
                            <i class='fa fa-edit'></i>
                        </button>
                        <button class='btn btn-outline-danger btnDelete' 
                                data-id='{$row['id_rapel']}'
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
?>