<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Tautkan tunjangan & potongan ke detail kontrak.
 *
 * Satu detail kontrak bisa punya BANYAK tunjangan/potongan, jadi relasinya
 * ditampung sebagai kolom FK baru di tabel yang sudah ada (bukan tabel baru):
 * - tb_tunjangan.id_detail_kontrak -> tb_detail_kontrak.id_detail
 * - tb_potongan.id_detail_kontrak  -> tb_detail_kontrak.id_detail
 *
 * id_karier dibuat nullable agar satu baris milik karier ATAU detail kontrak
 * (diatur di level aplikasi: tepat satu yang terisi).
 */
final class LinkTunjanganPotonganToDetailKontrak extends AbstractMigration
{
    public function change(): void
    {
        $this->table('tb_tunjangan')
            ->addColumn('id_detail_kontrak', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
                'after' => 'id_karier',
                'comment' => 'Relasi ke tb_detail_kontrak.id_detail (null bila milik karier)',
            ])
            ->changeColumn('id_karier', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->addIndex(['id_detail_kontrak'], ['name' => 'idx_tunjangan_detail'])
            ->addForeignKey('id_detail_kontrak', 'tb_detail_kontrak', 'id_detail', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'fk_tunjangan_detail',
            ])
            ->update();

        $this->table('tb_potongan')
            ->addColumn('id_detail_kontrak', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
                'after' => 'id_karier',
                'comment' => 'Relasi ke tb_detail_kontrak.id_detail (null bila milik karier)',
            ])
            ->changeColumn('id_karier', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->addIndex(['id_detail_kontrak'], ['name' => 'idx_potongan_detail'])
            ->addForeignKey('id_detail_kontrak', 'tb_detail_kontrak', 'id_detail', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'fk_potongan_detail',
            ])
            ->update();
    }
}
