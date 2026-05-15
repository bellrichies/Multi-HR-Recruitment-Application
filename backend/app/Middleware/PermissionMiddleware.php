<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;

class PermissionMiddleware
{
    public function handle(Request $request, ?string $parameter = null): void
    {
        if ($parameter === null || $parameter === '') {
            throw new HttpException('Permission middleware requires a permission name.', 500);
        }

        $user = $request->user();

        if ($user === null) {
            throw new HttpException('Authentication is required before permission checks.', 401);
        }

        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true)) {
            return;
        }

        if (! in_array($parameter, $user['permissions'] ?? [], true)) {
            throw new HttpException('You do not have permission to perform this action.', 403);
        }
    }
}
