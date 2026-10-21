<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Menghapus kolom status dari tb_tipe_karyawan.
 * Semua tipe karyawan selalu dianggap aktif.
 */
final class DropStatusFromTbTipeKaryawan extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tb_tipe_karyawan');

        if ($table->hasColumn('status')) {
            $table->removeColumn('status')->save();
        }
    }

    public function down(): void
    {
        $table = $this->table('tb_tipe_karyawan');

        if (!$table->hasColumn('status')) {
            $table
                ->addColumn('status', 'enum', [
                    'values'  => ['Aktif', 'Tidak Aktif'],
                    'null'    => false,
                    'default' => 'Aktif',
                ])
                ->save();
        }
    }
}
