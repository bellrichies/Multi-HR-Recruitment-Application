<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Auth\Repositories\TokenRepository;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Users\Repositories\UserRepository;
use App\Modules\Wallet\Services\WalletService;
use App\Support\Auth\JwtService;
use App\Support\Auth\Password;

class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
        private readonly TokenRepository $tokens,
        private readonly JwtService $jwt,
        private readonly AuditLogService $audit,
        private readonly WalletService $wallets
    ) {
    }

    public function login(array $credentials, array $context = []): array
    {
        $user = $this->users->findByEmail($credentials['email']);

        if ($user === null || ! Password::verify($credentials['password'], $user['password_hash'])) {
            $this->audit->record([
                'actor_id' => $user['id'] ?? null,
                'action' => 'auth.login_failed',
                'module' => 'auth',
                'entity_type' => 'user',
                'entity_id' => $user['id'] ?? null,
                'new_values' => ['email' => strtolower($credentials['email'])],
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
            ]);
            throw new HttpException('Invalid login credentials.', 401);
        }

        if ($user['status'] !== 'active') {
            $this->audit->record([
                'actor_id' => (int) $user['id'],
                'action' => 'auth.login_blocked',
                'module' => 'auth',
                'entity_type' => 'user',
                'entity_id' => (int) $user['id'],
                'new_values' => ['status' => $user['status']],
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
            ]);
            throw new HttpException('Account is not active.', 403);
        }

        $roles = $this->users->roles((int) $user['id']);
        $permissions = $this->users->permissions((int) $user['id']);
        $this->users->markLastLogin((int) $user['id']);
        $user = $this->users->findById((int) $user['id']) ?? $user;
        $ttl = (int) config('auth.jwt_ttl', 3600);
        $token = $this->jwt->generate([
            'sub' => (int) $user['id'],
            'email' => $user['email'],
            'roles' => array_column($roles, 'slug'),
            'permissions' => $permissions,
        ], $ttl);

        $this->audit->record([
            'actor_id' => (int) $user['id'],
            'action' => 'auth.login_success',
            'module' => 'auth',
            'entity_type' => 'user',
            'entity_id' => (int) $user['id'],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return compact('user', 'roles', 'permissions', 'token', 'ttl');
    }

    public function register(array $data, string $roleSlug, array $context = []): array
    {
        if ($this->users->findByEmail($data['email']) !== null) {
            throw new HttpException('Email address is already registered.', 409, [
                'email' => ['Email address is already registered.'],
            ]);
        }

        $role = $this->roles->findBySlug($roleSlug);

        if ($role === null) {
            throw new HttpException('Registration role is not configured.', 500);
        }

        return Database::transaction(function () use ($data, $role): array {
            $user = $this->users->create([
                'uuid' => $this->uuid(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password_hash' => Password::hash($data['password']),
                'status' => 'active',
            ]);

            $this->users->assignRoles((int) $user['id'], [(int) $role['id']]);
            $this->wallets->getOrCreate((int) $user['id']);

            $this->audit->record([
                'actor_id' => (int) $user['id'],
                'action' => 'auth.register',
                'module' => 'auth',
                'entity_type' => 'user',
                'entity_id' => (int) $user['id'],
                'new_values' => ['role' => $role['slug'], 'email' => $user['email']],
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
            ]);

            return [
                'user' => $user,
                'roles' => [$role],
                'permissions' => $this->users->permissions((int) $user['id']),
            ];
        });
    }

    public function userFromToken(string $token): array
    {
        $payload = $this->jwt->parse($token);

        if (! empty($payload['jti']) && $this->tokens->isBlacklisted((string) $payload['jti'])) {
            throw new HttpException('Token has been revoked.', 401);
        }

        $user = $this->users->findById((int) ($payload['sub'] ?? 0));

        if ($user === null || $user['status'] !== 'active') {
            throw new HttpException('Authenticated user is not available.', 401);
        }

        $user['roles'] = $this->users->roles((int) $user['id']);
        $user['permissions'] = $this->users->permissions((int) $user['id']);
        $user['token_payload'] = $payload;

        return $user;
    }

    public function logout(array $user): void
    {
        $payload = $user['token_payload'] ?? [];

        if (! isset($payload['jti'], $payload['exp'])) {
            return;
        }

        $this->tokens->blacklist(
            (string) $payload['jti'],
            (int) $user['id'],
            date('Y-m-d H:i:s', (int) $payload['exp'])
        );
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
