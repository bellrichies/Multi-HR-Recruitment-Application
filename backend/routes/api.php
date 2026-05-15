<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Permissions\Controllers\PermissionController;
use App\Modules\Roles\Controllers\RoleController;
use App\Modules\Users\Controllers\UserRoleController;

/** @var Router $router */

$router->get('/api/v1/health', function (Request $request): void {
    Response::success([
        'service' => config('app.name'),
        'environment' => config('app.env'),
        'timestamp' => date(DATE_ATOM),
    ], 'API is healthy.');
})->middleware(['security_headers']);

$router->post('/api/v1/auth/register/job-seeker', [AuthController::class, 'registerJobSeeker'])
    ->middleware(['security_headers', 'rate_limit:20,60', 'json']);
$router->post('/api/v1/auth/register/recruiter', [AuthController::class, 'registerRecruiter'])
    ->middleware(['security_headers', 'rate_limit:20,60', 'json']);
$router->post('/api/v1/auth/login', [AuthController::class, 'login'])
    ->middleware(['security_headers', 'rate_limit:10,60', 'json']);
$router->post('/api/v1/auth/logout', [AuthController::class, 'logout'])
    ->middleware(['security_headers', 'auth']);
$router->get('/api/v1/auth/me', [AuthController::class, 'me'])
    ->middleware(['security_headers', 'auth']);

$router->get('/api/v1/roles', [RoleController::class, 'index'])
    ->middleware(['security_headers', 'auth', 'permission:roles.view']);
$router->post('/api/v1/roles', [RoleController::class, 'store'])
    ->middleware(['security_headers', 'auth', 'permission:roles.create', 'json']);
$router->get('/api/v1/permissions', [PermissionController::class, 'index'])
    ->middleware(['security_headers', 'auth', 'permission:permissions.view']);
$router->post('/api/v1/users/{id}/roles', [UserRoleController::class, 'store'])
    ->middleware(['security_headers', 'auth', 'permission:roles.assign', 'json']);
