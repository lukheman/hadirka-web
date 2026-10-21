<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Menghubungkan tb_karyawan ke tb_tipe_karyawan via id_tipe_karyawan (FK).
 * Pengaturan jam kerja dipindahkan dari tb_karyawan ke tb_tipe_karyawan.
 */
final class AddIdTipeKaryawanToTbKaryawan extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tb_karyawan');

        // 1. Tambah kolom id_tipe_karyawan
        if (!$table->hasColumn('id_tipe_karyawan')) {
            $table->addColumn('id_tipe_karyawan', 'integer', [
                'null'    => true,
                'default' => null,
                'after'   => 'tipe_karyawan',
            ])->save();
        }

        // 2. Migrasi data: mapping tipe_karyawan string -> id_tipe_karyawan
        $this->execute("
            UPDATE tb_karyawan k
            JOIN tb_tipe_karyawan t ON t.nama_tipe COLLATE utf8mb4_general_ci = k.tipe_karyawan COLLATE utf8mb4_general_ci
            SET k.id_tipe_karyawan = t.id_tipe_karyawan
        ");

        // Sisanya yang masih NULL (jika ada tipe yang tidak cocok), arahkan ke 'Kontrak' (id 1)
        $this->execute("
            UPDATE tb_karyawan
            SET id_tipe_karyawan = 1
            WHERE id_tipe_karyawan IS NULL
        ");

        // 3. Hapus kolom jam_kerja lama dari tb_karyawan (sudah ada di tb_tipe_karyawan)
        if ($table->hasColumn('jam_kerja_mulai')) {
            $table->removeColumn('jam_kerja_mulai')->save();
        }
        if ($table->hasColumn('jam_kerja_selesai')) {
            $table->removeColumn('jam_kerja_selesai')->save();
        }

        // 4. Tambah FK constraint
        if ($table->hasColumn('id_tipe_karyawan')) {
            $table->addForeignKey(['id_tipe_karyawan'], 'tb_tipe_karyawan', ['id_tipe_karyawan'], [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])->save();
        }
    }

    public function down(): void
    {
        $table = $this->table('tb_karyawan');

        if ($table->hasColumn('id_tipe_karyawan')) {
            $table->removeColumn('id_tipe_karyawan')->save();
        }
    }
}
