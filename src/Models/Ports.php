<?php
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2014 Ron Harwood and the BNT development team
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
// Origin: classes/Ports.php
// File: src/Models/Ports.php

namespace BlackNova\Models;

use Bnt\Translate;

enum Ports: string
{
    case ORE = 'ore';
    case NONE = 'none';
    case ENERGY = 'energy';
    case ORGANICS = 'organics';
    case GOODS = 'goods';
    case SPECIAL = 'special';

    public function getLocalizedName(array $langvars = []): string
    {
        return match ($this) {
            self::ORE => $langvars['l_ore'] ?? Translate::get('common.l_ore'),
            self::NONE => $langvars['l_none'] ?? Translate::get('common.l_none'),
            self::ENERGY => $langvars['l_energy'] ?? Translate::get('common.l_energy'),
            self::ORGANICS => $langvars['l_organics'] ?? Translate::get('common.l_organics'),
            self::GOODS => $langvars['l_goods'] ?? Translate::get('common.l_goods'),
            self::SPECIAL => $langvars['l_special'] ?? Translate::get('common.l_special'),
        };
    }
}
