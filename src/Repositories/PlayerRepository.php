<?php declare(strict_types=1);
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2014 Ron Harwood and the BNT development team
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
// File: classes/Players/PlayersGateway.php

namespace BlackNova\Repositories;
// Domain Entity organization pattern, Players objects

use BlackNova\Models\Player;
use BlackNova\Models\Ship;
use BlackNova\Services\Db;

class PlayerRepository // Gateway for SQL calls related to Players
{
    private string $tableName;

    public function __construct()
    {
        $this->tableName = Db::table('ships');
    }

    public static function selectPlayersLoggedIn($since_stamp, $stamp): int
    {
        // SQL call that selected the number (count) of logged in ships (should be players)
        // where last login time is between the since_stamp, and the current timestamp ($stamp)
        // But it excludes xenobes.
        $sql = "SELECT COUNT(*) AS loggedin FROM " . Db::table('ships') . " " .
            "WHERE " . Db::table('ships') . ".last_login BETWEEN timestamp '"
            . $since_stamp . "' AND timestamp '" . $stamp . "' AND email NOT LIKE '%@xenobe'";
        $stmt = Db::query($sql);
        $row = $stmt->fetchObject(); // Fetch the associated object from the select
        return (int)$row->loggedin;
    }

    /**
     * Creates a new player in the database with initial ship and resources.
     *
     * @param string $email Player's email address
     * @param string $characterName Player's character name
     * @param string $shipName Player's ship name
     * @param string $passwordHash Hashed password (use password_hash())
     * @param string $ipAddress Player's IP address
     * @param string $lang Language preference
     * @return int The newly created player's ship_id
     */
    public function create(
        string $email,
        string $characterName,
        string $shipName,
        string $passwordHash,
        string $ipAddress,
        string $lang = 'english'
    ): int {
        $config = config();

        // Calculate starting turns based on game state
        $stmt = Db::query("SELECT MAX(turns_used + turns) AS mturns FROM $this->tableName");
        $row = $stmt->fetch();
        $mturns = $row['mturns'] ?? 0;

        if ($mturns > $config->max_turns) {
            $mturns = $config->max_turns;
        }

        $timestamp = date('Y-m-d H:i:s');

        $stmt = Db::prepare(
            "INSERT INTO $this->tableName (
                ship_name, ship_destroyed, character_name, password, email,
                armor_pts, credits, ship_energy, ship_fighters, turns,
                on_planet, dev_warpedit, dev_genesis, dev_beacon, dev_emerwarp,
                dev_escapepod, dev_fuelscoop, dev_minedeflector, dev_lssd,
                last_login, ip_address,
                trade_colonists, trade_fighters, trade_torps, trade_energy,
                cleared_defences, lang
            ) VALUES (
                :ship_name, 'N', :character_name, :password, :email,
                :armor_pts, :credits, :ship_energy, :ship_fighters, :turns,
                'N', :dev_warpedit, :dev_genesis, :dev_beacon, :dev_emerwarp,
                :dev_escapepod, :dev_fuelscoop, :dev_minedeflector, :dev_lssd,
                :last_login, :ip_address,
                'Y', 'N', 'N', 'Y',
                NULL, :lang
            )"
        );

        $stmt->execute([
            'ship_name' => $shipName,
            'character_name' => $characterName,
            'password' => $passwordHash,
            'email' => $email,
            'armor_pts' => $config->start_armor,
            'credits' => $config->start_credits,
            'ship_energy' => $config->start_energy,
            'ship_fighters' => $config->start_fighters,
            'turns' => $mturns,
            'dev_warpedit' => $config->start_editors,
            'dev_genesis' => $config->start_genesis,
            'dev_beacon' => $config->start_beacon,
            'dev_emerwarp' => $config->start_emerwarp,
            'dev_escapepod' => $config->start_escape_pod,
            'dev_fuelscoop' => $config->start_scoop,
            'dev_minedeflector' => $config->start_minedeflectors,
            'dev_lssd' => $config->start_lssd,
            'last_login' => $timestamp,
            'ip_address' => $ipAddress,
            'lang' => $lang,
        ]);

