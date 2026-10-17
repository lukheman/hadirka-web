<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Satukan data absen — SATU tabel untuk HRD, rekap, payroll, dan aplikasi mobile.
 *
 * Log absen aplikasi (dulu tabel terpisah `tb_absen_mobile`) kini menulis
 * langsung ke `tb_absen` yang diperluas. Baris dari aplikasi ditandai
 * `sumber='mobile'`, `ket_absen='HADIR'`, `jam_kerja='1'` (hari penuh berbayar)
 * sehingga rekap harian, payroll, dan halaman Absensi Mobile otomatis ikut
 * menghitungnya.
 *
 * Kolom baru `tb_absen`:
 *   - sumber            : 'web' (input HRD/CSV) atau 'mobile' (aplikasi)
 *   - id_lokasi         : FK ke tb_site.id_site (site terdekat saat absen)
 *   - nama_lokasi       : snapshot nama site saat absen
 *   - latitude/longitude: titik GPS karyawan saat absen
 *   - jarak_meter       : jarak ke titik site (meter)
 *   - foto_selfie_path  : file selfie (uploads/absensi/...)
 *   - waktu_masuk/keluar: 'HH:MM WITA' dari aplikasi
 *   - status_kehadiran  : 'Tepat Waktu' / 'Terlambat {n}m' / 'Sudah Selesai'
 *   - keterlambatan_menit, is_tepat_waktu, dibuat_pada
 *
 * Data `tb_absen_mobile` yang sudah ada dipindah, lalu tabelnya di-DROP.
 */
final class SatukanTbAbsen extends AbstractMigration
{
    public function up(): void
    {
        $this->perluasTbAbsen();
        $this->pindahDataAbsenMobile();
    }

    public function down(): void
    {
        $this->buatKembaliTbAbsenMobile();
        $this->kembaliTbAbsenMinimal();
    }

    private function perluasTbAbsen(): void
    {
        $t = $this->table('tb_absen');
        $berubah = false;

        if (!$t->hasColumn('sumber')) {
            $t->addColumn('sumber', 'string', [
                'limit'    => 10,
                'null'     => false,
                'default'  => 'web',
                'after'    => 'ket_absen',
                'comment'  => 'web = input HRD/CSV; mobile = absen aplikasi',
            ]);
            $berubah = true;
        }
        if (!$t->hasColumn('id_lokasi')) {
            $t->addColumn('id_lokasi', 'integer', [
                'null'    => true,
                'after'   => 'sumber',
                'comment' => 'Site terdekat saat absen (tb_site.id_site)',
            ]);
            $berubah = true;
        }
        if (!$t->hasColumn('nama_lokasi')) {
            $t->addColumn('nama_lokasi', 'string', ['limit' => 100, 'null' => true, 'after' => 'id_lokasi']);
            $berubah = true;
        }
        if (!$t->hasColumn('latitude')) {
            $t->addColumn('latitude', 'double', ['null' => true, 'after' => 'nama_lokasi']);
            $berubah = true;
        }
        if (!$t->hasColumn('longitude')) {
            $t->addColumn('longitude', 'double', ['null' => true, 'after' => 'latitude']);
            $berubah = true;
        }
        if (!$t->hasColumn('jarak_meter')) {
            $t->addColumn('jarak_meter', 'double', [
                'null'    => true,
                'after'   => 'longitude',
                'comment' => 'Jarak ke titik site (meter)',
            ]);
            $berubah = true;
        }
        if (!$t->hasColumn('foto_selfie_path')) {
            $t->addColumn('foto_selfie_path', 'text', ['null' => true, 'after' => 'jarak_meter']);
            $berubah = true;
        }
        if (!$t->hasColumn('waktu_masuk')) {
            $t->addColumn('waktu_masuk', 'string', [
                'limit'   => 20,
                'null'    => true,
                'after'   => 'foto_selfie_path',
                'comment' => 'Format: HH:MM WITA (dari aplikasi)',
            ]);
            $berubah = true;
        }
        if (!$t->hasColumn('waktu_keluar')) {
            $t->addColumn('waktu_keluar', 'string', ['limit' => 20, 'null' => true, 'after' => 'waktu_masuk']);
            $berubah = true;
        }
        if (!$t->hasColumn('status_kehadiran')) {
            $t->addColumn('status_kehadiran', 'string', [
                'limit'  => 50,
                'null'   => true,
                'after'  => 'waktu_keluar',
                'comment' => 'Tepat Waktu / Terlambat {n}m / Sudah Selesai',
            ]);
            $berubah = true;
        }
        if (!$t->hasColumn('keterlambatan_menit')) {
            $t->addColumn('keterlambatan_menit', 'integer', ['null' => false, 'default' => 0, 'after' => 'status_kehadiran']);
            $berubah = true;
        }
        if (!$t->hasColumn('is_tepat_waktu')) {
            $t->addColumn('is_tepat_waktu', 'boolean', ['null' => false, 'default' => 1, 'after' => 'keterlambatan_menit']);
            $berubah = true;
        }
        if (!$t->hasColumn('dibuat_pada')) {
            $t->addColumn('dibuat_pada', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'after' => 'is_tepat_waktu']);
            $berubah = true;
        }

