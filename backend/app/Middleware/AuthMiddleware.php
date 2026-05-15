<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;

class AuthMiddleware
{
    public function handle(Request $request, ?string $parameter = null): void
    {
        if ($request->bearerToken() === null) {
            throw new HttpException('Authentication token is required.', 401);
        }

        // JWT validation is implemented in Phase 2 with the Auth module.
    }
}
