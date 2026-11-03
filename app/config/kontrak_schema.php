<?php
// Menjaga instalasi hasil import schema lama tetap kompatibel dengan modul kontrak.
function ensure_kontrak_schema(PDO $db): void
{
    $has = $db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_template_kontrak' AND COLUMN_NAME = 'jenis_kontrak'");
    $has->execute();
    if (!(int)$has->fetchColumn()) {
        $db->exec("ALTER TABLE tb_template_kontrak ADD jenis_kontrak VARCHAR(30) NOT NULL DEFAULT 'KARYAWAN BARU' AFTER nama_kontrak");
    }
    $db->exec("CREATE TABLE IF NOT EXISTS tb_arsip_kontrak (
        id_arsip INT UNSIGNED NOT NULL AUTO_INCREMENT,
        id_karyawan INT NOT NULL,
        file VARCHAR(255) NOT NULL,
        nama_asli VARCHAR(255) NOT NULL,
        ukuran INT UNSIGNED NOT NULL DEFAULT 0,
        keterangan VARCHAR(255) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_arsip),
        KEY idx_arsip_karyawan (id_karyawan)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
