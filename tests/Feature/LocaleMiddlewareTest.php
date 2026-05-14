<?php declare(strict_types=1);
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2026 Simon Dann
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

final class LocaleMiddlewareTest extends BootsApp
{
    public function test_lang_query_param_sets_session_lang(): void
    {
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/'))
            ->withMethod('GET')
            ->withQueryParams(['lang' => 'french']));

        $this->assertSessionEquals('lang', 'french');
    }

    public function test_missing_lang_query_param_does_not_set_session_lang(): void
    {
        $this->runRequest(new ServerRequest()
            ->withUri(new Uri('/'))
            ->withMethod('GET'));

        $this->assertSessionMissingKey('lang');
    }
}
