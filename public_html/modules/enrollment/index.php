<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../auth/session.php';

define('BASE_PATH', __DIR__);
spl_autoload_register(static function ($class) {
    $file = BASE_PATH . '/classes/' . $class . '.php';
    if (is_file($file)) require_once $file;
});

$page = $_GET['page'] ?? 'dashboard-overview';
if (!is_string($page) || !isset(EnrollmentPage::ROUTES[$page])) {
    $page = 'dashboard-overview';
}

$pageController = new PageController($page);
EnrollmentPage::forRoute($page)->render($pageController);
