<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Response;
use PHPUnit\Framework\TestCase;

class FoundationTest extends TestCase
{
    public function testSuccessResponseShape(): void
    {
        ob_start();

        Response::success(['ok' => true], 'Done.');

        $payload = json_decode((string) ob_get_clean(), true);

        $this->assertTrue($payload['success']);
        $this->assertSame('Done.', $payload['message']);
        $this->assertSame(['ok' => true], $payload['data']);
        $this->assertArrayHasKey('meta', $payload);
    }
}
