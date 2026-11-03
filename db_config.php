<?php
// db_config.php
// Single source of truth for DB credentials.
// Reads Railway/Heroku/Docker env vars first, falls back to local.

return [
    'host'     => getenv('MYSQLHOST')     ?: (getenv('DB_HOST')     ?: 'localhost'),
    'port'     => getenv('MYSQLPORT')     ?: (getenv('DB_PORT')     ?: '3306'),
    'database' => getenv('MYSQLDATABASE') ?: (getenv('DB_DATABASE') ?: 'payr_bcp'),
    'username' => getenv('MYSQLUSER')     ?: (getenv('DB_USERNAME') ?: 'payr_bestlink'),
    'password' => getenv('MYSQLPASSWORD') ?: (getenv('DB_PASSWORD') ?: '12345'),
];