        return Db::lastInsertId();
    }

    public function findByEmail($email): ?Player
    {
        $stmt = Db::prepare("SELECT * FROM $this->tableName WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);

        if (!$row = $stmt->fetch()) return null;

        return $this->mapToPlayer($row);
    }

    public function findById(int $shipId): ?Player
    {
        $stmt = Db::prepare("SELECT * FROM $this->tableName WHERE ship_id = :ship_id LIMIT 1");
        $stmt->execute(['ship_id' => $shipId]);

        if (!$row = $stmt->fetch()) return null;

        return $this->mapToPlayer($row);
    }

    /**
     * Resets the ship to its default state.
     *
     * @param int $shipId
     * @return bool
     */
    public function resetShipById(int $shipId): bool
    {
        $stmt = Db::prepare(
            "UPDATE $this->tableName 
                SET 
                    hull=0, 
                    engines=0, 
                    power=0, 
                    computer=0, 
                    sensors=0, 
                    beams=0, torp_launchers=0, 
                    torps=0, 
                    armor=0, 
                    armor_pts=100, 
                    cloak=0, 
                    shields=0, 
                    sector=1, 
                    ship_ore=0, 
                    ship_organics=0, 
                    ship_energy=1000, 
                    ship_colonists=0, 
                    ship_goods=0, 
                    ship_fighters=100, 
                    ship_damage=0, 
                    on_planet='N', 
                    dev_warpedit=0, 
                    dev_genesis=0, 
                    dev_beacon=0, 
                    dev_emerwarp=0, 
                    dev_escapepod='N', 
                    dev_fuelscoop='N', 
                    dev_minedeflector=0, 
                    ship_destroyed='N',
                    dev_lssd='N' 
                WHERE ship_id = :ship_id"
        );
        return $stmt->execute(['ship_id' => $shipId]);
    }

    /**
     * Zeroes out the ship and sets it to destroyed. It does not delete the ship, nor
     * does it move the ship to another sector. I like the idea of showing a count of
     * nearby destroyed ships on the main screen.
     *
     * @param int $shipId
     * @return bool
     */
    public function destroyShipById(int $shipId): bool
    {
        $stmt = Db::prepare(
            "UPDATE $this->tableName 
                SET 
                    hull=0, 
                    engines=0, 
                    power=0, 
                    computer=0, 
                    sensors=0, 
                    beams=0,
                    torp_launchers=0, 
                    torps=0, 
                    armor=0, 
                    armor_pts=0, 
                    cloak=0, 
                    shields=0, 
                    ship_ore=0, 
                    ship_organics=0, 
                    ship_energy=0, 
                    ship_colonists=0, 
                    ship_goods=0, 
                    ship_fighters=0, 
                    ship_damage=0, 
                    on_planet='N', 
                    dev_warpedit=0, 
                    dev_genesis=0, 
                    dev_beacon=0, 
                    dev_emerwarp=0, 
                    dev_escapepod='N', 
                    dev_fuelscoop='N', 
                    dev_minedeflector=0, 
                    ship_destroyed='Y',
                    dev_lssd='N' 
                WHERE ship_id = :ship_id"
        );
        return $stmt->execute(['ship_id' => $shipId]);
    }

    /**
     * This function should be called if the player ship is destroyed. It will check if
     * the ship had an escape pod or if the player is relatively new to the game. If so,
     * it will give the player a new ship.
     *
     * @param Player $player
     * @return bool
     */
    public function destroyPlayerShip(Player $player): bool
    {
        if (
            $player->ship->hasDevice('escapePod') ||
            $player->isNew()
        ) {
            return $this->resetShipById($player->shipId);
        }

        return $this->destroyShipById($player->shipId);
    }

    public function updateLastLogin(int $shipId, string $ipAddress): bool
    {
        $stmt = Db::prepare(
            "UPDATE $this->tableName 
             SET last_login = :timestamp, ip_address = :ip 
             WHERE ship_id = :ship_id"
        );

        return $stmt->execute([
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $ipAddress,
            'ship_id' => $shipId,
        ]);
    }

    public function isIpBanned(string $ipAddress): bool
    {
        $table = Db::table('bans');

        $stmt = Db::prepare("SELECT COUNT(*) as count FROM $table WHERE :ip LIKE ban_mask AND ban_type = :ban_type");;
        $stmt->execute(['ip' => $ipAddress, 'ban_type' => IP_BAN]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    private function mapToPlayer(array $row): Player
    {
        $ship = new Ship(
            name: $row['ship_name'],
            sector: (int)$row['sector'],
            destroyed: $row['ship_destroyed'] === 'Y',
            hull: (int)$row['hull'],
            engines: (int)$row['engines'],
            power: (int)$row['power'],
            computer: (int)$row['computer'],
            sensors: (int)$row['sensors'],
            armor: (int)$row['armor'],
            shields: (int)$row['shields'],
            beams: (int)$row['beams'],
            torpLaunchers: (int)$row['torp_launchers'],
            cloak: (int)$row['cloak'],
            devices: [
                'warpEdits' => (int)$row['dev_warpedit'],
                'genesis' => (int)$row['dev_genesis'],
                'beacons' => (int)$row['dev_beacon'],
                'emergencyWarp' => (int)$row['dev_emerwarp'],
                'mineDeflectors' => (int)$row['dev_minedeflector'],

                'escapePod' => $row['dev_escapepod'] === 'Y',
                'fuelScoop' => $row['dev_fuelscoop'] === 'Y',
                'lssd' => $row['dev_lssd'] === 'Y',
            ],
        );

        return new Player(
            shipId: (int)$row['ship_id'],
            email: $row['email'],
            characterName: $row['character_name'],
            passwordHash: $row['password'],
            turns: (int)$row['turns'],
            ipAddress: $row['ip_address'],
            ship: $ship,
            lastLogin: $row['last_login'] ? (int)$row['last_login'] : null,
        );
    }
}
