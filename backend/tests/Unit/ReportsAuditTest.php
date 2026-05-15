<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Reports\Repositories\ReportRepository;
use App\Modules\Reports\Resources\ActivityLogResource;
use App\Modules\Reports\Resources\AuditLogResource;
use PHPUnit\Framework\TestCase;

class ReportsAuditTest extends TestCase
{
    public function testPhaseNinePermissionsAreConfigured(): void
    {
        $permissions = config('permissions.permissions', []);

        $this->assertContains('reports.view', $permissions);
        $this->assertContains('reports.export', $permissions);
        $this->assertContains('audit.view', $permissions);
    }

    public function testChartPayloadIsChartJsFriendly(): void
    {
        $payload = (new ReportRepository())->chartFromPairs([
            ['label' => 'open', 'value' => 3],
            ['label' => 'placed', 'value' => 2],
        ], 'Applications');

        $this->assertSame(['open', 'placed'], $payload['labels']);
        $this->assertSame('Applications', $payload['datasets'][0]['label']);
        $this->assertSame([3.0, 2.0], $payload['datasets'][0]['data']);
    }

    public function testAuditLogResourceHidesRawJsonColumns(): void
    {
        $resource = AuditLogResource::make([
            'id' => 10,
            'actor_id' => 2,
            'actor_name' => 'Admin User',
            'actor_email' => 'admin@example.com',
            'action' => 'reports.export',
            'module' => 'reports',
            'entity_type' => 'report',
            'entity_id' => null,
            'old_values_json' => null,
            'new_values_json' => json_encode(['type' => 'financial']),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => '2026-05-15 00:00:00',
        ]);

        $this->assertSame(['type' => 'financial'], $resource['new_values']);
        $this->assertArrayNotHasKey('new_values_json', $resource);
    }

    public function testActivityLogResourceHidesRawJsonColumns(): void
    {
        $resource = ActivityLogResource::make([
            'id' => 20,
            'user_id' => 3,
            'user_name' => 'Candidate User',
            'user_email' => 'candidate@example.com',
            'activity_type' => 'dashboard.viewed',
            'description' => 'Dashboard viewed.',
            'metadata_json' => json_encode(['dashboard' => 'job_seeker']),
            'created_at' => '2026-05-15 00:00:00',
        ]);

        $this->assertSame(['dashboard' => 'job_seeker'], $resource['metadata']);
        $this->assertArrayNotHasKey('metadata_json', $resource);
    }

    public function testPhaseNineRoutesAreRegistered(): void
    {
        $routes = file_get_contents(base_path('routes/api.php')) ?: '';

        foreach ([
            '/api/v1/reports/admin/summary',
            '/api/v1/reports/hr-officer/summary',
            '/api/v1/reports/relationship-officer/summary',
            '/api/v1/reports/recruiter/summary',
            '/api/v1/reports/job-seeker/summary',
            '/api/v1/reports/financial',
            '/api/v1/reports/placements',
            '/api/v1/reports/audit',
            '/api/v1/reports/export/{type}',
        ] as $route) {
            $this->assertStringContainsString($route, $routes);
        }
    }
}
