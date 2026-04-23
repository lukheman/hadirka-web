<?php
require '../../config/koneksi.php';

$no = 1;

// Tangkap parameter filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'Aktif';
$filter_site = isset($_GET['site']) ? $_GET['site'] : '';
$filter_jabatan = isset($_GET['jabatan']) ? $_GET['jabatan'] : '';

// Gunakan array untuk menampung kondisi WHERE secara dinamis
$conditions = [];

// 1. Kondisi Status
if ($filter_status !== 'Semua') {
    $status_aman = $koneksi->real_escape_string($filter_status);
    $conditions[] = "kr.status_karyawan = '$status_aman'";
}

// 2. Kondisi Site
if (!empty($filter_site)) {
    $site_aman = $koneksi->real_escape_string($filter_site);
    $conditions[] = "k.id_site = '$site_aman'";
}

// 3. Kondisi Jabatan
if (!empty($filter_jabatan)) {
    $jabatan_aman = $koneksi->real_escape_string($filter_jabatan);
    $conditions[] = "k.id_jabatan = '$jabatan_aman'";
}

// Rangkai WHERE clause jika ada kondisi yang aktif
$where_clause = "";
if (count($conditions) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// Tambahkan WHERE clause ke dalam query
$q = $koneksi->query("SELECT k.id_karier, k.id_karyawan, kr.nik_karyawan, kr.nama_lengkap, kr.status_karyawan, j.nama_jabatan, k.tgl_mulai, k.tgl_selesai, k.file_kontrak 
                      FROM tb_karier k
                      INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan
                      INNER JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
                      $where_clause
                      ORDER BY kr.nik_karyawan ASC");

while ($r = $q->fetch_assoc()) { 
    // Logika Status Dokumen
    if (!empty($r['file_kontrak'])) {
        $status_dokumen = '<span class="badge bg-success">Sudah Upload</span>';
        $btn_lihat = '<a href="app/uploads/kontrak/'.$r['file_kontrak'].'" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat PDF</a>';
    } else {
        $status_dokumen = '<span class="badge bg-danger">Belum Ada</span>';
        $btn_lihat = '';
    }
    
    // Beri warna merah pada nama jika statusnya bukan Aktif
    $nama_tampil = htmlspecialchars($r['nama_lengkap']);
    if (strtolower($r['status_karyawan']) !== 'aktif') {
        $nama_tampil .= ' <br><small class="text-danger">('.htmlspecialchars($r['status_karyawan']).')</small>';
    }
?>
    <tr class="">
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($r['nik_karyawan']) ?></td>
        <td class="text-start"><?= $nama_tampil ?></td>
        <td><?= htmlspecialchars($r['nama_jabatan']) ?></td>
        <td><?= $r['tgl_mulai'] ?> s/d <?= (!empty($r['tgl_selesai']) && $r['tgl_selesai'] !== '0000-00-00') ? $r['tgl_selesai'] : 'Sekarang' ?></td>
        <td class="text-center"><?= $status_dokumen ?></td>
        <td class="text-center">
            <?= $btn_lihat ?>
            <button class="btn btn-sm btn-primary btnUploadKontrak" 
                data-id_karier="<?= $r['id_karier'] ?>" 
                data-id_karyawan="<?= $r['id_karyawan'] ?>" 
                data-nama="<?= htmlspecialchars($r['nama_lengkap']) ?>">
                <i class="fas fa-upload"></i> Upload
            </button>
        </td>
    </tr>
<?php } ?>