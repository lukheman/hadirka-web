<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/pengalaman_schema.php';

ensure_pengalaman_schema($koneksi);

$statement = $koneksi->query(
    "SELECT
        k.id_karyawan,
        k.nama_lengkap,
        k.nik_karyawan,
        k.tgl_lahir,
        k.status_karyawan,
        t.tgl_keluar,
        t.alasan_keluar,
        j.nama_jabatan,
        kr.tgl_mulai,
        kr.tgl_selesai
    FROM tb_karyawan k
    JOIN tb_tereliminasi t ON t.id_karyawan = k.id_karyawan
    LEFT JOIN tb_karier kr ON kr.id_karier = (
        SELECT x.id_karier
        FROM tb_karier x
        WHERE x.id_karyawan = k.id_karyawan
        ORDER BY x.tgl_mulai DESC, x.id_karier DESC
        LIMIT 1
    )
    LEFT JOIN tb_jabatan j ON j.id_jabatan = kr.id_jabatan
    WHERE k.status_karyawan != 'Aktif'
    ORDER BY k.nama_lengkap"
);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'data' => $statement->fetchAll(),
]);
