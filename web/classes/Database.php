<?php
// classes/Database.php

class Database
{
    private static ?PDO $instance = null;
    private static bool $attempted = false;

    private function __construct() {}

    public static function getConnection(): ?PDO
    {
        if (!self::$attempted) {
            self::$attempted = true;
            try {
                $configPath = dirname(__DIR__, 2) . '/db_config.php';
                if (!is_readable($configPath)) {
                    throw new RuntimeException('Database configuration is missing or unreadable: ' . $configPath);
                }
                $config = require $configPath;
                if (!is_array($config)) {
                    throw new RuntimeException('Database configuration must return an array: ' . $configPath);
                }
                foreach (['host', 'port', 'database', 'username', 'password'] as $key) {
                    if (!array_key_exists($key, $config)) {
                        throw new RuntimeException('Database configuration is missing key: ' . $key);
                    }
                }

                $dsn = "mysql:host={$config['host']};port={$config['port']};"
                    . "dbname={$config['database']};charset=utf8mb4";
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (Throwable $e) {
                error_log('Database connect failed: ' . $e->getMessage());
                return null;
            }
        }
        return self::$instance;
    }
}
