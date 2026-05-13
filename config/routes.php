<?php

use BlackNova\Http\Controllers\Auth\LoginController;
use BlackNova\Http\Controllers\MainController;
use BlackNova\Http\Controllers\PagesController;
use BlackNova\Http\Middleware\AuthMiddleware;
use BlackNova\Http\Middleware\GameClosedMiddleware;
use BlackNova\Http\Middleware\LocaleMiddleware;
use BlackNova\Services\Auth\AuthenticationService;
use League\Route\RouteGroup;
use League\Route\Router;
use Photogabble\Tuppence\App;
use BlackNova\Http\Controllers\Auth\LogoutController;

return function(Router $router, App $app) {
    $router->middleware(new LocaleMiddleware);
    $router->middleware(new GameClosedMiddleware);

    // Authentication routes
    $router->map('POST', '/login', [LoginController::class, 'processLogin']);
    $router->map('GET', '/logout', [LogoutController::class, 'processLogout']);

    $router->map('GET', '/', [PagesController::class, 'homepage']);
    $router->map('GET', '/news', [PagesController::class, 'news']);

    $router
        ->group('/', function (RouteGroup $router) use ($app) {
            $router->middleware(new AuthMiddleware($app->getContainer()->get(AuthenticationService::class)));
            $router->map('GET', '/main', [MainController::class, 'dashboard']);
        });
};
