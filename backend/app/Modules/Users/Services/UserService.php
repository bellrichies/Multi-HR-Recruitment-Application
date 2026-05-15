<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Users\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
        private readonly AuditLogService $audit
    ) {
    }

    public function assignRoles(int $userId, array $roleIds, array $context = []): array
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        foreach ($roleIds as $roleId) {
            if ($this->roles->findById((int) $roleId) === null) {
                throw new HttpException('One or more roles were not found.', 422, [
                    'role_ids' => ['One or more roles were not found.'],
                ]);
            }
        }

        $oldRoles = $this->users->roles($userId);
        $this->users->assignRoles($userId, $roleIds);
        $newRoles = $this->users->roles($userId);

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? null,
            'action' => 'users.roles_assigned',
            'module' => 'users',
            'entity_type' => 'user',
            'entity_id' => $userId,
            'old_values' => ['roles' => array_column($oldRoles, 'slug')],
            'new_values' => ['roles' => array_column($newRoles, 'slug')],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return [
            'user' => $this->users->findById($userId),
            'roles' => $newRoles,
            'permissions' => $this->users->permissions($userId),
        ];
    }
}
