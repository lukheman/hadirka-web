<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * NIK karyawan tidak lagi diwajibkan unik.
 *
 * index UNIK diganti index biasa agar dua karyawan boleh memiliki NIK yang
 * sama (misal data sementara/belum valid), sementara pencarian tetap cepat.
 */
final class AllowDuplicateNikKaryawan extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tb_karyawan');

        if ($table->hasIndexByName('nik_karyawan')) {
            $table->removeIndexByName('nik_karyawan')->update();
        }

        if (!$table->hasIndexByName('idx_nik_karyawan')) {
            $table->addIndex(['nik_karyawan'], ['name' => 'idx_nik_karyawan'])->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('tb_karyawan');

        if ($table->hasIndexByName('idx_nik_karyawan')) {
            $table->removeIndexByName('idx_nik_karyawan')->update();
        }

        if (!$table->hasIndexByName('nik_karyawan')) {
            $table->addIndex(['nik_karyawan'], ['unique' => true, 'name' => 'nik_karyawan'])->update();
        }
    }
}
