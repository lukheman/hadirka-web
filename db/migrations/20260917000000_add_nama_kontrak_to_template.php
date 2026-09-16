<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Tambah nama kontrak di tb_template_kontrak.
 * Nullable agar template lama tetap valid.
 */
final class AddNamaKontrakToTemplate extends AbstractMigration
{
    public function change(): void
    {
        $this->table('tb_template_kontrak')
            ->addColumn('nama_kontrak', 'string', [
                'limit' => 150,
                'null' => true,
                'default' => null,
                'after' => 'id_template',
                'comment' => 'Nama kontrak (mis. Kontrak Kerja Operator 2026)',
            ])
            ->update();
    }
}
