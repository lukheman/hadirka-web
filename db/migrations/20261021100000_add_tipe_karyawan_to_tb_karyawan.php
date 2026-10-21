<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Menambahkan tipe karyawan pada data karyawan.
 *
 * Nilai yang dipakai aplikasi:
 * - Kontrak : karyawan dengan kontrak kerja (gaji bulanan).
 * - Harian  : karyawan harian (gaji dihitung per hari hadir).
 *
 * Data lama otomatis terisi 'Kontrak' agar tidak ada karyawan tanpa tipe.
 */
final class AddTipeKaryawanToTbKaryawan extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tb_karyawan');

        if (!$table->hasColumn('tipe_karyawan')) {
            $table
                ->addColumn('tipe_karyawan', 'enum', [
                    'values'  => ['Kontrak', 'Harian'],
                    'null'    => false,
                    'default' => 'Kontrak',
                    'after'   => 'status_karyawan',
                    'comment' => 'Tipe karyawan: Kontrak / Harian',
                ])
                ->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('tb_karyawan');

        if ($table->hasColumn('tipe_karyawan')) {
            $table->removeColumn('tipe_karyawan')->update();
        }
    }
}
