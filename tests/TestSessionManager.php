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
// File: tests/TestSessionManager.php

namespace BlackNova\Tests;

use BlackNova\Models\Player;
use BlackNova\Services\Auth\SessionInterface;

class TestSessionManager implements SessionInterface
{
    private array $data = [];

    public function login(Player $player): void
    {
        $this->data['user_id'] = $player->shipId;
        $this->data['logged_in'] = true;
    }

    public function logout(): void
    {
        $this->data = [];
    }

    public function isLoggedIn(): bool
    {
        return isset($this->data['logged_in']) && $this->data['logged_in'] === true;
    }

    public function getUserId(): ?int
    {
        return $this->data['user_id'] ?? null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function flash(string $key, mixed $value): void
    {
        $flash = $this->get('_flash', []);
        $flash[$key] = $value;
        $this->set('_flash', $flash);
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $flash = $this->get('_flash', []);
        $value = $flash[$key] ?? $default;
        unset($flash[$key]);
        return $value;
    }

    public function getSessionData(): array
    {
        return $this->data;
    }
}