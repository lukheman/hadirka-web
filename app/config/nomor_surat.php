<?php
// Helper nomor surat kontrak otomatis: TPJS-KONTRAK-{kode_jabatan}/{urut 4 digit}/{tahun tgl_mulai}.
// Counter global di tb_nomor_urut (nama='kontrak'), increment atomik via LAST_INSERT_ID
// sehingga aman dari duplikat walau banyak request bersamaan.

function generate_no_surat($koneksi, $kode_jabatan, $tgl_mulai)
{
    $kode = trim((string)$kode_jabatan);
    if ($kode === '') {
        throw new Exception('Kode jabatan belum diisi. Lengkapi kode jabatan terlebih dahulu.');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$tgl_mulai)) {
        throw new Exception('Tanggal mulai tidak valid.');
    }
    $tahun = substr($tgl_mulai, 0, 4);

    if (!$koneksi->query("UPDATE tb_nomor_urut SET terakhir = LAST_INSERT_ID(terakhir + 1) WHERE nama = 'kontrak'")) {
        throw new Exception('Gagal membuat nomor surat: ' . $koneksi->error);
    }
    $r = $koneksi->query('SELECT LAST_INSERT_ID() AS seq');
    $seq = ($r && $row = $r->fetch_assoc()) ? (int)$row['seq'] : 0;
    if ($seq <= 0) {
        throw new Exception('Gagal membuat nomor surat.');
    }

    return sprintf('TPJS-KONTRAK-%s/%04d/%s', $kode, $seq, $tahun);
}
