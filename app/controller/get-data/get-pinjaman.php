<?php
require_once __DIR__ . '/../../config/koneksi.php';

// 1. Tangkap parameter bulan dari frontend
$bulan = isset($_GET['bulan']) ? mysqli_real_escape_string($koneksi, $_GET['bulan']) : '';

// 2. Siapkan klausa WHERE jika parameter bulan tidak kosong
$whereClause = "";
if (!empty($bulan)) {
    // Parameter bulan berformat YYYY-MM (contoh: 2024-03)
    // Kita gunakan LIKE untuk mencari tanggal yang berawalan tahun-bulan tersebut
    $whereClause = " WHERE p.tgl_pinjaman LIKE '$bulan%' ";
}

// 3. Sisipkan $whereClause ke dalam query utama (DAN FIX BUG RELASI GANDA & DATA HILANG)
$query = "SELECT p.id_pinjaman, k.id_karyawan, k.nik_karyawan, k.nama_lengkap, p.nominal_pinjaman, p.tgl_pinjaman, p.tgl_bayar_pinjaman, p.ket_pinjaman, COALESCE(j.nama_jabatan, '-') AS nama_jabatan
          FROM tb_pinjaman p
          JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
          LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan 
          LEFT JOIN tb_jabatan j ON j.id_jabatan = kr.id_jabatan
          LEFT JOIN tb_site s ON s.id_site = kr.id_site
          $whereClause
          GROUP BY p.id_pinjaman
          ORDER BY p.tgl_pinjaman DESC, p.tgl_bayar_pinjaman ASC";

$result = mysqli_query($koneksi, $query);
$no = 1;

// 4. Jika data kosong setelah difilter, tampilkan pesan tabel kosong
if (mysqli_num_rows($result) == 0) {
    // Biarkan kosong, DataTables yang akan memunculkan pesan "Data tidak ditemukan"
    echo "";
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td class='text-center'>{$no}</td>";
        echo "<td>{$row['nik_karyawan']}</td>";
        echo "<td>{$row['nama_lengkap']}</td>";
        echo "<td>{$row['nama_jabatan']}</td>";
        echo "<td>" . date('d/m/Y', strtotime($row['tgl_pinjaman'])) . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($row['tgl_bayar_pinjaman'])) . "</td>";
        echo "<td>Rp " . number_format($row['nominal_pinjaman'], 0, ",", ".") . "</td>"; // Saya tambahkan format 'Rp' agar lebih rapi
        echo "<td>{$row['ket_pinjaman']}</td>";
        echo "<td class='text-center'>
                <button class='btn btn-primary btn-sm btnEdit'
                    data-id='{$row['id_pinjaman']}'
                    data-idkaryawan='{$row['id_karyawan']}'
                    data-tglpinjaman='{$row['tgl_pinjaman']}'
                    data-tglbayar='{$row['tgl_bayar_pinjaman']}'
                    data-pinjaman='{$row['nominal_pinjaman']}'
                    data-ket='{$row['ket_pinjaman']}'>
                    <i class='fa fa-edit'></i>
                </button>
                <button class='btn btn-danger btn-sm btnDelete' data-id='{$row['id_pinjaman']}'>
                    <i class='fa fa-trash'></i>
                </button>
              </td>";
        echo "</tr>";
        $no++;
    }
}
