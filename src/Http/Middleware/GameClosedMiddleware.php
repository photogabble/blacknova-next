<?php
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2014 Ron Harwood and the BNT development team
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
// Origin: classes/Game.php
// File: src/Http/Middleware/GameClosedMiddleware.php

namespace BlackNova\Http\Middleware;

use Bnt\Footer;
use Bnt\Header;

class GameClosedMiddleware
{
    public static function isGameClosed($pdo_db, $bntreg, $lang, $template, $langvars)
    {

        if ($bntreg->game_closed)
        {
            $title = $langvars['l_login_closed_message'];
            Header::display($pdo_db, $lang, $template, $title);
            echo $langvars['l_login_closed_message'];
            Footer::display($pdo_db, $lang, $bntreg, $template);
            die();
        }
        else
        {
            return false;
        }
    }
}
