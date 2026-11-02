<?php

require_once __DIR__ . '/../bootstrap.php';

$controller = new EventController();
$controller->handleRequest();
PageView::render('events', $controller->viewData());
