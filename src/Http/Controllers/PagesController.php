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
// File: src/Http/Controllers/PagesController.php

namespace BlackNova\Http\Controllers;

use Bnt\Languages;
use Bnt\News;
use Bnt\Translate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PagesController extends Controller
{
    public function homepage(): ResponseInterface
    {
        return $this->view('index.tpl', [
            'title' => Translate::get('index.l_welcome_bnt'),
            'list_of_langs' => Languages::listAvailable('english'),

            'link_forums' => $this->reg->link_forums,
            'admin_mail' => $this->reg->admin_mail,

            'body_class' => 'index',
            'news_ticker_active' => false,

            'error_message' => session()->getFlash('error_message'),
        ]);
    }

    public function news(ServerRequestInterface $request): ResponseInterface
    {
        $startDate = $request->getQueryParams()['startdate'] ?? date('Y/m/d');
        $validFormat = preg_match('/([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/', $startDate, $regs);

        if ($validFormat !=1 || !checkdate((int)$regs[2], (int)$regs[3], (int)$regs[1]))
        {
            $startDate = date('Y/m/d');
        }

        return $this->view('news.tpl', [
            'title' => Translate::get('news.l_news_title'),

            'day' => $startDate,
            'previous_day' => News::previousDay($startDate),
            'next_day' => News::nextDay($startDate),
            'days_news' => News\NewsGateway::selectNewsByDay($startDate),

            'body_class' => 'bnt',
            'news_ticker_active' => true,
        ]);
    }

}