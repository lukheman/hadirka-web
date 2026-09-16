<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Tambah kolom no_surat di tb_karier (nomor surat kontrak).
 * Nullable agar riwayat karier lama tetap valid.
 */
final class AddNoSuratToTbKarier extends AbstractMigration
{
    public function change(): void
    {
        $this->table('tb_karier')
            ->addColumn('no_surat', 'string', [
                'limit' => 100,
                'null' => true,
                'default' => null,
                'after' => 'file_kontrak',
                'comment' => 'Nomor surat kontrak (mis. 344/TJS/V/2026)',
            ])
            ->update();
    }
}
