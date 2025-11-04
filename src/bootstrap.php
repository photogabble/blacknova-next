<?php

use Photogabble\Tuppence\App;
use Smarty\Smarty;

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

// Register database connection
$container->add('db', function () {
    return \BlackNova\Services\Db::initDb();
});
//
//// Register template engine
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

return $app;