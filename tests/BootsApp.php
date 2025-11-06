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
// File: tests/BootsApp.php

namespace BlackNova\Tests;

use BlackNova\Services\Auth\SessionInterface;
use BlackNova\Services\Db;
use Photogabble\Tuppence\App;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

abstract class BootsApp extends TestCase
{
    protected App $app;

    protected TestEmitter $emitter;

    protected TestSessionManager $session;

    // NOTE: set this to false in tests that need to run in a transaction.
    protected bool $useTransactions = true;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Initialise the database schema, this gives us a clean slate for each test run,
        // each test runs within its own transaction which is rolled back at teardown.
        DatabaseSetup::initialize();
    }

    public function setUp(): void
    {
        $this->cleanSession();
        $this->bootApp();

        if ($this->useTransactions && Db::isActive()) {
            Db::beginTransaction();
        }
    }

    public function tearDown(): void
    {
        if ($this->useTransactions && Db::isActive() && Db::inTransaction()) {
            Db::rollback();
        }

        $this->cleanSession();
    }

    protected function cleanSession(): void
    {
        // Clear the session data array
        //$_SESSION = [];

        // If the session is active, destroy it
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // In CLI mode, we may need to manually unset the session cookie
        if (isset($_COOKIE[session_name()])) {
            unset($_COOKIE[session_name()]);
        }
    }

    protected function bootApp(): void
    {
        // Setting $emitter which is used by bootstrap.php to replace the default emitter with TestEmitter.
        $this->emitter = $emitter = new TestEmitter();
        $this->app = include __DIR__ . '/../src/bootstrap.php';

        // Replace SessionManager with a test-friendly version that doesn't use real sessions
        $this->session = new TestSessionManager();
        $this->app->getContainer()->extend(SessionInterface::class)->setConcrete(function() {
            return $this->session;
        });
    }

    protected function runRequest(ServerRequest $request): string
    {
        $this->app->run($request);
        return (string)$this->emitter->getResponse()->getBody();
    }

    protected function assertResponseOk(): void
    {
        $this->assertEquals(200, $this->emitter->getResponse()->getStatusCode());
    }

    protected function assertResponseCodeEquals($code = 200): void
    {
        $this->assertEquals($code, $this->emitter->getResponse()->getStatusCode());
    }

    protected function assertResponseContains(string $needle): void
    {
        $this->assertStringContainsString($needle, (string)$this->emitter->getResponse()->getBody());
    }

    protected function assertResponseRedirectsTo(string $expectedLocation, int $expectedCode = 302): void
    {
        $response = $this->emitter->getResponse();

        $this->assertEquals(
            $expectedCode,
            $response->getStatusCode(),
            "Expected redirect status code {$expectedCode}, got {$response->getStatusCode()}"
        );

        $this->assertTrue(
            $response->hasHeader('Location'),
            'Response does not have a Location header'
        );

        $this->assertEquals(
            $expectedLocation,
            $response->getHeaderLine('Location'),
            "Expected redirect to '{$expectedLocation}', got '{$response->getHeaderLine('Location')}'"
        );
    }

    protected function assertSessionHasKey(string $key): void
    {
        $this->assertArrayHasKey(
            $key,
            $this->session->getSessionData(),
            "Session does not contain key '{$key}'"
        );
    }

    protected function assertSessionEquals(string $key, mixed $expectedValue): void
    {
        $this->assertSessionHasKey($key);
        $this->assertEquals(
            $expectedValue,
            $this->session->get($key),
            "Session value for key '{$key}' does not match expected value"
        );
    }

    protected function assertSessionFlashHasKey(string $key): void
    {
        $this->assertArrayHasKey(
            '_flash',
            $this->session->getSessionData(),
            'Session does not contain _flash array'
        );

        $this->assertArrayHasKey(
            $key,
            $this->session->get('_flash'),
            "Flash message with key '{$key}' not found in session"
        );
    }

    protected function assertSessionFlashEquals(string $key, mixed $expectedValue): void
    {
        $this->assertSessionFlashHasKey($key);

        $flash = $this->session->get('_flash');

        $this->assertEquals(
            $expectedValue,
            $flash[$key],
            "Flash message '{$key}' does not match expected value"
        );
    }
}