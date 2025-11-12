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
// File: src/Models/Player.php

namespace BlackNova\Models;

use BlackNova\Repositories\BanRepository;
use BlackNova\Services\Db;
use Bnt\Footer;
use Bnt\Header;
use Bnt\Languages;
use SensitiveParameter;

final readonly class Player
{
    public function __construct(
        public int    $shipId,
        public string $email,
        public int    $score,
        public int    $credits,
        public string $characterName,
        #[SensitiveParameter] public string $passwordHash,
        public string $lang,
        public int    $turns,
        public int    $turns_used,
        public string $ipAddress,
        public Ship   $ship,
        public ?int   $lastLogin = null,
    )
    {
    }

    public function isNew(): bool
    {
        if (!config('newbie_nice', false)) return false;

        return ($this->ship->hull <= config('newbie_hull', 0)) &&
            ($this->ship->engines <= config('newbie_engines', 0)) &&
            ($this->ship->power <= config('newbie_power', 0)) &&
            ($this->ship->computer <= config('newbie_computer', 0)) &&
            ($this->ship->sensors <= config('newbie_sensors', 0)) &&
            ($this->ship->armor <= config('newbie_armor', 0)) &&
            ($this->ship->shields <= config('newbie_shields', 0)) &&
            ($this->ship->beams <= config('newbie_beams', 0)) &&
            ($this->ship->torpLaunchers <= config('newbie_torp_launchers', 0)) &&
            ($this->ship->cloak <= config('newbie_cloak', 0));
    }

    public function isOnPlanet(): bool
    {
        return $this->ship->onPlanet;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function getInsignia(): string
    {
        for ($i = 0; $i < 20; $i++) {
            $value = pow(2, $i * 2) * 1000;
            if ($this->score <= $value) {
                return 'l_insignia_' . $i;
            }
        }

        // Player has outranked our highest rank, so just return that.
        return 'l_insignia_19';
    }

    public function toArray(): array
    {
        return [
            'ship_id' => $this->shipId,
            'email' => $this->email,
            'character_name' => $this->characterName,
            'score' => $this->score,
            'credits' => $this->credits,
            'turns' => [
                'available' => $this->turns,
                'consumed' => $this->turns_used,
            ],
            'ip_address' => $this->ipAddress,
            'last_login' => $this->lastLogin,
            'ship' => $this->ship->toArray(),
        ];
    }

    public static function HandleAuth($pdo_db, $lang, $langvars, $bntreg, $template)
    {
        $flag = true;
        $error_status = null;

        if (array_key_exists('username', $_SESSION) === false)
        {
            $_SESSION['username'] = null;
        }

        if (is_null($_SESSION['username']) === false)
        {
            $sql = "SELECT ip_address, password, last_login, ship_id, ship_destroyed, dev_escapepod FROM ". Db::table('ships') ." WHERE email=:email LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':email', $_SESSION['username']);
            $stmt->execute();
            $playerinfo = $stmt->fetch();

            if ($playerinfo !== false)
            {
                $stamp = date('Y-m-d H:i:s');
                $timestamp['now']  = (int) strtotime($stamp);
                $timestamp['last'] = (int) strtotime($playerinfo['last_login']);

                // Update the players last_login every 60 seconds to cut back SQL Queries.
                if ($timestamp['now'] >= ($timestamp['last'] + 60))
                {
                    $sql = "UPDATE ". Db::table('ships') ." SET last_login = :last_login, ip_address = :ip_address WHERE ship_id=:ship_id";
                    $stmt = $pdo_db->prepare($sql);
                    $stmt->bindParam(':last_login', $stamp);
                    $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
                    $stmt->bindParam(':ship_id', $playerinfo['ship_id']);
                    $stmt->execute();
                    \BlackNova\Services\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                    // Reset the last activity time on the session so that the session renews - this is the
                    // replacement for the (now removed) update_cookie function.
                    $_SESSION['last_activity'] = $timestamp['now'];
                }
                $flag = false;
            }
        }

        if ($flag)
        {
            $title = $langvars['l_error'];
            $error_status .= str_replace('[here]', "<a href='index.php'>" . $langvars['l_here'] . '</a>', $langvars['l_global_needlogin']);
            $title = $langvars['l_error'];
            Header::display($pdo_db, $lang, $template, $title);
            echo $error_status;
            Footer::display($pdo_db, $lang, $bntreg, $template);
            die();
        }
        else
        {
            return $playerinfo;
        }
    }

    public static function HandleBan($pdo_db, $lang, $timestamp, $template, $playerinfo)
    {
        // Check to see if the player is banned every 60 seconds (may need to ajust this).
        if ($timestamp['now'] >= ($timestamp['last'] + 60))
        {
            $ban_result = BanRepository::isBanned($pdo_db, $lang, null, $playerinfo);
            if ($ban_result===false|| (array_key_exists('ban_type', $ban_result)&&$ban_result['ban_type']===ID_WATCH))
            {
                return false;
            }
            else
            {
                // Set login status to false, then clear the session array, and clear the session cookie
                $_SESSION['logged_in'] = false;
                $_SESSION = array();
                setcookie('blacknova_session', '', 0, '/');

                // Destroy the session entirely
                session_destroy();

                $error_status = "<div style='font-size:18px; color:#FF0000;'>\n";
                if (array_key_exists('ban_type', $ban_result) && $ban_result['ban_type'] === ID_LOCKED)
                {
                    $error_status .= 'Your account has been Locked';
                }
                else
                {
                    $error_status .= 'Your account has been Banned';
                }

                if (array_key_exists('public_info', $ban_result) && mb_strlen(trim($ban_result['public_info'])) >0)
                {
                    $error_status .=" for the following:<br>\n";
                    $error_status .="<br>\n";
                    $error_status .="<div style='font-size:16px; color:#FFFF00;'>";
                    $error_status .= $ban_result['public_info'] . "</div>\n";
                }
                $error_status .= "</div>\n";
                $error_status .= "<br>\n";
                $error_status .= "<div style='color:#FF0000;'>Maybe you will behave yourself next time.</div>\n";
                $error_status .= "<br />\n";
                $error_status .= str_replace('[here]', "<a href='index.php'>" . $langvars['l_here'] . '</a>', $langvars['l_global_mlogin']);

                $title = $langvars['l_error'];
                Header::display($pdo_db, $lang, $template, $title);
                echo $error_status;
                Footer::display($pdo_db, $lang, $bntreg, $template);
                die();
            }
        }
    }
}
