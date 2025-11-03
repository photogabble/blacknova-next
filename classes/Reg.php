<?php
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2014 Ron Harwood and the BNT development team
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
// File: classes/Reg.php

namespace Bnt;

/**
 * BNT Registry object
 *
 * This class contains all the configuration variables for the game. These are initially
 * loaded from a `.ini` file. During installation these variables are copied to the database
 * from where this class then loads them.
 *
 * TODO: Rename class to Config?
 */
final class Reg
{
    private array $config = [];

    public function __construct()
    {
        // Get the config_values from the DB - This is a pdo operation
        $stmt = "SELECT name,value,type FROM ".Db::table('gameconfig');
        $result = Db::query($stmt);

        if ($result !== false) // If the database is not live, this will give false, and db calls will fail silently
        {
            $big_array = $result->fetchAll();
            if (!empty ($big_array))
            {
                foreach ($big_array as $row)
                {
                    $name = $row['name'];
                    $value = $row['value'];
                    settype($value, $row['type']);

                    $this->config[$name] = $value;
                }

                return;
            }
        }

        // Slurp in config variables from the ini file directly
        $ini_file = 'config/classic_config.ini.php'; // This is hard-coded for now, but when we get multiple game support, we may need to change this.
        $ini_keys = parse_ini_file($ini_file, true);
        foreach ($ini_keys as $config_category => $config_line)
        {
            foreach ($config_line as $config_key => $config_value)
            {
                $this->config[$config_key] = $config_value;
            }
        }
    }

    public function __get(string $name): mixed
    {
        return $this->config[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->config[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->config[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->config[$name]);
    }
}