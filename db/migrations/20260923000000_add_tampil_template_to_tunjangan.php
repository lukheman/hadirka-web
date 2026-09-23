<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Pilihan tampil/sembunyi tunjangan di dokumen kontrak.
 *
 * Kolom tampil_template TINYINT(1) DEFAULT 1 di:
 * - tb_tunjangan (milik karier)
 * - tb_tunjangan_kontrak (milik detail kontrak)
 * Data lama otomatis terisi 1 (tetap tampil, perilaku lama).
 * Flag hanya mengatur render dokumen kontrak, tidak mengubah payroll.
 */
final class AddTampilTemplateToTunjangan extends AbstractMigration
{
    public function change(): void
    {
        $this->table('tb_tunjangan')
            ->addColumn('tampil_template', 'boolean', [
                'null' => false,
                'default' => true,
                'signed' => false,
                'after' => 'jumlah_tunjangan',
                'comment' => '1=tampilkan di dokumen kontrak, 0=sembunyikan',
            ])
            ->update();

        $this->table('tb_tunjangan_kontrak')
            ->addColumn('tampil_template', 'boolean', [
                'null' => false,
                'default' => true,
                'signed' => false,
                'after' => 'jumlah_tunjangan',
                'comment' => '1=tampilkan di dokumen kontrak, 0=sembunyikan',
            ])
            ->update();
    }
}
