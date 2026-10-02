<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Hapus modul Data Internal (karyawan internal + bonus/rapel/potongan/pinjaman).
 *
 * - up()   : drop 5 tabel tb_*_internal (anak dulu, induk terakhir).
 * - down() : no-op (struktur dikembalikan via baseline + restore backup bila perlu).
 */
final class DropInternalTables extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'tb_bonus_internal',
            'tb_rapel_internal',
            'tb_potongan_internal',
            'tb_pinjaman_internal',
            'tb_karyawan_internal',
        ] as $table) {
            $this->execute("DROP TABLE IF EXISTS `{$table}`");
        }

        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Tidak dikembalikan otomatis. Restore dari backup / baseline bila perlu.
    }
}
