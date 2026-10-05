<?php
/**
 * ============================================
 * EXPORT EXCEL DATA THR
 * ============================================
 */

// Sesuaikan path koneksi database dengan struktur folder Anda
require_once __DIR__ . '/../../config/koneksi.php';

// Tangkap parameter filter dari GET URL
$periode = isset($_GET['periode']) ? $_GET['periode'] : '';
$site = isset($_GET['site']) ? $_GET['site'] : '';
$jabatan = isset($_GET['jabatan']) ? $_GET['jabatan'] : '';
$bank = isset($_GET['bank']) ? $_GET['bank'] : '';

// Penamaan file dinamis
$filename = "Data_THR" . ($periode ? "_$periode" : "_Semua_Tahun") . ".xls";

// Header untuk memaksakan browser mendownload file sebagai Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// ----------------------------------------------------
// BUILD WHERE CLAUSE SESUAI FILTER
// ----------------------------------------------------
$where = [];
$params = [];

if (!empty($periode)) {
    $where[] = "r.periode = ?";
    $params[] = (int)$periode;
}
if (!empty($site)) {
    $where[] = "s.nama_site = ?";
    $params[] = $site;
}
if (!empty($jabatan)) {
    $where[] = "j.nama_jabatan = ?";
    $params[] = $jabatan;
}
if (!empty($bank)) {
    // Normalisasi pencarian Bank
    if ($bank == 'BELUM_PUNYA') {
        $where[] = "(i.nama_bank IS NULL OR i.nama_bank = '' OR i.nama_bank = 'BELUM PUNYA REKENING')";
    } else if ($bank == 'LAINNYA') {
        $where[] = "(i.nama_bank NOT LIKE '%MANDIRI%' AND i.nama_bank NOT LIKE '%BRI%' AND i.nama_bank NOT LIKE '%BNI%' AND i.nama_bank NOT LIKE '%BCA%' AND i.nama_bank IS NOT NULL AND i.nama_bank != 'BELUM PUNYA REKENING')";
    } else {
        $where[] = "i.nama_bank LIKE ?";
        $params[] = "%$bank%";
    }
}

// Rangkai WHERE
$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// ----------------------------------------------------
// QUERY DATABASE
// ----------------------------------------------------
$sql = "SELECT 
            k.nik_karyawan, 
            k.nama_lengkap,
            s.nama_site,
            j.nama_jabatan,
            i.atas_nama_rek,
            i.no_rek,
            i.nama_bank,
            r.nominal_thr
        FROM tb_thr r
        INNER JOIN tb_karyawan k ON r.id_karyawan = k.id_karyawan
        LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
        LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_site s ON kr.id_site = s.id_site
        LEFT JOIN tb_jabatan j ON kr.id_jabatan = j.id_jabatan
        $whereClause
        ORDER BY r.periode DESC, k.nama_lengkap ASC";

$stmt = $koneksi->prepare($sql);
$stmt->execute($params);
$rowsThr = $stmt->fetchAll();
$q = $rowsThr;
?>

<table border="1">
    <thead>
        <tr>
            <th style="background-color: #f2f2f2;">No</th>
            <th style="background-color: #f2f2f2;">ID Karyawan</th>
            <th style="background-color: #f2f2f2;">Nama Lengkap</th>
            <th style="background-color: #f2f2f2;">Site</th>
            <th style="background-color: #f2f2f2;">Jabatan</th>
            <th style="background-color: #f2f2f2;">Atas Nama Rekening</th>
            <th style="background-color: #f2f2f2;">Nomor Rekening</th>
            <th style="background-color: #f2f2f2;">Nama Bank</th>
            <th style="background-color: #f2f2f2;">Nominal THR</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (count($q) > 0) {
            $no = 1;
            foreach ($q as $row) {
                // Tangani nilai kosong
                $site = $row['nama_site'] ? $row['nama_site'] : '-';
                $jabatan = $row['nama_jabatan'] ? $row['nama_jabatan'] : '-';
                $nama_bank = $row['nama_bank'] ? $row['nama_bank'] : 'BELUM PUNYA REKENING';
                $atas_nama = $row['atas_nama_rek'] ? $row['atas_nama_rek'] : '-';
                
                // Tambahkan spasi khusus pada nomor rekening agar Excel tidak menjadikannya format Scientific (misal: 1.23E+15)
                $no_rek = $row['no_rek'] ? "&nbsp;" . $row['no_rek'] : '-';

                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . htmlspecialchars($row['nik_karyawan']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nama_lengkap']) . "</td>";
                echo "<td>" . htmlspecialchars($site) . "</td>";
                echo "<td>" . htmlspecialchars($jabatan) . "</td>";
                echo "<td>" . htmlspecialchars($atas_nama) . "</td>";
                echo "<td>" . $no_rek . "</td>";
                echo "<td>" . htmlspecialchars($nama_bank) . "</td>";
                echo "<td>" . $row['nominal_thr'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9' style='text-align:center;'>Tidak ada data yang sesuai filter</td></tr>";
        }
        ?>
    </tbody>
</table>