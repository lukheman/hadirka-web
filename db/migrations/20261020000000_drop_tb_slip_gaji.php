<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * tb_slip_gaji tidak lagi dipakai.
 * API mobile menghitung slip langsung dari periode payroll + slip_helper.
 */
final class DropTbSlipGaji extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('tb_slip_gaji')) {
            $this->table('tb_slip_gaji')->drop()->save();
        }
    }

    public function down(): void
    {
        $this->table('tb_slip_gaji', [
            'id' => 'id_slip_gaji',
            'collation' => 'utf8mb4_general_ci',
            'comment' => 'Deprecated: slip dihitung langsung dari payroll',
        ])
            ->addColumn('id_karyawan', 'integer', ['null' => false])
            ->addColumn('periode', 'string', ['limit' => 50, 'null' => false])
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
}
