<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seed master jabatan yang dipakai site PT. IPIP dan PT. HBB.
 *
 * Jalankan dengan:
 *   ./vendor/bin/phinx seed:run -s JabatanSeeder
 */
final class JabatanSeeder extends AbstractSeed
{
    public function run(): void
    {
        $names = [
            'TUKANG KAYU',
            'TUKANG BESI',
            'TUKANG BATU',
            'ELEKTRIK',
            'OPERATOR EXCAVATOR',
            'DRIVER DUMP TRUCK',
            'CREW MEKANIK',
            'CLEANING STAFF',
            'CONCRETE PUMP',
            'COOK HELPER',
            'CREW BONGKAR',
            'CREW LOGISTIK',
            'CREW MEKANIK1',
            'CREW PORT',
            'CREW UMUM',
            'MOBILE CRANE',
            'OPERATOR FORKLIFT',
            'OPERATOR LOADER',
            'OPERATOR MIXER',
            'OPERATOR TRAILER',
            'RIGGER',
            'TALLY CLERK',
            'WELDER',
            'OPERATOR FUEL TANK',
            'DRIVER BUS',
            'CREW',
            'CREW CHECKER',
            'DRIVER LV',
            'ELEKTRIKAL PEMASANGAN',
            'OPERATOR BULLDOZER',
            'OPERATOR MOBILE CRANE',
            'PEKERJA LISTRIK PERBAIKAN',
            'TUKANG LISTRIK PEMASANGAN',
            'WELDER CO',
            'WELDER STIK',
            'OPERATOR MOBILE CRANE 80T',
            'CREW PENGHIJAUAN',
            'MEKANIK MOBIL',
            'OPERATOR BOOMTRUCK',
            'OPERATOR GRADER',
            'HELPER',
            'SAFETY',
            'TUKANG LISTRIK & AIR',
            'FUELMAN',
            'ADMIN JAGO',
            'CLEANING SERVICE',
            'DRIVER MOBIL TANGKI BBM',
            'GENERAL WORKER',
            'LIFTING WORKER',
            'MAINTENANCE WORKER',
            'METAL SHEET WORKER',
            'OPERATOR BOMAG',
            'STEVEDORES',
            'TALLY CLERK STAFF',
            'WAKIL FOREMAN CREW',
            'TANKER DRIVER',
            'OPERATOR COMPACTOR',
            'DRIVER MINI TRUCK',
            'OPERATOR EXCAVATOR RODA',
            'MEKANIK',
            'DRIVER WATER TRUCK',
            'DRIVER TRAILER',
            'OPERATOR EXCAVATOR LONG ARM',
            'DRIVER MOBIL MAINTENANCE',
            'DIREKTUR',
            'CREW TRUK SAMPAH DAN SEPTIK TANK',
            'CREW REPAIRMAN',
            'DRIVER TRUK SAMPAH DAN SEPTIK TANK',
            'Pengawas',
        ];

        $existingRows = $this->fetchAll('SELECT nama_jabatan FROM tb_jabatan');
        $existing = array_map(
            static fn($value): string => mb_strtoupper((string) $value),
            array_column($existingRows, 'nama_jabatan')
        );

        $new = [];
        foreach ($names as $nama) {
            if (!in_array(mb_strtoupper($nama), $existing, true)) {
                $new[] = ['nama_jabatan' => $nama];
            }
        }

        if ($new === []) {
            echo "Semua jabatan sudah ada, seed dilewati.\n";
            return;
        }

        $this->table('tb_jabatan')->insert($new)->saveData();

        echo count($new) . " jabatan berhasil dibuat.\n";
    }
}
