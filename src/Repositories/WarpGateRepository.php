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
// File: classes/Players/WarpGateRepository.php

namespace BlackNova\Repositories;

use BlackNova\Services\Db;

class WarpGateRepository
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = Db::table('links');
    }

    /**
     * Returns an array of sector ids able to be navigated to from the given sector id.
     * In the future I would like this to return a collection of WarpGate models instead
     * which makes sense once I add the concept of certain warp gates having a limit on
     * how many times they can be used.
     *
     * @param int $sectorId
     * @return array
     */
    public function getDestinationsFromSector(int $sectorId): array
    {
        $stmt = Db::prepare(
            "SELECT link_dest FROM $this->tableName WHERE link_start = :sector_id"
        );
        $stmt->execute(['sector_id' => $sectorId]);

        return array_reduce($stmt->fetchAll(), function ($carry, $item) {
            $carry[] = $item['link_dest'];
            return $carry;
        }, []);
    }
}