<?php
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
// File: sched_igb.php

if (strpos($_SERVER['PHP_SELF'], 'sector_igb.php')) // Prevent direct access to this file
{
    die('Blacknova Traders error: You cannot access this file directly.');
}

$exponinter = pow($bntreg->ibank_interest + 1, $multiplier);
$expoloan = pow($bntreg->ibank_loaninterest + 1, $multiplier);

echo "<strong>IBANK</strong><p>";

$ibank_result = $db->Execute("UPDATE ".\Bnt\Db::table('ibank_accounts')." SET balance = balance * ?, loan = loan * ?", array($exponinter, $expoloan));
Bnt\Db::logDbErrors($db, $ibank_result, __LINE__, __FILE__);
echo "All IGB accounts updated ($multiplier times).<p>";

$multiplier = 0;
?>
