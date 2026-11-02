<?php

require_once __DIR__ . '/../bootstrap.php';

$controller = new ContactController();
$controller->handleRequest();
PageView::render('contact', $controller->viewData());
