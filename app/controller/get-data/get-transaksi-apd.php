<?php
include "../../config/koneksi.php";

// Tangkap Parameter Filter
$site       = $_GET['site'] ?? '';
$departemen = $_GET['departemen'] ?? '';
$jabatan    = $_GET['jabatan'] ?? '';
$karyawan   = $_GET['karyawan'] ?? '';

// Build Kondisi WHERE
$where = ["1=1"];

if (!empty($site)) {
    $where[] = "c.id_site = '" . mysqli_real_escape_string($koneksi, $site) . "'";
}
if (!empty($departemen)) {
    $where[] = "c.id_departemen = '" . mysqli_real_escape_string($koneksi, $departemen) . "'";
}
if (!empty($jabatan)) {
    $where[] = "c.id_jabatan = '" . mysqli_real_escape_string($koneksi, $jabatan) . "'";
}
if (!empty($karyawan)) {
    $where[] = "p.id_karyawan = '" . mysqli_real_escape_string($koneksi, $karyawan) . "'";
}

$whereClause = implode(" AND ", $where);

// Query dengan JOIN komprehensif ke tb_karier untuk filter
$sql = "SELECT p.*, k.nama_lengkap, k.nik_karyawan, a.nama_apd, a.kategori_apd,
               s.nama_site, d.nama_departemen, j.nama_jabatan
        FROM pengambilan_apd p
        JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
        JOIN master_apd a ON p.id_apd = a.id_apd
        LEFT JOIN tb_karier c ON c.id_karyawan = k.id_karyawan AND (c.tgl_selesai IS NULL OR c.tgl_selesai >= CURDATE())
        LEFT JOIN tb_site s ON c.id_site = s.id_site
        LEFT JOIN tb_departemen d ON c.id_departemen = d.id_departemen
        LEFT JOIN tb_jabatan j ON c.id_jabatan = j.id_jabatan
        WHERE $whereClause
        ORDER BY p.tanggal_pengambilan DESC";

$query = mysqli_query($koneksi, $sql);

if (mysqli_num_rows($query) == 0) {
    echo '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat transaksi.</td></tr>';
} else {
    while ($row = mysqli_fetch_assoc($query)) {
        // Ambil nama jabatan, jika kosong tampilkan teks default
        $jabatan = $row['nama_jabatan'] ? $row['nama_jabatan'] : 'Belum ada jabatan';

        echo '
        <tr>
            <td><i class="bx bx-calendar me-1"></i> ' . date('d M Y', strtotime($row['tanggal_pengambilan'])) . '</td>
            <td>
                <div class="fw-bold">' . $row['nama_lengkap'] . '</div>
                <small class="text-muted">' . $row['nik_karyawan'] . ' | ' . $jabatan . '</small>
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
?>