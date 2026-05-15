<?php

declare(strict_types=1);

namespace App\Modules\Permissions\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Permissions\Resources\PermissionResource;
use App\Modules\Permissions\Services\PermissionService;

class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    public function index(Request $request): void
    {
        $this->success(
            array_map(fn (array $permission): array => PermissionResource::make($permission), $this->permissions->all()),
            'Permissions retrieved successfully.'
        );
    }
}
