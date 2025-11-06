<?php

use BlackNova\Http\Controllers\Auth\LoginController;
use BlackNova\Http\Controllers\PagesController;
use BlackNova\Http\Middleware\LocaleMiddleware;
use League\Route\Router;

return function(Router $router) {
    $router->middleware(new LocaleMiddleware);

    // Authentication routes
    $router->map('POST', '/login', [LoginController::class, 'processLogin']);;

    $router->map('GET', '/', [PagesController::class, 'homepage']);
    $router->map('GET', '/news', [PagesController::class, 'news']);
};