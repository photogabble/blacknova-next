<?php

use BlackNova\Http\Controllers\Auth\LoginController;
use BlackNova\Http\Controllers\PagesController;
use BlackNova\Http\Middleware\AuthMiddleware;
use BlackNova\Http\Middleware\LocaleMiddleware;
use BlackNova\Services\Auth\AuthenticationService;
use League\Route\RouteGroup;
use League\Route\Router;
use Photogabble\Tuppence\App;

return function(Router $router, App $app) {
    $router->middleware(new LocaleMiddleware);

    // Authentication routes
    $router->map('POST', '/login', [LoginController::class, 'processLogin']);

    $router->map('GET', '/', [PagesController::class, 'homepage']);
    $router->map('GET', '/news', [PagesController::class, 'news']);
};