<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Bedakan tunjangan/potongan milik karier vs milik detail kontrak.
 *
 * - tb_tunjangan / tb_potongan               : murni milik karier (id_karier NOT NULL lagi,
 *                                             kolom id_detail_kontrak dihapus).
 * - tb_tunjangan_kontrak / tb_potongan_kontrak: murni milik detail kontrak
 *                                             (FK ke tb_detail_kontrak, CASCADE).
 *
 * Data yang sudah terlanjur tersambung ke detail kontrak dipindahkan
 * ke tabel baru saat migrate (dan dikembalikan saat rollback).
 */
final class SeparateTunjanganPotonganKontrak extends AbstractMigration
{
    public function up(): void
    {
        $tunjangan = $this->table('tb_tunjangan_kontrak', [
            'id' => 'id_tunjangan_kontrak',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => 'Tunjangan milik detail kontrak (bisa banyak per detail)',
        ]);
        $tunjangan
            ->addColumn('id_detail_kontrak', 'integer', [
                'signed' => false,
                'null' => false,
                'comment' => 'Relasi ke tb_detail_kontrak.id_detail',
            ])
            ->addColumn('nama_tunjangan', 'string', [
                'limit' => 25,
                'null' => false,
            ])
            ->addColumn('jumlah_tunjangan', 'string', [
                'limit' => 25,
                'null' => false,
            ])
            ->addIndex(['id_detail_kontrak'], ['name' => 'idx_tk_detail'])
            ->addForeignKey('id_detail_kontrak', 'tb_detail_kontrak', 'id_detail', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'fk_tk_detail',
            ])
            ->create();

        $potongan = $this->table('tb_potongan_kontrak', [
            'id' => 'id_potongan_kontrak',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => 'Potongan milik detail kontrak (bisa banyak per detail)',
        ]);
        $potongan
            ->addColumn('id_detail_kontrak', 'integer', [
                'signed' => false,
                'null' => false,
                'comment' => 'Relasi ke tb_detail_kontrak.id_detail',
            ])
            ->addColumn('jenis_potongan', 'string', [
                'limit' => 25,
                'null' => false,
            ])
            ->addColumn('jumlah_potongan', 'string', [
                'limit' => 25,
                'null' => false,
            ])
            ->addIndex(['id_detail_kontrak'], ['name' => 'idx_pk_detail'])
            ->addForeignKey('id_detail_kontrak', 'tb_detail_kontrak', 'id_detail', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'fk_pk_detail',
            ])
            ->create();

        // Pindahkan baris milik detail kontrak ke tabel baru.
        $this->execute(
            'INSERT INTO tb_tunjangan_kontrak (id_detail_kontrak, nama_tunjangan, jumlah_tunjangan) ' .
            'SELECT id_detail_kontrak, nama_tunjangan, jumlah_tunjangan FROM tb_tunjangan ' .
            'WHERE id_detail_kontrak IS NOT NULL'
        );
        $this->execute(
            'INSERT INTO tb_potongan_kontrak (id_detail_kontrak, jenis_potongan, jumlah_potongan) ' .
            'SELECT id_detail_kontrak, jenis_potongan, jumlah_potongan FROM tb_potongan ' .
            'WHERE id_detail_kontrak IS NOT NULL'
        );
        $this->execute('DELETE FROM tb_tunjangan WHERE id_detail_kontrak IS NOT NULL');
        $this->execute('DELETE FROM tb_potongan WHERE id_detail_kontrak IS NOT NULL');

        // Kembalikan tb_tunjangan / tb_potongan murni milik karier.
        $this->table('tb_tunjangan')
            ->dropForeignKey('id_detail_kontrak')
            ->removeColumn('id_detail_kontrak')
            ->update();
        $this->table('tb_tunjangan')
            ->changeColumn('id_karier', 'integer', ['null' => false])
            ->update();

        $this->table('tb_potongan')
            ->dropForeignKey('id_detail_kontrak')
            ->removeColumn('id_detail_kontrak')
            ->update();
        $this->table('tb_potongan')
            ->changeColumn('id_karier', 'integer', ['null' => false])
            ->update();
    }

    public function down(): void
    {
        // Kembalikan kolom penghubung di tabel karier.
        $this->table('tb_tunjangan')
            ->addColumn('id_detail_kontrak', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
                'after' => 'id_karier',
            ])
            ->changeColumn('id_karier', 'integer', ['null' => true, 'default' => null])
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
            ])
            ->changeColumn('id_karier', 'integer', ['null' => true, 'default' => null])
            ->addIndex(['id_detail_kontrak'], ['name' => 'idx_potongan_detail'])
            ->addForeignKey('id_detail_kontrak', 'tb_detail_kontrak', 'id_detail', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'fk_potongan_detail',
            ])
            ->update();

        // Kembalikan datanya lalu hapus tabel khusus kontrak.
        $this->execute(
            'INSERT INTO tb_tunjangan (id_karier, id_detail_kontrak, nama_tunjangan, jumlah_tunjangan) ' .
            'SELECT NULL, id_detail_kontrak, nama_tunjangan, jumlah_tunjangan FROM tb_tunjangan_kontrak'
        );
        $this->execute(
            'INSERT INTO tb_potongan (id_karier, id_detail_kontrak, jenis_potongan, jumlah_potongan) ' .
            'SELECT NULL, id_detail_kontrak, jenis_potongan, jumlah_potongan FROM tb_potongan_kontrak'
        );

        $this->table('tb_tunjangan_kontrak')->drop()->save();
        $this->table('tb_potongan_kontrak')->drop()->save();
    }
}
