<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;

class CsrfMiddleware
{
    public function handle(Request $request, ?string $parameter = null): void
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $sessionToken = $_SESSION['_csrf_token'] ?? null;
        $requestToken = $request->header('X-CSRF-TOKEN') ?? $request->input('_token');

        if ($sessionToken === null || ! hash_equals((string) $sessionToken, (string) $requestToken)) {
            throw new HttpException('Invalid CSRF token.', 419);
        }
    }
}
