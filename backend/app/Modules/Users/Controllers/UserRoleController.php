<?php

declare(strict_types=1);

namespace App\Modules\Users\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Users\Requests\AssignUserRolesRequest;
use App\Modules\Users\Resources\UserResource;
use App\Modules\Users\Services\UserService;

class UserRoleController extends Controller
{
    public function __construct(
        private readonly UserService $users,
        private readonly AssignUserRolesRequest $assignUserRolesRequest
    ) {
    }

    public function store(Request $request, string $id): void
    {
        $data = $this->assignUserRolesRequest->validate($request);
        $result = $this->users->assignRoles((int) $id, $data['role_ids'], [
            'actor_id' => $request->user()['id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success(
            UserResource::make($result['user'], $result['roles'], $result['permissions']),
            'User roles assigned successfully.'
        );
    }
}
