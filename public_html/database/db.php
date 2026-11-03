<?php

class Database {
    // Shared hosting and environment-variable database settings.
    private $host;
    private $port;
    private $db;
    private $user;
    private $pass;
    private $conn;

    public function __construct() {
        // Load shared environment-variable settings and local defaults.
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
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->db   = $config['database'];
        $this->user = $config['username'];
        $this->pass = $config['password'];

        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";

        // Let the caller log failures and choose the appropriate response format.
        $this->conn = new PDO($dsn, $this->user, $this->pass);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getRoles() {
        $query = "SELECT role_id, role_name FROM sd_roles ORDER BY role_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(); // Defaults to FETCH_ASSOC now because of line 23
    }
    
    public function getConnection() {
        return $this->conn;
    }
}


