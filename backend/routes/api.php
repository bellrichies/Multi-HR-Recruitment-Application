<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Modules\Applications\Controllers\ApplicationController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\HR\Controllers\HrOfficerProfileController;
use App\Modules\Jobs\Controllers\JobController;
use App\Modules\JobSeekers\Controllers\JobSeekerProfileController;
use App\Modules\Matching\Controllers\CandidateController;
use App\Modules\Matching\Controllers\CandidateUnlockController;
use App\Modules\Payments\Controllers\PaymentController;
use App\Modules\Permissions\Controllers\PermissionController;
use App\Modules\Recruiters\Controllers\RecruiterProfileController;
use App\Modules\RelationshipOfficers\Controllers\RelationshipOfficerProfileController;
use App\Modules\Roles\Controllers\RoleController;
use App\Modules\Users\Controllers\UserRoleController;
use App\Modules\Wallet\Controllers\WalletController;

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

$router->get('/api/v1/public/jobs', [JobController::class, 'publicIndex'])
    ->middleware(['security_headers']);
$router->get('/api/v1/public/jobs/{slug}', [JobController::class, 'publicShow'])
    ->middleware(['security_headers']);
$router->get('/api/v1/jobs', [JobController::class, 'index'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.view']);
$router->post('/api/v1/jobs', [JobController::class, 'store'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.create', 'json']);
$router->post('/api/v1/jobs/{id}/apply', [ApplicationController::class, 'apply'])
    ->middleware(['security_headers', 'auth', 'permission:applications.create', 'json']);
$router->get('/api/v1/jobs/{id}/candidate-matches', [CandidateController::class, 'jobMatches'])
    ->middleware(['security_headers', 'auth', 'permission:candidates.match']);
$router->post('/api/v1/jobs/{id}/match-candidates', [CandidateController::class, 'matchCandidates'])
    ->middleware(['security_headers', 'auth', 'permission:candidates.match', 'json']);
$router->get('/api/v1/jobs/{id}', [JobController::class, 'show'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.view']);
$router->put('/api/v1/jobs/{id}', [JobController::class, 'update'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.update', 'json']);
$router->post('/api/v1/jobs/{id}/submit-for-approval', [JobController::class, 'submitForApproval'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.update']);
$router->post('/api/v1/jobs/{id}/approve', [JobController::class, 'approve'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.approve']);
$router->post('/api/v1/jobs/{id}/publish', [JobController::class, 'publish'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.publish']);
$router->post('/api/v1/jobs/{id}/pause', [JobController::class, 'pause'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.update']);
$router->post('/api/v1/jobs/{id}/close', [JobController::class, 'close'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.close']);
$router->post('/api/v1/jobs/{id}/cancel', [JobController::class, 'cancel'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.close']);
$router->post('/api/v1/jobs/{id}/assign-hr-officer', [JobController::class, 'assignHrOfficer'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.assign', 'json']);
$router->post('/api/v1/jobs/{id}/assign-relationship-officer', [JobController::class, 'assignRelationshipOfficer'])
    ->middleware(['security_headers', 'auth', 'permission:jobs.assign', 'json']);

$router->get('/api/v1/applications', [ApplicationController::class, 'index'])
    ->middleware(['security_headers', 'auth', 'permission:applications.view']);
$router->get('/api/v1/applications/{id}', [ApplicationController::class, 'show'])
    ->middleware(['security_headers', 'auth', 'permission:applications.view']);
$router->post('/api/v1/applications/{id}/move-stage', [ApplicationController::class, 'moveStage'])
    ->middleware(['security_headers', 'auth', 'permission:applications.move_stage', 'json']);
$router->post('/api/v1/applications/{id}/shortlist', [ApplicationController::class, 'shortlist'])
    ->middleware(['security_headers', 'auth', 'permission:applications.shortlist']);
$router->post('/api/v1/applications/{id}/reject', [ApplicationController::class, 'reject'])
    ->middleware(['security_headers', 'auth', 'permission:applications.reject']);
$router->post('/api/v1/applications/{id}/withdraw', [ApplicationController::class, 'withdraw'])
    ->middleware(['security_headers', 'auth']);

$router->get('/api/v1/candidates/discover', [CandidateController::class, 'discover'])
    ->middleware(['security_headers', 'auth', 'permission:candidates.discover']);
$router->get('/api/v1/candidates/{id}/summary', [CandidateController::class, 'summary'])
    ->middleware(['security_headers', 'auth', 'permission:candidates.discover']);
$router->get('/api/v1/candidates/{id}/full-profile', [CandidateController::class, 'fullProfile'])
    ->middleware(['security_headers', 'auth', 'permission:candidates.view_full_profile']);
$router->post('/api/v1/candidates/{id}/unlock', [CandidateUnlockController::class, 'store'])
    ->middleware(['security_headers', 'auth', 'permission:candidates.unlock', 'json']);

$router->get('/api/v1/wallet', [WalletController::class, 'show'])
    ->middleware(['security_headers', 'auth', 'permission:wallet.view']);
$router->post('/api/v1/wallet/fund', [WalletController::class, 'fund'])
    ->middleware(['security_headers', 'auth', 'permission:wallet.fund', 'json']);
$router->get('/api/v1/wallet/transactions', [WalletController::class, 'transactions'])
    ->middleware(['security_headers', 'auth', 'permission:transactions.view']);

$router->post('/api/v1/payments/paystack/callback', [PaymentController::class, 'callback'])
    ->middleware(['security_headers', 'json']);
$router->post('/api/v1/payments/paystack/webhook', [PaymentController::class, 'webhook'])
    ->middleware(['security_headers']);
$router->get('/api/v1/payments', [PaymentController::class, 'index'])
    ->middleware(['security_headers', 'auth', 'permission:payments.view']);
$router->get('/api/v1/payments/{id}', [PaymentController::class, 'show'])
    ->middleware(['security_headers', 'auth', 'permission:payments.view']);
