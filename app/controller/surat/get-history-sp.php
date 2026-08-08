<?php
require_once __DIR__ . '/../../config/koneksi.php';

$q = $koneksi->query("SELECT sp.*, kr.nama_lengkap 
    FROM tb_surat_peringatan sp
    INNER JOIN tb_karyawan kr ON sp.id_karyawan = kr.id_karyawan
    ORDER BY sp.id_sp DESC");

while ($r = $q->fetch_assoc()) {
    $tgl_berakhir = strtotime($r['tgl_berakhir']);
    $status_badge = (time() > $tgl_berakhir) ? "<span class='badge bg-success'>Expired (Selesai)</span>" : "<span class='badge bg-danger'>Aktif</span>";

    $no_surat = htmlspecialchars($r['no_surat']);
    $nama = htmlspecialchars($r['nama_lengkap']);
    $sanksi = htmlspecialchars($r['jenis_sanksi']);

    echo "<tr>
        <td>{$no_surat}</td>
        <td>{$nama}</td>
        <td><strong>{$sanksi}</strong></td>
        <td>" . date('d-m-Y', strtotime($r['tgl_surat'])) . "</td>
        <td>" . date('d-m-Y', $tgl_berakhir) . " ({$r['masa_berlaku_bulan']} Bln)</td>
        <td>{$status_badge}</td>
        <td>
            <button class='btn btn-primary btn-sm btnPrintPDF' data-id='{$r['id_sp']}' title='Print PDF'><i class='fa fa-print'></i></button>
            <button class='btn btn-danger btn-sm btnDeleteSP' data-id='{$r['id_sp']}' title='Hapus'><i class='fa fa-trash'></i></button>
        </td>
    </tr>";
}