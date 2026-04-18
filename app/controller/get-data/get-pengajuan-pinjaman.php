<?php
require '../../config/koneksi.php';

// Ambil data pengajuan yang statusnya masih pending + Join ke tb_identitas
$query = "SELECT p.*, k.nama_lengkap, k.nik_karyawan,
                 i.nama_bank, i.no_rek, i.atas_nama_rek
          FROM tb_pengajuan_pinjaman p
          JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
          LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
          ORDER BY p.tgl_pengajuan DESC";

$result = mysqli_query($koneksi, $query);
$no = 1;

if (mysqli_num_rows($result) == 0) {
    echo "";
} else {
    while($row = mysqli_fetch_assoc($result)){
        $status_badge = '';
        if($row['status_pinjaman'] == 'pending') $status_badge = '<span class="badge bg-warning">Pending</span>';
        else if($row['status_pinjaman'] == 'disetujui') $status_badge = '<span class="badge bg-success">Disetujui</span>';
        else if($row['status_pinjaman'] == 'ditolak') $status_badge = '<span class="badge bg-danger">Ditolak</span>';

        // Tambahkan base URL tempat file uploads disimpan
        // Sesuaikan URL ini dengan alamat aslimu (sama seperti UPLOADS_BASE_URL di Flutter)
        $base_url_uploads = "http://api-hris.triputrajayasultra.com/"; 

        // Link Foto - Sisipkan base URL di depannya
        $foto_wajah = !empty($row['foto_wajah']) ? "<a href='{$base_url_uploads}{$row['foto_wajah']}' target='_blank' class='btn btn-xs btn-outline-info mb-1'>Cek Wajah</a><br>" : "";
        $foto_id = !empty($row['foto_id_card']) ? "<a href='{$base_url_uploads}{$row['foto_id_card']}' target='_blank' class='btn btn-xs btn-outline-primary'>Cek ID</a>" : "";

        // Format Info Rekening
        $info_rekening = "<span class='text-muted italic'><small>Belum disetting</small></span>";
        if (!empty($row['nama_bank']) && !empty($row['no_rek'])) {
            $info_rekening = "<b>{$row['nama_bank']}</b><br>
                              {$row['no_rek']}<br>
                              <small class='text-muted'>a.n {$row['atas_nama_rek']}</small>";
        }

        // // Tombol Aksi (Hanya muncul jika status pending)
        // $btn_aksi = "-";
        // if($row['status_pinjaman'] == 'pending'){
        //     $btn_aksi = "
        //         <button class='btn btn-success btn-sm btnProses'
        //             data-id='{$row['id_pengajuan_pinjaman']}'
        //             data-nama='{$row['nama_lengkap']}'
        //             data-nominal='{$row['nominal_pinjaman']}'>
        //             <i class='fa fa-check-square'></i> Proses
        //         </button>";
        // }

        // Tombol Aksi berupa Dropdown Menu
        $btn_aksi = "
        <div class='dropdown'>
            <button type='button' class='btn btn-sm btn-icon btn-outline-secondary rounded-pill dropdown-toggle hide-arrow' data-bs-toggle='dropdown'>
                <i class='fa fa-ellipsis-v'></i>
            </button>
            <div class='dropdown-menu dropdown-menu-end shadow border-0'>
                <div class='dropdown-header text-uppercase small fw-bold'>Aksi Pengajuan</div>
                <li>
                    <a class='dropdown-item d-flex align-items-center btnProses' href='javascript:void(0)'
                        data-id='{$row['id_pengajuan_pinjaman']}'
                        data-nama='{$row['nama_lengkap']}'
                        data-nominal='{$row['nominal_pinjaman']}'>
                        <i class='fa fa-check-square me-2 text-primary'></i> Proses (ACC/Tolak + Ket)
                    </a>
                </li>
                <li><a class='dropdown-item d-flex align-items-center' href='javascript:void(0)' onclick='updateStatusPengajuan({$row['id_pengajuan_pinjaman']}, \"pending\")'><i class='fa fa-clock me-2 text-warning'></i> Set Pending</a></li>
                <li><a class='dropdown-item d-flex align-items-center' href='javascript:void(0)' onclick='updateStatusPengajuan({$row['id_pengajuan_pinjaman']}, \"ditolak\")'><i class='fa fa-times-circle me-2 text-danger'></i> Set Ditolak Cepat</a></li>
                <div class='dropdown-divider'></div>
                <li><a class='dropdown-item d-flex align-items-center text-danger' href='javascript:void(0)' onclick='deletePengajuan({$row['id_pengajuan_pinjaman']})'><i class='fa fa-trash me-2'></i> Hapus Permanen</a></li>
            </div>
        </div>";

        echo "<tr>";
        echo "<td class='text-center'>{$no}</td>";
        echo "<td>{$row['nik_karyawan']}<br><b>{$row['nama_lengkap']}</b></td>";
        echo "<td>".date('d M Y', strtotime($row['tgl_pengajuan']))."</td>";
        echo "<td>Rp ".number_format($row['nominal_pinjaman'],0,",",".")."</td>";
        echo "<td>{$row['alasan_pinjaman']}</td>";
        echo "<td>{$foto_wajah}{$foto_id}</td>";
        echo "<td>{$info_rekening}</td>";
        echo "<td class='text-center'>{$status_badge}</td>";
        echo "<td class='text-center'>{$btn_aksi}</td>";
        echo "</tr>";
        $no++;
    }
}
?>