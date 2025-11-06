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
// File:

namespace BlackNova\Tests\Feature;

use BlackNova\Repositories\BanRepository;
use BlackNova\Repositories\PlayerRepository;
use BlackNova\Tests\BootsApp;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;

final class AuthenticationTest extends BootsApp
{

    private PlayerRepository $playerRepository;
    private BanRepository $banRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->playerRepository = $this->app->getContainer()
            ->get(PlayerRepository::class);

        $this->banRepository = $this->app->getContainer()
            ->get(BanRepository::class);
    }

    public function test_homepage_returns_okay(): void
    {
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/'))
            ->withMethod('GET'));
        $this->assertResponseOk();
    }

    public function test_login_redirects_to_home_when_game_is_closed(): void
    {
        // Set the game_closed flag to true
        config()->game_closed = true;

        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => 'test@example.com',
                'pass' => 'password123'
            ]));

        $this->assertResponseRedirectsTo('/');
        $this->assertSessionFlashEquals('error_message', 'The game is currently closed');
    }

    public function test_login_redirects_to_main_when_already_authenticated(): void
    {
        $this->session->set('logged_in', true);
        $this->session->set('user_id', 123);

        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => 'test@example.com',
                'pass' => 'password123'
            ]));

        $this->assertResponseRedirectsTo('/main');
    }

    public function test_post_login_request_validates_input():void
    {
        // Missing email and password field
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/login'))
            ->withMethod('POST'));

        $this->assertResponseRedirectsTo('/');
        $this->assertSessionFlashEquals('error_message', 'Invalid username or password');

        // Missing email field
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'pass' => 'password123'
            ]));

        $this->assertResponseRedirectsTo('/');
        $this->assertSessionFlashEquals('error_message', 'Invalid username or password');

        // Missing password field
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => 'test@example.com'
            ]));

        $this->assertResponseRedirectsTo('/');
        $this->assertSessionFlashEquals('error_message', 'Invalid username or password');

        // Empty email
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => '',
                'pass' => 'password123'
            ]));

        $this->assertResponseRedirectsTo('/');
        $this->assertSessionFlashEquals('error_message', 'Invalid username or password');

        // Empty password
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => 'test@example.com',
                'pass' => ''
            ]));

        $this->assertResponseRedirectsTo('/');
        $this->assertSessionFlashEquals('error_message', 'Invalid username or password');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => 'invalid@example.com',
                'pass' => 'wrongpassword'
            ]));

        $this->assertResponseRedirectsTo('/');
        $this->assertSessionFlashEquals('error_message', 'Invalid email or password.');
    }

    public function test_login_redirects_to_new_character_when_ship_destroyed(): void
    {
        // Set up a player with destroyed ship in database
        // This test assumes you have a way to create test data
        $id = $this->createTestPlayerWithDestroyedShip('destroyed@example.com', 'password123');

        $this->runRequest(new ServerRequest(['REMOTE_ADDR' => '127.0.0.1'])
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => 'destroyed@example.com',
                'pass' => 'password123'
            ]));

        $this->assertResponseRedirectsTo('/new-character');
        $this->assertSessionHasKey('destroyed_ship_id');
        $this->assertSessionEquals('destroyed_ship_id', $id);
        $this->assertSessionFlashHasKey('error_message');
    }

    public function test_login_shows_banned_page_when_ip_is_banned(): void
    {
        // Set up a player with banned IP in database
        $this->createTestPlayerWithBannedIP('banned@example.com', 'password123', '192.168.1.100');

        $this->runRequest(new ServerRequest(['REMOTE_ADDR' => '192.168.1.100'])
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => 'banned@example.com',
                'pass' => 'password123'
            ]));

        $this->assertResponseCodeEquals(403);
        $this->assertResponseContains('Player Banned');
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        // Create a valid test player
        $id = $this->createTestPlayer('valid@example.com', 'password123');

        $this->runRequest(new ServerRequest(['REMOTE_ADDR' => '127.0.0.1'])
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => 'valid@example.com',
                'pass' => 'password123'
            ]));

        $this->assertResponseRedirectsTo('/main');
        $this->assertSessionEquals('user_id', $id);
        $this->assertSessionEquals('logged_in', true);
    }

    public function test_login_uses_default_ip_when_remote_addr_missing(): void
    {
        $id = $this->createTestPlayer('testip@example.com', 'password123');

        $this->runRequest(new ServerRequest() // No REMOTE_ADDR
            ->withUri(new Uri('/login'))
            ->withMethod('POST')
            ->withParsedBody([
                'email' => 'testip@example.com',
                'pass' => 'password123'
            ]));

        $this->assertResponseRedirectsTo('/main');

        // Should still process with default IP 0.0.0.0
        $player = $this->playerRepository->findById($id);
        $this->assertEquals('0.0.0.0', $player->ipAddress);
    }

    private function createTestPlayer(string $email, string $password, string $ipAddress = '127.0.0.1'): int
    {
        return $this->playerRepository->create(
            email: $email,
            characterName: 'Test Character',
            shipName: 'Test Ship',
            passwordHash: password_hash($password, PASSWORD_DEFAULT),
            ipAddress: $ipAddress,
        );
    }

    private function createTestPlayerWithDestroyedShip(string $email, string $password): int
    {
        $id = $this->createTestPlayer($email, $password);
        $this->playerRepository->destroyShipById($id);

        return $id;
    }

    private function createTestPlayerWithBannedIP(string $email, string $password, string $ipAddress): int
    {
        $id = $this->createTestPlayer($email, $password, $ipAddress);
        $this->banRepository->createIpBan($ipAddress, 'test');
        return $id;
    }

}
