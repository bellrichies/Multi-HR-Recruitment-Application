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

        // Permission lookup is implemented in Phase 2 with RBAC tables and services.
    }
}
