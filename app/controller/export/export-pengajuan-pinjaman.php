<?php
/**
 * ============================================
 * EXPORT EXCEL DATA PENGAJUAN PINJAMAN
 * ============================================
 */

// Sesuaikan path koneksi database dengan struktur folder Anda
require_once __DIR__ . '/../../config/koneksi.php';

// Penamaan file dinamis beserta tanggal & waktu saat ini
$filename = "Data_Pengajuan_Pinjaman_" . date('Ymd_His') . ".xls";

// Header untuk memaksakan browser mendownload file sebagai Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// ----------------------------------------------------
// QUERY DATABASE (Sama seperti get-pengajuan-pinjaman.php)
// ----------------------------------------------------
$sql = "SELECT p.*, k.nama_lengkap, k.nik_karyawan,
               i.nama_bank, i.no_rek, i.atas_nama_rek
        FROM tb_pengajuan_pinjaman p
        JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
        LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
        ORDER BY p.tgl_pengajuan DESC";

$q = mysqli_query($koneksi, $sql);
?>

<table border="1">
    <thead>
        <tr>
            <th style="background-color: #f2f2f2;">No</th>
            <th style="background-color: #f2f2f2;">NIK Karyawan</th>
            <th style="background-color: #f2f2f2;">Nama Lengkap</th>
            <th style="background-color: #f2f2f2;">Tanggal Pengajuan</th>
            <th style="background-color: #f2f2f2;">Nominal Pinjaman (Rp)</th>
            <th style="background-color: #f2f2f2;">Alasan Pinjaman</th>
            <th style="background-color: #f2f2f2;">Atas Nama Rekening</th>
            <th style="background-color: #f2f2f2;">Nomor Rekening</th>
            <th style="background-color: #f2f2f2;">Nama Bank</th>
            <th style="background-color: #f2f2f2;">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($q && mysqli_num_rows($q) > 0) {
            $no = 1;
            while ($row = mysqli_fetch_assoc($q)) {
                // Tangani nilai kosong
                $nama_bank = $row['nama_bank'] ? $row['nama_bank'] : 'BELUM PUNYA REKENING';
                $atas_nama = $row['atas_nama_rek'] ? $row['atas_nama_rek'] : '-';
                
                // Tambahkan spasi khusus (&nbsp;) agar Excel tidak menjadikannya format Scientific (misal: 1.23E+15)
                $no_rek = $row['no_rek'] ? "&nbsp;" . $row['no_rek'] : '-';

                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . htmlspecialchars($row['nik_karyawan']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nama_lengkap']) . "</td>";
                echo "<td>" . date('d-m-Y', strtotime($row['tgl_pengajuan'])) . "</td>";
                echo "<td>" . $row['nominal_pinjaman'] . "</td>";
                echo "<td>" . htmlspecialchars($row['alasan_pinjaman']) . "</td>";
                echo "<td>" . htmlspecialchars($atas_nama) . "</td>";
                echo "<td>" . $no_rek . "</td>";
                echo "<td>" . htmlspecialchars($nama_bank) . "</td>";
                echo "<td>" . strtoupper($row['status_pinjaman']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='10' style='text-align:center;'>Belum ada data pengajuan pinjaman</td></tr>";
        }
        ?>
    </tbody>
</table>