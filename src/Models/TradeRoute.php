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
// File: src/Models/Ship.php

namespace BlackNova\Models;

use BlackNova\Models\Planet\Planet;
use BlackNova\Models\Sector\Sector;

class TradeRoute {
    public function __construct(
        public int $id,
        public Sector|Planet $source,
        public Sector|Planet $destination,
        public int $ownerId,
        public string $sourceType,
        public string $destinationType,
        public string $moveType,
        public int $circuit,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source->toArray(),
            'destination' => $this->destination->toArray(),
            'owner_id' => $this->ownerId,
            'source_type' => $this->sourceType,
            'destination_type' => $this->destinationType,
            'move_type' => $this->moveType,
            'circuit' => $this->circuit,
        ];
    }
}