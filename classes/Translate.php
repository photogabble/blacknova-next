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
// File: classes/Translate.php

namespace Bnt;

use BlackNova\Services\Db;

class Translate
{
    private static array $langvars = [];

    private static array $loaded = [];

    public static string $language = 'english';

    public static function get(string $key): string
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) throw new \InvalidArgumentException('Invalid key format');


        // If already loaded, return the value
        if (array_key_exists($parts[0], self::$loaded) && array_key_exists($parts[1], self::$loaded[$parts[0]])){
            return self::$loaded[$parts[0]][$parts[1]];
        }

        $language = session()->get('lang', config()->default_lang);

        // Attempt to load the value from the database
        if (Db::isActive()) {
            $query = "SELECT name, value FROM ".Db::table('languages')." WHERE category = :category AND section = :language;";
            $result = Db::prepare($query);

            $result->bindParam(':category', $parts[0]);
            $result->bindParam(':language', $language);
            $result->execute();

            $list = [];

            while (($row = $result->fetch()) !== false)
            {
                $list[$row['name']] = $row['value'];
            }

            if (count($list) > 0) {
                self::$loaded[$parts[0]] = $list;
                return self::$loaded[$parts[0]][$parts[1]] ?? $key;
            }

            return $key;
        }

        // Fall back to the ini files
        $ini_file = APP_ROOT . '/languages/' . $language . '.ini';
        foreach (parse_ini_file($ini_file, true) as $category => $values) {
            if (!isset(self::$loaded[$category])) self::$loaded[$category] = [];
            foreach ($values as $name => $value) {
                self::$loaded[$category][$name] = $value;
            }
        }

        return self::$loaded[$parts[0]][$parts[1]] ?? $key;
    }

    public static function load($db = null, $language = null, $categories = null)
    {
        // Check if all supplied args are valid, if not return false.
        if (is_null($db) || is_null($language) || !is_array($categories))
        {
            return false;
        }

        if (!Db::isActive())
        {
            // Slurp in language variables from the ini file directly
            $ini_file = APP_ROOT . '/languages/' . $language . '.ini';
            $ini_keys = parse_ini_file($ini_file, true);
            foreach ($ini_keys as $config_category => $config_line)
            {
                foreach ($config_line as $config_key => $config_value)
                {
                    self::$langvars[$config_key] = $config_value;
                }
            }

            return self::$langvars;
        }

        // Populate the $langvars array
        $placeholders = array_reduce($categories, function ($carry) {
            $index = count($carry);
            $carry[] = ":cat{$index}";
            return $carry;
        }, []);

        $placeholderString = implode(',', $placeholders);

        $query = "SELECT name, value FROM ".Db::table('languages')." WHERE category IN ($placeholderString) AND section = :language;";
        $result = Db::prepare($query);

        foreach ($categories as $index => $category){
            $result->bindValue(":cat{$index}", $category);
        }

        $result->bindParam(':language', $language);
        $result->execute();

        while (($row = $result->fetch()) !== false)
        {
            self::$langvars[$row['name']] = $row['value'];
        }

        return self::$langvars;
    }
}
