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
// File: sched_tow.php

if (strpos($_SERVER['PHP_SELF'], 'sched_tow.php')) // Prevent direct access to this file
{
    die('Blacknova Traders error: You cannot access this file directly.');
}

echo "<strong>ZONES</strong><br><br>";
echo "Towing bigger players out of restricted zones...";
$num_to_tow = 0;
do
{
    $res = $db->Execute("SELECT ship_id,character_name,hull,sector,".\Bnt\Db::table('universe').".zone_id,max_hull FROM ".\Bnt\Db::table('ships').",".\Bnt\Db::table('universe').",".\Bnt\Db::table('zones')." WHERE sector=sector_id AND ".\Bnt\Db::table('universe').".zone_id=".\Bnt\Db::table('zones').".zone_id AND max_hull<>0 AND ((".\Bnt\Db::table('ships').".hull + ".\Bnt\Db::table('ships').".engines + ".\Bnt\Db::table('ships').".computer + ".\Bnt\Db::table('ships').".beams + ".\Bnt\Db::table('ships').".torp_launchers + ".\Bnt\Db::table('ships').".shields + ".\Bnt\Db::table('ships').".armor)/7) >max_hull AND ship_destroyed='N'");
    Bnt\Db::logDbErrors($db, $res, __LINE__, __FILE__);
    if ($res)
    {
        $num_to_tow = $res->RecordCount();
        echo "<br>$num_to_tow players to tow:<br>";
        while (!$res->EOF)
        {
            $row = $res->fields;
            echo "...towing $row[character_name] out of $row[sector] ...";
            $newsector = Bnt\Rand::betterRand(0, $sector_max - 1);
            echo " to sector $newsector.<br>";
            $query = $db->Execute("UPDATE ".\Bnt\Db::table('ships')." SET sector = ?, cleared_defences=' ' WHERE ship_id=?", array($newsector, $row['ship_id']));
            Bnt\Db::logDbErrors($db, $query, __LINE__, __FILE__);
            Bnt\PlayerLog::writeLog($db, $row['ship_id'], LOG_TOW, "$row[sector]|$newsector|$row[max_hull]");
            Bnt\LogMove::writeLog($db, $row['ship_id'], $newsector);
            $res->MoveNext();
        }
    }
    else
    {
        echo "<br>No players to tow.<br>";
    }
} while ($num_to_tow);

echo "<br>";
$multiplier = 0; // No need to run this again
?>
