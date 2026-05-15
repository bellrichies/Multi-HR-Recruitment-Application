<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Resources;

class PermissionResource
{
    public static function make(array $permission): array
    {
        return [
            'id' => (int) $permission['id'],
            'name' => $permission['name'],
            'slug' => $permission['slug'],
            'module' => $permission['module'],
            'description' => $permission['description'],
            'created_at' => $permission['created_at'],
            'updated_at' => $permission['updated_at'],
        ];
    }
}
