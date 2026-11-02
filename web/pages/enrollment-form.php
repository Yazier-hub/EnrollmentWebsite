<?php

require_once __DIR__ . '/../bootstrap.php';

$controller = new EnrollmentFormController();
$controller->handleRequest();
PageView::render('enrollment-form', $controller->viewData());
