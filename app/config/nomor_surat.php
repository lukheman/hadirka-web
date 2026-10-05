<?php
// Helper nomor surat kontrak otomatis: TPJS-KONTRAK-{kode_jabatan}/{urut 4 digit}/{tahun tgl_mulai}.
// Urutan = terbesar yang terpakai (counter tb_nomor_urut vs data tb_karier) + 1,
// dengan kunci baris counter saat generate agar aman dari duplikat walau request bersamaan.

if (!function_exists('peek_no_urut')) {
    // Estimasi urutan berikutnya TANPA mengonsumsi counter (untuk preview).
    // Sama dengan generate: lanjut dari yang terbesar (counter vs data) + 1.
    function peek_no_urut($koneksi)
    {
        $last = 0;
        $qc = $koneksi->query("SELECT terakhir FROM tb_nomor_urut WHERE nama = 'kontrak' LIMIT 1");
        if ($qc) {
            $rowC = $qc->fetch();
            if ($rowC) $last = (int)$rowC['terakhir'];
        }
        $mx = 0;
        $qm = $koneksi->query(
            "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no_surat, '/', 2), '/', -1) AS UNSIGNED)), 0) AS mx " .
            "FROM tb_karier WHERE no_surat REGEXP '^TPJS-KONTRAK-[^/]+/[0-9]+/[0-9]{4}$'"
        );
        if ($qm) {
            $rowM = $qm->fetch();
            if ($rowM) $mx = (int)$rowM['mx'];
        }
        return max($last, $mx) + 1;
    }
}
if (!function_exists('generate_no_surat')) {
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

    // Nomor urut = (terbesar yang terpakai di tb_karier / counter, mana yang lebih besar) + 1.
    // Baris counter dikunci (FOR UPDATE) agar request bersamaan tidak menghasilkan nomor ganda.
    $koneksi->beginTransaction();
    try {
        $koneksi->query("INSERT IGNORE INTO tb_nomor_urut (nama, terakhir) VALUES ('kontrak', 0)");
        $lock = $koneksi->query("SELECT terakhir FROM tb_nomor_urut WHERE nama = 'kontrak' FOR UPDATE");
        $lockRow = $lock ? $lock->fetch() : false;
        if (!$lockRow) throw new Exception('Counter nomor surat tidak tersedia.');
        $last = (int)$lockRow['terakhir'];

        $qm = $koneksi->query(
            "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no_surat, '/', 2), '/', -1) AS UNSIGNED)), 0) AS mx " .
            "FROM tb_karier WHERE no_surat REGEXP '^TPJS-KONTRAK-[^/]+/[0-9]+/[0-9]{4}$'"
        );
        $mxRow = $qm ? $qm->fetch() : false;
        if ($mxRow === false) throw new Exception('Gagal membaca nomor terakhir.');
        $mx = (int)$mxRow['mx'];

        $seq = max($last, $mx) + 1;
        $stSeq = $koneksi->prepare("UPDATE tb_nomor_urut SET terakhir = ? WHERE nama = 'kontrak'");
        $stSeq->execute([$seq]);
        $koneksi->commit();
    } catch (Throwable $e) {
        error_log('generate_no_surat: ' . $e->getMessage());
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        throw $e instanceof Exception ? $e : new Exception($e->getMessage());
    }

    return sprintf('TPJS-KONTRAK-%s/%04d/%s', $kode, $seq, $tahun);
}
}
