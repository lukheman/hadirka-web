<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seed user "master" dengan level tertinggi (level = 0), mengikuti
 * konvensi yang sudah dipakai di data existing (user 'master',
 * jabatan "Master", level = 0).
 *
 * Jalankan dengan:
 *   ./vendor/bin/phinx seed:run -s UserSeeder
 */
final class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        $username = 'master';

        // Cegah duplikat kalau seeder dijalankan lebih dari sekali.
        $existing = $this->fetchRow(
            "SELECT id_user FROM tb_user WHERE username = 'master'"
        );

        if ($existing) {
            echo "User '{$username}' sudah ada (id_user={$existing['id_user']}), seed dilewati.\n";
            return;
        }

        $data = [
            [
                'username'  => $username,
                // bcrypt via password_hash(), konsisten dengan hash existing (prefix $2y$)
                'password'  => password_hash('password123', PASSWORD_DEFAULT),
                'nama_user' => 'Master Admin',
                'jabatan'   => 'Master',
                'foto'      => '',
                'site'      => 0,
                'level'     => 0,
            ],
        ];

        $this->table('tb_user')->insert($data)->saveData();

        echo "User '{$username}' berhasil dibuat.\n";
    }
}
