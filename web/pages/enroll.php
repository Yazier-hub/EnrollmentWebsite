<?php

require_once __DIR__ . '/../bootstrap.php';

$controller = new EnrollmentController();
$controller->handleRequest();
PageView::render('enroll', $controller->viewData());
