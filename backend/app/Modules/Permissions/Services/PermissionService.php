<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Services;

use App\Modules\Permissions\Repositories\PermissionRepository;

class PermissionService
{
    public function __construct(private readonly PermissionRepository $permissions)
    {
    }

    public function all(): array
    {
        return $this->permissions->all();
    }
}
