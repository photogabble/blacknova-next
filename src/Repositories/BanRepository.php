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
// File: classes/CheckBan.php
//
// Returns a Boolean false when no account info or no ban found.
// Returns an array which contains the ban information when it has found something.
// Calling code needs to act on the returned information (boolean false or array of ban info).

namespace BlackNova\Repositories;

use BlackNova\Models\Player;
use BlackNova\Services\Db;
use PDO;

final class BanRepository
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = Db::table('bans');
    }

    /**
     * @deprecated do not commit this file until this function is removed
     */
    public static function isBanned($pdo_db, $lang, $langvars, $playerinfo = false)
    {
        return false;
    }

    /**
     * Check if a player is banned
     *
     * @param Player $player Player object containing ship_id and ip_address
     * @return array|false Returns ban information array if banned, false otherwise
     */
    public function isPlayerBanned(Player $player): array|false
    {
        // Check for IP Ban
        $ipBan = $this->checkIpBan($player->ipAddress);
        if ($ipBan !== false) {
            return $ipBan;
        }

        // Check for ID Watch, Ban, Lock, 24H Ban etc linked to the player's ShipID
        $idBan = $this->checkIdBan($player->shipId);
        if ($idBan !== false) {
            return $idBan;
        }

        // Check for Multi Ban (IP, ID)
        $multiBan = $this->checkMultiBan($player->ipAddress, $player->shipId);
        if ($multiBan !== false) {
            return $multiBan;
        }

        // No ban found
        return false;
    }

    public function createIpBan(string $ipAddress, string $reason): bool
    {
        $sql = "INSERT INTO {$this->tableName} (ban_type, ban_date, ban_mask, public_info, admin_info) 
                VALUES (:ban_type, :ban_date, :ban_mask, :public_info, :admin_info)";

        return Db::exec($sql, [
                'ban_type' => IP_BAN,
                'ban_date' => date('Y-m-d H:i:s'),
                'ban_mask' => $ipAddress,
                'public_info' => $reason,
                'admin_info' => '',
            ]) > 0;
    }

    /**
     * Check for IP-based ban
     *
     * @param string $ipAddress IP address to check
     * @return array|false Ban information or false
     */
    private function checkIpBan(string $ipAddress): array|false
    {
        $sql = "SELECT * FROM {$this->tableName} 
                WHERE (ban_type = :ban_type AND ban_mask = :ban_mask1) 
                   OR (ban_mask = :ban_mask2)";

        $stmt = Db::prepare($sql);
        $stmt->bindValue(':ban_type', IP_BAN, PDO::PARAM_INT);
        $stmt->bindValue(':ban_mask1', $ipAddress);
        $stmt->bindValue(':ban_mask2', $ipAddress);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? $result : false;
    }

    /**
     * Check for ID-based ban (ship_id)
     * Returns the highest/worst ban type if multiple bans exist
     *
     * @param int $shipId Ship ID to check
     * @return array|false Ban information or false
     */
    private function checkIdBan(int $shipId): array|false
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ban_ship = :ban_ship";

        $stmt = Db::prepare($sql);
        $stmt->bindValue(':ban_ship', $shipId, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            return false;
        }

        // Return the highest ban type (i.e., worst type of ban)
        $highestBan = ['ban_type' => 0];
        foreach ($results as $ban) {
            if ($ban['ban_type'] > $highestBan['ban_type']) {
                $highestBan = $ban;
            }
        }

        return $highestBan;
    }

    /**
     * Check for Multi Ban (both IP and ID)
     *
     * @param string $ipAddress IP address to check
     * @param int $shipId Ship ID to check
     * @return array|false Ban information or false
     */
    private function checkMultiBan(string $ipAddress, int $shipId): array|false
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

        $sql = "SELECT * FROM {$this->tableName} 
                WHERE ban_type = :ban_type 
                  AND (ban_mask = :ban_mask1 OR ban_mask = :ban_mask2 OR ban_ship = :ban_ship)";

        $stmt = Db::prepare($sql);
        $stmt->bindValue(':ban_type', MULTI_BAN, PDO::PARAM_INT);
        $stmt->bindValue(':ban_mask1', $ipAddress, PDO::PARAM_STR);
        $stmt->bindValue(':ban_mask2', $remoteAddr, PDO::PARAM_STR);
        $stmt->bindValue(':ban_ship', $shipId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? $result : false;
    }

    /**
     * Get all active bans for a ship ID
     *
     * @param int $shipId Ship ID to check
     * @return array Array of ban records
     */
    public function getBansByShipId(int $shipId): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ban_ship = :ban_ship";

        $stmt = Db::prepare($sql);
        $stmt->bindValue(':ban_ship', $shipId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active bans for an IP address
     *
     * @param string $ipAddress IP address to check
     * @return array Array of ban records
     */
    public function getBansByIpAddress(string $ipAddress): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE ban_mask = :ban_mask";

        $stmt = Db::prepare($sql);
        $stmt->bindValue(':ban_mask', $ipAddress);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
