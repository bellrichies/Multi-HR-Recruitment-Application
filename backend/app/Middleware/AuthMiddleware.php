<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;
use App\Modules\Auth\Services\AuthService;

class AuthMiddleware
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function handle(Request $request, ?string $parameter = null): void
    {
        $token = $request->bearerToken();

        if ($token === null) {
            throw new HttpException('Authentication token is required.', 401);
        }

        $request->setAttribute('user', $this->auth->userFromToken($token));
    }
}
