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

    public function testEmptyCollectionDataRemainsArray(): void
    {
        ob_start();

        Response::success([], 'Records retrieved successfully.', [
            'current_page' => 1,
            'per_page' => 20,
            'total' => 0,
            'last_page' => 1,
        ]);

        $payload = json_decode((string) ob_get_clean(), true);

        $this->assertSame([], $payload['data']);
        $this->assertSame(0, $payload['meta']['total']);
    }
}
