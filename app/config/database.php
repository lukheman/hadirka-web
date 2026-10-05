<?php
// app/config/database.php
// Helper koneksi terpusat (PDO, MySQL/MariaDB): development vs production.
//
// - APP_ENV=development -> default lokal (127.0.0.1/root//ipm_hris),
//   database otomatis dibuat bila belum ada.
// - APP_ENV=production  -> konfigurasi WAJIB eksplisit di .env (fail-fast).
//
// Konvensi PDO proyek:
// - $koneksi adalah PDO (ERRMODE_EXCEPTION, FETCH_ASSOC, EMULATE_PREPARES=false).
// - Query SELALU pakai prepare+execute([...]) bila ada input variabel.
// - SELECT satu baris: $st = $koneksi->prepare(...); $st->execute([...]); $row = $st->fetch();
// - SELECT banyak:      $st->execute([...]); $rows = $st->fetchAll();
// - INSERT/UPDATE/DELETE: $st->execute([...]); $n = $st->rowCount();
// - Last ID: (int) $koneksi->lastInsertId(). Transaksi: beginTransaction/commit/rollBack.
// - Error: try/catch (PDOException). Duplikat: ($e->errorInfo[1] ?? null) === 1062.
// - num_rows mysqli TIDAK ada padanan langsung: pakai fetch()/fetchAll()+count().

require_once __DIR__ . '/env.php';

function is_dev_env(): bool
{
    $env = strtolower((string) env('APP_ENV', 'production'));
    return $env === 'development' || $env === 'dev' || $env === 'local';
}

/**
 * Ambil konfigurasi DB sesuai APP_ENV.
 * @return array{host:string,port:int,user:string,pass:string,name:string,is_dev:bool}
 */
function db_config(): array
{
    $is_dev = is_dev_env();

    if ($is_dev) {
        return [
            'host'   => (string) env('DB_HOST', '127.0.0.1'),
            'port'   => (int) env('DB_PORT', 3306),
            'user'   => (string) env('DB_USERNAME', 'root'),
            'pass'   => (string) env('DB_PASSWORD', ''),
            'name'   => (string) env('DB_DATABASE', 'ipm_hris'),
            'is_dev' => true,
        ];
    }

    return [
        'host'   => (string) env('DB_HOST', '127.0.0.1'),
        'port'   => (int) env('DB_PORT', 3306),
        'user'   => (string) env('DB_USERNAME', ''),
        'pass'   => (string) env('DB_PASSWORD', ''),
        'name'   => (string) env('DB_DATABASE', ''),
        'is_dev' => false,
    ];
}

function pdo_dsn(array $cfg, bool $withDb = true): string
{
    $dsn = 'mysql:host=' . $cfg['host'] . ';port=' . $cfg['port'];
    if ($withDb) {
        $dsn .= ';dbname=' . $cfg['name'];
    }
    return $dsn . ';charset=utf8mb4';
}

function pdo_options(): array
{
    return [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
}

/**
 * Konek ke MySQL/MariaDB via PDO. Di development, database otomatis dibuat
 * bila belum ada sehingga `php -S localhost:8000` langsung jalan.
 *
 * @throws RuntimeException bila konfigurasi/koneksi gagal.
 */
function db_connect(): PDO
{
    $cfg = db_config();

    if (!$cfg['is_dev'] && ($cfg['user'] === '' || $cfg['name'] === '')) {
        throw new RuntimeException(
            'Database configuration missing. Set DB_USERNAME and DB_DATABASE in .env (production).'
        );
    }

    if ($cfg['name'] === '') {
        throw new RuntimeException('Database configuration missing. Set DB_DATABASE in .env.');
    }

    try {
        return new PDO(pdo_dsn($cfg), $cfg['user'], $cfg['pass'], pdo_options());
    } catch (PDOException $e) {
        // 1049 = Unknown database. Di development: buatkan lalu sambung ulang.
        $isUnknownDb = ($e->errorInfo[1] ?? null) == 1049
            || str_contains(strtolower($e->getMessage()), 'unknown database');

        if ($cfg['is_dev'] && $isUnknownDb) {
            try {
                $tmp = new PDO(pdo_dsn($cfg, false), $cfg['user'], $cfg['pass'], pdo_options());
                $safe = '`' . str_replace('`', '``', $cfg['name']) . '`';
                $tmp->exec("CREATE DATABASE IF NOT EXISTS {$safe} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                $tmp = null;
                return new PDO(pdo_dsn($cfg), $cfg['user'], $cfg['pass'], pdo_options());
            } catch (PDOException $e2) {
                error_log('Dev DB auto-create failed: ' . $e2->getMessage());
                throw new RuntimeException(
                    'Database connection failed. Pastikan MariaDB/MySQL jalan di ' .
                    $cfg['host'] . ':' . $cfg['port'] . ' dan user "' . $cfg['user'] . '" benar.'
                );
            }
        }

        error_log('Database connection failed: ' . $e->getMessage());
        if ($cfg['is_dev']) {
            throw new RuntimeException(
                'Database connection failed. Pastikan MariaDB/MySQL jalan ' .
                '(' . $cfg['host'] . ':' . $cfg['port'] . ', user "' . $cfg['user'] . '"). ' .
                'Asli: ' . $e->getMessage()
            );
        }
        throw new RuntimeException('Database connection failed.');
    }
}
