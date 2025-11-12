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

use BlackNova\Models\Message;
use BlackNova\Models\Player;
use BlackNova\Services\Db;

class MessageRepository extends AbstractRepository
{
    protected string $tableName = 'messages';

    public function getPendingNotificationsForPlayer(Player $player): array
    {
        return array_map(
            fn($row) => $this->mapToModel($row),
            Db::select(
                "SELECT * FROM {$this->tableName} WHERE recp_id = :recipient_id AND notified = false",
                [
                    'recipient_id' => $player->shipId
                ]
            )
        );
    }

    protected function mapToModel(array $row): object
    {
        return new Message(
            id: $row['ID'],
            subject: $row['subject'],
            message: $row['message'],
            sender: $row['sender_id'],
            receiver: $row['recp_id'],
            sentAt: $row['sent'],
            notified: (bool)$row['notified']
        );
    }
}