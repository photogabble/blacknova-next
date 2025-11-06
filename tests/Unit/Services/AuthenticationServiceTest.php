<?php declare(strict_types=1);

namespace BlackNova\Tests\Unit\Services;

use BlackNova\Models\Player;
use BlackNova\Models\Ship;
use BlackNova\Repositories\PlayerRepository;
use BlackNova\Services\Auth\AuthenticationService;
use BlackNova\Services\Auth\SessionManager;
use BlackNova\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class AuthenticationServiceTest extends TestCase
{
    private PlayerRepository|MockObject $playerRepository;
    private SessionManager|MockObject $sessionManager;
    private AuthenticationService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playerRepository = $this->createMock(PlayerRepository::class);
        $this->sessionManager = $this->createMock(SessionManager::class);

        $this->authService = new AuthenticationService(
            $this->playerRepository,
            $this->sessionManager
        );
    }

    public function test_attempt_fails_when_ip_is_banned(): void
    {
        $this->playerRepository
            ->expects($this->once())
            ->method('isIpBanned')
            ->with('192.168.1.1')
            ->willReturn(true);

        $result = $this->authService->attempt(
            'test@example.com',
            'password',
            '192.168.1.1'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('ip_banned', $result['code']);
        $this->assertEquals('Your IP address has been banned.', $result['message']);
    }

    public function test_attempt_fails_with_invalid_email(): void
    {
        $this->playerRepository
            ->method('isIpBanned')
            ->willReturn(false);

        $this->playerRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('invalid@example.com')
            ->willReturn(null);

        $result = $this->authService->attempt(
            'invalid@example.com',
            'password',
            '192.168.1.1'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('invalid_credentials', $result['code']);
    }

    public function test_attempt_succeeds_with_valid_credentials(): void
    {
        $ship = new Ship(
            name: 'Test Ship',
            sector: 1,
            destroyed: false,
            hull: 100,
            engines: 0,
            power: 0,
            computer: 0,
            sensors: 0,
            armor: 0,
            shields: 0,
            beams: 10,
            torpLaunchers: 10,
            cloak: 0,
        );

        $player = new Player(
            shipId: 1,
            email: 'test@example.com',
            characterName: 'Test Player',
            passwordHash: password_hash('password', PASSWORD_DEFAULT),
            turns: 100,
            ipAddress: '192.168.1.1',
            ship: $ship
        );

        $this->playerRepository
            ->method('isIpBanned')
            ->willReturn(false);

        $this->playerRepository
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($player);

        $this->playerRepository
            ->expects($this->once())
            ->method('updateLastLogin')
            ->with(1, '192.168.1.1');

        $this->sessionManager
            ->expects($this->once())
            ->method('login')
            ->with($player);

        $result = $this->authService->attempt(
            'test@example.com',
            'password',
            '192.168.1.1'
        );

        $this->assertTrue($result['success']);
        $this->assertSame($player, $result['player']);
    }
}