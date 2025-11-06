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
// File: src/Http/Controllers/Auth/LoginController.php

namespace BlackNova\Http\Controllers\Auth;

use BlackNova\Http\Controllers\Controller;
use BlackNova\Services\Auth\AuthenticationService;
use BlackNova\Services\Auth\SessionInterface;
use Bnt\Reg;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Smarty\Smarty;

final class LoginController extends Controller
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

    public function processLogin(ServerRequestInterface $request): ResponseInterface
    {
        // If game is closed redirect to the home page
        if ($this->reg->game_closed) {
            $this->session->flash('error_message', 'The game is currently closed'); // TODO: Translate (l_login_closed_message)
            return new RedirectResponse('/');
        }

        // If already logged in, redirect to the main page
        if ($this->authService->check()) {
            return new RedirectResponse('/main');
        }

        $body = $request->getParsedBody();
        $email = $body['email'] ?? '';
        $password = $body['pass'] ?? '';

        // Validate Input
        if (empty($email) || empty($password)) {
            $this->session->flash('error_message', 'Invalid username or password'); // TODO: Translate
            return new RedirectResponse('/');
        }

        // Get client IP
        $serverParams = $request->getServerParams();
        $ipAddress = $serverParams['REMOTE_ADDR'] ?? '0.0.0.0';

        // Attempt authentication
        $result = $this->authService->attempt($email, $password, $ipAddress);

        // Handle specific error cases
        if (!$result['success']) {
            switch ($result['code']) {
                case 'ship_destroyed':
                    $this->session->flash('error_message', $result['message']);
                    $this->session->set('destroyed_ship_id', $result['player']->shipId);
                    return new RedirectResponse('/new-character');

                case 'ip_banned':
                    return $this->view('auth/player-banned.tpl', [
                        'title' => 'Access Denied',
                        'message' => $result['message']
                    ], 403);

                default:
                    $this->session->flash('error_message', $result['message']);
                    return new RedirectResponse('/');
            }
        }

        // Authentication successful, redirect to the main page
        return new RedirectResponse('/main');
    }
}
