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
// File: src/Services/Auth/AuthenticationService.php
// TODO: Add translations

namespace BlackNova\Services\Auth;

use BlackNova\Models\Player;
use BlackNova\Repositories\PlayerRepository;
use BlackNova\Services\Db;
use Bnt\PlayerLog;

final class AuthenticationService
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly SessionInterface   $sessionManager
    )
    {
    }

    public function attempt(string $email, #[\SensitiveParameter] string $password, string $ipAddress): array
    {
        // Check if IP is banned
        if ($this->playerRepository->isIpBanned($ipAddress)) {
            return [
                'success' => false,
                'message' => 'Your IP address has been banned.',
                'code' => 'ip_banned'
            ];
        }

        $player = $this->playerRepository->findByEmail($email);

        if (!$player) {
            return [
                'success' => false,
                'message' => 'Invalid email or password.',
                'code' => 'invalid_credentials'
            ];
        }

        // Verify password
        if (!$player->verifyPassword($password)) {
            $this->logFailedAttempt($player->shipId, $ipAddress);
            return [
                'success' => false,
                'message' => 'Invalid email or password.',
                'code' => 'invalid_credentials'
            ];
        }

        // Check if Player's ship has been destroyed this is their game over
        if ($player->ship->isDestroyed()) {
            return [
                'success' => false,
                'message' => 'Your ship has been destroyed. You must create a new character.', // TODO l_login_died
                'code' => 'ship_destroyed',
                'player' => $player
            ];
        }

        // Update last login
        $this->playerRepository->updateLastLogin($player->shipId, $ipAddress);

        // Create session
        $this->sessionManager->login($player);

        return [
            'success' => true,
            'message' => 'Login successful.',
            'player' => $player
        ];
    }

    public function logout(): void
    {
        $this->sessionManager->logout();
    }

    public function check(): bool
    {
        return $this->sessionManager->isLoggedIn();
    }

    public function user(): ?Player
    {
        if (!$this->check()) return null;

        $userId = $this->sessionManager->getUserId();
        return $this->playerRepository->findById($userId);
    }

    private function logFailedAttempt(int $shipId, string $ipAddress): void
    {
        PlayerLog::writeLog(
            Db::connection(),
            $shipId,
            LOG_BADLOGIN,
            $ipAddress
        );

        // Note: login2.php also used AdminLog::writeLog but that feels like
        //       duplication to me, so I have left it out in this refactoring.
    }
}