<?php

namespace BlackNova\Http\Controllers;

use BlackNova\Services\Db;
use Bnt\News\NewsGateway;
use Bnt\Players\PlayersGateway;
use Bnt\Reg;
use Bnt\Translate;
use Laminas\Diactoros\Response\HtmlResponse;
use Smarty\Smarty;

abstract class Controller
{
    protected Smarty $smarty;
    protected Reg $reg;

    public function __construct(Smarty $smarty, Reg $reg)
    {
        $this->reg = $reg;
        $this->smarty = $smarty;
    }

    public function view($view, $data = []): HtmlResponse
    {
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
            'news'
        ]);

        // Make the SF logo a little bit larger to balance the extra line from
        // the benchmark for page generation.
        if ($this->reg->footer_show_debug) {
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
        if ($lastRun = \Bnt\Scheduler\SchedulerGateway::selectSchedulerLastRun()) {
            $secondsLeft = ($this->reg->sched_ticks * 60) - (time() - $lastRun);
            $displayUpdateTicker = true;
        }

        $online = 0;
        $newsTicker = [];
        // Suppress the news ticker on the IGB and index pages by setting this to false
        $newsTickerActive = $data['news_ticker_active'] ?? true;

        if ($dbActive) {
            $online = PlayersGateway::selectPlayersLoggedIn(
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
            // TODO: add helper function to get current language
            'lang' => 'english',
            'link' => '',

            'update_ticker' => [
                'display' => $displayUpdateTicker,
                'seconds_left' => $secondsLeft,
                'sched_ticks' => $this->reg->sched_ticks
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

            'footer_show_debug' => $this->reg->footer_show_debug,
            'cur_year' => date('Y'),

            // TODO: same as template_dir below, not sure if this is needed anymore?
            'template' => 'classic',
        ];

        if ($newsTickerActive) $this->smarty->assign('news', $newsTicker);

        // TODO: remove need for `variables` key, we should just be passing the merged data array
        $this->smarty->assign('variables', array_merge($default, $data));

        // TODO: move assets into public directory
        $this->smarty->assign('template_dir', 'templates/classic');

        $this->smarty->assign('langvars', $langvars);
        return new HtmlResponse($this->smarty->fetch($view));
    }
}