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

use Photogabble\Tuppence\App;
use Smarty\Smarty;

define('APP_START', microtime(true));

if (!defined('APP_ROOT')) define('APP_ROOT', realpath(__DIR__ . '/../'));

include APP_ROOT . '/vendor/autoload.php';

// Load environment variables
//$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
//$dotenv->load();

// Load configuration
//$dbConfig = require __DIR__ . '/../config/database.php';
//$appConfig = require __DIR__ . '/../config/app.php';

// Create Tuppence app
$app = new App();

// Get the DI container
$container = $app->getContainer();

// Initiate database connection
\BlackNova\Services\Db::initDb(APP_ROOT . '/config/db_config.php');

// Load Configuration
$container->add('config', function () {
    return new \Bnt\Reg();
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
//// Register session manager
//$container->add(SessionManager::class, function() {
//    return new SessionManager();
//});
//
//// Register repositories
//$container->add(PlayerRepository::class, function() use ($container, $dbConfig) {
//    return new PlayerRepository(
//        $container->get('db'),
//        $dbConfig['prefix'] ?? 'bnt_'
//    );
//});
//
//// Register services
//$container->add(AuthenticationService::class, function() use ($container) {
//    return new AuthenticationService(
//        $container->get(PlayerRepository::class),
//        $container->get(SessionManager::class)
//    );
//});

function app(): App {
    return App::getInstance();
}

function config(?string $key = null, mixed $default = null): mixed {
    $app = App::getInstance();

    /** @var \Bnt\Reg $config */
    $config = $app->getContainer()->get('config');

    if (is_null($key)) return $config;
    return $config->{$key} ?? $default;
}

return $app;