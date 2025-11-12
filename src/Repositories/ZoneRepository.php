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
// File: classes/Players/ZoneRepository.php

namespace BlackNova\Repositories;

use BlackNova\Models\Sector\ZoneSettings;
use BlackNova\Services\Db;

class ZoneRepository
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = Db::table('zones');
    }

    public function create(
        string       $name,
        ZoneSettings $settings,
        ?int         $ownerId = null,
        bool         $corpOwned = false
    ): false|int
    {
        Db::exec("
            INSERT INTO $this->tableName (
                zone_name, owner, corp_zone, allow_beacon,
                allow_attack, allow_planetattack, allow_warpedit,
                allow_planet, allow_trade, allow_defenses, max_hull
            ) VALUES (
                :name, :owner_id, :corp_zone, :allow_beacon,
                :allow_attack, :allow_planet_attack, :allow_warp_edit,
                :allow_planet, :allow_trade, :allow_defenses, :max_hull
            )
        ", [
            'name' => $name,
            'owner_id' => $ownerId ?? 0, // TODO: make column nullable
            'corp_zone' => $corpOwned ? 'Y' : 'N',

            'allow_beacon' => $settings->allowBeacon ? 'Y' : 'N',
            'allow_attack' => $settings->allowAttack ? 'Y' : 'N',
            'allow_planet_attack' => $settings->allowPlanetAttack ? 'Y' : 'N',
            'allow_warp_edit' => $settings->allowWarpEdit ? 'Y' : 'N',
            'allow_planet' => $settings->allowPlanet ? 'Y' : 'N',
            'allow_trade' => $settings->allowTrade ? 'Y' : 'N',
            'allow_defenses' => $settings->allowDefense ? 'Y' : 'N',

            'max_hull' => $settings->maxHull,
        ]);

        return Db::lastInsertId();
    }
}