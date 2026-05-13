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
// File: tests/TestCase.php

namespace BlackNova\Tests;

use BlackNova\Services\Db;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected static bool $useDatabase = false;

    // NOTE: set this to false in tests that need to run in a transaction.
    protected static bool $useTransactions = true;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (static::$useDatabase) {
            // Initialise the database schema, this gives us a clean slate for each test run,
            // each test runs within its own transaction which is rolled back at teardown.
            DatabaseSetup::initialize();
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (static::$useDatabase && static::$useTransactions && Db::isActive()) {
            Db::beginTransaction();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (static::$useDatabase && static::$useTransactions && Db::inTransaction()) {
            Db::rollback();
        }
    }
}
