<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Notifications\Resources\NotificationResource;
use App\Modules\Notifications\Services\NotificationService;
use PHPUnit\Framework\TestCase;

class MessagingNotificationTest extends TestCase
{
    public function testPhaseEightPermissionsAreConfigured(): void
    {
        $permissions = config('permissions.permissions', []);

        foreach (['messages.view', 'messages.send', 'notifications.view', 'notifications.manage'] as $permission) {
            $this->assertContains($permission, $permissions);
        }
    }

    public function testNotificationResourceDecodesStructuredData(): void
    {
        $resource = NotificationResource::make([
            'id' => 1,
            'title' => 'Interview scheduled',
            'body' => 'Your interview has been scheduled.',
            'type' => NotificationService::EVENTS[4],
            'channel' => 'in_app',
            'data_json' => json_encode(['interview_id' => 10]),
            'read_at' => null,
            'created_at' => '2026-05-15 00:00:00',
        ]);

        $this->assertSame(['interview_id' => 10], $resource['data']);
    }
}
