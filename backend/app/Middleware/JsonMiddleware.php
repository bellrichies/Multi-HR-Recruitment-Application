<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;

class JsonMiddleware
{
    public function handle(Request $request, ?string $parameter = null): void
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $contentType = $request->header('Content-Type', '');

        if (is_string($contentType) && str_contains(strtolower($contentType), 'application/json')) {
            return;
        }

        throw new HttpException('Expected application/json request body.', 415);
    }
}
