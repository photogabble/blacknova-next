<?php declare(strict_types=1);
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
// File: classes/SectorDefense.php

namespace Bnt;

class SectorDefense
{
    public function __construct(
        public int $id,
        public int $sectorId,
        public int $ownerId,
        public string $type = 'M',
        public int $quantity = 0,
        public string $setting = 'toll'
    ) {}

    public function image(): array
    {
        return match ($this->type) {
            'M' => ['src' => 'mines.png', 'alt' => Translate::get('common.l_mines')],
            'F' => ['src' => 'fighters.png', 'alt' => Translate::get('common.l_fighters')]
        };
    }

    public function name(): string
    {
        $name = match ($this->type) {
            'M' => Translate::get('common.l_mines'),
            'F' => Translate::get('common.l_fighters'),
        };

        if ($this->type === 'M') return $name;

        return trim($name . ' ' . match ($this->setting) {
            'attack' => Translate::get('modify_defences.l_md_attack'),
            'toll' => Translate::get('modify_defences.l_md_toll'),
        });
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sector_id' => $this->sectorId,
            'owner_id' => $this->ownerId,
            'image' => $this->image(),
            'type' => $this->type,
            'name' => $this->name(),
            'quantity' => $this->quantity,
            'setting' => $this->setting
        ];
    }

    public static function messageDefenseOwner($db, $sector, $message)
    {
        $res = $db->Execute("SELECT ship_id FROM ".\BlackNova\Services\Db::table('sector_defence')." WHERE sector_id = ?;", array($sector));
        Db::logDbErrors($db, $res, __LINE__, __FILE__);

        if ($res instanceof ADORecordSet)
        {
            while (!$res->EOF)
            {
                player_log($db, $res->fields['ship_id'], LOG_RAW, $message);
                $res->MoveNext();
            }
        }
    }
}
