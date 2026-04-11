<?php
include "../../config/koneksi.php";

// Query dengan JOIN untuk mendapatkan nama karyawan dan nama APD
$sql = "SELECT p.*, k.nama_lengkap, k.nik_karyawan, a.nama_apd, a.kategori_apd 
        FROM pengambilan_apd p
        JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
        JOIN master_apd a ON p.id_apd = a.id_apd
        ORDER BY p.tanggal_pengambilan DESC";

$query = mysqli_query($koneksi, $sql);

if (mysqli_num_rows($query) == 0) {
    echo '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat transaksi.</td></tr>';
} else {
    while ($row = mysqli_fetch_assoc($query)) {
        echo '
        <tr>
            <td><i class="bx bx-calendar me-1"></i> ' . date('d M Y', strtotime($row['tanggal_pengambilan'])) . '</td>
            <td>
                <div class="fw-bold">' . $row['nama_lengkap'] . '</div>
                <small class="text-muted">' . $row['nik_karyawan'] . '</small>
            </td>
            <td>
                <span class="badge bg-label-info">' . $row['nama_apd'] . '</span>
                <div class="small text-muted">' . $row['kategori_apd'] . '</div>
            </td>
            <td><span class="fw-bold">' . $row['jumlah'] . '</span> Pcs</td>
            <td class="text-center">
    <button class="btn btn-sm btn-icon btn-label-warning me-1" onclick="editTransaksi(' . $row['id_pengambilan'] . ')">
        <i class="bx bx-edit"></i>
    </button>
    <button class="btn btn-sm btn-icon btn-label-danger" onclick="deleteTransaksi(' . $row['id_pengambilan'] . ')">
        <i class="bx bx-trash"></i>
    </button>
</td>
        </tr>';
    }
}