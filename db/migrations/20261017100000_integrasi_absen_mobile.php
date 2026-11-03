<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Integrasi aplikasi absensi mobile (GPS + selfie + geofence) ke database website.
 *
 * Latar belakang: aplikasi Flutter "Absensi" sebelumnya berdiri sendiri dengan
 * database db_absen_ipm + backend Node.js. Mulai sekarang seluruh datanya
 * disatukan ke database website (satu sistem penuh):
 *
 *  - tb_site          : + kolom geofence (kode_lokasi, latitude, longitude,
 *                       radius_meter, deskripsi_lokasi, is_aktif).
 *  - tb_absen_mobile  : BARU — log absensi GPS + selfie dari aplikasi.
 *                       (tb_absen lama tetap dipakai rekap harian/payroll.)
 *  - tb_notifikasi    : BARU — notifikasi per karyawan untuk aplikasi.
 *  - tb_slip_gaji     : BARU — slip gaji yang dirilis HR untuk aplikasi.
 *  - tb_pengajuan_pinjaman: foto_wajah & foto_id_card dilonggarkan (NULL)
 *                       karena pengajuan dari aplikasi mobile tidak
 *                       mengirim foto (beda dengan alur web lama).
 */
final class IntegrasiAbsenMobile extends AbstractMigration
{
    public function change(): void
    {
        // ---------------------------------------------------------------
        // 1. tb_site — kolom geofence aplikasi absensi
        // ---------------------------------------------------------------
        $site = $this->table('tb_site');

        if (!$site->hasColumn('kode_lokasi')) {
            $site->addColumn('kode_lokasi', 'string', [
                'limit' => 50,
                'null' => true,
                'after' => 'nama_site',
                'comment' => 'Kode lokasi untuk aplikasi absensi',
            ]);
        }
        if (!$site->hasColumn('latitude')) {
            $site->addColumn('latitude', 'double', [
                'null' => true,
                'after' => 'lokasi',
                'comment' => 'Titik pusat geofence (latitude)',
            ]);
        }
        if (!$site->hasColumn('longitude')) {
            $site->addColumn('longitude', 'double', [
                'null' => true,
                'after' => 'latitude',
                'comment' => 'Titik pusat geofence (longitude)',
            ]);
        }
        if (!$site->hasColumn('radius_meter')) {
            $site->addColumn('radius_meter', 'double', [
                'default' => 80,
                'null' => false,
                'after' => 'longitude',
                'comment' => 'Radius absensi valid (meter)',
            ]);
        }
        if (!$site->hasColumn('deskripsi_lokasi')) {
            $site->addColumn('deskripsi_lokasi', 'text', [
                'null' => true,
                'after' => 'radius_meter',
                'comment' => 'Deskripsi lokasi untuk aplikasi',
            ]);
        }
        if (!$site->hasColumn('is_aktif')) {
            $site->addColumn('is_aktif', 'boolean', [
                'default' => 1,
                'null' => false,
                'after' => 'deskripsi_lokasi',
                'comment' => 'Tampil di aplikasi absensi',
            ]);
        }

        $site->update();

        // ---------------------------------------------------------------
        // 2. tb_absen_mobile — log absensi GPS + selfie dari aplikasi
        // ---------------------------------------------------------------
        if (!$this->hasTable('tb_absen_mobile')) {
            $absen = $this->table('tb_absen_mobile', [
                'id' => 'id_absen',
                'collation' => 'utf8mb4_general_ci',
                'comment' => 'Log absensi aplikasi mobile (GPS + selfie)',
            ]);

            $absen
                ->addColumn('id_karyawan', 'integer', ['null' => false])
                ->addColumn('id_lokasi', 'integer', ['null' => true, 'comment' => 'FK ke tb_site.id_site'])
                ->addColumn('tanggal_absen', 'date', ['null' => false])
                ->addColumn('waktu_masuk', 'string', ['limit' => 20, 'null' => false, 'comment' => 'Format: HH:MM WITA'])
                ->addColumn('waktu_keluar', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('nama_lokasi', 'string', ['limit' => 100, 'null' => false, 'comment' => 'Snapshot nama site saat absen'])
                ->addColumn('latitude', 'double', ['null' => false, 'default' => 0])
                ->addColumn('longitude', 'double', ['null' => false, 'default' => 0])
                ->addColumn('jarak_meter', 'double', ['null' => false, 'default' => 0, 'comment' => 'Jarak ke titik site (meter)'])
                ->addColumn('status_kehadiran', 'string', ['limit' => 50, 'null' => false])
                ->addColumn('keterlambatan_menit', 'integer', ['null' => false, 'default' => 0])
                ->addColumn('is_tepat_waktu', 'boolean', ['null' => false, 'default' => 1])
                ->addColumn('foto_selfie_path', 'text', ['null' => true])
                ->addColumn('dibuat_pada', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['id_karyawan', 'tanggal_absen'], ['unique' => true, 'name' => 'uq_absen_mobile_harian'])
                ->addIndex(['tanggal_absen'], ['name' => 'idx_absen_mobile_tanggal'])
                ->addIndex(['id_lokasi'], ['name' => 'idx_absen_mobile_lokasi'])
                ->addForeignKey('id_karyawan', 'tb_karyawan', 'id_karyawan', [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                    'constraint' => 'fk_absen_mobile_karyawan',
                ])
                ->addForeignKey('id_lokasi', 'tb_site', 'id_site', [
                    'delete' => 'SET_NULL',
                    'update' => 'CASCADE',
                    'constraint' => 'fk_absen_mobile_site',
                ])
                ->create();
        }

        // ---------------------------------------------------------------
        // 3. tb_notifikasi — notifikasi per karyawan untuk aplikasi
        // ---------------------------------------------------------------
        if (!$this->hasTable('tb_notifikasi')) {
            $notif = $this->table('tb_notifikasi', [
                'id' => 'id_notifikasi',
                'collation' => 'utf8mb4_general_ci',
                'comment' => 'Notifikasi in-app untuk aplikasi karyawan',
            ]);

            $notif
                ->addColumn('id_karyawan', 'integer', ['null' => false])
                ->addColumn('judul', 'string', ['limit' => 150, 'null' => false])
                ->addColumn('pesan', 'text', ['null' => false])
                ->addColumn('kategori', 'string', ['limit' => 50, 'null' => false, 'default' => 'presensi'])
                ->addColumn('is_dibaca', 'boolean', ['null' => false, 'default' => 0])
                ->addColumn('dibuat_pada', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['id_karyawan', 'is_dibaca'], ['name' => 'idx_notifikasi_karyawan'])
                ->addForeignKey('id_karyawan', 'tb_karyawan', 'id_karyawan', [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                    'constraint' => 'fk_notifikasi_karyawan',
                ])
                ->create();
        }

        // ---------------------------------------------------------------
        // 4. tb_slip_gaji — slip yang dirilis HR untuk aplikasi
        // ---------------------------------------------------------------
        if (!$this->hasTable('tb_slip_gaji')) {
            $slip = $this->table('tb_slip_gaji', [
                'id' => 'id_slip_gaji',
                'collation' => 'utf8mb4_general_ci',
                'comment' => 'Slip gaji yang dirilis ke aplikasi karyawan',
            ]);

            $slip
                ->addColumn('id_karyawan', 'integer', ['null' => false])
                ->addColumn('periode', 'string', ['limit' => 50, 'null' => false, 'comment' => 'Contoh: Agustus 2026'])
                ->addColumn('gaji_pokok', 'double', ['null' => false, 'default' => 0])
                ->addColumn('tunjangan', 'double', ['null' => false, 'default' => 0])
                ->addColumn('potongan', 'double', ['null' => false, 'default' => 0])
                ->addColumn('total_diterima', 'double', ['null' => false, 'default' => 0])
                ->addColumn('status_rilis', 'string', ['limit' => 20, 'null' => false, 'default' => 'DITERBITKAN'])
                ->addColumn('catatan', 'text', ['null' => true])
                ->addColumn('dibuat_pada', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['id_karyawan', 'periode'], ['name' => 'idx_slip_gaji_karyawan'])
                ->addForeignKey('id_karyawan', 'tb_karyawan', 'id_karyawan', [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                    'constraint' => 'fk_slip_gaji_karyawan',
                ])
                ->create();
        }

        // ---------------------------------------------------------------
        // 5. tb_pengajuan_pinjaman — foto dilonggarkan untuk pengajuan app
        // ---------------------------------------------------------------
        $pinjaman = $this->table('tb_pengajuan_pinjaman');
        if ($pinjaman->hasColumn('foto_wajah')) {
            $pinjaman->changeColumn('foto_wajah', 'string', ['limit' => 255, 'null' => true]);
        }
        if ($pinjaman->hasColumn('foto_id_card')) {
            $pinjaman->changeColumn('foto_id_card', 'string', ['limit' => 255, 'null' => true]);
        }
        $pinjaman->update();
    }
}
