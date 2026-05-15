<?php

declare(strict_types=1);

namespace App\Modules\Users\Resources;

class UserResource
{
    public static function make(array $user, array $roles = [], array $permissions = []): array
    {
        return [
            'id' => (int) $user['id'],
            'uuid' => $user['uuid'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'status' => $user['status'],
            'email_verified_at' => $user['email_verified_at'],
            'phone_verified_at' => $user['phone_verified_at'],
            'last_login_at' => $user['last_login_at'],
            'roles' => array_map(fn (array $role): array => [
                'id' => (int) $role['id'],
                'name' => $role['name'],
                'slug' => $role['slug'],
            ], $roles),
            'permissions' => array_values($permissions),
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at'],
        ];
    }
}
