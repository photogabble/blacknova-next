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
// File: src/Models/Sector/Sector.php

namespace BlackNova\Models\Sector;

use BlackNova\Models\Ports;

final readonly class Sector
{
    public function __construct(
        public int     $id,
        public ?string $name,
        public int     $zoneId,
        public string  $zoneName,
        public string  $portType,
        public int     $portOrganics,
        public int     $portOre,
        public int     $portGoods,
        public int     $portEnergy,
        public ?string $beacon,
        public float   $angle1,
        public float   $angle2,
        public int     $distance,
        public int     $fighters,
        public array   $links = [],
    )
    {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'beacon' => $this->beacon,

            'zone' => [
                'id' => $this->zoneId,
                'name' => $this->zoneName,
            ],

            'port' => [
                'type' => $this->portType,
                'name' => Ports::from($this->portType)->getLocalizedName(),

                'organics' => $this->portOrganics,
                'ore' => $this->portOre,
                'goods' => $this->portGoods,
                'energy' => $this->portEnergy,
            ],
        ];
    }
}