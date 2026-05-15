<?php

declare(strict_types=1);

namespace App\Modules\Roles\Services;

use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Roles\Repositories\RoleRepository;

class RoleService
{
    public function __construct(
        private readonly RoleRepository $roles,
        private readonly AuditLogService $audit
    )
    {
    }

    public function all(): array
    {
        return $this->roles->all();
    }

    public function create(array $data, array $context = []): array
    {
        $slug = strtolower(trim((string) $data['slug']));
        $slug = preg_replace('/[^a-z0-9_]+/', '_', $slug) ?: '';

        if ($slug === '') {
            throw new HttpException('Role slug is invalid.', 422, ['slug' => ['Role slug is invalid.']]);
        }

        if ($this->roles->findBySlug($slug) !== null) {
            throw new HttpException('Role slug already exists.', 409, ['slug' => ['Role slug already exists.']]);
        }

        $role = $this->roles->create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'is_system' => false,
        ]);

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? null,
            'action' => 'roles.create',
            'module' => 'roles',
            'entity_type' => 'role',
            'entity_id' => (int) $role['id'],
            'new_values' => $role,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return $role;
    }
}
