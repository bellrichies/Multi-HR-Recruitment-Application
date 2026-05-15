<?php

declare(strict_types=1);

namespace App\Modules\Roles\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Roles\Requests\CreateRoleRequest;
use App\Modules\Roles\Resources\RoleResource;
use App\Modules\Roles\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roles,
        private readonly CreateRoleRequest $createRoleRequest
    ) {
    }

    public function index(Request $request): void
    {
        $this->success(
            array_map(fn (array $role): array => RoleResource::make($role), $this->roles->all()),
            'Roles retrieved successfully.'
        );
    }

    public function store(Request $request): void
    {
        $role = $this->roles->create($this->createRoleRequest->validate($request), [
            'actor_id' => $request->user()['id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success(RoleResource::make($role), 'Role created successfully.', [], 201);
    }
}
