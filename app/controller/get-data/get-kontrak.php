<?php
require_once __DIR__ . '/../../config/koneksi.php';

$no = 1;

// Query mengambil daftar Jabatan, menghitung total karyawan aktif, dan mengambil sampel nama file
$q = $koneksi->query("
    SELECT 
        j.id_jabatan, 
        j.nama_jabatan,
        COUNT(k.id_karier) as total_karyawan,
        MAX(k.file_kontrak) as sample_file
    FROM tb_jabatan j
    LEFT JOIN tb_karier k ON j.id_jabatan = k.id_jabatan 
        AND (k.tgl_selesai IS NULL OR k.tgl_selesai >= CURDATE())
    LEFT JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan 
        AND kr.status_karyawan = 'Aktif'
    GROUP BY j.id_jabatan
    ORDER BY j.nama_jabatan ASC
");

while ($r = $q->fetch_assoc()) { 
    $total = $r['total_karyawan'];
    $file_sample = $r['sample_file'];

    // Logika Status Dokumen
    if (!empty($file_sample)) {
        $status_dokumen = '<span class="badge bg-success">Tersedia</span>';
        $btn_lihat = '<a href="app/uploads/kontrak/'.$file_sample.'" target="_blank" class="btn btn-sm btn-info" title="Lihat Template"><i class="fas fa-eye"></i></a>';
        $btn_hapus = '<button class="btn btn-sm btn-danger btnHapusFile" data-id_jabatan="'.$r['id_jabatan'].'" data-nama_jabatan="'.htmlspecialchars($r['nama_jabatan']).'" title="Hapus File Massal"><i class="fas fa-trash"></i></button>';
    } else {
        $status_dokumen = '<span class="badge bg-danger">Belum Ada</span>';
        $btn_lihat = '';
        $btn_hapus = '';
    }

    $badge_karyawan = $total > 0 ? 'bg-primary' : 'bg-secondary';
    $btn_disabled = $total > 0 ? '' : 'disabled';
?>
    <tr>
        <td class="text-center align-middle"><?= $no++ ?></td>
        <td class="text-start align-middle fw-bold"><?= htmlspecialchars($r['nama_jabatan']) ?></td>
        <td class="text-center align-middle">
            <span class="badge <?= $badge_karyawan ?>"><?= $total ?> Orang</span>
        </td>
        <td class="text-center align-middle"><?= $status_dokumen ?></td>
        <td class="text-center align-middle">
            <div class="d-flex justify-content-center align-items-center gap-1">
                <?= $btn_lihat ?>
                <?= $btn_hapus ?>

                <button class="btn btn-sm btn-primary btnUploadKontrak" 
                    data-id_jabatan="<?= $r['id_jabatan'] ?>" 
                    data-nama_jabatan="<?= htmlspecialchars($r['nama_jabatan']) ?>"
                    data-total="<?= $total ?>"
                    title="Upload Kontrak Massal" <?= $btn_disabled ?>>
                    <i class="fas fa-upload"></i> Upload Massal
                </button>
            </div>
        </td>
    </tr>
<?php } ?>