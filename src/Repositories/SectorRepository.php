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
// File: classes/Players/SectorRepository.php

namespace BlackNova\Repositories;

use BlackNova\Models\Ports;
use BlackNova\Models\Sector\Sector;
use BlackNova\Services\Db;

class SectorRepository
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = Db::table('universe');
    }

    public function findById(int $id): ?Sector
    {
        $zoneTableName = Db::table('zones');

        $stmt = Db::prepare("
            SELECT $this->tableName.*, $zoneTableName.zone_name 
            FROM $this->tableName 
            JOIN $zoneTableName ON $this->tableName.zone_id = $zoneTableName.zone_id
            WHERE sector_id = :id
            LIMIT 1
        ");

        $stmt->execute(['id' => $id]);

        if (!$row = $stmt->fetch()) return null;
        return $this->mapToSector($row);
    }

    /**
     * @param array $ids
     * @return Sector[]
     */
    public function findByIds(array $ids): array
    {
        if (count($ids) === 0) return [];

        $zoneTableName = Db::table('zones');
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = Db::prepare("
            SELECT $this->tableName.*, $zoneTableName.zone_name 
            FROM $this->tableName
            JOIN $zoneTableName ON $this->tableName.zone_id = $zoneTableName.zone_id
            WHERE sector_id IN ($placeholders) 
            LIMIT ?
        ");

        $stmt->execute([...array_values($ids), count($ids)]);

        return array_map(fn($row) => $this->mapToSector($row), $stmt->fetchAll());
    }

    public function create(
        int    $zoneId,
        float  $angle1,
        float  $angle2,
        int    $distance,
        ?Ports $portType = Ports::NONE,
        ?string $name = null,
    ): false|int
    {
        if (!Db::exec("
            INSERT INTO $this->tableName (
                zone_id, angle1, angle2, distance, port_type, sector_name
            ) VALUES (
                :zone_id, :angle1, :angle2, :distance, :port_type, :sector_name
            ) 
        ", [
            'zone_id' => $zoneId,
            'angle1' => $angle1,
            'angle2' => $angle2,
            'distance' => $distance,
            'port_type' => $portType ?? Ports::NONE,
            'sector_name' => $name
        ])) return false;

        return Db::lastInsertId();
    }

    private function mapToSector(array $row): Sector
    {
        return new Sector(
            id: (int)$row['sector_id'],
            name: $row['sector_name'],
            zoneId: (int)$row['zone_id'],
            zoneName: $row['zone_name'],
            portType: $row['port_type'],
            portOrganics: (int)$row['port_organics'],
            portOre: (int)$row['port_ore'],
            portGoods: (int)$row['port_goods'],
            portEnergy: (int)$row['port_energy'],
            beacon: $row['beacon'],
            angle1: (float)$row['angle1'],
            angle2: (float)$row['angle2'],
            distance: (int)$row['distance'],
            fighters: (int)$row['fighters']
        );
    }
}