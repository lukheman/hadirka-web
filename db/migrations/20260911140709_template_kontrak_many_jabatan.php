<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Satu template kontrak bisa berlaku untuk BANYAK jabatan (many-to-many).
 *
 * - Buat tabel pivot tb_template_kontrak_jabatan (id_template, id_jabatan).
 * - Pindahkan relasi lama (tb_template_kontrak.id_jabatan) ke pivot.
 * - Hapus kolom id_jabatan + unique-nya dari tb_template_kontrak.
 */
final class TemplateKontrakManyJabatan extends AbstractMigration
{
    public function up(): void
    {
        $pivot = $this->table('tb_template_kontrak_jabatan', [
            'id' => 'id',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => 'Relasi template kontrak <-> jabatan (banyak ke banyak)',
        ]);

        $pivot
            ->addColumn('id_template', 'integer', [
                'null' => false,
                'comment' => 'Relasi ke tb_template_kontrak.id_template',
            ])
            ->addColumn('id_jabatan', 'integer', [
                'null' => false,
                'comment' => 'Relasi ke tb_jabatan.id_jabatan',
            ])
            ->addColumn('created_at', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['id_template', 'id_jabatan'], [
                'unique' => true,
                'name' => 'uq_template_jabatan',
            ])
            ->addIndex(['id_jabatan'], ['name' => 'idx_jabatan'])
            ->create();

        // Pindahkan relasi lama ke pivot (aman bila tabel masih kosong).
        $this->execute(
            'INSERT INTO tb_template_kontrak_jabatan (id_template, id_jabatan) ' .
            'SELECT id_template, id_jabatan FROM tb_template_kontrak ' .
            'WHERE id_jabatan IS NOT NULL'
        );

        $this->table('tb_template_kontrak')
            ->removeIndexByName('uq_template_jabatan')
            ->removeColumn('id_jabatan')
            ->update();
    }

    public function down(): void
    {
        $this->table('tb_template_kontrak')
            ->addColumn('id_jabatan', 'integer', [
                'null' => true,
                'default' => null,
                'after' => 'id_template',
            ])
            ->addIndex(['id_jabatan'], ['unique' => true, 'name' => 'uq_template_jabatan'])
            ->update();

        // Kembalikan satu jabatan per template (yang pertama ditautkan).
        $this->execute(
            'UPDATE tb_template_kontrak t ' .
            'JOIN (SELECT id_template, MIN(id_jabatan) AS id_jabatan ' .
            'FROM tb_template_kontrak_jabatan GROUP BY id_template) j ' .
            'ON j.id_template = t.id_template ' .
            'SET t.id_jabatan = j.id_jabatan'
        );

        $this->table('tb_template_kontrak_jabatan')->drop()->save();
    }
}
