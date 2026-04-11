<?php
require '../../config/koneksi.php';

// Ambil data pengajuan yang statusnya masih pending
$query = "SELECT p.*, k.nama_lengkap, k.nik_karyawan 
          FROM tb_pengajuan_pinjaman p
          JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
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

        // Tombol Aksi (Hanya muncul jika status pending)
        $btn_aksi = "-";
        if($row['status_pinjaman'] == 'pending'){
            $btn_aksi = "
                <button class='btn btn-success btn-sm btnProses'
                    data-id='{$row['id_pengajuan_pinjaman']}'
                    data-nama='{$row['nama_lengkap']}'
                    data-nominal='{$row['nominal_pinjaman']}'>
                    <i class='fa fa-check-square'></i> Proses
                </button>";
        }

        echo "<tr>";
        echo "<td class='text-center'>{$no}</td>";
        echo "<td>{$row['nik_karyawan']}<br><b>{$row['nama_lengkap']}</b></td>";
        echo "<td>".date('d M Y', strtotime($row['tgl_pengajuan']))."</td>";
        echo "<td>Rp ".number_format($row['nominal_pinjaman'],0,",",".")."</td>";
        echo "<td>{$row['alasan_pinjaman']}</td>";
        echo "<td>{$foto_wajah}{$foto_id}</td>";
        echo "<td class='text-center'>{$status_badge}</td>";
        echo "<td class='text-center'>{$btn_aksi}</td>";
        echo "</tr>";
        $no++;
    }
}
?>