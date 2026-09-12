<?php

/**
 * Get Riwayat Karier per Karyawan (untuk tab Riwayat Karier di modal detail)
 * Mengembalikan baris <tr> HTML.
 */

header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

$id_karyawan = isset($_GET['id_karyawan']) ? (int)$_GET['id_karyawan'] : 0;

if ($id_karyawan <= 0) {
    echo "<tr><td colspan='8' class='text-center'>ID karyawan tidak valid</td></tr>";
    exit;
}

$stmt = $koneksi->prepare("SELECT k.*, s.nama_site, d.nama_departemen, j.nama_jabatan
    FROM tb_karier k
    LEFT JOIN tb_site s ON k.id_site = s.id_site
    LEFT JOIN tb_departemen d ON k.id_departemen = d.id_departemen
    LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
    WHERE k.id_karyawan = ?
    ORDER BY k.tgl_mulai DESC, k.id_karier DESC");
$stmt->bind_param("i", $id_karyawan);
$stmt->execute();
$q = $stmt->get_result();

if ($q->num_rows === 0) {
    echo "<tr><td colspan='8' class='text-center'>Belum ada riwayat karier</td></tr>";
    exit;
}

$no = 1;
while ($r = $q->fetch_assoc()) {
    $tgl_mulai = !empty($r['tgl_mulai']) ? date('d-m-Y', strtotime($r['tgl_mulai'])) : '-';
    $tgl_selesai = !empty($r['tgl_selesai']) ? date('d-m-Y', strtotime($r['tgl_selesai'])) : '-';
    $badge = $no === 1
        ? "<span class='badge bg-success'>Terbaru</span>"
        : "<span class='badge bg-secondary'>Riwayat</span>";

    echo "<tr class='text-center'>"
        . "<td>{$no}</td>"
        . "<td>" . htmlspecialchars($r['nama_site'] ?? '-') . "</td>"
        . "<td>" . htmlspecialchars($r['nama_departemen'] ?? '-') . "</td>"
        . "<td>" . htmlspecialchars($r['nama_jabatan'] ?? '-') . "</td>"
        . "<td class='text-end'>" . rupiah($r['gaji_pokok'] ?? 0) . "</td>"
        . "<td>{$tgl_mulai}</td>"
        . "<td>{$tgl_selesai}</td>"
        . "<td>{$badge}</td>"
        . "</tr>";
    $no++;
}
