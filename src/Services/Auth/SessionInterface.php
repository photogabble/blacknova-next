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
// File: src/Services/Auth/SessionInterface.php

namespace BlackNova\Services\Auth;

use BlackNova\Models\Player;

interface SessionInterface
{
    /**
     * Log in a player and store their information in the session
     */
    public function login(Player $player): void;

    /**
     * Log out the current user and destroy the session
     */
    public function logout(): void;

    /**
     * Check if a user is currently logged in
     */
    public function isLoggedIn(): bool;

    /**
     * Get the current user's ID
     */
    public function getUserId(): ?int;

    /**
     * Get a value from the session
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a value in the session
     */
    public function set(string $key, mixed $value): void;

    /**
     * Set a flash message (available only for the next request)
     */
    public function flash(string $key, mixed $value): void;

    /**
     * Get and remove a flash message
     */
    public function getFlash(string $key, mixed $default = null): mixed;
}