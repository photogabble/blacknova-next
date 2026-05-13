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
// File: src/Http/Controllers/Auth/LogoutController.php

namespace BlackNova\Http\Controllers\Auth;

use Bnt\PlayerLog;
use Bnt\Translate;
use BlackNova\Services\Db;
use BlackNova\Services\Score;
use BlackNova\Http\Controllers\Controller;
use BlackNova\Services\Auth\AuthenticationService;
use BlackNova\Services\Auth\SessionInterface;
use Bnt\Reg;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Smarty\Smarty;

final class LogoutController extends Controller
{
    private SessionInterface $session;
    private AuthenticationService $authService;

    public function __construct(
        AuthenticationService $auth,
        SessionInterface $session,
        Smarty         $smarty,
        Reg            $reg
    ){
        $this->authService = $auth;
        $this->session = $session;
        parent::__construct($smarty, $reg);
    }

    public function processLogout(ServerRequestInterface $request): ResponseInterface
    {
        // If already logged out, redirect to the homepage
        if (!$this->authService->check()) {
            return new RedirectResponse('/');
        }

        // Get client IP
        $serverParams = $request->getServerParams();
        $ipAddress = $serverParams['REMOTE_ADDR'] ?? '0.0.0.0';

        $characterName = 'Unknown';
        $score = 0;

        if ($user = $this->authService->user()) {
            PlayerLog::writeLog(
                Db::connection(),
                $user->shipId,
                LOG_LOGOUT,
                $ipAddress
            );

            $characterName = $user->characterName;
            $score = Score::updateScore($user->shipId);
        }

        $this->authService->logout();

        $text = __('logout.l_logout_text', [
            'name' => $characterName,
            'here' => '<a href="/">' . __('common.l_here') . '</a>',
        ]);

        return $this->view('logout.tpl', [
            'character_name' => $characterName,
            'current_score' => $score,

            'l_logout_text_replaced' => $text,
            'linkback' => ["fulltext" => __('global_funcs.l_global_mlogin'), "link" => "/"],
        ]);
    }
}
