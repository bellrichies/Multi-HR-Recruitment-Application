<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

/** @var Router $router */

$router->get('/', function (Request $request): void {
    Response::success([
        'name' => config('app.name'),
        'api' => '/api/v1',
    ], 'Multi-HR Platform backend is running.');
})->middleware(['security_headers']);
