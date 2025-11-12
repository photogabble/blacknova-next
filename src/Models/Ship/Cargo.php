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
// File: src/Models/Ship/Cargo.php

namespace BlackNova\Models\Ship;

final readonly class Cargo
{
    public function __construct(
        public int $ore,
        public int $organics,
        public int $goods,
        public int $energy,
        public int $colonists
    ) {}

    public function toArray(): array
    {
        return [
            'ore' => number_format($this->ore),
            'organics' => number_format($this->organics),
            'goods' => number_format($this->goods),
            'energy' => number_format($this->energy),
            'colonists' => number_format($this->colonists),
        ];
    }
}