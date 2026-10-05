<?php

require_once __DIR__ . '/app/config/env.php';

$appEnv = strtolower((string) env('APP_ENV', 'production'));
$isDev = $appEnv === 'development' || $appEnv === 'dev' || $appEnv === 'local';

// Development: default lokal + auto-create, cukup APP_ENV=development.
// Production: wajib isi DB_* di .env (tanpa default kredensial).
$dbHost = (string) env('DB_HOST', '127.0.0.1');
$dbPort = (string) env('DB_PORT', '3306');
$dbName = (string) env('DB_DATABASE', $isDev ? 'ipm_hris' : '');
$dbUser = (string) env('DB_USERNAME', $isDev ? 'root' : '');
$dbPass = (string) env('DB_PASSWORD', '');

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => $isDev ? 'development' : 'production',
        'production' => [
            'adapter' => 'mysql',
            'host' => $dbHost,
            'name' => $dbName,
            'user' => $dbUser,
            'pass' => $dbPass,
            'port' => $dbPort,
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => $dbHost,
            'name' => $dbName,
            'user' => $dbUser,
            'pass' => $dbPass,
            'port' => $dbPort,
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => $dbHost,
            'name' => $dbName,
            'user' => $dbUser,
            'pass' => $dbPass,
            'port' => $dbPort,
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
