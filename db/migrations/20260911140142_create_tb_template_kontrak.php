<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Tabel template file kontrak per jabatan.
 *
 * Satu jabatan = satu template aktif (UNIQUE id_jabatan).
 * Menggantikan pola lama "satu file massal ditautkan ke banyak baris
 * tb_karier.file_kontrak" agar riwayat template terdokumentasi
 * (nama file asli, ukuran, waktu upload).
 */
final class CreateTbTemplateKontrak extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tb_template_kontrak', [
            'id' => 'id_template',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => 'Template file kontrak (PDF) per jabatan',
        ]);

        $table
            ->addColumn('id_jabatan', 'integer', [
                'null' => false,
                'comment' => 'Relasi ke tb_jabatan.id_jabatan (satu template aktif per jabatan)',
            ])
            ->addColumn('file', 'string', [
                'limit' => 255,
                'null' => false,
                'comment' => 'Nama file tersimpan di app/uploads/kontrak/',
            ])
            ->addColumn('nama_file', 'string', [
                'limit' => 255,
                'null' => true,
                'default' => null,
                'comment' => 'Nama file asli saat di-upload',
            ])
            ->addColumn('ukuran', 'integer', [
                'null' => false,
                'default' => 0,
                'comment' => 'Ukuran file dalam bytes',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('updated_at', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['id_jabatan'], ['unique' => true, 'name' => 'uq_template_jabatan'])
            ->create();
    }
}
