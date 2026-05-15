<?php

declare(strict_types=1);

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Repositories\AuditLogRepository;

class AuditLogService
{
    public function __construct(private readonly AuditLogRepository $auditLogs)
    {
    }

    public function record(array $data): void
    {
        try {
            $this->auditLogs->create($data);
        } catch (\Throwable) {
            // Audit failures must be visible in logs but should not break auth/RBAC workflows.
            error_log('Audit log write failed for action: ' . ($data['action'] ?? 'unknown'));
        }
    }
}
