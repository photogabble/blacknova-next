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

use BlackNova\Tests\BootsApp;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;

final class MainDashboardTest extends BootsApp
{
    public function test_main_dashboard_requires_login(): void
    {
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/main'))
            ->withMethod('GET'));
        $this->assertResponseRedirectsTo('/');
    }

    public function test_main_dashboard_returns_okay(): void
    {
        $this->seedUniverse();

        $player = $this->createTestPlayer('test@example.com', 'password123');
        $this->actingAs($player);

        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/main'))
            ->withMethod('GET'));
        $this->assertResponseOk();
    }

    public function test_main_redirects_if_player_on_planet(): void
    {
        $player = $this->createTestPlayer('test@example.com', 'password123');
        $this->playerRepository->landOnPlanet($player, 123);
        $this->actingAs($player);

        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/main'))
            ->withMethod('GET'));
        $this->assertResponseRedirectsTo('/planet.php?planet_id=123');
    }

}
