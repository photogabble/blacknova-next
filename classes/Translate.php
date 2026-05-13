<?php
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2014 Ron Harwood and the BNT development team
// Copyright (C) 2025-2026 Simon Dann
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
// File: classes/Translate.php

namespace Bnt;

use RuntimeException;
use InvalidArgumentException;
use BlackNova\Services\Auth\SessionInterface;

class Translate
{
    private SessionInterface $session;

    private Reg $reg;

    private array $values = [];

    public function __construct(
        SessionInterface $session,
        Reg $reg
    ){
        $this->session = $session;
        $this->reg = $reg;

        $ini_file = APP_ROOT . '/languages/' . $this->currentLanguage() . '.ini';
        if (!file_exists($ini_file)) {
            throw new RuntimeException('Invalid language file');
        }

        foreach (parse_ini_file($ini_file, true) as $category => $values) {
            if (!isset($this->values[$category])) $this->values[$category] = [];
            foreach ($values as $name => $value) {
                $this->values[$category][$name] = $value;
            }
        }
    }

    public function currentLanguage(): string
    {
        return $this->session->get('lang', $this->reg->default_lang);
    }

    public function get(string $key): string
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) throw new InvalidArgumentException('Invalid key format');

        return $this->values[$parts[0]][$parts[1]] ?? $key;
    }

    public function all(): array
    {
        // TODO: this has been reduced for consumption by the legacy static load method. It *should* only return $this->values ideally.
        return array_reduce($this->values, function ($carry, $category) {
            foreach ($category as $key => $value) {
                $carry[$key] = $value;
            }
            return $carry;
        }, []);
    }

    /**
     * @deprecated ideally use __('category.key') or the all method if you need the list
     */
    public static function load($db = null, $language = null, $categories = null)
    {
        return app(Translate::class)->all();
    }
}
