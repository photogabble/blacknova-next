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

use BlackNova\Repositories\PlayerRepository;
use BlackNova\Services\Auth\SessionInterface;
use BlackNova\Services\Auth\SessionManager;
use BlackNova\Services\Db;
use Bnt\News\NewsGateway;
use Bnt\Reg;
use Bnt\Scheduler\SchedulerGateway;
use Bnt\Translate;
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
Db::initDb(APP_ROOT . '/config/db_config.php');

// Load Configuration
$container->addShared(Reg::class, function () {
    return new Reg(APP_ROOT . '/config/classic_config.ini.php');
});

// Register template engine
$container->add(Smarty::class, function () {
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
$container->add(SessionInterface::class, function () {
    return new SessionManager();
});

// Load routes
$routes = require APP_ROOT . '/config/routes.php';
$routes($app->getRouter(), $app);

// Helpers
if (!function_exists('app')) {
    function app(?string $abstract = null): mixed
    {
        if (!is_null($abstract)) return App::getInstance()->getContainer()->get($abstract);
        return App::getInstance();
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        /** @var Reg $config */
        $config = App::getInstance()
            ->getContainer()
            ->get(Reg::class);

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

if (!function_exists('view')) {
    function view($view, $data = []): string
    {
        /** @var Reg $reg */
        $reg = app()->getContainer()->get(Reg::class);

        /** @var Smarty $smarty */
        $smarty = app()->getContainer()->get(Smarty::class);

        // Default Data originally obtained from `footer_t.php`
        // TODO: replace hard coded values with dynamic values

        $dbActive = Db::isActive();
        // TODO: create lang(...) helper
        $langvars = Translate::load(Db::connection(), 'english', [
            'main',
            'login',
            'logout',
            'index',
            'common',
            'regional',
            'footer',
            'global_includes',
            'news',
            'admin',
            'combat',
        ]);

        // Make the SF logo a little bit larger to balance the extra line from
        // the benchmark for page generation.
        if ($reg->footer_show_debug) {
            $sf_logo_type = '14';
            $sf_logo_width = "150";
            $sf_logo_height = "40";
        } else {
            $sf_logo_type = '11';
            $sf_logo_width = "120";
            $sf_logo_height = "30";
        }

        $secondsLeft = 0;
        $displayUpdateTicker = false;

        // The last run is an (int) count of players currently logged in or false if DB is not active
        if ($lastRun = SchedulerGateway::selectSchedulerLastRun()) {
            $secondsLeft = ($reg->sched_ticks * 60) - (time() - $lastRun);
            $displayUpdateTicker = true;
        }

        $online = 0;
        $newsTicker = [];
        // Suppress the news ticker on the IGB and index pages by setting this to false
        $newsTickerActive = $data['news_ticker_active'] ?? true;

        if ($dbActive) {
            $online = PlayerRepository::selectPlayersLoggedIn(
                date("Y-m-d H:i:s", time() - 5 * 60), // Five minutes ago
                date("Y-m-d H:i:s", time())
            );

            if ($newsTickerActive) {
                $news = NewsGateway::selectNewsByDay(date("Y-m-d"));
                if (count($news) == 0) {
                    $newsTicker = [[
                        'url' => null,
                        'text' => $langvars['l_news_none'],
                        'type' => null,
                        'delay' => 5,
                    ]];
                } else {
                    $newsTicker = array_reduce($news, function ($carry, $item) {
                        $carry[] = [
                            'url' => "/news",
                            'text' => $item['headline'],
                            'type' => $item['news_type'],
                            'delay' => 5,
                        ];
                        return $carry;
                    }, []);

                    $newsTicker[] = [
                        'url' => null,
                        'text' => 'End of News', // TODO translate
                        'type' => null,
                        'delay' => 5,
                    ];
                }
            }
        }

        $default = [
            'body_class' => 'bnt',

            // TODO: add helper function to get current language
            'lang' => 'english',
            'link' => '',

            'update_ticker' => [
                'display' => $displayUpdateTicker,
                'seconds_left' => $secondsLeft,
                'sched_ticks' => $reg->sched_ticks
            ],

            'players_online' => $online,

            'suppress_logo' => false,
            'sf_logo_type' => $sf_logo_type,
            'sf_logo_height' => $sf_logo_height,
            'sf_logo_width' => $sf_logo_width,
            'sf_logo_link' => '',

            // These are only displayed if footer_show_debug is true
            'elapsed' => round(microtime(true) - APP_START, 3),
            'mem_peak_usage' => floor(memory_get_peak_usage() / 1024),
            'num_queries' => count(Db::getQueryLog()),

            'footer_show_debug' => $reg->footer_show_debug,
            'cur_year' => date('Y'),

            // TODO: same as template_dir below, not sure if this is needed anymore?
            'template' => 'classic',
        ];

        if ($newsTickerActive) $smarty->assign('news', $newsTicker);

        // TODO: remove need for `variables` key, we should just be passing the merged data array
        $smarty->assign('variables', array_merge($default, $data));
        // TODO: move assets into public directory
        $smarty->assign('template_dir', 'templates/classic');
        $smarty->assign('langvars', $langvars);

        return $smarty->fetch($view);
    }
}

if (!function_exists('dd')) {
    function dd(...$args)
    {
        dump(...$args);
        die();
    }
}

return $app;