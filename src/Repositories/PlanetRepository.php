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
// File: classes/Players/PlanetRepository.php

namespace BlackNova\Repositories;

use BlackNova\Models\Planet\Planet;
use BlackNova\Models\Planet\Production;
use BlackNova\Services\Db;
use Bnt\Translate;
use PDO;

class PlanetRepository
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = Db::table('planets');
    }

    public function findById(int $id): ?Planet
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE planet_id = :planet_id LIMIT 1";
        $stmt = Db::prepare($sql);
        $stmt->bindParam(':planet_id', $id, PDO::PARAM_INT);

        if (!$row = $stmt->fetch()) return null;

        return $this->mapToPlanet($row);
    }

    /**
     * @param array $ids
     * @return Planet[]
     */
    public function findByIds(array $ids): array
    {
        if (count($ids) === 0) return [];

        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = Db::prepare("SELECT * FROM $this->tableName WHERE planet_id IN ($placeholders) LIMIT ?");
        $stmt->execute([...array_values($ids), count($ids)]);

        return array_map(fn ($row) => $this->mapToPlanet($row), $stmt->fetchAll());
    }

    public function getPlanetsInSector(int $sector_id): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE sector_id = :sector_id";
        $stmt = Db::prepare($sql);
        $stmt->bindParam(':sector_id', $sector_id, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($row) => $this->mapToPlanet($row), $stmt->fetchAll());
    }

    private function mapToPlanet(array $row): Planet
    {
        return new Planet(
            id: $row['planet_id'],
            name: $row['name'] ?? Translate::get('common.l_unnamed'),
            sectorId: $row['sector_id'],
            organics: $row['organics'],
            ore: (int)$row['ore'],
            goods: (int)$row['goods'],
            energy: (int)$row['energy'],
            colonists: (int)$row['colonists'],
            credits: (int)$row['credits'],
            fighters: (int)$row['fighters'],
            torpedoes: (int)$row['torps'],
            isDefeated: $row['defeated'] === 'Y',
            marketplaceOpen: $row['sells'] === 'Y',
            hasBase: $row['base'] === 'Y',
            corpId: $row['corp'],
            ownerId: $row['owner'],
            production: new Production(
                organics: (int)$row['prod_organics'],
                ore: (int)$row['prod_ore'],
                goods: (int)$row['prod_goods'],
                energy: (int)$row['prod_energy'],
                fighters: (int)$row['prod_fighters'],
                torpedoes: (int)$row['prod_torp'],
            )
        );
    }
}