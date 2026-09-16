<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Tabel nilai kontrak per template.
 *
 * Satu template = satu baris detail (UNIQUE id_template).
 * Dihapus otomatis bila template-nya dihapus (ON DELETE CASCADE).
 */
final class CreateTbDetailKontrak extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tb_detail_kontrak', [
            'id' => 'id_detail',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => 'Nilai kontrak per template (satu template satu detail)',
        ]);

        $table
            ->addColumn('id_template', 'integer', [
                'signed' => false,
                'null' => false,
                'comment' => 'Relasi ke tb_template_kontrak.id_template',
            ])
            ->addColumn('gaji_pokok', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'default' => 0,
                'null' => false,
                'comment' => 'Gaji pokok pada kontrak',
            ])
            ->addColumn('upah_lembur', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'default' => 0,
                'null' => false,
                'comment' => 'Upah lembur pada kontrak',
            ])
            ->addColumn('potongan_izin', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'default' => 0,
                'null' => false,
                'comment' => 'Potongan izin pada kontrak',
            ])
            ->addColumn('potongan_mangkir', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'default' => 0,
                'null' => false,
                'comment' => 'Potongan mangkir pada kontrak',
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
            ->addIndex(['id_template'], ['unique' => true, 'name' => 'uq_detail_template'])
            ->addForeignKey('id_template', 'tb_template_kontrak', 'id_template', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'fk_detail_template',
            ])
            ->create();
    }
}
