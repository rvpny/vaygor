<?php
/**
 * Koneksi database + pembaca .env.
 * Pemakaian: $pdo = db();
 */

function env_all(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }
    $file = dirname(__DIR__) . '/.env';
    $cfg = is_file($file) ? (parse_ini_file($file, false, INI_SCANNER_TYPED) ?: []) : [];
    return $cfg;
}

function env(string $key, $default = null)
{
    $cfg = env_all();
    return $cfg[$key] ?? $default;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        env('DB_HOST', '127.0.0.1'),
        env('DB_PORT', '3306'),
        env('DB_DATABASE', 'db_futsal')
    );

    $pdo = new PDO($dsn, (string) env('DB_USERNAME', 'root'), (string) env('DB_PASSWORD', ''), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}
