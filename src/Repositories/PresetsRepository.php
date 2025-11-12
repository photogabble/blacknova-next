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

use BlackNova\Models\Preset;
use BlackNova\Services\Db;

class PresetsRepository
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = Db::table('presets');
    }

    public function getPresetsByPlayer(int $playerId): array
    {
        $stmt = Db::prepare("SELECT * FROM $this->tableName WHERE ship_id = :player_id LIMIT :preset_max");
        $stmt->execute([
            'player_id' => $playerId,
            'preset_max' => config('preset_max'),
        ]);

        $presets = $stmt->fetchAll();

        // Presets should always be an array of length preset_max
        $remainder = config('preset_max') - count($presets);

        if ($remainder > 0) {
            // Default is sector one
            $presets = array_merge($presets, array_fill(0, $remainder, ['preset' => 1, 'type' => 'R']));
        }

        return array_map(function ($preset) {
            return new Preset(
                id: $preset['id'] ?? null,
                sector: $preset['preset'],
                type: $preset['type'],
            );
        }, $presets);
    }

}