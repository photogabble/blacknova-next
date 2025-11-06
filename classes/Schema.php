<?php
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2014 Ron Harwood and the BNT development team
// Copyright (C) 2025 Simon Dann
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
// File: classes/Schema.php

namespace Bnt;

use BlackNova\Services\Db;
use DirectoryIterator;

class Schema
{
    public static function destroy(string $schemaDirectory): array
    {
        $i = 0;

        $schema_files = new DirectoryIterator($schemaDirectory);
        $destroy_table_results = [];

        foreach ($schema_files as $schema_filename)
        {
            $table_timer = new Timer;
            $table_timer->start(); // Start benchmarking

            if ($schema_filename->isFile() && $schema_filename->getExtension() == 'sql' && !strpos($schema_filename, '-seq'))
            {
                // Routine to handle persistent database tables. If a SQL schema file starts with persist-, then it is a persistent table. Fix the name.
                $persist_file = (mb_substr($schema_filename, 0, 8) === 'persist-');
                if ($persist_file)
                {
                    $tablename = mb_substr($schema_filename, 8, -4);
                }
                else
                {
                    $tablename = mb_substr($schema_filename, 0, -4);
                }

                if (!$persist_file)
                {
                    if (Db::exec('DROP TABLE ' . Db::table($tablename)))
                    {
                        $destroy_table_results[$i]['result'] = true;
                    } else {
                        $errorinfo = Db::connection()->errorInfo();
                        $destroy_table_results[$i]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                    }
                } else {
                    $destroy_table_results[$i]['result'] = 'Skipped - Persistent table';
                }

                $destroy_table_results[$i]['name'] = Db::table($tablename);
                $table_timer->stop();
                $destroy_table_results[$i]['time'] = $table_timer->elapsed();
                $i++;
            }
        }

        return $destroy_table_results;
    }

    public static function create(string $schemaDirectory): array
    {
        $i = 0;
        define('PDO_SUCCESS', (string) '00000'); // PDO gives an error code of string 00000 if successful. Not extremely helpful.
        $schema_files = new DirectoryIterator($schemaDirectory);

        // New SQL Schema table creation
        $create_table_results = array();

        foreach ($schema_files as $schema_filename)
        {
            $table_timer = new Timer;
            $table_timer->start(); // Start benchmarking

            if ($schema_filename->isFile() && $schema_filename->getExtension() == 'sql')
            {
                // Routine to handle persistent database tables. If a SQL schema file starts with persist-, then it is a persistent table. Fix the name.
                $persist_file = (mb_substr($schema_filename, 0, 8) === 'persist-');
                if ($persist_file)
                {
                    $tablename = mb_substr($schema_filename, 8, -4);
                }
                else
                {
                    $tablename = mb_substr($schema_filename, 0, -4);
                }

                // Slurp the SQL call from schema, and turn it into an SQL string
                $sql_query = file_get_contents($schemaDirectory . '/' . $schema_filename);

                // Replace the default prefix (bnt_) with the chosen table prefix from the game.
                $sql_query = preg_replace('/bnt_/', Db::$prefix, $sql_query);

                // TODO: Remove all comments from SQL
                // TODO: Test handling invalid SQL to ensure it hits the error logger below AND the visible output during running
                Db::prepare($sql_query)->execute();

                if (Db::connection()->errorCode() !== PDO_SUCCESS) {
                    $errorinfo = Db::connection()->errorInfo();
                    $create_table_results[$i]['result'] = $errorinfo[1] . ': ' . $errorinfo[2];
                } else {
                    $create_table_results[$i]['result'] = true;
                }

                $create_table_results[$i]['name'] = Db::table($tablename);
                $table_timer->stop();
                $create_table_results[$i]['time'] = $table_timer->elapsed();
                $i++;
            }
        }

        return $create_table_results;
    }
}
