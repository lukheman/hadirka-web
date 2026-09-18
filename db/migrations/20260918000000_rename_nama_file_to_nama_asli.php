<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Samakan nama kolom template kontrak menjadi `nama_asli`.
 *
 * Latar: migrasi awal membuat kolom `nama_file`, lalu di localhost kolomnya
 * diganti manual menjadi `nama_asli` (seluruh kode memakai nama itu), sehingga
 * database yang jalan murni dari migrasi (hosting) masih bernama `nama_file`
 * dan INSERT ... nama_asli gagal ("Unknown column 'nama_asli'").
 *
 * Guard hasColumn membuat migrasi ini aman di kedua kondisi:
 * - hosting  : nama_file ada, nama_asli belum -> rename jalan.
 * - localhost: sudah nama_asli -> dilewati (no-op).
 */
final class RenameNamaFileToNamaAsli extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tb_template_kontrak');
        $hasOld = $table->hasColumn('nama_file');
        $hasNew = $table->hasColumn('nama_asli');

        if ($hasOld && !$hasNew) {
            $table->renameColumn('nama_file', 'nama_asli')->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('tb_template_kontrak');
        $hasOld = $table->hasColumn('nama_file');
        $hasNew = $table->hasColumn('nama_asli');

        if ($hasNew && !$hasOld) {
            $table->renameColumn('nama_asli', 'nama_file')->update();
        }
    }
}
