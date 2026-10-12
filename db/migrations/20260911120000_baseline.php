<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration baseline: merepresentasikan struktur database ipm_hris
 * yang SUDAH ADA (dibuat manual, sebelum pakai Phinx).
 *
 * - up()   : membuat seluruh 50 tabel dari dump schema-only (dipakai untuk
 *            environment BARU / kosong, misal local dev / staging baru).
 * - down() : drop seluruh tabel (kebalikan dari up(), untuk rollback).
 *
 * PENTING untuk database PRODUKSI yang tabelnya sudah ada:
 * JANGAN jalankan migration ini di sana. Cukup tandai sebagai "sudah applied"
 * dengan insert manual ke tabel phinxlog (lihat instruksi yang menyertai file ini).
 */
final class Baseline extends AbstractMigration
{
    /**
     * Path ke file dump schema-only (hasil mysqldump --no-data).
     */
    private function schemaFile(): string
    {
        return __DIR__ . '/schema/baseline_schema.sql';
    }

    public function up(): void
    {
        $sql = file_get_contents($this->schemaFile());

        if ($sql === false) {
            throw new RuntimeException('Tidak bisa membaca file: ' . $this->schemaFile());
        }

        // File dump ini setiap statement-nya diakhiri ";" di akhir baris,
        // dan tidak ada ";" di tengah baris (sudah diverifikasi), jadi aman displit begini.
        $statements = preg_split('/;\s*\R/', $sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement === '') {
                continue;
            }

            // Lewati baris yang isinya cuma komentar SQL (-- ...)
            if (preg_match('/^--/', $statement)) {
                continue;
            }

            $this->execute($statement);
        }
    }

    public function down(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'tb_user_token',
            'tb_user',
            'tb_tunjangan',
            'tb_transaksi_keuangan',
            'tb_thr',
            'tb_tereliminasi',
            'tb_surat_peringatan',
            'tb_site',
            'tb_sakit',
            'tb_reset_otp',
            'tb_rapel_internal',
            'tb_rapel',
            'tb_priode',
            'tb_potongan_tidak_tetap',
            'tb_potongan_internal',
            'tb_potongan',
            'tb_pinjaman_internal',
            'tb_pinjaman',
            'tb_pengumuman',
            'tb_pengaturan',
            'tb_pengajuan_pinjaman',
            'tb_pengajuan_pengeluaran',
            'tb_pengajuan_izin',
            'tb_pengajuan_cuti',
            'tb_pengajuan_apd',
            'tb_loker',
            'tb_libur',
            'tb_kontak_darurat',
            'tb_kategori_pengeluaran',
            'tb_kategori_keuangan',
            'tb_karyawan_internal',
            'tb_karyawan',
            'tb_karier',
            'tb_jatah_cuti',
            'tb_jabatan',
            'tb_identitas',
            'tb_departemen',
            'tb_cuti',
            'tb_calon_karyawan',
            'tb_bukti_sks',
            'tb_bonus_internal',
            'tb_apd',
            'tb_absen',
            'pengembalian_apd',
            'pengambilan_apd',
            'master_apd',
            'm_ter_pph21',
            'm_jenis_pelanggaran',
            'm_jenis_keluar',
            'footer_shortcuts',
        ];

        foreach ($tables as $table) {
            $this->execute("DROP TABLE IF EXISTS `{$table}`");
        }

        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
