<?php

declare(strict_types=1);

namespace App\Modules\Jobs\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Jobs\Repositories\JobAssignmentRepository;
use App\Modules\Jobs\Repositories\JobRepository;
use App\Modules\Jobs\Repositories\JobSkillRepository;
use App\Modules\Jobs\Repositories\JobStatusLogRepository;
use App\Modules\Recruiters\Repositories\RecruiterProfileRepository;
use App\Modules\Users\Repositories\UserRepository;

class JobService
{
    private const TRANSITIONS = [
        'draft' => ['pending_approval', 'published', 'open', 'cancelled'],
        'pending_approval' => ['published', 'cancelled'],
        'published' => ['open', 'paused', 'closed', 'cancelled'],
        'open' => ['assigned', 'paused', 'closed', 'filled', 'cancelled'],
        'assigned' => ['open', 'paused', 'closed', 'filled', 'cancelled'],
        'paused' => ['open', 'closed', 'cancelled'],
        'closed' => [],
        'cancelled' => [],
        'filled' => [],
    ];

    public function __construct(
        private readonly JobRepository $jobs,
        private readonly JobSkillRepository $skills,
        private readonly JobAssignmentRepository $assignments,
        private readonly JobStatusLogRepository $statusLogs,
        private readonly RecruiterProfileRepository $recruiters,
        private readonly UserRepository $users,
        private readonly AuditLogService $audit
    ) {
    }

