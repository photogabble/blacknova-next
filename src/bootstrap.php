<?php declare(strict_types=1);
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2025 Simon Dann
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: src/bootstrap.php

use BlackNova\Services\Auth\SessionInterface;
use BlackNova\Services\Auth\SessionManager;
use Photogabble\Tuppence\App;
use Smarty\Smarty;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;

define('APP_START', microtime(true));

if (!defined('APP_ROOT')) define('APP_ROOT', realpath(__DIR__ . '/../'));

include APP_ROOT . '/vendor/autoload.php';
include APP_ROOT . '/global_defines.php';

// Load environment variables
//$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
//$dotenv->load();

// Load configuration
//$dbConfig = require __DIR__ . '/../config/database.php';
//$appConfig = require __DIR__ . '/../config/app.php';

/** @var EmitterInterface|null $emitter set if in UnitTesting */
$app = new App($emitter ?? null);

// Get the DI container
$container = $app->getContainer();

// Initiate database connection
\BlackNova\Services\Db::initDb(APP_ROOT . '/config/db_config.php');

// Load Configuration
$container->addShared(\Bnt\Reg::class, function (){
    return new \Bnt\Reg(APP_ROOT . '/config/classic_config.ini.php');
});

// Register template engine
$container->add(Smarty::class, function() {
    if (!is_dir(APP_ROOT . '/templates')) {
        die('Error: The templates/ subdirectory under the main BNT directory does not exist. Please create it.');
    }

    if (!is_writable(APP_ROOT . '/templates/_cache')) {
        die('Error: The templates/_cache directory needs to have its permissions set to be writable by the web server user.');
    }

    if (!is_writable(APP_ROOT . '/templates/_compile')) {
        die('Error: The templates/_compile directory needs to have its permissions set to be writable by the web server user.');
    }

    // TODO: add theme support

    $smarty = new Smarty();
    $smarty->setCompileDir(APP_ROOT . '/templates/_compile');
    $smarty->setCacheDir(APP_ROOT . '/templates/_cache');
    $smarty->setConfigDir(APP_ROOT . '/templates/_configs');
    $smarty->setTemplateDir(APP_ROOT . '/templates/classic');

    $smarty->enableSecurity();

    return $smarty;
});

//
// Register session manager
$container->add(SessionInterface::class, function() {
    return new SessionManager();
});

// Load routes
$routes = require APP_ROOT . '/config/routes.php';
$routes($app->getRouter(), $app);

if (!function_exists('app')) {
    function app(?string $abstract = null): mixed
    {
        if (!is_null($abstract)) return App::getInstance()->getContainer()->get($abstract);
        return App::getInstance();
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed {
        /** @var \Bnt\Reg $config */
        $config = App::getInstance()
            ->getContainer()
            ->get(\Bnt\Reg::class);

        if (is_null($key)) return $config;
        return $config->{$key} ?? $default;
    }
}

if (!function_exists('session')) {
    function session(): SessionManager
    {
        return App::getInstance()
            ->getContainer()
            ->get(SessionManager::class);
    }
}

if (!function_exists('dd')) {
    function dd(...$args) {
        dump(...$args);
        die();
    }
}

return $app;