<?php

declare(strict_types=1);

namespace App\Modules\HR\Services;

use App\Modules\Audit\Services\AuditLogService;
use App\Modules\HR\Repositories\HrOfficerProfileRepository;

class HrOfficerProfileService
{
    public function __construct(
        private readonly HrOfficerProfileRepository $profiles,
        private readonly AuditLogService $audit
    ) {
    }

    public function mine(int $userId): ?array
    {
        return $this->profiles->findByUserId($userId);
    }

    public function update(int $userId, array $data, array $context): array
    {
        $old = $this->profiles->findByUserId($userId);
        $profile = $this->profiles->upsert($userId, $data);

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? $userId,
            'action' => 'hr_officers.profile_updated',
            'module' => 'hr_officers',
            'entity_type' => 'hr_officer_profile',
            'entity_id' => (int) $profile['id'],
            'old_values' => $old,
            'new_values' => $profile,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return $profile;
    }
}
