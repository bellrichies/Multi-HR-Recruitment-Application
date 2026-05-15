<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\HR\Controllers\HrOfficerProfileController;
use App\Modules\JobSeekers\Controllers\JobSeekerProfileController;
use App\Modules\Permissions\Controllers\PermissionController;
use App\Modules\Recruiters\Controllers\RecruiterProfileController;
use App\Modules\RelationshipOfficers\Controllers\RelationshipOfficerProfileController;
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

$router->get('/api/v1/recruiters/profile', [RecruiterProfileController::class, 'mine'])
    ->middleware(['security_headers', 'auth', 'permission:recruiters.update']);
$router->put('/api/v1/recruiters/profile', [RecruiterProfileController::class, 'update'])
    ->middleware(['security_headers', 'auth', 'permission:recruiters.update', 'json']);
$router->post('/api/v1/recruiters/documents', [RecruiterProfileController::class, 'uploadDocument'])
    ->middleware(['security_headers', 'auth', 'permission:recruiters.update']);
$router->get('/api/v1/recruiters/{id}', [RecruiterProfileController::class, 'show'])
    ->middleware(['security_headers', 'auth', 'permission:recruiters.view']);
$router->post('/api/v1/recruiters/{id}/verify', [RecruiterProfileController::class, 'verify'])
    ->middleware(['security_headers', 'auth', 'permission:recruiters.verify']);
$router->post('/api/v1/recruiters/{id}/reject', [RecruiterProfileController::class, 'reject'])
    ->middleware(['security_headers', 'auth', 'permission:recruiters.verify', 'json']);

$router->get('/api/v1/job-seekers/profile', [JobSeekerProfileController::class, 'mine'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.update']);
$router->put('/api/v1/job-seekers/profile', [JobSeekerProfileController::class, 'update'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.update', 'json']);
$router->post('/api/v1/job-seekers/skills', [JobSeekerProfileController::class, 'addSkill'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.update', 'json']);
$router->post('/api/v1/job-seekers/work-experiences', [JobSeekerProfileController::class, 'addWorkExperience'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.update', 'json']);
$router->post('/api/v1/job-seekers/educations', [JobSeekerProfileController::class, 'addEducation'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.update', 'json']);
$router->post('/api/v1/job-seekers/certifications', [JobSeekerProfileController::class, 'addCertification'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.update']);
$router->post('/api/v1/job-seekers/documents', [JobSeekerProfileController::class, 'uploadDocument'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.update']);
$router->post('/api/v1/job-seekers/guarantors', [JobSeekerProfileController::class, 'addGuarantor'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.update']);
$router->post('/api/v1/job-seekers/documents/{id}/review', [JobSeekerProfileController::class, 'reviewDocument'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.verify', 'json']);
$router->get('/api/v1/job-seekers/{id}', [JobSeekerProfileController::class, 'show'])
    ->middleware(['security_headers', 'auth', 'permission:job_seekers.view']);

$router->get('/api/v1/hr-officers/profile', [HrOfficerProfileController::class, 'mine'])
    ->middleware(['security_headers', 'auth', 'permission:hr_officers.update']);
$router->put('/api/v1/hr-officers/profile', [HrOfficerProfileController::class, 'update'])
    ->middleware(['security_headers', 'auth', 'permission:hr_officers.update', 'json']);
$router->get('/api/v1/relationship-officers/profile', [RelationshipOfficerProfileController::class, 'mine'])
    ->middleware(['security_headers', 'auth', 'permission:relationship_officers.update']);
$router->put('/api/v1/relationship-officers/profile', [RelationshipOfficerProfileController::class, 'update'])
    ->middleware(['security_headers', 'auth', 'permission:relationship_officers.update', 'json']);