        if ($berubah) {
            $t->update();
        }

        if (!$t->hasForeignKey(['id_lokasi'])) {
            $t->addForeignKey('id_lokasi', 'tb_site', 'id_site', [
                'delete'     => 'SET_NULL',
                'update'     => 'CASCADE',
                'constraint' => 'fk_absen_lokasi',
            ]);
            $t->update();
        }
    }

    private function pindahDataAbsenMobile(): void
    {
        if (!$this->hasTable('tb_absen_mobile')) {
            return;
        }

        $pdo = $this->getAdapter()->getConnection();
        $rows = $this->fetchAll('SELECT * FROM tb_absen_mobile');

        $stCek = $pdo->prepare('SELECT id_absen, ket_absen FROM tb_absen WHERE id_karyawan = ? AND tgl_absen = ? LIMIT 1');
        $stUpd = $pdo->prepare(
            'UPDATE tb_absen
             SET sumber = ?, id_lokasi = ?, nama_lokasi = ?, latitude = ?, longitude = ?,
                 jarak_meter = ?, foto_selfie_path = ?, waktu_masuk = ?, waktu_keluar = ?,
                 status_kehadiran = ?, keterlambatan_menit = ?, is_tepat_waktu = ?, dibuat_pada = ?
             WHERE id_absen = ?'
        );
        $stIns = $pdo->prepare(
            'INSERT INTO tb_absen
                (id_karyawan, tgl_absen, jam_kerja, ket_absen, sumber, id_lokasi, nama_lokasi,
                 latitude, longitude, jarak_meter, foto_selfie_path, waktu_masuk, waktu_keluar,
                 status_kehadiran, keterlambatan_menit, is_tepat_waktu, dibuat_pada)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        foreach ($rows as $r) {
            $stCek->execute([$r['id_karyawan'], $r['tanggal_absen']]);
            $existing = $stCek->fetch();

            if ($existing) {
                // Satu baris per karyawan per hari — perkaya baris yang sudah ada.
                $stUpd->execute([
                    'mobile',
                    $r['id_lokasi'],
                    $r['nama_lokasi'],
                    $r['latitude'],
                    $r['longitude'],
                    $r['jarak_meter'],
                    $r['foto_selfie_path'],
                    $r['waktu_masuk'],
                    $r['waktu_keluar'],
                    $r['status_kehadiran'],
                    $r['keterlambatan_menit'],
                    $r['is_tepat_waktu'] ? 1 : 0,
                    $r['dibuat_pada'],
                    (int)$existing['id_absen'],
                ]);
            } else {
                $stIns->execute([
                    $r['id_karyawan'],
                    $r['tanggal_absen'],
                    '1',
                    'HADIR',
                    'mobile',
                    $r['id_lokasi'],
                    $r['nama_lokasi'],
                    $r['latitude'],
                    $r['longitude'],
                    $r['jarak_meter'],
                    $r['foto_selfie_path'],
                    $r['waktu_masuk'],
                    $r['waktu_keluar'],
                    $r['status_kehadiran'],
                    $r['keterlambatan_menit'],
                    $r['is_tepat_waktu'] ? 1 : 0,
                    $r['dibuat_pada'],
                ]);
            }
        }

        $this->execute('DROP TABLE tb_absen_mobile');
        echo 'Data tb_absen_mobile dipindah (' . count($rows) . ' baris), tabel dihapus.';
    }

    private function buatKembaliTbAbsenMobile(): void
    {
        if ($this->hasTable('tb_absen_mobile')) {
            return;
        }

        $absen = $this->table('tb_absen_mobile', [
            'id'        => 'id_absen',
            'collation' => 'utf8mb4_general_ci',
            'comment'   => 'Log absensi aplikasi mobile (GPS + selfie)',
        ]);
        $absen
            ->addColumn('id_karyawan', 'integer', ['null' => false])
            ->addColumn('id_lokasi', 'integer', ['null' => true, 'comment' => 'FK ke tb_site.id_site'])
            ->addColumn('tanggal_absen', 'date', ['null' => false])
            ->addColumn('waktu_masuk', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('waktu_keluar', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('nama_lokasi', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('latitude', 'double', ['null' => false, 'default' => 0])
            ->addColumn('longitude', 'double', ['null' => false, 'default' => 0])
            ->addColumn('jarak_meter', 'double', ['null' => false, 'default' => 0])
            ->addColumn('status_kehadiran', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('keterlambatan_menit', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('is_tepat_waktu', 'boolean', ['null' => false, 'default' => 1])
            ->addColumn('foto_selfie_path', 'text', ['null' => true])
            ->addColumn('dibuat_pada', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['id_karyawan', 'tanggal_absen'], ['unique' => true, 'name' => 'uq_absen_mobile_harian'])
            ->addIndex(['tanggal_absen'], ['name' => 'idx_absen_mobile_tanggal'])
            ->addIndex(['id_lokasi'], ['name' => 'idx_absen_mobile_lokasi'])
            ->addForeignKey('id_karyawan', 'tb_karyawan', 'id_karyawan', [
                'delete'     => 'CASCADE',
                'update'     => 'CASCADE',
                'constraint' => 'fk_absen_mobile_karyawan',
            ])
            ->addForeignKey('id_lokasi', 'tb_site', 'id_site', [
                'delete'     => 'SET_NULL',
                'update'     => 'CASCADE',
                'constraint' => 'fk_absen_mobile_site',
            ])
            ->create();

        // Pindahkan kembali baris absen aplikasi (bila ada).
        $pdo = $this->getAdapter()->getConnection();
        $rows = $this->fetchAll(
            "SELECT * FROM tb_absen WHERE sumber = 'mobile'"
        );
        $stIns = $pdo->prepare(
            'INSERT INTO tb_absen_mobile
                (id_karyawan, id_lokasi, tanggal_absen, waktu_masuk, waktu_keluar, nama_lokasi,
                 latitude, longitude, jarak_meter, status_kehadiran, keterlambatan_menit,
                 is_tepat_waktu, foto_selfie_path, dibuat_pada)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($rows as $r) {
            $stIns->execute([
                $r['id_karyawan'], $r['id_lokasi'], $r['tgl_absen'], $r['waktu_masuk'],
                $r['waktu_keluar'], $r['nama_lokasi'], $r['latitude'], $r['longitude'],
                $r['jarak_meter'], $r['status_kehadiran'], $r['keterlambatan_menit'],
                $r['is_tepat_waktu'] ? 1 : 0, $r['foto_selfie_path'], $r['dibuat_pada'],
            ]);
        }
    }

    private function kembaliTbAbsenMinimal(): void
    {
        $t = $this->table('tb_absen');

        if ($t->hasForeignKey(['id_lokasi'])) {
            $this->execute('ALTER TABLE tb_absen DROP FOREIGN KEY fk_absen_lokasi');
        }
        foreach ([
            'dibuat_pada', 'is_tepat_waktu', 'keterlambatan_menit', 'status_kehadiran',
            'waktu_keluar', 'waktu_masuk', 'foto_selfie_path', 'jarak_meter',
            'longitude', 'latitude', 'nama_lokasi', 'id_lokasi', 'sumber',
        ] as $col) {
            if ($t->hasColumn($col)) {
                $t->removeColumn($col);
            }
        }
        $t->update();

        // Sisa baris dari aplikasi ditandai ket HADIR + jam 1: tetap dipertahankan
        // sebagai rekap harian biasa.
    }
}
