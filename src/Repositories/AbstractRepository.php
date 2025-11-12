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
// File: classes/Players/AbstractRepository.php

namespace BlackNova\Repositories;

use BlackNova\Services\Db;

abstract class AbstractRepository {

    protected string $tableName;

    protected string $keyField = 'id';

    public function __construct()
    {
        $this->tableName = Db::table($this->tableName);
    }

    public function findById(int $id): ?object
    {
        $stmt = Db::prepare("SELECT * FROM $this->tableName WHERE $this->keyField = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        if (!$row = $stmt->fetch()) return null;

        return $this->mapToModel($row);
    }

    /**
     * @param array $ids
     * @return object[]
     */
    public function findByIds(array $ids): array
    {
        if (count($ids) === 0) return [];

        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = Db::prepare("SELECT * FROM $this->tableName WHERE $this->keyField IN ($placeholders) LIMIT ?");
        $stmt->execute([...array_values($ids), count($ids)]);

        return array_map(fn ($row) => $this->mapToModel($row), $stmt->fetchAll());
    }

    abstract protected function mapToModel(array $row): object;
}