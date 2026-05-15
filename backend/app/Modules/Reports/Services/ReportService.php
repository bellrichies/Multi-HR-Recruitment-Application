<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Reports\Repositories\ReportRepository;

class ReportService
{
    public function __construct(
        private readonly ReportRepository $reports,
        private readonly AuditLogService $audit
    ) {
    }

    public function superAdminDashboard(array $user): array
    {
        $this->requireSuperAdmin($user);

        return $this->reports->superAdminSummary();
    }

    public function hrOfficerDashboard(array $user): array
    {
        $this->requireRole($user, 'hr_officer');

        return $this->reports->hrSummary((int) $user['id']);
    }

    public function relationshipOfficerDashboard(array $user): array
    {
        $this->requireRole($user, 'relationship_officer');

        return $this->reports->relationshipOfficerSummary((int) $user['id']);
    }

    public function recruiterDashboard(array $user): array
    {
        $this->requireRole($user, 'recruiter');

        return $this->reports->recruiterSummary((int) $user['id']);
    }

    public function jobSeekerDashboard(array $user): array
    {
        $this->requireRole($user, 'job_seeker');

        return $this->reports->jobSeekerSummary((int) $user['id']);
    }

    public function financial(array $user): array
    {
        $this->requireSuperAdmin($user);

        return $this->reports->financialReport();
    }

    public function placements(array $user): array
    {
        $this->requireSuperAdmin($user);

        return $this->reports->placementReport();
    }

    public function audit(array $user, array $filters, int $page, int $perPage): array
    {
        $this->requireSuperAdmin($user);

        return $this->reports->auditLogs($filters, $page, $perPage);
    }

    public function export(string $type, array $user, array $context): array
    {
        $this->requireSuperAdmin($user);

        if (! in_array($type, ['financial', 'placements', 'audit', 'activity'], true)) {
            throw new HttpException('Unsupported report export type.', 404);
        }

        $rows = $this->reports->exportRows($type);
        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? null,
            'action' => 'reports.export',
            'module' => 'reports',
            'entity_type' => 'report',
            'entity_id' => null,
            'new_values' => ['type' => $type, 'row_count' => count($rows)],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return [
            'type' => $type,
            'generated_at' => date(DATE_ATOM),
            'rows' => $rows,
        ];
    }

    private function requireSuperAdmin(array $user): void
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (! in_array('super_admin', $roles, true)) {
            throw new HttpException('You are not allowed to access platform-wide reports.', 403);
        }
    }

    private function requireRole(array $user, string $role): void
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true) || in_array($role, $roles, true)) {
            return;
        }

        throw new HttpException('You are not allowed to access this dashboard.', 403);
    }
}
