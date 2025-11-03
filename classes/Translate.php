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
// File: classes/Translate.php

namespace Bnt;

class Translate
{
    private static array $langvars = [];

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
            $ini_file = './languages/' . $language . '.ini.php';
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
        else
        {
            // Populate the $langvars array
            foreach ($categories as $category)
            {
                // Select from the database and return the value of the language variables requested, but do not use caching
                $query = "SELECT name, value FROM ".Db::table('languages')." WHERE category = :category AND section = :language;";
                $result = Db::prepare($query);

                // It is possible to use a single prepare, and multiple executes, but it makes the logic of this section much less clear.
                $result->bindParam(':category', $category);
                $result->bindParam(':language', $language);
                $result->execute();

                while (($row = $result->fetch()) !== false)
                {
                    self::$langvars[$row['name']] = $row['value'];
                }
            }

            return self::$langvars;
        }
    }
}
