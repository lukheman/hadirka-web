<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Tambah kode jabatan di tb_jabatan.
 * Nullable agar data lama tetap valid; UNIQUE (NULL ganda tetap diizinkan MySQL).
 */
final class AddKodeJabatan extends AbstractMigration
{
    public function change(): void
    {
        $this->table('tb_jabatan')
            ->addColumn('kode_jabatan', 'string', [
                'limit' => 50,
                'null' => true,
                'default' => null,
                'after' => 'id_jabatan',
                'comment' => 'Kode unik jabatan (mis. JBT-001)',
            ])
            ->addIndex(['kode_jabatan'], ['unique' => true, 'name' => 'uq_jabatan_kode'])
            ->update();
    }
}
