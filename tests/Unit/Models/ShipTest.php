<?php declare(strict_types=1);

namespace BlackNova\Tests\Unit\Models;

use BlackNova\Models\Ship;
use BlackNova\Models\Ship\Cargo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ShipTest extends TestCase
{
    public function test_constructor_creates_ship_with_required_properties(): void
    {
        $ship = new Ship(
            name: 'USS Enterprise',
            sector: 1,
            destroyed: false,
            hull: 10,
            engines: 10,
            power: 10,
            computer: 10,
            sensors: 10,
            armor: 10,
            shields: 10,
            beams: 10,
            torpLaunchers: 10,
            cloak: 10
        );

        $this->assertSame('USS Enterprise', $ship->name);
        $this->assertSame(1, $ship->sector);
        $this->assertFalse($ship->destroyed);
        $this->assertSame(10, $ship->hull);
        $this->assertFalse($ship->onPlanet);
        $this->assertNull($ship->planetId);
    }

    public function test_constructor_creates_ship_with_optional_cargo(): void
    {
        $cargo = new Cargo(
            ore: 100,
            organics: 200,
            goods: 300,
            energy: 400,
            colonists: 500
        );

        $ship = new Ship(
            name: 'Cargo Ship',
            sector: 5,
            destroyed: false,
            hull: 5,
            engines: 5,
            power: 5,
            computer: 5,
            sensors: 5,
            armor: 5,
            shields: 5,
            beams: 5,
            torpLaunchers: 5,
            cloak: 5,
            cargo: $cargo
        );

        $this->assertSame(100, $ship->cargo->ore);
        $this->assertSame(200, $ship->cargo->organics);
        $this->assertSame(300, $ship->cargo->goods);
        $this->assertSame(400, $ship->cargo->energy);
        $this->assertSame(500, $ship->cargo->colonists);
    }

    public function test_isDestroyed_returns_true_when_ship_is_destroyed(): void
    {
        $ship = new Ship(
            name: 'Destroyed Ship',
            sector: 1,
            destroyed: true,
            hull: 0,
            engines: 0,
            power: 0,
            computer: 0,
            sensors: 0,
            armor: 0,
            shields: 0,
            beams: 0,
            torpLaunchers: 0,
            cloak: 0
        );

        $this->assertTrue($ship->isDestroyed());
    }

    public function test_isDestroyed_returns_false_when_ship_is_not_destroyed(): void
    {
        $ship = new Ship(
            name: 'Active Ship',
            sector: 1,
            destroyed: false,
            hull: 10,
            engines: 10,
            power: 10,
            computer: 10,
            sensors: 10,
            armor: 10,
            shields: 10,
            beams: 10,
            torpLaunchers: 10,
            cloak: 10
        );

        $this->assertFalse($ship->isDestroyed());
    }

    public function test_hasDevice_returns_true_for_boolean_device(): void
    {
        $ship = new Ship(
            name: 'Ship',
            sector: 1,
            destroyed: false,
            hull: 10,
            engines: 10,
            power: 10,
            computer: 10,
            sensors: 10,
            armor: 10,
            shields: 10,
            beams: 10,
            torpLaunchers: 10,
            cloak: 10,
            devices: ['escape_pod' => true]
        );

        $this->assertTrue($ship->hasDevice('escape_pod'));
    }

    public function test_hasDevice_returns_false_for_boolean_false_device(): void
    {
        $ship = new Ship(
            name: 'Ship',
            sector: 1,
            destroyed: false,
            hull: 10,
            engines: 10,
            power: 10,
            computer: 10,
            sensors: 10,
            armor: 10,
            shields: 10,
            beams: 10,
            torpLaunchers: 10,
            cloak: 10,
            devices: ['escape_pod' => false]
        );

        $this->assertFalse($ship->hasDevice('escape_pod'));
    }

    public function test_hasDevice_returns_true_for_numeric_device_with_quantity(): void
    {
        $ship = new Ship(
            name: 'Ship',
            sector: 1,
            destroyed: false,
            hull: 10,
            engines: 10,
            power: 10,
            computer: 10,
            sensors: 10,
            armor: 10,
            shields: 10,
            beams: 10,
            torpLaunchers: 10,
            cloak: 10,
            devices: ['mines' => 5]
        );

        $this->assertTrue($ship->hasDevice('mines'));
    }

    public function test_hasDevice_returns_false_for_numeric_device_with_zero_quantity(): void
    {
        $ship = new Ship(
            name: 'Ship',
            sector: 1,
            destroyed: false,
            hull: 10,
            engines: 10,
            power: 10,
            computer: 10,
            sensors: 10,
            armor: 10,
            shields: 10,
            beams: 10,
            torpLaunchers: 10,
            cloak: 10,
            devices: ['mines' => 0]
        );

        $this->assertFalse($ship->hasDevice('mines'));
    }

    public function test_hasDevice_returns_false_for_nonexistent_device(): void
    {
        $ship = new Ship(
            name: 'Ship',
            sector: 1,
            destroyed: false,
            hull: 10,
            engines: 10,
            power: 10,
            computer: 10,
            sensors: 10,
            armor: 10,
            shields: 10,
            beams: 10,
            torpLaunchers: 10,
            cloak: 10,
            devices: []
        );

        $this->assertFalse($ship->hasDevice('nonexistent'));
    }

    #[DataProvider('levelProvider')]
    public function test_getLevel_returns_correct_level_based_on_tech(
        int $tech,
        int $expectedLevel
    ): void
    {
        $ship = new Ship(
            name: 'Ship',
            sector: 1,
            destroyed: false,
            hull: $tech,
            engines: $tech,
            power: $tech,
            computer: $tech,
            sensors: $tech,
            armor: $tech,
            shields: $tech,
            beams: $tech,
            torpLaunchers: $tech,
            cloak: $tech
        );

        $this->assertSame($expectedLevel, $ship->getLevel());
    }

    public static function levelProvider(): array
    {
        return [
            'Level 0: tech below 8' => [7, 0],
            'Level 0: tech at 7' => [7, 0],
            'Level 1: tech at 8' => [8, 1],
            'Level 1: tech at 11' => [11, 1],
            'Level 2: tech at 12' => [12, 2],
            'Level 2: tech at 15' => [15, 2],
            'Level 3: tech at 16' => [16, 3],
            'Level 3: tech at 19' => [19, 3],
            'Level 4: tech at 20' => [20, 4],
            'Level 4: tech at 25' => [25, 4],
        ];
    }

    #[DataProvider('imageProvider')]
    public function test_image_returns_correct_image_based_on_level(
        int    $level,
        string $expectedImage
    ): void
    {
        $tech = match ($level) {
            0 => 7,
            1 => 10,
            2 => 14,
            3 => 18,
            4 => 22,
        };

        $ship = new Ship(
            name: 'Ship',
            sector: 1,
            destroyed: false,
            hull: $tech,
            engines: $tech,
            power: $tech,
            computer: $tech,
            sensors: $tech,
            armor: $tech,
            shields: $tech,
            beams: $tech,
            torpLaunchers: $tech,
            cloak: $tech
        );

        $this->assertSame($expectedImage, $ship->image());
    }

    public static function imageProvider(): array
    {
        return [
            'Level 0 ship' => [0, 'tinyship.png'],
            'Level 1 ship' => [1, 'smallship.png'],
            'Level 2 ship' => [2, 'mediumship.png'],
            'Level 3 ship' => [3, 'largeship.png'],
            'Level 4 ship' => [4, 'hugeship.png'],
        ];
    }

    public function test_toArray_returns_complete_ship_data(): void
    {
        $cargo = new Cargo(
            ore: 100,
            organics: 200,
            goods: 300,
            energy: 400,
            colonists: 500
        );

        $ship = new Ship(
            name: 'Test Ship',
            sector: 42,
            destroyed: false,
            hull: 15,
            engines: 15,
            power: 15,
            computer: 15,
            sensors: 15,
            armor: 15,
            shields: 15,
            beams: 15,
            torpLaunchers: 15,
            cloak: 15,
            devices: ['escape_pod' => true, 'mines' => 10],
            cargo: $cargo,
            onPlanet: true,
            planetId: 7
        );

        $array = $ship->toArray();

        $this->assertSame('Test Ship', $array['name']);
        $this->assertSame(2, $array['level']);
        $this->assertSame('mediumship.png', $array['image']);
        $this->assertSame(42, $array['sector']);
        $this->assertFalse($array['destroyed']);
        $this->assertSame(15, $array['hull']);
        $this->assertSame(15, $array['engines']);
        $this->assertSame(15, $array['power']);
        $this->assertSame(15, $array['computer']);
        $this->assertSame(15, $array['sensors']);
        $this->assertSame(15, $array['armor']);
        $this->assertSame(15, $array['shields']);
        $this->assertSame(15, $array['beams']);
        $this->assertSame(15, $array['torp_launchers']);
        $this->assertSame(15, $array['cloak']);
        $this->assertIsArray($array['devices']);
        $this->assertTrue($array['devices']['escape_pod']);
        $this->assertSame(10, $array['devices']['mines']);
        $this->assertIsArray($array['cargo']);
        $this->assertSame('100', $array['cargo']['ore']);
        $this->assertSame('200', $array['cargo']['organics']);
        $this->assertSame('300', $array['cargo']['goods']);
        $this->assertSame('400', $array['cargo']['energy']);
        $this->assertSame('500', $array['cargo']['colonists']);
    }

    public function test_ship_with_onPlanet_and_planetId(): void
    {
        $ship = new Ship(
            name: 'Landed Ship',
            sector: 10,
            destroyed: false,
            hull: 10,
            engines: 10,
            power: 10,
            computer: 10,
            sensors: 10,
            armor: 10,
            shields: 10,
            beams: 10,
            torpLaunchers: 10,
            cloak: 10,
            onPlanet: true,
            planetId: 123
        );

        $this->assertTrue($ship->onPlanet);
        $this->assertSame(123, $ship->planetId);
    }
}