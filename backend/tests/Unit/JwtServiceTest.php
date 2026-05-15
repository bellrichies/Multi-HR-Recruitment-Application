<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\HttpException;
use App\Support\Auth\JwtService;
use PHPUnit\Framework\TestCase;

class JwtServiceTest extends TestCase
{
    public function testTokenCanBeGeneratedAndParsed(): void
    {
        $service = new JwtService();
        $token = $service->generate(['sub' => 10, 'email' => 'admin@example.com'], 60);
        $payload = $service->parse($token);

        $this->assertSame(10, $payload['sub']);
        $this->assertSame('admin@example.com', $payload['email']);
        $this->assertArrayHasKey('jti', $payload);
    }

    public function testExpiredTokenIsRejected(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Token expired.');

        $service = new JwtService();
        $token = $service->generate(['sub' => 10], -1);

        $service->parse($token);
    }
}
