<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Menambahkan durasi jam kerja harian khusus karyawan Kontrak.
 */
final class AddDurasiJamKerjaToTbKaryawan extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tb_karyawan');

        if (!$table->hasColumn('jam_kerja')) {
            $table->addColumn('jam_kerja', 'enum', [
                'values'  => ['7', '8'],
                'null'    => true,
                'default' => null,
                'after'   => 'id_tipe_karyawan',
                'comment' => 'Durasi jam kerja harian karyawan Kontrak',
            ])->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('tb_karyawan');

        if ($table->hasColumn('jam_kerja')) {
            $table->removeColumn('jam_kerja')->update();
        }
    }
}
