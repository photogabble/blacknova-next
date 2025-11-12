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
// File: src/Models/Planet/Planet.php

namespace BlackNova\Models\Planet;

final class Planet
{
    public function __construct(
        public int        $id,
        public string     $name,
        public int        $sectorId,
        public int        $organics,
        public int        $ore,
        public int        $goods,
        public int        $energy,
        public int        $colonists,
        public int        $credits,
        public int        $fighters,
        public int        $torpedoes,
        public bool       $isDefeated = false,
        public bool       $marketplaceOpen = false,
        public bool       $hasBase = false,
        public ?int       $corpId = null,
        public ?int       $ownerId = null,
        public Production $production = new Production(
            organics: 20, ore: 0, goods: 0, energy: 0, fighters: 0, torpedoes: 0,
        ),
    )
    {}

    public function getLevel(): int
    {
        // TODO: implement similar to Ship but using Planet specific values (see REFACTORING.md extensions section for more details)
        return 0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sector_id' => $this->sectorId,

            'storage' => [
                'organics' => $this->organics,
                'ore' => $this->ore,
                'goods' => $this->goods,
                'energy' => $this->energy,
                'colonists' => $this->colonists,
                'credits' => $this->credits,
                'fighters' => $this->fighters,
                'torpedoes' => $this->torpedoes,
            ],

            'production' => $this->production->toArray(),

            'is_defeated' => $this->isDefeated,
            'marketplace_open' => $this->marketplaceOpen,
            'has_base' => $this->hasBase,

            'corp_id' => $this->corpId,
            'owner_id' => $this->ownerId,
        ];
    }
}