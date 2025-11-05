<?php
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
// File: classes/Db.php
//
// Class for managing the database inside BNT

namespace BlackNova\Services;

use Bnt\AdminLog;
use PDO;
use PDOException;

class Db
{
    private static ?PDO $connection = null;
    public static string $type = '';
    public static string $prefix = '';
    private static bool $logErrors = true;
    private static bool $isActive = false;

    /**
     * Check if the database can be connected to and the game has been installed.
     * @return bool
     */
    public static function isActive(): bool
    {
        if (self::$isActive) return true;
        try {
            $pdo = self::connection();
            $results = $pdo->query("SELECT * FROM " . self::$prefix . "gameconfig LIMIT 1");

            self::$isActive = $results !== false && $results->rowCount() > 0;
            return self::$isActive;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Initialize and return the database connection
     *
     * @param string|null $configPath
     * @return PDO
     */
    public static function initDb(?string $configPath = './config/db_config.php'): PDO
    {
        require $configPath;

        try {
            if ($db_type === 'postgres9') {
                $port = $db_port ?? 5432;
                $pdo = new PDO(
                    "pgsql:host={$db_host};port={$port};dbname={$db_name}",
                    $db_user,
                    $db_pwd
                );
                self::$type = 'pgsql';
            } else {
                $pdo = new PDO(
                    "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4",
                    $db_user,
                    $db_pwd
                );
                self::$type = 'mysql';
            }

            // Disable emulated prepares so that we get true prepared statements
            // These are slightly slower, but also far safer in a number of cases that matter
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            self::$prefix = $db_prefix;
            self::$connection = $pdo;

            return $pdo;
        } catch (PDOException $e) {
            $err_msg = 'The Kabal Invasion - General error: Unable to connect to the ' . $db_type .
                ' Database.<br> Database Error: ' . $e->getMessage() . "<br>\n";
            die($err_msg);
        }
    }

    /**
     * Get the database connection instance
     *
     * @return PDO
     */
    public static function connection(): PDO
    {
        if (self::$connection === null) {
            self::initDb();
        }

        return self::$connection;
    }

    /**
     * Get the full table name with prefix
     */
    public static function table(string $tableName): string
    {
        return self::$prefix . $tableName;
    }

    public static function query(string $sql): \PDOStatement
    {
        try {
            return self::connection()->query($sql);
        } catch (PDOException $e) {
            self::handleException($e, $sql);
            throw $e; // Re-throw so calling code can handle it
        }
    }

    public static function exec(string $sql, array $params = []): int
    {
        $stmt = self::prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, match (gettype($value)) {
                'boolean' => PDO::PARAM_BOOL,
                'integer' => PDO::PARAM_INT,
                'NULL' => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            });
        }
        return $stmt->execute();
    }

    public static function prepare(string $sql): \PDOStatement
    {
        try {
            return self::connection()->prepare($sql);
        } catch (PDOException $e) {
            self::handleException($e, $sql);
            throw $e;
        }
    }

    public static function setErrorLogging(bool $enabled): void
    {
        self::$logErrors = $enabled;
    }

    private static function handleException(PDOException $e, string $query): void
    {
        if (!self::$logErrors) {
            return;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $trace[2] ?? $trace[1] ?? [];

        $file = $caller['file'] ?? 'unknown';
        $line = $caller['line'] ?? 0;
        $safePage = htmlentities($_SERVER['PHP_SELF'] ?? '', ENT_HTML5, 'UTF-8');

        $textError = sprintf(
            "Database error in %s on line %d (called from: %s)\nError: %s\nQuery: %s",
            basename($file),
            $line,
            $safePage,
            $e->getMessage(),
            $query
        );

        // Log to file or database if possible
        try {
            error_log($textError);

            // Only try to write to AdminLog if DB is active
            if (self::isActive()) {
                AdminLog::writeLog(self::connection(), LOG_RAW, $textError);
            }
        } catch (\Exception $logException) {
            // If logging fails, at least log to PHP error log
            error_log("Failed to log database error: " . $logException->getMessage());
        }
    }

    /**
     * Kept for backward compatibility during refactoring.
     * @deprecated Use exception handling instead
     */
    public static function logDbErrors($db, string $query, int $line, string $page): string|bool
    {
        return true;
    }
}

