<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seed master site PT. IPIP dan PT. HBB.
 *
 * Jalankan dengan:
 *   ./vendor/bin/phinx seed:run -s SiteSeeder
 */
final class SiteSeeder extends AbstractSeed
{
    public function run(): void
    {
        $sites = [
            ['nama_site' => 'PT. IPIP', 'lokasi' => 'IPIP'],
            ['nama_site' => 'PT. HBB', 'lokasi' => 'IPIP'],
        ];

        $existingRows = $this->fetchAll('SELECT nama_site FROM tb_site');
        $existing = array_column($existingRows, 'nama_site');

        $new = array_values(array_filter(
            $sites,
            static fn(array $site): bool => !in_array($site['nama_site'], $existing, true)
        ));

        if ($new === []) {
            echo "Semua site sudah ada, seed dilewati.\n";
            return;
        }

        $this->table('tb_site')->insert($new)->saveData();

        $names = implode(', ', array_column($new, 'nama_site'));
        echo "Site berhasil dibuat: {$names}.\n";
    }
}
