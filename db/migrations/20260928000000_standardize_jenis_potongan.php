<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Samakan ejaan jenis potongan ke baku:
 * PPH21, BPJS KETENAGAKERJAAN 2%, BPJS KESEHATAN 1%.
 *
 * Idempotent: hanya mengubah baris yang masih memakai ejaan lama.
 */
final class StandardizeJenisPotongan extends AbstractMigration
{
    public function up(): void
    {
        $map = [
            'BPJS Kesehatan' => 'BPJS KESEHATAN 1%',
            'BPJS Ketenagakerjaan' => 'BPJS KETENAGAKERJAAN 2%',
            'PPh 21' => 'PPH21',
        ];

        foreach (['tb_potongan_kontrak', 'tb_potongan'] as $table) {
            foreach ($map as $lama => $baku) {
                $this->execute(sprintf(
                    "UPDATE `%s` SET `jenis_potongan` = '%s' WHERE `jenis_potongan` = '%s'",
                    $table,
                    $baku,
                    $lama
                ));
            }
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan: ejaan baku adalah bentuk yang diinginkan.
    }
}
