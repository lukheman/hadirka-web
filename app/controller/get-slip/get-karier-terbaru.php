<?php
// header('Content-Type: application/json');
header("Content-Type: application/json; charset=UTF-8");
require '../../config/koneksi.php'; // sesuaikan path

$id_karyawan = $_GET['id_karyawan'] ?? '';
$tgl_a = $_GET['tgl_a'] ?? date('Y-m-01');
$tgl_b = $_GET['tgl_b'] ?? date('Y-m-d');

if (!$id_karyawan) {
    echo json_encode(['status' => 'error', 'message' => 'id_karyawan kosong']);
    exit;
}

// Query sederhana: ambil karier yang berlaku pada <= tgl_b (terbaru)
$sql = "SELECT * FROM tb_karier
        WHERE id_karyawan = ?
        AND tanggal_berlaku <= ?
        ORDER BY tanggal_berlaku DESC
        LIMIT 1";

try {
    $stKarier = $koneksi->prepare($sql);
    $stKarier->execute([$id_karyawan, $tgl_b]);
    $data = $stKarier->fetch();
} catch (PDOException $e) {
    error_log('get-karier-terbaru: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data karier']);
    exit;
}

if (!$data) {
    echo json_encode(['status' => 'empty', 'message' => 'Tidak ada data karier']);
    exit;
}

// NOTE: jika tabel karier memiliki kolom nama_karyawan, jabatan, dst. Pastikan field sesuai.
// contoh mapping minimal:
$result = [
    'id_karyawan' => $data['id_karyawan'] ?? $id_karyawan,
    'nama_karyawan' => $data['nama_karyawan'] ?? $data['nama'] ?? null,
    'jabatan' => $data['id_jabatan'] ?? $data['jabatan'] ?? null,
    'gaji_pokok' => (float)($data['gaji_pokok'] ?? 0),
    't_daerah' => (float)($data['t_daerah'] ?? $data['t_daerah'] ?? 0),
    't_hidup' => (float)($data['t_hidup'] ?? 0),
    't_makan' => (float)($data['t_makan'] ?? 0),
    't_skil' => (float)($data['t_skil'] ?? $data['t_skill'] ?? 0),
    // optional: total lembur pre-calculated or 0
    'lembur_total' => (float)($data['lembur'] ?? 0),
    'tanggal_berlaku' => $data['tanggal_berlaku'] ?? null
];

echo json_encode(['status' => 'success', 'data' => $result]);
