<?php

/**
 * Bootstrap the application
 * @var \Photogabble\Tuppence\App $app
 */
$app = require __DIR__ . '/../src/bootstrap.php';

// Load routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app->getRouter());

// Run the application
$app->run();