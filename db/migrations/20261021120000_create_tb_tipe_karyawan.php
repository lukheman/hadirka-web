<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Tabel master tipe karyawan (Kontrak, Harian, dll.).
 * Pengaturan jam kerja (mulai/selesai) disimpan di sini per tipe karyawan,
 * sehingga satu tipe dapat dipakai banyak karyawan.
 */
final class CreateTbTipeKaryawan extends AbstractMigration
{
    public function up(): void
    {
        $this->table('tb_tipe_karyawan', [
            'id' => false,
            'primary_key' => 'id_tipe_karyawan',
        ])
        ->addColumn('id_tipe_karyawan', 'integer', ['identity' => true])
        ->addColumn('nama_tipe', 'string', ['limit' => 50, 'null' => false])
        ->addColumn('jam_kerja_mulai', 'time', ['null' => true, 'default' => null])
        ->addColumn('jam_kerja_selesai', 'time', ['null' => true, 'default' => null])
        ->addColumn('keterangan', 'string', ['limit' => 255, 'null' => true, 'default' => null])
        ->addColumn('status', 'enum', [
            'values' => ['Aktif', 'Tidak Aktif'],
            'null' => false,
            'default' => 'Aktif',
        ])
        ->addIndex(['nama_tipe'], ['unique' => true, 'name' => 'uq_tipe_karyawan_nama'])
        ->create();

        // Seed data default
        $this->execute("
            INSERT INTO tb_tipe_karyawan (nama_tipe, jam_kerja_mulai, jam_kerja_selesai, keterangan)
            VALUES ('Kontrak', '08:00:00', '17:00:00', 'Karyawan kontrak bulanan')
        ");
        $this->execute("
            INSERT INTO tb_tipe_karyawan (nama_tipe, jam_kerja_mulai, jam_kerja_selesai, keterangan)
            VALUES ('Harian', '08:00:00', '16:00:00', 'Karyawan harian (upah per hari)')
        ");
    }

    public function down(): void
    {
        $this->table('tb_tipe_karyawan')->drop()->save();
    }
}
