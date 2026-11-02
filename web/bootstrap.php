<?php

// Shared startup for the public PHP entry points.
spl_autoload_register(static function (string $class): void {
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $class)) {
        return;
    }
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
