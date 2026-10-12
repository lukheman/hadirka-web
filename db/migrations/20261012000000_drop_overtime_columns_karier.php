<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Hapus kolom overtime/lembur & denda dari tb_karier (penyederhanaan gaji Okt 2026).
 *
 * Rumus gaji sekarang hanya:
 *   bersih = total_nilai_kehadiran * gaji_pokok - panjar
 * sehingga kolom berikut tidak dipakai lagi:
 *   lembur, ot_pertama, ot_kedua, spl, ot1_spl, ot2_spl, pot_izin, pot_mangkir
 */
final class DropOvertimeColumnsKarier extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tb_karier');
        foreach (['lembur', 'ot_pertama', 'ot_kedua', 'spl', 'ot1_spl', 'ot2_spl', 'pot_izin', 'pot_mangkir'] as $col) {
            if ($table->hasColumn($col)) {
                $table->removeColumn($col);
            }
        }
        $table->update();
    }
}
