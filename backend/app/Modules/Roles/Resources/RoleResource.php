<?php

declare(strict_types=1);

namespace App\Modules\Roles\Resources;

class RoleResource
{
    public static function make(array $role): array
    {
        return [
            'id' => (int) $role['id'],
            'name' => $role['name'],
            'slug' => $role['slug'],
            'description' => $role['description'],
            'is_system' => (bool) $role['is_system'],
            'created_at' => $role['created_at'],
            'updated_at' => $role['updated_at'],
        ];
    }
}
