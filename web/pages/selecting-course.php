<?php

require_once __DIR__ . '/../bootstrap.php';

$controller = new CourseSelectionController();
$controller->handleRequest();
PageView::render('selecting-course', $controller->viewData());
