<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Menyimpan metode checkout pada log absensi utama.
 *
 * Nilai yang dipakai aplikasi:
 * - manual   : checkout dilakukan karyawan dan memiliki foto selfie.
 * - otomatis : checkout dibuat sistem setelah karyawan berada di luar radius
 *              lokasi kerja selama durasi kebijakan yang ditentukan.
 */
final class AddMetodeCheckoutToTbAbsen extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tb_absen');

        if (!$table->hasColumn('metode_checkout')) {
            $table
                ->addColumn('metode_checkout', 'string', [
                    'limit'   => 20,
                    'null'    => true,
                    'default' => null,
                    'after'   => 'waktu_keluar',
                    'comment' => 'manual atau otomatis; NULL bila belum checkout',
                ])
                ->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('tb_absen');

        if ($table->hasColumn('metode_checkout')) {
            $table->removeColumn('metode_checkout')->update();
        }
    }
}
