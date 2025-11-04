<?php

use BlackNova\Http\Controllers\Auth\LoginController;
use League\Route\Router;

return function(Router $router) {
    // Authentication routes
    $router->map('GET', '/login', [LoginController::class, 'showLoginForm']);

    $router->map('GET', '/', [PagesController::class, 'homepage']);
    $router->map('GET', '/news', [PagesController::class, 'news']);
};