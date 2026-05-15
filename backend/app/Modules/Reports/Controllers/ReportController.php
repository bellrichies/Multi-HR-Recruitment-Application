<?php

declare(strict_types=1);

namespace App\Modules\Reports\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Reports\Resources\AuditLogResource;
use App\Modules\Reports\Resources\ReportResource;
use App\Modules\Reports\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function adminSummary(Request $request): void
    {
        $this->success(
            ReportResource::dashboard($this->reports->superAdminDashboard($request->user())),
            'Super Admin dashboard retrieved successfully.'
        );
    }

    public function hrOfficerSummary(Request $request): void
    {
        $this->success(
            ReportResource::dashboard($this->reports->hrOfficerDashboard($request->user())),
            'HR Officer dashboard retrieved successfully.'
        );
    }

    public function relationshipOfficerSummary(Request $request): void
    {
        $this->success(
            ReportResource::dashboard($this->reports->relationshipOfficerDashboard($request->user())),
            'Relationship Officer dashboard retrieved successfully.'
        );
    }

    public function recruiterSummary(Request $request): void
    {
        $this->success(
            ReportResource::dashboard($this->reports->recruiterDashboard($request->user())),
            'Recruiter dashboard retrieved successfully.'
        );
    }

    public function jobSeekerSummary(Request $request): void
    {
        $this->success(
            ReportResource::dashboard($this->reports->jobSeekerDashboard($request->user())),
            'Job Seeker dashboard retrieved successfully.'
        );
    }

    public function financial(Request $request): void
    {
        $this->success(ReportResource::report($this->reports->financial($request->user())), 'Financial report retrieved successfully.');
    }

    public function placements(Request $request): void
    {
        $this->success(ReportResource::report($this->reports->placements($request->user())), 'Placement report retrieved successfully.');
    }

    public function audit(Request $request): void
    {
        $result = $this->reports->audit($request->user(), $this->auditFilters($request), (int) $request->query('page', 1), (int) $request->query('per_page', 20));

        $this->success(AuditLogResource::collection($result['data']), 'Audit logs retrieved successfully.', $result['meta']);
    }

    public function export(Request $request, string $type): void
    {
        $this->success(
            ReportResource::export($this->reports->export($type, $request->user(), $this->context($request))),
            'Report export generated successfully.'
        );
    }

    private function auditFilters(Request $request): array
    {
        return array_filter([
            'actor_id' => $request->query('actor_id'),
            'module' => $request->query('module'),
            'entity_type' => $request->query('entity_type'),
            'action' => $request->query('action'),
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function context(Request $request): array
    {
        return [
            'actor_id' => $request->user()['id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
