<?php

namespace BlackNova\Http\Controllers;

use Laminas\Diactoros\Response\HtmlResponse;
use Smarty\Smarty;

abstract class Controller
{
    protected Smarty $smarty;

    public function __construct(Smarty $smarty)
    {

        $this->smarty = $smarty;
    }

    public function view($view, $data = []): HtmlResponse
    {
        $this->smarty->assign($data);
        return new HtmlResponse($this->smarty->fetch($view));
    }
}