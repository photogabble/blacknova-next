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
// File: classes/Scheduler/SchedulerGateway.php

namespace Bnt\Scheduler; // Domain Entity organization pattern, Players objects

use BlackNova\Services\Db;

class SchedulerGateway // Gateway for SQL calls related to Players
{
    public static function selectSchedulerLastRun(): false|int
    {
        // It is possible to have this call run before the game is setup, so we need to test to ensure the db is active
        if (!Db::isActive()) return false;

        // SQL call that selects the last run of the scheduler, and only one record
        $sql = "SELECT last_run FROM ". Db::table('scheduler')." LIMIT 1";
        $stmt = Db::query($sql);
        $row = $stmt->fetchObject();

        if (is_object($row)) {
            return (int) $row->last_run; // Return the int value of the last scheduler run
        }


        return false; // If anything goes wrong, db not active, etc, return false
    }
}
