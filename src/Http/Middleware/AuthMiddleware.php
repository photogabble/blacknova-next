<?php declare(strict_types=1);
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2014 Ron Harwood and the BNT development team
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
// Origin: classes/Login.php
// File: src/Http/Middleware/AuthMiddleware.php

namespace BlackNova\Http\Middleware;

use BlackNova\Models\Player;
use BlackNova\Services\Auth\AuthenticationService;
use Bnt\Game;
use Bnt\Ship;
use Bnt\Translate;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    private AuthenticationService $auth;

    public function __construct(AuthenticationService $auth) {
        $this->auth = $auth;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->auth->check()) return $handler->handle($request);

        // Note: the Login class this middleware originated from also checked if the
        //       game was closed and if the player has been banned. The Game Closed
        //       check should be its own Middleware, and banning will log the player
        //       out, with them seeing the ban message upon logging back in.

        session()->flash('error_message', 'You must be logged in to access this page.');
        return new RedirectResponse('/');
    }

    public static function checkLogin($pdo_db, $lang, $langvars, $bntreg, $template)
    {
        // Database driven language entries
        $langvars = Translate::load($pdo_db, $lang, array('login', 'global_funcs', 'common', 'footer', 'self_destruct'));

        // Check if game is closed - Ignore the false return if it is open
        Game::isGameClosed($pdo_db, $bntreg, $lang, $template, $langvars);

        // Handle authentication check - Will die if fails, or return correct playerinfo
        $playerinfo = Player::HandleAuth($pdo_db, $lang, $langvars, $bntreg, $template);

        // Establish timestamp for interval in checking bans
        $stamp = date('Y-m-d H:i:s');
        $timestamp['now']  = (int) strtotime($stamp);
        $timestamp['last'] = (int) strtotime($playerinfo['last_login']);

        // Check for ban - Ignore the false return if not
        Player::HandleBan($pdo_db, $lang, $timestamp, $template, $playerinfo);

        // Check for destroyed ship - Ignore the false return if not
        //Ship::isDestroyed($pdo_db, $lang, $bntreg, $langvars, $template, $playerinfo);

        return true;
    }
}
