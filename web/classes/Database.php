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
            $host     = getenv('DB_HOST') ?: 'localhost';
            $port     = getenv('DB_PORT') ?: '3306';
            $dbname   = getenv('DB_NAME') ?: 'payr_bcp';
            $username = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
            $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

            try {
                self::$instance = new PDO(
                    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                error_log('Database connect failed: ' . $e->getMessage());
                return null;
            }
        }
        return self::$instance;
    }
}
