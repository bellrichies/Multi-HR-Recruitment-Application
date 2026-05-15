<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, ?string $parameter = null): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer-when-downgrade');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
}
