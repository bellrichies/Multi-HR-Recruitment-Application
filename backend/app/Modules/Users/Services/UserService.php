<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Users\Repositories\UserRepository;
use App\Modules\Wallet\Services\WalletService;
use App\Support\Auth\Password;

class UserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
        private readonly AuditLogService $audit,
        private readonly WalletService $wallets
    ) {
    }

    public function list(array $filters): array
    {
        return $this->users->list($filters, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 20));
    }

    public function show(int $userId): array
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        return [
            'user' => $user,
            'roles' => $this->users->roles($userId),
            'permissions' => $this->users->permissions($userId),
        ];
    }

    public function create(array $data, array $context = []): array
    {
        $roleIds = array_values(array_unique(array_map('intval', $data['role_ids'] ?? [])));
        $roles = $this->validatedRoles($roleIds);

        if ($roleIds !== []) {
            $this->assertCanAssignRoles($context);
        }

        $this->assertEmailIsAvailable((string) $data['email']);
        $phone = (($data['phone'] ?? '') === '') ? null : $data['phone'];
        $this->assertPhoneIsAvailable($phone);

        return Database::transaction(function () use ($data, $phone, $roleIds, $roles, $context): array {
            $user = $this->users->create([
                'uuid' => $this->uuid(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $phone,
                'password_hash' => Password::hash($data['password']),
                'status' => ($data['status'] ?? '') !== '' ? $data['status'] : 'active',
            ]);

            if ($roleIds !== []) {
                $this->users->assignRoles((int) $user['id'], $roleIds);
            }

            if ($this->requiresWallet($roles)) {
                $this->wallets->getOrCreate((int) $user['id']);
            }

            $this->audit->record([
                'actor_id' => $context['actor_id'] ?? null,
                'action' => 'users.create',
                'module' => 'users',
                'entity_type' => 'user',
                'entity_id' => (int) $user['id'],
                'new_values' => [
                    'email' => $user['email'],
                    'status' => $user['status'],
                    'roles' => array_column($roles, 'slug'),
                ],
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
            ]);

            $userId = (int) $user['id'];

            return [
                'user' => $this->users->findById($userId),
                'roles' => $this->users->roles($userId),
                'permissions' => $this->users->permissions($userId),
            ];
        });
    }

    public function update(int $userId, array $data, array $context = []): array
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        $this->assertEmailIsAvailable((string) $data['email'], $userId);
        $phone = (($data['phone'] ?? '') === '') ? null : $data['phone'];
        $this->assertPhoneIsAvailable($phone, $userId);

        $payload = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $phone,
        ];

        if (($data['password'] ?? '') !== '') {
            $payload['password_hash'] = Password::hash((string) $data['password']);
        }

        $updated = $this->users->update($userId, $payload);

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? null,
            'action' => 'users.update',
            'module' => 'users',
            'entity_type' => 'user',
            'entity_id' => $userId,
            'old_values' => [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
            ],
            'new_values' => [
                'first_name' => $updated['first_name'] ?? null,
                'last_name' => $updated['last_name'] ?? null,
                'email' => $updated['email'] ?? null,
                'phone' => $updated['phone'] ?? null,
                'password_changed' => isset($payload['password_hash']),
            ],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return $this->show($userId);
    }

    public function assignRoles(int $userId, array $roleIds, array $context = []): array
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
        $newRoleModels = $this->validatedRoles($roleIds);

        $oldRoles = $this->users->roles($userId);
        $oldRoleSlugs = array_column($oldRoles, 'slug');
        $newRoleSlugs = array_column($newRoleModels, 'slug');
        sort($oldRoleSlugs);
        sort($newRoleSlugs);

        if ((int) ($context['actor_id'] ?? 0) === $userId && $oldRoleSlugs !== $newRoleSlugs) {
            throw new HttpException('You cannot change your own roles.', 422, [
                'role_ids' => ['You cannot change your own roles.'],
            ]);
        }

        if (
            $user['status'] === 'active'
            && in_array('super_admin', $oldRoleSlugs, true)
            && ! in_array('super_admin', $newRoleSlugs, true)
            && $this->users->activeUsersWithRole('super_admin') <= 1
        ) {
            throw new HttpException('At least one active Super Admin must remain.', 422, [
                'role_ids' => ['At least one active Super Admin must remain.'],
            ]);
        }

        $this->users->assignRoles($userId, $roleIds);
        $newRoles = $this->users->roles($userId);

        if ($this->requiresWallet($newRoles)) {
            $this->wallets->getOrCreate($userId);
        }

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

    public function suspend(int $userId, array $context = []): array
    {
        return $this->setStatus($userId, 'suspended', $context, 'users.suspend');
    }

    public function activate(int $userId, array $context = []): array
    {
        return $this->setStatus($userId, 'active', $context, 'users.activate');
    }

    public function deactivate(int $userId, array $context = []): array
    {
        return $this->setStatus($userId, 'deactivated', $context, 'users.deactivate');
    }

    private function setStatus(int $userId, string $status, array $context, string $action): array
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw new HttpException('User not found.', 404);
        }

        if ((int) ($context['actor_id'] ?? 0) === $userId && $status !== 'active') {
            throw new HttpException('You cannot disable your own account.', 422);
        }

        $roles = $this->users->roles($userId);

        if (
            $user['status'] === 'active'
            && $status !== 'active'
            && in_array('super_admin', array_column($roles, 'slug'), true)
            && $this->users->activeUsersWithRole('super_admin') <= 1
        ) {
            throw new HttpException('At least one active Super Admin must remain.', 422);
        }

        $updated = $this->users->updateStatus($userId, $status);

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? null,
            'action' => $action,
            'module' => 'users',
            'entity_type' => 'user',
            'entity_id' => $userId,
            'old_values' => ['status' => $user['status']],
            'new_values' => ['status' => $status],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return [
            'user' => $updated,
            'roles' => $roles,
            'permissions' => $this->users->permissions($userId),
        ];
    }

    private function validatedRoles(array $roleIds): array
    {
        $roles = [];

        foreach ($roleIds as $roleId) {
            $role = $this->roles->findById((int) $roleId);

            if ($role === null) {
                throw new HttpException('One or more roles were not found.', 422, [
                    'role_ids' => ['One or more roles were not found.'],
                ]);
            }

            $roles[] = $role;
        }

        return $roles;
    }

    private function assertEmailIsAvailable(string $email, ?int $ignoreUserId = null): void
    {
        $existing = $this->users->findByEmail($email);

        if ($existing !== null && (int) $existing['id'] !== $ignoreUserId) {
            throw new HttpException('Email address is already registered.', 409, [
                'email' => ['Email address is already registered.'],
            ]);
        }
    }

    private function assertPhoneIsAvailable(mixed $phone, ?int $ignoreUserId = null): void
    {
        if ($phone === null || $phone === '') {
            return;
        }

        $existing = $this->users->findByPhone((string) $phone);

        if ($existing !== null && (int) $existing['id'] !== $ignoreUserId) {
            throw new HttpException('Phone number is already registered.', 409, [
                'phone' => ['Phone number is already registered.'],
            ]);
        }
    }

    private function assertCanAssignRoles(array $context): void
    {
        $roles = array_column($context['actor_roles'] ?? [], 'slug');
        $permissions = $context['actor_permissions'] ?? [];

        if (in_array('super_admin', $roles, true) || in_array('roles.assign', $permissions, true)) {
            return;
        }

        throw new HttpException('You do not have permission to assign roles.', 403);
    }

    private function requiresWallet(array $roles): bool
    {
        $slugs = array_column($roles, 'slug');

        return $slugs !== [] && array_diff($slugs, ['super_admin']) !== [];
    }

    private function uuid(): string
    {
        if (function_exists('uuid_create_local')) {
            return uuid_create_local();
        }

        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
