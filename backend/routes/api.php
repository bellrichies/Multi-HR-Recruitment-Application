<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

/** @var Router $router */

$router->get('/api/v1/health', function (Request $request): void {
    Response::success([
        'service' => config('app.name'),
        'environment' => config('app.env'),
        'timestamp' => date(DATE_ATOM),
    ], 'API is healthy.');
})->middleware(['security_headers']);
