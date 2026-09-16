<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Penomoran surat kontrak otomatis.
 *
 * - tb_nomor_urut: counter global (nama='kontrak'), di-seed dari nomor
 *   berpola TPJS-KONTRAK-* yang sudah ada (bila ada) agar tidak tabrakan.
 * - tb_karier.no_surat: UNIQUE agar nomor tidak ganda (NULL tetap boleh banyak).
 */
final class NomorSuratKontrak extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tb_nomor_urut', [
            'id' => false,
            'primary_key' => ['nama'],
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => 'Counter nomor surat global',
        ]);
        $table
            ->addColumn('nama', 'string', [
                'limit' => 50,
                'null' => false,
                'comment' => 'Nama counter, mis. kontrak',
            ])
            ->addColumn('terakhir', 'integer', [
                'signed' => false,
                'null' => false,
                'default' => 0,
                'comment' => 'Nomor urut terakhir yang dipakai',
            ])
            ->create();

        $this->execute("INSERT INTO tb_nomor_urut (nama, terakhir) VALUES ('kontrak', 0)");
        $this->execute(
            "UPDATE tb_nomor_urut SET terakhir = (" .
            "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(no_surat, '/', 2), '/', -1) AS UNSIGNED)), 0) " .
            "FROM tb_karier WHERE no_surat REGEXP '^TPJS-KONTRAK-[^/]+/[0-9]+/[0-9]{4}$'" .
            ") WHERE nama = 'kontrak'"
        );

        $this->table('tb_karier')
            ->addIndex(['no_surat'], ['unique' => true, 'name' => 'uq_karier_no_surat'])
            ->update();
    }

    public function down(): void
    {
        $this->table('tb_karier')->removeIndexByName('uq_karier_no_surat')->update();
        $this->table('tb_nomor_urut')->drop()->save();
    }
}
