<?php
require_once __DIR__ . '/../../config/koneksi.php';

// 1. Tangkap parameter dari frontend
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$site = isset($_GET['site']) ? $_GET['site'] : '';
$jabatan = isset($_GET['jabatan']) ? $_GET['jabatan'] : '';

// 2. Siapkan klausa WHERE
$where = ["1=1"];
$params = [];
if (!empty($bulan)) {
    // PERBAIKAN: Hilangkan kata " WHERE " di dalam sini
    $where[] = "p.tgl_potongan LIKE ?";
    $params[] = $bulan . '%';
}
if (!empty($site)) {
    $where[] = "kr.id_site = ?";
    $params[] = $site;
}
if (!empty($jabatan)) {
    $where[] = "kr.id_jabatan = ?";
    $params[] = $jabatan;
}

$whereClause = " WHERE " . implode(" AND ", $where);

// 3. Sisipkan $whereClause ke dalam query utama
$query = "SELECT p.id_potongan_tidak_tetap, k.id_karyawan, k.nik_karyawan, k.nama_lengkap, p.nominal_potongan, p.tgl_potongan, p.jenis_potongan, p.ket_potongan, COALESCE(j.nama_jabatan, '-') AS nama_jabatan
          FROM tb_potongan_tidak_tetap p
          JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
          LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
          LEFT JOIN tb_jabatan j ON j.id_jabatan = kr.id_jabatan
          $whereClause
          GROUP BY p.id_potongan_tidak_tetap
          ORDER BY p.tgl_potongan DESC";

$stPot = $koneksi->prepare($query);
$stPot->execute($params);
$rowsPot = $stPot->fetchAll();
$no = 1;

// 4. Jika data kosong setelah difilter, tampilkan pesan tabel kosong
if (count($rowsPot) == 0) {
    // Biarkan kosong, DataTables yang akan memunculkan pesan "Data tidak ditemukan"
    echo "";
} else {
    foreach ($rowsPot as $row) {
        echo "<tr>";
        echo "<td class='text-center'>{$no}</td>";
        echo "<td>{$row['nik_karyawan']}</td>";
        echo "<td>{$row['nama_lengkap']}</td>";
        echo "<td>{$row['nama_jabatan']}</td>";
        echo "<td>{$row['tgl_potongan']}</td>";
        echo "<td>{$row['jenis_potongan']}</td>";
        echo "<td>Rp " . number_format($row['nominal_potongan'], 0, ",", ".") . "</td>"; 
        echo "<td>{$row['ket_potongan']}</td>";
        echo "<td class='text-center'>
                <button class='btn btn-warning btn-sm btnEdit'
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
