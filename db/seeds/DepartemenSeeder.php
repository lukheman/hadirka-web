<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seed master departemen yang dipakai site PT. IPIP dan PT. HBB.
 *
 * Jalankan dengan:
 *   ./vendor/bin/phinx seed:run -s DepartemenSeeder
 */
final class DepartemenSeeder extends AbstractSeed
{
    public function run(): void
    {
        $names = [
            'LOGISTIK',
            'GA',
            'DKJ',
            'PORT',
            'ENGINEERING',
            'MANAJEMEN ENGINEERING 1',
            'MANAJEMEN PERALATAN',
            'MANAJEMEN UMUM',
            'GUDANG',
            'SAFETY',
            'INTERNAL',
        ];

        $existingRows = $this->fetchAll('SELECT nama_departemen FROM tb_departemen');
        $existing = array_column($existingRows, 'nama_departemen');

        $new = [];
        foreach ($names as $nama) {
            if (!in_array($nama, $existing, true)) {
                $new[] = ['nama_departemen' => $nama];
            }
        }

        if ($new === []) {
            echo "Semua departemen sudah ada, seed dilewati.\n";
            return;
        }

        $this->table('tb_departemen')->insert($new)->saveData();

        echo count($new) . " departemen berhasil dibuat.\n";
    }
}
