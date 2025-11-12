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
// File: src/Http/Controllers/Controller.php

namespace BlackNova\Http\Controllers;

use Bnt\Reg;
use Laminas\Diactoros\Response\HtmlResponse;
use Smarty\Smarty;

abstract class Controller
{
    protected Smarty $smarty;
    protected Reg $reg;

    public function __construct()
    {
        $this->reg = app()->getContainer()->get(Reg::class);
        $this->smarty = app()->getContainer()->get(Smarty::class);
    }

    public function view($view, $data = [], int $status = 200): HtmlResponse
    {
        return new HtmlResponse(view($view, $data), $status);
    }
}