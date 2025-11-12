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
// File: classes/Players/SectorDefenseRepository.php

namespace BlackNova\Repositories;

use BlackNova\Services\Db;
use Bnt\SectorDefense;

class SectorDefenseRepository
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = Db::table('sector_defence');
    }

    /**
     * @param int $sectorId
     * @return SectorDefense[]
     */
    public function getDefenseInSector(int $sectorId): array
    {
        $shipTable = Db::table('ships');

        $stmt = Db::prepare("
            SELECT * FROM $this->tableName 
            JOIN $shipTable ON $shipTable.ship_id = $this->tableName.ship_id
            WHERE sector_id = :sector_id
        ");
        $stmt->execute(['sector_id' => $sectorId]);

        return array_map([$this, 'mapToSectorDefense'], $stmt->fetchAll());
    }

    private function mapToSectorDefense(array $row): SectorDefense
    {
        return new SectorDefense(
            id: $row['defence_id'],
            sectorId: $row['sector_id'],
            ownerId: $row['ship_id'],
            type: $row['defence_type'],
            quantity: $row['quantity'],
            setting: $row['fm_setting'],
        );
    }

}