<?php declare(strict_types=1);
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
// Origin: classes/BntShip.php
// File: src/Models/Ship.php

namespace BlackNova\Models;

use ADORecordSet;
use BlackNova\Models\Ship\Cargo;
use BlackNova\Services\Db;
use Bnt\CalcLevels;
use Bnt\PlayerLog;

final readonly class Ship
{
    public function __construct(
        public string $name,
        public int    $sector,
        public bool   $destroyed,
        public int    $hull,
        public int    $engines,
        public int    $power,
        public int    $computer,
        public int    $sensors,
        public int    $armor,
        public int    $shields,
        public int    $beams,
        public int    $torpLaunchers,
        public int    $cloak,
        public array  $devices = [],
        public Cargo  $cargo = new Cargo(
            ore: 0,organics: 0,goods: 0,energy: 0, colonists: 0
        ),
        public bool   $onPlanet = false,
        public ?int   $planetId = null,
    )
    {
    }

    public function isDestroyed(): bool
    {
        return $this->destroyed;
    }

    public function hasDevice(string $device): bool
    {
        if (array_key_exists($device, $this->devices)) {
            if (is_bool($this->devices[$device])) {
                return $this->devices[$device];
            }

            return $this->devices[$device] >= 1;
        }

        return false;
    }

    public function getLevel(): int
    {
        $level = CalcLevels::avgTech([
            'hull' => $this->hull,
            'engines' => $this->engines,
            'computer' => $this->computer,
            'armor' => $this->armor,
            'shields' => $this->shields,
            'beams' => $this->beams,
            'torp_launchers' => $this->torpLaunchers,
        ]);

        if ($level < 8) return 0;
        if ($level < 12) return 1;
        if ($level < 16) return 2;
        if ($level < 20) return 3;

        return 4;
    }

    public function image(): string
    {
        return match($this->getLevel()) {
            0 => 'tinyship.png',
            1 => 'smallship.png',
            2 => 'mediumship.png',
            3 => 'largeship.png',
            4 => 'hugeship.png',
        };
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'level' => $this->getLevel(),
            'image' => $this->image(),
            'sector' => $this->sector,
            'destroyed' => $this->destroyed,
            'hull' => $this->hull,
            'engines' => $this->engines,
            'power' => $this->power,
            'computer' => $this->computer,
            'sensors' => $this->sensors,
            'armor' => $this->armor,
            'shields' => $this->shields,
            'beams' => $this->beams,
            'torp_launchers' => $this->torpLaunchers,
            'cloak' => $this->cloak,
            'devices' => $this->devices,
            'cargo' => $this->cargo->toArray(),
        ];
    }

    /**
     * @deprecated will be moved to ShipRepository/PlayerRepository
     * @param $db
     * @param $ship_id
     * @return void
     */
    public static function leavePlanet($db, $ship_id)
    {
        $own_pl_result = $db->Execute("SELECT * FROM ".\BlackNova\Services\Db::table('planets')." WHERE owner = ?", array($ship_id));
        Db::logDbErrors($db, $own_pl_result, __LINE__, __FILE__);

        if ($own_pl_result instanceof ADORecordSet)
        {
            while (!$own_pl_result->EOF)
            {
                $row = $own_pl_result->fields;
                $on_pl_result = $db->Execute("SELECT * FROM ".\BlackNova\Services\Db::table('ships')." WHERE on_planet = 'Y' AND planet_id = ? AND ship_id <> ?", array($row['planet_id'], $ship_id));
                Db::logDbErrors($db, $on_pl_result, __LINE__, __FILE__);
                if ($on_pl_result instanceof ADORecordSet)
                {
                    while (!$on_pl_result->EOF)
                    {
                        $cur = $on_pl_result->fields;
                        $uppl_res = $db->Execute("UPDATE ".\BlackNova\Services\Db::table('ships')." SET on_planet = 'N',planet_id = '0' WHERE ship_id = ?", array($cur['ship_id']));
                        Db::logDbErrors($db, $uppl_res, __LINE__, __FILE__);
                        PlayerLog::writeLog($db, $cur['ship_id'], LOG_PLANET_EJECT, $cur['sector'] .'|'. $row['character_name']);
                        $on_pl_result->MoveNext();
                    }
                }
                $own_pl_result->MoveNext();
            }
        }
    }
}

