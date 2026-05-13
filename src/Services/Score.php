<?php declare(strict_types=1);
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2014 Ron Harwood and the BNT development team
// Copyright (C) 2026 Simon Dann
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
// File: classes/Score.php

namespace BlackNova\Services;

class Score
{
    public static function updateScore(int $ship_id): int
    {
        $upgrade_factor = config('upgrade_factor');
        $upgrade_cost = config('upgrade_cost');
        $torpedo_price = config('torpedo_price');
        $armor_price = config('armor_price');
        $fighter_price = config('fighter_price');
        $ore_price = config('ore_price');
        $organics_price = config('organics_price');
        $goods_price = config('goods_price');
        $energy_price = config('energy_price');
        $colonist_price = config('colonist_price');
        $dev_genesis_price = config('dev_genesis_price');
        $dev_beacon_price = config('dev_beacon_price');
        $dev_emerwarp_price = config('dev_emerwarp_price');
        $dev_warpedit_price = config('dev_warpedit_price');
        $dev_minedeflector_price = config('dev_minedeflector_price');
        $dev_escapepod_price = config('dev_escapepod_price');
        $dev_fuelscoop_price = config('dev_fuelscoop_price');
        $dev_lssd_price = config('dev_lssd_price');
        $base_ore = config('base_ore');
        $base_goods = config('base_goods');
        $base_organics = config('base_organics');
        $base_credits = config('base_credits');

        // These are all SQL Queries, so treat them like them.
        $calc_hull              = "ROUND(POW($upgrade_factor, hull))";
        $calc_engines           = "ROUND(POW($upgrade_factor, engines))";
        $calc_power             = "ROUND(POW($upgrade_factor, power))";
        $calc_computer          = "ROUND(POW($upgrade_factor, computer))";
        $calc_sensors           = "ROUND(POW($upgrade_factor, sensors))";
        $calc_beams             = "ROUND(POW($upgrade_factor, beams))";
        $calc_torp_launchers    = "ROUND(POW($upgrade_factor, torp_launchers))";
        $calc_shields           = "ROUND(POW($upgrade_factor, shields))";
        $calc_armor             = "ROUND(POW($upgrade_factor, armor))";
        $calc_cloak             = "ROUND(POW($upgrade_factor, cloak))";
        $calc_levels            = "($calc_hull + $calc_engines + $calc_power + $calc_computer + $calc_sensors + $calc_beams + $calc_torp_launchers + $calc_shields + $calc_armor + $calc_cloak) * $upgrade_cost";

        $calc_torps             = Db::table('ships') .".torps * $torpedo_price";
        $calc_armor_pts         = "armor_pts * $armor_price";
        $calc_ship_ore          = "ship_ore * $ore_price";
        $calc_ship_organics     = "ship_organics * $organics_price";
        $calc_ship_goods        = "ship_goods * $goods_price";
        $calc_ship_energy       = "ship_energy * $energy_price";
        $calc_ship_colonists    = "ship_colonists * $colonist_price";
        $calc_ship_fighters     = "ship_fighters * $fighter_price";
        $calc_equip             = "$calc_torps + $calc_armor_pts + $calc_ship_ore + $calc_ship_organics + $calc_ship_goods + $calc_ship_energy + $calc_ship_colonists + $calc_ship_fighters";

        $calc_dev_warpedit      = "dev_warpedit * $dev_warpedit_price";
        $calc_dev_genesis       = "dev_genesis * $dev_genesis_price";
        $calc_dev_beacon        = "dev_beacon * $dev_beacon_price";
        $calc_dev_emerwarp      = "dev_emerwarp * $dev_emerwarp_price";
        $calc_dev_escapepod     = "IF(dev_escapepod='Y', $dev_escapepod_price, 0)";
        $calc_dev_fuelscoop     = "IF(dev_fuelscoop='Y', $dev_fuelscoop_price, 0)";
        $calc_dev_lssd          = "IF(dev_lssd='Y', $dev_lssd_price, 0)";
        $calc_dev_minedeflector = "dev_minedeflector * $dev_minedeflector_price";
        $calc_dev               = "$calc_dev_warpedit + $calc_dev_genesis + $calc_dev_beacon + $calc_dev_emerwarp + $calc_dev_escapepod + $calc_dev_fuelscoop + $calc_dev_minedeflector + $calc_dev_lssd";

        $calc_planet_goods      = "SUM(".Db::table('planets').".organics) * $organics_price + SUM(".Db::table('planets').".ore) * $ore_price + SUM(".Db::table('planets').".goods) * $goods_price + SUM(".Db::table('planets').".energy) * $energy_price";
        $calc_planet_colonists  = "SUM(".Db::table('planets').".colonists) * $colonist_price";
        $calc_planet_defence    = "SUM(".Db::table('planets').".fighters) * $fighter_price + IF(".Db::table('planets').".base='Y', $base_credits + SUM(".Db::table('planets').".torps) * $torpedo_price, 0)";
        $calc_planet_credits    = "SUM(".Db::table('planets').".credits)";

        $pl_score_res = Db::select(
            "SELECT IF(COUNT(*)>0, $calc_planet_goods + $calc_planet_colonists + $calc_planet_defence + $calc_planet_credits, 0) AS planet_score FROM ".Db::table('planets')." WHERE owner=?",
            [$ship_id]
        );

        $planet_score = (count($pl_score_res) === 1)
            ? $pl_score_res[0]['planet_score']
            : 0;

        $ship_score_res = Db::select(
            "SELECT IF(COUNT(*)>0, $calc_levels + $calc_equip + $calc_dev + ".Db::table('ships').".credits, 0) AS ship_score FROM ".Db::table('ships')." LEFT JOIN ".Db::table('planets')." ON ".Db::table('planets').".owner=ship_id WHERE ship_id=? AND ship_destroyed='N'",
            [$ship_id]
        );

        $ship_score = (count($ship_score_res) === 1)
            ? $ship_score_res[0]['ship_score']
            : 0;

        $bank_score_res = Db::select(
            "SELECT (balance - loan) AS bank_score FROM ".Db::table('ibank_accounts')." WHERE ship_id = ?;",
            [$ship_id]
        );

        $bank_score = (count($bank_score_res) === 1)
            ? $bank_score_res[0]['bank_score']
            : 0;

        $score = $ship_score + $planet_score + $bank_score;
        if ($score < 0) $score = 0;

        $score = (int) round(sqrt($score));
        Db::exec(
            "UPDATE ".Db::table('ships')." SET score=? WHERE ship_id=?",
            [$score, $ship_id]
        );

        return $score;
    }
}
