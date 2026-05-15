<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Resources\AuthResource;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Users\Resources\UserResource;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly LoginRequest $loginRequest,
        private readonly RegisterRequest $registerRequest
    ) {
    }

    public function login(Request $request): void
    {
        $data = $this->loginRequest->validate($request);
        $result = $this->auth->login($data, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success(
            AuthResource::token($result['user'], $result['roles'], $result['permissions'], $result['token'], $result['ttl']),
            'Login successful.'
        );
    }

    public function registerJobSeeker(Request $request): void
    {
        $result = $this->auth->register($this->registerRequest->validate($request), 'job_seeker', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success(
            UserResource::make($result['user'], $result['roles'], $result['permissions']),
            'Job seeker registered successfully.',
            [],
            201
        );
    }

    public function registerRecruiter(Request $request): void
    {
        $result = $this->auth->register($this->registerRequest->validate($request), 'recruiter', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success(
            UserResource::make($result['user'], $result['roles'], $result['permissions']),
            'Recruiter registered successfully.',
            [],
            201
        );
    }

    public function me(Request $request): void
    {
        $user = $request->user();

        $this->success(
            UserResource::make($user, $user['roles'] ?? [], $user['permissions'] ?? []),
            'Authenticated user retrieved successfully.'
        );
    }

    public function logout(Request $request): void
    {
        $this->auth->logout($request->user() ?? []);

        $this->success([], 'Logout successful.');
    }
}
