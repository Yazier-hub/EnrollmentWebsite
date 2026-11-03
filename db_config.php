<?php
// db_config.php
// Single source of truth for DB credentials.
// Environment variables take precedence over the hosting settings below.

return [
    'host'     => getenv('MYSQLHOST')     ?: (getenv('DB_HOST')     ?: '64.235.61.128'),
    'port'     => getenv('MYSQLPORT')     ?: (getenv('DB_PORT')     ?: '3306'),
    'database' => getenv('MYSQLDATABASE') ?: (getenv('DB_DATABASE') ?: 'payr_bcp'),
    'username' => getenv('MYSQLUSER')     ?: (getenv('DB_USERNAME') ?: 'payr_bestlink'),
    'password' => getenv('MYSQLPASSWORD') ?: (getenv('DB_PASSWORD') ?: '12345'),
];
