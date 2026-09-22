<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Bedakan tunjangan tetap vs tidak tetap.
 *
 * Kolom jenis_tunjangan ENUM('tetap','tidak_tetap') DEFAULT 'tetap' di:
 * - tb_tunjangan (milik karier)
 * - tb_tunjangan_kontrak (milik detail kontrak)
 * Data lama otomatis terisi 'tetap'.
 */
final class AddJenisTunjangan extends AbstractMigration
{
    public function change(): void
    {
        $this->table('tb_tunjangan')
            ->addColumn('jenis_tunjangan', 'enum', [
                'values' => ['tetap', 'tidak_tetap'],
                'null' => false,
                'default' => 'tetap',
                'after' => 'nama_tunjangan',
                'comment' => 'Tipe tunjangan: tetap / tidak_tetap',
            ])
            ->update();

        $this->table('tb_tunjangan_kontrak')
            ->addColumn('jenis_tunjangan', 'enum', [
                'values' => ['tetap', 'tidak_tetap'],
                'null' => false,
                'default' => 'tetap',
                'after' => 'nama_tunjangan',
                'comment' => 'Tipe tunjangan: tetap / tidak_tetap',
            ])
            ->update();
    }
}
