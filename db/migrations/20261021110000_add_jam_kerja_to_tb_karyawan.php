<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Menambahkan pengaturan jam kerja pada data karyawan.
 *
 * Kolom:
 * - jam_kerja_mulai  : jam mulai kerja (HH:MM:SS)
 * - jam_kerja_selesai: jam selesai kerja (HH:MM:SS)
 *
 * Digunakan terutama untuk karyawan tipe Harian untuk menentukan rentang
 * jam kerja harian (mis. 08:00 s.d. 16:00).
 */
final class AddJamKerjaToTbKaryawan extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tb_karyawan');

        if (!$table->hasColumn('jam_kerja_mulai')) {
            $table
                ->addColumn('jam_kerja_mulai', 'time', [
                    'null'    => true,
                    'default' => null,
                    'after'   => 'tipe_karyawan',
                    'comment' => 'Jam mulai kerja (HH:MM:SS)',
                ])
                ->update();
        }

        if (!$table->hasColumn('jam_kerja_selesai')) {
            $table
                ->addColumn('jam_kerja_selesai', 'time', [
                    'null'    => true,
                    'default' => null,
                    'after'   => 'jam_kerja_mulai',
                    'comment' => 'Jam selesai kerja (HH:MM:SS)',
                ])
                ->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('tb_karyawan');

        if ($table->hasColumn('jam_kerja_selesai')) {
            $table->removeColumn('jam_kerja_selesai')->update();
        }

        if ($table->hasColumn('jam_kerja_mulai')) {
            $table->removeColumn('jam_kerja_mulai')->update();
        }
    }
}
