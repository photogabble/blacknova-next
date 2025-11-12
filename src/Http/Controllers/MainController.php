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
// Origin: main.php
// File: src/Http/Controllers/MainController.php

namespace BlackNova\Http\Controllers;

use BlackNova\Models\Planet\Planet;
use BlackNova\Models\Player;
use BlackNova\Models\Preset;
use BlackNova\Models\TradeRoute;
use BlackNova\Repositories\MessageRepository;
use BlackNova\Repositories\PlanetRepository;
use BlackNova\Repositories\PlayerRepository;
use BlackNova\Repositories\PresetsRepository;
use BlackNova\Repositories\SectorDefenseRepository;
use BlackNova\Repositories\SectorRepository;
use BlackNova\Repositories\TradeRouteRepository;
use BlackNova\Repositories\WarpGateRepository;
use BlackNova\Services\Auth\AuthenticationService;
use Bnt\CalcLevels;
use Bnt\Translate;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;

final class MainController extends Controller
{
    private AuthenticationService $auth;

    private SectorRepository $sectorRepository;

    private WarpGateRepository $warpGateRepository;

    private PresetsRepository $presetsRepository;

    private PlanetRepository $planetRepository;

    private PlayerRepository $playerRepository;

    private SectorDefenseRepository $sectorDefenseRepository;

    private TradeRouteRepository $tradeRouteRepository;

    private MessageRepository $messageRepository;

    public function __construct(
            AuthenticationService $auth,
            SectorRepository      $sectorRepository,
            WarpGateRepository    $warpGateRepository,
            PresetsRepository     $presetsRepository,
            PlanetRepository      $planetRepository,
            PlayerRepository      $playerRepository,
            SectorDefenseRepository $sectorDefenseRepository,
            TradeRouteRepository $tradeRouteRepository,
            MessageRepository $messageRepository
    )
    {
        parent::__construct();
        $this->auth = $auth;
        $this->sectorRepository = $sectorRepository;
        $this->warpGateRepository = $warpGateRepository;
        $this->presetsRepository = $presetsRepository;
        $this->planetRepository = $planetRepository;
        $this->playerRepository = $playerRepository;
        $this->sectorDefenseRepository = $sectorDefenseRepository;
        $this->tradeRouteRepository = $tradeRouteRepository;
        $this->messageRepository = $messageRepository;
    }

    public function dashboard(): ResponseInterface
    {
        $player = $this->auth->user();
        // If Player is on a Planet, redirect to the planet details page
        if ($player->isOnPlanet()) return new RedirectResponse('/planet.php?planet_id=' . $player->ship->planetId);

        $sector = $this->sectorRepository->findById($player->ship->sector);

        $planets = $this->planetRepository->getPlanetsInSector($sector->id);
        $defences = $this->sectorDefenseRepository->getDefenseInSector($sector->id);

        $owners = array_reduce($this->playerRepository->findByIds(array_unique([
                ...array_map(fn($planet) => $planet->ownerId, $planets),
                ...array_map(fn($defence) => $defence->ownerId, $defences),
        ])), function(array $carry, Player $player) {
            $carry[$player->shipId] = $player;
            return $carry;
        }, []);

        return $this->view('main.tpl', [
                'player' => [
                        'is_admin' => $player->email === config('admin_mail'),
                        'rank' => $player->getInsignia(),
                        'gravatar' => config('enable_gravatars') ? 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($player->email))) . '?s=100&d=mm&r=g' : null,
                        ...$player->toArray(),
                ],

                'presets' => array_map(
                        fn(Preset $preset) => $preset->toArray(),
                        $this->presetsRepository->getPresetsByPlayer($player->shipId)
                ),

                'trade_routes' => array_map(
                        fn(TradeRoute $tradeRoute) => $tradeRoute->toArray(),
                        $this->tradeRouteRepository->getPlayerTradeRoutesForSector($player, $sector->id)
                ),

                'sector' => [
                        ...$sector->toArray(),

                    // Related Data
                        'links' => $this->warpGateRepository->getDestinationsFromSector($sector->id),
                        'planets' => array_map(function (Planet $planet) use ($owners) {
                            $level = isset($owners[$planet->ownerId])
                                    ? CalcLevels::avgTech($owners[$planet->ownerId]->ship->toArray(), 'planet')
                                    : 0;

                            if ($level < 8) $level = 0;
                            else if ($level < 12) $level = 1;
                            else if ($level < 16) $level = 2;
                            else if ($level < 20) $level = 3;
                            else $level = 4;

                            return [
                                    ...$planet->toArray(),
                                    'owner_name' => $owners[$planet->ownerId]->characterName ?? ('('.Translate::get('common.l_unowned').')'),
                                    'level' => $level,
                                    'image' => match($level) {
                                        0 => 'tinyplanet.png',
                                        1 => 'smallplanet.png',
                                        2 => 'mediumplanet.png',
                                        3 => 'largeplanet.png',
                                        4 => 'hugeplanet.png',
                                    }
                            ];
                        }, $planets),
                        'defences' => array_map(fn($defence) => [
                                ...$defence->toArray(),
                                'player_owned' => $player->shipId === $defence->ownerId,
                                'owner_name' => $owners[$defence->ownerId]->characterName ?? ('('.Translate::get('common.l_unowned').')'),
                        ], $defences),
                        'ships_detected' => array_map(
                                fn(Player $player) => $player->toArray(),
                                $this->playerRepository->detectInSector($player, $sector->id),
                        ),
                ],

            // TODO: update each message to set its sent flag true
                'messages' => count($this->messageRepository->getPendingNotificationsForPlayer($player)),

                'allow_ksm' => config('allow_ksm'),
        ]);
    }
}