    public function list(array $user, array $filters): array
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('recruiter', $roles, true) && ! in_array('super_admin', $roles, true)) {
            $profile = $this->recruiters->findByUserId((int) $user['id']);
            $filters['recruiter_id'] = $profile['id'] ?? 0;
        } elseif (in_array('hr_officer', $roles, true) && ! in_array('super_admin', $roles, true)) {
            $filters['assigned_hr_officer_id'] = (int) $user['id'];
        } elseif (in_array('relationship_officer', $roles, true) && ! in_array('super_admin', $roles, true)) {
            $filters['assigned_relationship_officer_id'] = (int) $user['id'];
        }

        return $this->jobs->list($filters, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 20));
    }

    public function publicList(array $filters): array
    {
        $filters['public'] = true;

        return $this->jobs->list($filters, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 20));
    }

    public function show(int $id, array $user): array
    {
        $job = $this->requireJob($id);
        $this->authorizeView($job, $user);

        return $this->withRelations($job);
    }

    public function publicShow(string $slug): array
    {
        $job = $this->jobs->findBySlug($slug);

        if ($job === null || ! in_array($job['status'], config('jobs.public_statuses', ['published', 'open', 'assigned']), true)) {
            throw new HttpException('Job not found.', 404);
        }

        return $this->withRelations($job);
    }

    public function create(array $data, array $user, array $context): array
    {
        $recruiterId = $this->resolveRecruiterId($data, $user);
        $slug = $this->uniqueSlug((string) $data['title']);

        return Database::transaction(function () use ($data, $user, $context, $recruiterId, $slug): array {
            $selfAssignment = $this->selfAssignmentForCreator($user);
            $job = $this->jobs->create(array_merge($data, [
                'uuid' => uuid_create_local(),
                'recruiter_id' => $recruiterId,
                'created_by' => (int) $user['id'],
                'slug' => $slug,
                'assigned_hr_officer_id' => $selfAssignment === 'hr_officer' ? (int) $user['id'] : null,
                'assigned_relationship_officer_id' => $selfAssignment === 'relationship_officer' ? (int) $user['id'] : null,
            ]));
            $this->skills->sync((int) $job['id'], $data['skills'] ?? []);

            if ($selfAssignment !== null) {
                $this->assignments->create((int) $job['id'], (int) $user['id'], (int) $user['id'], $selfAssignment);
            }

            $this->auditJob($context, 'jobs.create', null, $job);

            return $this->withRelations($job);
        });
    }

    public function update(int $id, array $data, array $user, array $context): array
    {
        $old = $this->requireJob($id);
        $this->authorizeManage($old, $user);

        if (in_array($old['status'], config('jobs.terminal_statuses', ['closed', 'cancelled', 'filled']), true)) {
            throw new HttpException('Closed, cancelled, or filled jobs cannot be updated.', 422);
        }

        return Database::transaction(function () use ($id, $data, $context, $old): array {
            $job = $this->jobs->update($id, $data);
            $this->skills->sync($id, $data['skills'] ?? []);
            $this->auditJob($context, 'jobs.update', $old, $job);

            return $this->withRelations($job);
        });
    }

    public function submitForApproval(int $id, array $user, array $context): array
    {
        if (! (bool) config('jobs.approval_required', true)) {
            return $this->changeStatus($id, 'open', $user, $context, 'jobs.submit_without_approval');
        }

        return $this->changeStatus($id, 'pending_approval', $user, $context, 'jobs.submit_for_approval');
    }

    public function approve(int $id, array $user, array $context): array
    {
        return $this->changeStatus($id, 'published', $user, $context, 'jobs.approve');
    }

    public function publish(int $id, array $user, array $context): array
    {
        $job = $this->requireJob($id);

        if ((bool) config('jobs.approval_required', true) && $job['status'] === 'draft') {
            throw new HttpException('Job must be submitted and approved before publishing.', 422, [
                'status' => ['Job approval is required before publishing.'],
            ]);
        }

        return $this->changeStatus($id, 'open', $user, $context, 'jobs.publish');
    }

    public function pause(int $id, array $user, array $context): array
    {
        return $this->changeStatus($id, 'paused', $user, $context, 'jobs.pause');
    }

    public function close(int $id, array $user, array $context): array
    {
        return $this->changeStatus($id, 'closed', $user, $context, 'jobs.close');
    }

    public function cancel(int $id, array $user, array $context): array
    {
        return $this->changeStatus($id, 'cancelled', $user, $context, 'jobs.cancel');
    }

    public function assign(int $id, int $assignedTo, string $type, array $user, array $context): array
    {
        $old = $this->requireJob($id);
        $this->authorizeManage($old, $user);

        if ($this->users->findById($assignedTo) === null) {
            throw new HttpException('Assigned user not found.', 404);
        }

        $assignedRoles = array_column($this->users->roles($assignedTo), 'slug');

        if (! in_array($type, $assignedRoles, true)) {
            throw new HttpException('Assigned user does not have the required role.', 422, [
                'user_id' => ['Assigned user does not have the required role.'],
            ]);
        }

        return Database::transaction(function () use ($id, $assignedTo, $type, $user, $context, $old): array {
            $assignment = $this->assignments->create($id, $assignedTo, (int) $user['id'], $type);
            $job = $this->jobs->assign($id, $type, $assignedTo);
            $this->statusLogs->create($id, $old['status'], $job['status'], (int) $user['id'], 'jobs.assign_' . $type);
            $this->auditJob($context, 'jobs.assign_' . $type, $old, $job, ['assignment_id' => (int) $assignment['id']]);

            return $this->withRelations($job);
        });
    }

    private function changeStatus(int $id, string $status, array $user, array $context, string $action): array
    {
        $old = $this->requireJob($id);
        $this->authorizeManage($old, $user);
        $this->ensurePublishable($old, $status);

        if (! in_array($status, self::TRANSITIONS[$old['status']] ?? [], true)) {
            throw new HttpException('Invalid job status transition.', 422, [
                'status' => ["Cannot move job from {$old['status']} to {$status}."],
            ]);
        }

        $job = $this->jobs->updateStatus($id, $status);
        $this->statusLogs->create($id, $old['status'], $status, (int) $user['id'], $action);
        $this->auditJob($context, $action, $old, $job);

        return $this->withRelations($job);
    }

    private function resolveRecruiterId(array $data, array $user): int
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('recruiter', $roles, true) && ! in_array('super_admin', $roles, true)) {
            $profile = $this->recruiters->findByUserId((int) $user['id']);

            if ($profile === null || ! $this->recruiterProfileComplete($profile)) {
                throw new HttpException('Complete recruiter company profile before posting jobs.', 422);
            }

            return (int) $profile['id'];
        }

        if (empty($data['recruiter_id'])) {
            throw new HttpException('Recruiter profile id is required for staff-created jobs.', 422, [
                'recruiter_id' => ['Recruiter profile id is required.'],
            ]);
        }

        if ($this->recruiters->findById((int) $data['recruiter_id']) === null) {
            throw new HttpException('Recruiter profile not found.', 404);
        }

        return (int) $data['recruiter_id'];
    }

    private function recruiterProfileComplete(array $profile): bool
    {
        foreach (['company_name', 'company_email', 'company_phone', 'industry', 'company_size', 'address'] as $field) {
            if (empty($profile[$field])) {
                return false;
            }
        }

        return true;
    }

    private function selfAssignmentForCreator(array $user): ?string
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true) || in_array('recruiter', $roles, true)) {
            return null;
        }

        if (in_array('hr_officer', $roles, true)) {
            return 'hr_officer';
        }

        if (in_array('relationship_officer', $roles, true)) {
            return 'relationship_officer';
        }

        return null;
    }

    private function ensurePublishable(array $job, string $targetStatus): void
    {
        if (! in_array($targetStatus, ['published', 'open'], true)) {
            return;
        }

        foreach (['title', 'description', 'location', 'employment_type', 'work_mode'] as $field) {
            if (empty($job[$field])) {
                throw new HttpException('Required fields must be completed before publishing.', 422);
            }
        }
    }

    private function authorizeView(array $job, array $user): void
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true)) {
            return;
        }

        $this->authorizeManage($job, $user);
    }

    private function authorizeManage(array $job, array $user): void
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true)) {
            return;
        }

        if (in_array('recruiter', $roles, true)) {
            $profile = $this->recruiters->findByUserId((int) $user['id']);

            if ($profile !== null && (int) $profile['id'] === (int) $job['recruiter_id']) {
                return;
            }
        }

        if (in_array('hr_officer', $roles, true) && (int) ($job['assigned_hr_officer_id'] ?? 0) === (int) $user['id']) {
            return;
        }

        if (in_array('relationship_officer', $roles, true) && (int) ($job['assigned_relationship_officer_id'] ?? 0) === (int) $user['id']) {
            return;
        }

        throw new HttpException('You are not allowed to manage this job.', 403);
    }

    private function requireJob(int $id): array
    {
        $job = $this->jobs->findById($id);

        if ($job === null) {
            throw new HttpException('Job not found.', 404);
        }

        return $job;
    }

    private function withRelations(array $job): array
    {
        return [
            'job' => $job,
            'skills' => $this->skills->forJob((int) $job['id']),
            'assignments' => $this->assignments->forJob((int) $job['id']),
            'status_logs' => $this->statusLogs->forJob((int) $job['id']),
        ];
    }

    private function auditJob(array $context, string $action, ?array $old, array $new, array $extra = []): void
    {
        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? null,
            'action' => $action,
            'module' => 'jobs',
            'entity_type' => 'job',
            'entity_id' => (int) $new['id'],
            'old_values' => $old,
            'new_values' => array_merge($new, $extra),
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = slug_create($title);
        $slug = $base;
        $counter = 2;

        while ($this->jobs->slugExists($slug)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
