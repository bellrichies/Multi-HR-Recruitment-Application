<?php

declare(strict_types=1);

namespace App\Modules\Users\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Users\Requests\CreateUserRequest;
use App\Modules\Users\Requests\UpdateUserRequest;
use App\Modules\Users\Resources\UserResource;
use App\Modules\Users\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $users,
        private readonly CreateUserRequest $createUserRequest,
        private readonly UpdateUserRequest $updateUserRequest
    ) {
    }

    public function index(Request $request): void
    {
        $result = $this->users->list($request->query());
        $rolesByUser = $result['roles'] ?? [];
        $data = array_map(
            fn (array $user): array => UserResource::make($user, $rolesByUser[(int) $user['id']] ?? []),
            $result['data']
        );

        $this->success($data, 'Users retrieved successfully.', $result['meta']);
    }

    public function store(Request $request): void
    {
        $result = $this->users->create($this->createUserRequest->validate($request), $this->context($request));

        $this->success(
            UserResource::make($result['user'], $result['roles'], $result['permissions']),
            'User created successfully.',
            [],
            201
        );
    }

    public function show(Request $request, string $id): void
    {
        $result = $this->users->show((int) $id);

        $this->success(
            UserResource::make($result['user'], $result['roles'], $result['permissions']),
            'User retrieved successfully.'
        );
    }

    public function update(Request $request, string $id): void
    {
        $result = $this->users->update((int) $id, $this->updateUserRequest->validate($request), $this->context($request));

        $this->success(
            UserResource::make($result['user'], $result['roles'], $result['permissions']),
            'User updated successfully.'
        );
    }

    public function suspend(Request $request, string $id): void
    {
        $this->statusResponse($this->users->suspend((int) $id, $this->context($request)), 'User suspended successfully.');
    }

    public function activate(Request $request, string $id): void
    {
        $this->statusResponse($this->users->activate((int) $id, $this->context($request)), 'User activated successfully.');
    }

    public function deactivate(Request $request, string $id): void
    {
        $this->statusResponse($this->users->deactivate((int) $id, $this->context($request)), 'User deactivated successfully.');
    }

    private function statusResponse(array $result, string $message): void
    {
        $this->success(UserResource::make($result['user'], $result['roles'], $result['permissions']), $message);
    }

    private function context(Request $request): array
    {
        $user = $request->user() ?? [];

        return [
            'actor_id' => $user['id'] ?? null,
            'actor_roles' => $user['roles'] ?? [],
            'actor_permissions' => $user['permissions'] ?? [],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
