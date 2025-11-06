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
// File: tests/DatabaseSetup.php

namespace BlackNova\Tests;

use BlackNova\Services\Db;
use Bnt\Schema;
use RuntimeException;

class DatabaseSetup
{
    private static bool $initialized = false;

    /**
     * Initialise the test database schema
     * This runs once for the entire test suite
     */
    public static function initialize(): void
    {
        if (self::$initialized) return;

        Db::initDb(__DIR__ . '/../config/db_config.php');

        echo "\nSetting up test database schema...\n";

        // Destroy existing tables
        $destroyResults = Schema::destroy(__DIR__ . '/../schema/mysql');

        $failedDestroys = array_filter($destroyResults, fn($r) => $r['result'] !== true && $r['result'] !== 'Skipped - Persistent table');
        if (!empty($failedDestroys)) {
            echo "Warning: Some tables failed to drop:\n";
            foreach ($failedDestroys as $result) {
                echo "  - {$result['name']}: {$result['result']}\n";
            }
        }

        // Create fresh tables from schema
        $createResults = Schema::create(__DIR__ . '/../schema/mysql');

        $failedCreates = array_filter($createResults, fn($r) => $r['result'] !== true);
        if (!empty($failedCreates)) {
            $errorMsg = "Failed to create database schema:\n";
            foreach ($failedCreates as $result) {
                $errorMsg .= "  - {$result['name']}: {$result['result']}\n";
            }
            throw new RuntimeException($errorMsg);
        }

        echo "Database schema initialized successfully (" . count($createResults) . " tables created)\n\n";

        self::$initialized = true;
    }

    /**
     * Reset to ensure the schema is rebuilt on the next test run
     * Useful for development/debugging
     */
    public static function reset(): void
    {
        self::$initialized = false;
    }
}