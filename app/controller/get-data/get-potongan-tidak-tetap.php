<?php
require_once __DIR__ . '/../../config/koneksi.php';

// 1. Tangkap parameter bulan dari frontend
$bulan = isset($_GET['bulan']) ? mysqli_real_escape_string($koneksi, $_GET['bulan']) : '';

// 2. Siapkan klausa WHERE jika parameter bulan tidak kosong
$whereClause = "";
if (!empty($bulan)) {
    // Parameter bulan berformat YYYY-MM (contoh: 2024-03)
    // Kita gunakan LIKE untuk mencari tanggal yang berawalan tahun-bulan tersebut
    $whereClause = " WHERE p.tgl_potongan LIKE '$bulan%' ";
}

// 3. Sisipkan $whereClause ke dalam query utama
$query = "SELECT p.id_potongan_tidak_tetap, k.id_karyawan, k.nik_karyawan, k.nama_lengkap, p.nominal_potongan, p.tgl_potongan, p.jenis_potongan, p.ket_potongan, j.nama_jabatan
          FROM tb_potongan_tidak_tetap p
          JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
          JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
          JOIN tb_jabatan j ON j.id_jabatan = kr.id_jabatan
          JOIN tb_site s ON s.id_site = kr.id_site
          $whereClause
          ORDER BY p.tgl_potongan DESC";

$result = mysqli_query($koneksi, $query);
$no = 1;

// 4. Jika data kosong setelah difilter, tampilkan pesan tabel kosong
if (mysqli_num_rows($result) == 0) {
    // Biarkan kosong, DataTables yang akan memunculkan pesan "Data tidak ditemukan"
    echo "";
} else {
    while($row = mysqli_fetch_assoc($result)){
        echo "<tr>";
        echo "<td class='text-center'>{$no}</td>";
        echo "<td>{$row['nik_karyawan']}</td>";
        echo "<td>{$row['nama_lengkap']}</td>";
        echo "<td>{$row['nama_jabatan']}</td>";
        echo "<td>{$row['tgl_potongan']}</td>";
        echo "<td>{$row['jenis_potongan']}</td>";
        echo "<td>Rp ".number_format($row['nominal_potongan'],0,",",".")."</td>"; // Saya tambahkan format 'Rp' agar lebih rapi
        echo "<td>{$row['ket_potongan']}</td>";
        echo "<td class='text-center'>
                <button class='btn btn-primary btn-sm btnEdit'
                    data-id='{$row['id_potongan_tidak_tetap']}'
                    data-idkaryawan='{$row['id_karyawan']}'
                    data-tglpotongan='{$row['tgl_potongan']}'
                    data-jenispotongan='{$row['jenis_potongan']}'
                    data-potongan='{$row['nominal_potongan']}'
                    data-ket='{$row['ket_potongan']}'>
                    <i class='fa fa-edit'></i>
                </button>
                <button class='btn btn-danger btn-sm btnDelete' data-id='{$row['id_potongan_tidak_tetap']}'>
                    <i class='fa fa-trash'></i>
                </button>
              </td>";
        echo "</tr>";
        $no++;
    }
}
?>