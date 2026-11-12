<?php

function ensure_pengalaman_schema(PDO $db): void
{
    $db->exec(
        "CREATE TABLE IF NOT EXISTS tb_template_pengalaman (
            id_template INT UNSIGNED NOT NULL AUTO_INCREMENT,
            nama_template VARCHAR(150) NOT NULL,
            file VARCHAR(255) NOT NULL,
            nama_asli VARCHAR(255) NOT NULL,
            ukuran INT UNSIGNED NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id_template)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        "CREATE TABLE IF NOT EXISTS tb_arsip_pengalaman (
            id_arsip INT UNSIGNED NOT NULL AUTO_INCREMENT,
            id_karyawan INT NULL,
            file VARCHAR(255) NOT NULL,
            nama_asli VARCHAR(255) NOT NULL,
            ukuran INT UNSIGNED NOT NULL DEFAULT 0,
            keterangan VARCHAR(255) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id_arsip),
            KEY idx_pengalaman_karyawan (id_karyawan)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $column = $db->prepare(
        "SELECT IS_NULLABLE
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'tb_arsip_pengalaman'
           AND COLUMN_NAME = 'id_karyawan'"
    );
    $column->execute();

    if ($column->fetchColumn() === 'NO') {
        $db->exec('ALTER TABLE tb_arsip_pengalaman MODIFY id_karyawan INT NULL');
    }

    $nameColumn = $db->prepare(
        "SELECT COUNT(*)
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'tb_arsip_pengalaman'
           AND COLUMN_NAME = 'nama_karyawan'"
    );
    $nameColumn->execute();

    if (!(int) $nameColumn->fetchColumn()) {
        $db->exec(
            "ALTER TABLE tb_arsip_pengalaman
             ADD nama_karyawan VARCHAR(150) NULL AFTER id_karyawan"
        );
    }

    $db->exec(
        "CREATE TABLE IF NOT EXISTS tb_nomor_surat_pengalaman (
            id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            terakhir INT UNSIGNED NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $db->exec(
        'INSERT IGNORE INTO tb_nomor_surat_pengalaman (id, terakhir) VALUES (1, 0)'
    );
}
