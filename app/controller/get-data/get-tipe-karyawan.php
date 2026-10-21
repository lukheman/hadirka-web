<?php
error_reporting(0);
ini_set('display_errors', 0);

require __DIR__ . '/../../config/koneksi.php';

$q = $koneksi->query("SELECT * FROM tb_tipe_karyawan ORDER BY id_tipe_karyawan DESC");
$no = 1;

while ($r = $q->fetch()) {
    $jam = !empty($r['jam_kerja_mulai']) && !empty($r['jam_kerja_selesai'])
        ? date('h.i', strtotime($r['jam_kerja_mulai'])) . ' - ' . date('h.i', strtotime($r['jam_kerja_selesai']))
        : '-';
    echo "
    <tr>
        <td class='text-center'>{$no}</td>
        <td>" . htmlspecialchars($r['nama_tipe']) . "</td>
        <td class='text-center'>" . htmlspecialchars($jam) . "</td>
        <td>" . htmlspecialchars($r['keterangan'] ?? '-') . "</td>
        <td class='text-center'>
            <div class='d-flex justify-content-center gap-1'>
                <button type='button' class='btn btn-sm btn-warning btnEditTipe'
                    data-id='{$r['id_tipe_karyawan']}'
                    data-nama='" . htmlspecialchars($r['nama_tipe']) . "'
                    data-jam-mulai='" . htmlspecialchars($r['jam_kerja_mulai'] ?? '') . "'
                    data-jam-selesai='" . htmlspecialchars($r['jam_kerja_selesai'] ?? '') . "'
                    data-keterangan='" . htmlspecialchars($r['keterangan'] ?? '') . "'
                    title='Edit tipe karyawan'>
                    <i class='bx bx-edit me-1'></i>Edit
                </button>

                <button type='button' class='btn btn-sm btn-danger btnDeleteTipe'
                    data-id='{$r['id_tipe_karyawan']}'
                    data-nama='" . htmlspecialchars($r['nama_tipe']) . "'
                    title='Hapus tipe karyawan'>
                    <i class='bx bx-trash me-1'></i>Hapus
                </button>
            </div>
        </td>
    </tr>";
    $no++;
}
