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
// File: classes/Players/TradeRouteRepository.php

namespace BlackNova\Repositories;

use BlackNova\Models\Planet\Planet;
use BlackNova\Models\Player;
use BlackNova\Models\Sector\Sector;
use BlackNova\Models\TradeRoute;
use BlackNova\Services\Db;

class TradeRouteRepository
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = Db::table('traderoutes');
    }

    public function getPlayerTradeRoutesForSector(Player $player, int $sectorId): array
    {
        $planetTable = Db::table('planets');

        $found = [
            // Player Sector -> Sector Trade Routes
            ...Db::select("
                SELECT $this->tableName.* FROM $this->tableName 
                WHERE source_type = 'P' 
                    AND source_id = :sector_id 
                    AND owner = :player_id
            ", [
                'sector_id' => $sectorId,
                'player_id' => $player->shipId,
            ]),

            // Sector -> Player Planet Trade Routes
            ...Db::select("
                SELECT $this->tableName.* FROM $this->tableName
                JOIN $planetTable ON $planetTable.planet_id = $this->tableName.source_id
                WHERE source_type = 'L'
                    AND $planetTable.sector_id = :sector_id 
                    AND $this->tableName.owner = :player_id
            ", [
                'sector_id' => $sectorId,
                'player_id' => $player->shipId,
            ]),

            // Sector -> Team Planet Trade Routes
            ...Db::select("
                SELECT $this->tableName.* FROM $this->tableName
                JOIN $planetTable ON $planetTable.planet_id = $this->tableName.source_id
                WHERE source_type = 'C'
                    AND $planetTable.sector_id = :sector_id 
                    AND $this->tableName.owner = :player_id
            ", [
                'sector_id' => $sectorId,
                'player_id' => $player->shipId,
            ]),

            // TODO: Sector Defense Trade route
            // There existed a query for this in main.php using source_type = 'D' I assume it is
            // for automated deployment of defences to the target sector. Once refactoring is
            // complete this can be revisited.
        ];

        // Get all sector ids where source_type or target_type is 'P' or 'D'
        $sectorIds = array_keys(array_reduce($found, function(array $carry, array $row) {
            if ($row['source_type'] === 'P' || $row['source_type'] === 'D') $carry[$row['source_id']] = true;
            if ($row['dest_type'] === 'P' || $row['dest_type'] === 'D') $carry[$row['dest_id']] = true;
            return $carry;
        }, []));

        // Get all planet ids where source_type is 'L' or 'C'
        $planetIds = array_keys(array_reduce($found, function(array $carry, array $row) {
            if ($row['source_type'] === 'L' || $row['source_type'] === 'C') $carry[$row['source_id']] = true;
            if ($row['dest_type'] === 'L' || $row['dest_type'] === 'C') $carry[$row['dest_id']] = true;

            return $carry;
        }, []));

        $planets = array_reduce(new PlanetRepository()->findByIds($planetIds), function (array $carry, Planet $planet) {
            $carry[$planet->id] = $planet;
            return $carry;
        }, []);

        $sectors = array_reduce(new SectorRepository()->findByIds($sectorIds), function(array $carry, Sector $sector) {
            $carry[$sector->id] = $sector;
            return $carry;
        }, []);

        return array_map(fn($row) => $this->mapToTradeRoute($row, $planets, $sectors), $found);
    }

    private function mapToTradeRoute(array $row, array $planets, array $sectors): TradeRoute
    {
        return new TradeRoute(
            id: $row['traderoute_id'],
            source: match ($row['source_type']) {
                'P', 'D' => $sectors[$row['source_id']],
                'L', 'C' => $planets[$row['source_id']],
            },
            destination: match ($row['dest_type']) {
                'P', 'D' => $sectors[$row['dest_id']],
                'L', 'C' => $planets[$row['dest_id']],
            },
            ownerId: $row['owner'],
            sourceType: $row['source_type'],
            destinationType: $row['dest_type'],
            moveType: $row['move_type'],
            circuit: (int)$row['circuit'],
        );
    }
}